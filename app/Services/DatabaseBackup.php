<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Sauvegarde de la base en PHP pur.
 *
 * Volontairement sans `mysqldump` : l'outil est absent de beaucoup d'hébergements
 * mutualisés et son appel par `exec()` y est souvent désactivé. On lit les tables
 * via PDO et on écrit un fichier SQL rejouable tel quel dans phpMyAdmin.
 *
 * MySQL et SQLite sont gérés : la production tourne sur MySQL, mais savoir lire
 * SQLite permet de vérifier le dump dans la suite de tests plutôt que de le
 * croire sur parole.
 *
 * Les lignes sont parcourues par paquets afin qu'une table volumineuse ne charge
 * jamais l'intégralité des données en mémoire.
 */
class DatabaseBackup
{
    /** Nombre de lignes lues par requête. */
    private const CHUNK = 500;

    /** Nombre de lignes regroupées par instruction INSERT, pour un fichier compact. */
    private const ROWS_PER_INSERT = 50;

    /**
     * Écrit le dump dans le fichier indiqué et retourne le nombre de lignes sauvegardées.
     *
     * @param  array<int, string>  $skipTables  tables dont seule la structure est conservée
     */
    public function dumpTo(string $path, array $skipTables = []): int
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Impossible d'écrire la sauvegarde dans {$path}");
        }

        try {
            $this->writeHeader($handle);

            $rows = 0;
            foreach ($this->tables() as $table) {
                $this->writeStructure($handle, $table);

                if (! in_array($table, $skipTables, true)) {
                    $rows += $this->writeRows($handle, $table);
                }
            }

            $this->writeFooter($handle);

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /** @return array<int, string> */
    public function tables(): array
    {
        if ($this->driver() === 'sqlite') {
            return array_map(
                fn (object $row) => $row->name,
                DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
            );
        }

        $database = str_replace('`', '', DB::connection()->getDatabaseName());

        return array_map(
            fn (object $row) => array_values((array) $row)[0],
            DB::select('SHOW TABLES FROM `'.$database.'`')
        );
    }

    private function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /** @param  resource  $handle */
    private function writeHeader($handle): void
    {
        $lines = [
            '-- Sauvegarde Femme Sans Limites',
            '-- Base   : '.DB::connection()->getDatabaseName(),
            '-- Moteur : '.$this->driver(),
            '-- Date   : '.now()->toDateTimeString().' (UTC)',
            '',
        ];

        if ($this->driver() !== 'sqlite') {
            $lines[] = 'SET NAMES utf8mb4;';
            $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
            $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
            $lines[] = '';
        }

        fwrite($handle, implode("\n", $lines)."\n");
    }

    /** @param  resource  $handle */
    private function writeFooter($handle): void
    {
        if ($this->driver() !== 'sqlite') {
            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
        }
    }

    /** @param  resource  $handle */
    private function writeStructure($handle, string $table): void
    {
        $sql = $this->createStatement($table);

        if ($sql === null) {
            return;
        }

        fwrite($handle, "\n-- Structure de `{$table}`\n");
        fwrite($handle, 'DROP TABLE IF EXISTS '.$this->wrap($table).";\n");
        fwrite($handle, $sql.";\n");
    }

    private function createStatement(string $table): ?string
    {
        if ($this->driver() === 'sqlite') {
            $row = DB::selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?', ['table', $table]);

            return $row->sql ?? null;
        }

        $row = DB::select('SHOW CREATE TABLE `'.$table.'`');

        return array_values((array) ($row[0] ?? []))[1] ?? null;
    }

    /** @param  resource  $handle */
    private function writeRows($handle, string $table): int
    {
        $first = DB::table($table)->first();
        if ($first === null) {
            return 0;
        }

        $total = (int) DB::table($table)->count();
        fwrite($handle, "\n-- Données de `{$table}` ({$total} lignes)\n");

        $columns = array_map(fn (string $column) => $this->wrap($column), array_keys((array) $first));
        $prefix = 'INSERT INTO '.$this->wrap($table).' ('.implode(', ', $columns).') VALUES';

        $buffer = [];
        $written = 0;

        // Parcours par paquets : une table volumineuse ne doit jamais tenir en mémoire.
        // `orderByRaw('1')` fournit un ordre stable sans supposer l'existence d'un `id`.
        DB::table($table)->orderByRaw('1')->chunk(self::CHUNK, function ($rows) use ($handle, $prefix, &$buffer, &$written) {
            foreach ($rows as $row) {
                $buffer[] = '('.implode(', ', array_map([$this, 'quote'], (array) $row)).')';
                $written++;

                if (count($buffer) >= self::ROWS_PER_INSERT) {
                    fwrite($handle, $prefix."\n".implode(",\n", $buffer).";\n");
                    $buffer = [];
                }
            }
        });

        if ($buffer) {
            fwrite($handle, $prefix."\n".implode(",\n", $buffer).";\n");
        }

        return $written;
    }

    private function wrap(string $identifier): string
    {
        return $this->driver() === 'sqlite'
            ? '"'.str_replace('"', '""', $identifier).'"'
            : '`'.str_replace('`', '', $identifier).'`';
    }

    /** Échappement via PDO : gère les guillemets, les octets nuls et l'UTF-8. */
    private function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::getPdo()->quote((string) $value);
    }
}
