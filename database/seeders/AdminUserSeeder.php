<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Le mot de passe initial est lu depuis l'environnement (ADMIN_INITIAL_PASSWORD).
     * À défaut, un mot de passe aléatoire est généré et affiché une seule fois :
     * aucun identifiant n'est codé en dur dans le dépôt.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@fsl.com');
        $password = env('ADMIN_INITIAL_PASSWORD');
        $generated = false;

        if (blank($password)) {
            $password = Str::password(20);
            $generated = true;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin FSL'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );

        // S'assurer qu'un compte existant est bien promu administrateur.
        if (! $user->is_admin) {
            $user->update(['is_admin' => true]);
        }

        if ($generated) {
            $this->command?->warn("Compte admin « {$email} » créé. Mot de passe temporaire : {$password}");
            $this->command?->warn('Note-le maintenant et change-le après la première connexion.');
        }
    }
}
