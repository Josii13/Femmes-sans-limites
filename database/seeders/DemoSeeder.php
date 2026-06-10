<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Member;
use App\Services\MemberCardService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cardService = app(MemberCardService::class);

        // ── 3 membres de démonstration ──────────────────────────────────────
        $members = [
            [
                'name' => 'Aïcha Koné',
                'email' => 'aicha.kone@demo.com',
                'profession' => 'Entrepreneur & Coach Business',
                'country' => "Côte d'Ivoire",
                'city' => 'Abidjan',
                'type' => 'standard',
                'status' => 'active',
            ],
            [
                'name' => 'Fatou Diallo',
                'email' => 'fatou.diallo@demo.com',
                'profession' => 'Directrice Marketing',
                'country' => 'Sénégal',
                'city' => 'Dakar',
                'type' => 'gold',
                'status' => 'active',
            ],
            [
                'name' => 'Mariame Ouédraogo',
                'email' => 'mariame.ouedraogo@demo.com',
                'profession' => 'Médecin & Conférencière',
                'country' => 'Burkina Faso',
                'city' => 'Ouagadougou',
                'type' => 'premium',
                'status' => 'active',
            ],
        ];

        foreach ($members as $data) {
            $member = Member::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'member_number' => Member::generateNumber($data['type']),
                    'joined_at' => now(),
                    'expires_at' => now()->addYear(),
                ])
            );

            if (! $member->card_path) {
                $cardPath = $cardService->generate($member);
                $member->update(['card_path' => $cardPath]);
            }
        }

        // ── 2 événements de démonstration ───────────────────────────────────
        Event::updateOrCreate(
            ['slug' => 'forum-femmes-leaderes-2025'],
            [
                'title' => 'Forum des Femmes Leadères 2025',
                'slug' => 'forum-femmes-leaderes-2025',
                'description' => "Une journée immersive pour découvrir, partager et s'inspirer au contact des femmes qui font bouger les lignes en Afrique et dans la diaspora.\n\nAu programme :\n- Panels avec des femmes leaders de différents secteurs\n- Ateliers pratiques : leadership, finances personnelles, personal branding\n- Networking et échanges libres\n- Remise de distinctions FSL\n\nCet événement est ouvert à toutes les femmes qui souhaitent grandir, se connecter et s'élever. Venez avec votre énergie et votre ambition !",
                'short_description' => 'Une journée immersive pour se connecter, s\'inspirer et se dépasser au contact des femmes leaders d\'Afrique.',
                'event_date' => now()->addDays(30)->setTime(9, 0),
                'location' => 'Sofitel Hôtel Ivoire',
                'city' => 'Abidjan',
                'capacity' => 200,
                'is_paid' => false,
                'price' => null,
                'currency' => 'XOF',
                'payment_link' => null,
                'status' => 'published',
            ]
        );

        Event::updateOrCreate(
            ['slug' => 'masterclass-entrepreneuriat-feminin'],
            [
                'title' => 'Masterclass : Entrepreneuriat Féminin',
                'slug' => 'masterclass-entrepreneuriat-feminin',
                'description' => "Une masterclass intensive animée par des entrepreneures aguerries pour vous donner les clés du succès en affaires.\n\nContenu :\n- Comment structurer et financer son projet\n- Stratégies de croissance et scale-up\n- Erreurs à éviter quand on lance son business\n- Session Q&A avec les intervenantes\n- Remise d'un certificat de participation FSL\n\nNombre de places limité à 50 participantes pour garantir une expérience qualitative et des échanges personnalisés.",
                'short_description' => 'Masterclass intensive pour entrepreneures : structurer, financer et scaler son projet avec les meilleures.',
                'event_date' => now()->addDays(55)->setTime(14, 0),
                'location' => 'King Fahd Palace',
                'city' => 'Dakar',
                'capacity' => 50,
                'is_paid' => true,
                'price' => 25000,
                'currency' => 'XOF',
                'payment_link' => 'https://pay.example.com/fsl-masterclass',
                'status' => 'published',
            ]
        );

        $this->command->info('✅ 3 membres + 2 événements créés avec succès.');
    }
}
