<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

/**
 * Tarifs de départ. Les montants sont indicatifs : ils se règlent en back-office,
 * le rôle du seeder est seulement que les trois niveaux existent.
 */
class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'type' => 'standard',
                'name' => 'Standard',
                'description' => 'L’essentiel pour rejoindre la communauté et participer à sa vie.',
                // Gratuit par défaut : l'adhésion de base reste ouverte à toutes.
                'price' => null,
                'duration_months' => 12,
                'sort_order' => 1,
            ],
            [
                'type' => 'gold',
                'name' => 'Gold',
                'description' => 'Accès prioritaire aux inscriptions et réduction sur les événements payants.',
                'price' => 15000,
                'duration_months' => 12,
                'sort_order' => 2,
            ],
            [
                'type' => 'premium',
                'name' => 'Premium',
                'description' => 'Accompagnement complet : accès VIP, mentorat et ressources exclusives.',
                'price' => 35000,
                'duration_months' => 12,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            // updateOrCreate sur le type : rejouer le seeder ne doit pas écraser
            // un tarif ajusté par l'association… sauf à le vouloir explicitement.
            MembershipPlan::firstOrCreate(['type' => $plan['type']], $plan + ['currency' => 'XOF', 'is_active' => true]);
        }
    }
}
