<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAdminUser extends Command
{
    protected $signature = 'fsl:make-admin
                            {email : Adresse email de l\'administrateur}
                            {--name= : Nom affiché (par défaut : "Admin FSL")}
                            {--password= : Mot de passe (généré aléatoirement si omis)}';

    protected $description = 'Crée (ou promeut) un compte administrateur du back-office FSL';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $name = $this->option('name') ?: 'Admin FSL';
        $password = $this->option('password');
        $generated = false;

        if (blank($password)) {
            $password = Str::password(20);
            $generated = true;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_admin' => true],
        );

        $this->info("Administrateur « {$user->email} » prêt.");

        if ($generated) {
            $this->warn("Mot de passe temporaire : {$password}");
            $this->warn('Note-le maintenant — il ne sera plus affiché.');
        }

        return self::SUCCESS;
    }
}
