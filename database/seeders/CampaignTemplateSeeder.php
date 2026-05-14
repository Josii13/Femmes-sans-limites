<?php

namespace Database\Seeders;

use App\Models\CampaignTemplate;
use Illuminate\Database\Seeder;

class CampaignTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'    => 'Newsletter mensuelle',
                'subject' => '🌸 Actualités FSL — [Mois]',
                'type'    => 'text',
                'body'    => "Bonjour,\n\nNous sommes ravies de vous retrouver pour notre newsletter mensuelle !\n\n[Contenu principal du mois ici — événements à venir, nouveautés, inspirations...]\n\nMerci de faire partie de cette belle aventure avec nous.\n\nAvec toute notre affection,\nL'équipe Femmes Sans Limites",
                'cta_label' => null,
                'cta_url'   => null,
            ],
            [
                'name'    => 'Invitation à un événement',
                'subject' => '📅 Vous êtes invitée : [Nom de l\'événement]',
                'type'    => 'text_cta',
                'body'    => "Chère membre,\n\nNous avons le grand plaisir de vous inviter à notre prochain événement :\n\n[Nom de l'événement]\n📆 [Date et heure]\n📍 [Lieu ou lien visio]\n\n[Description de l'événement — ce qui vous attend, les intervenantes, les thèmes abordés...]\n\nLes places sont limitées, ne tardez pas à vous inscrire !\n\nÀ très bientôt,\nL'équipe FSL",
                'cta_label' => "Je m'inscris",
                'cta_url'   => '',
            ],
            [
                'name'    => 'Nouveau contenu disponible',
                'subject' => '📚 Nouveau sur FSL : [Titre du contenu]',
                'type'    => 'text_image_cta',
                'body'    => "Bonjour,\n\nUn nouveau contenu vient d'être ajouté sur notre plateforme, spécialement pour vous !\n\n[Titre du contenu]\n\n[Description — de quoi parle-t-il, à qui s'adresse-t-il, ce que vous allez apprendre ou découvrir...]\n\nCe contenu a été conçu pour vous aider à [bénéfice principal]. Ne passez pas à côté !\n\nBonne lecture,\nL'équipe Femmes Sans Limites",
                'cta_label' => 'Découvrir maintenant',
                'cta_url'   => '',
            ],
            [
                'name'    => 'Message de bienvenue membre',
                'subject' => '🎉 Bienvenue dans la communauté Femmes Sans Limites !',
                'type'    => 'text',
                'body'    => "Chère [Prénom],\n\nNous sommes infiniment heureuses de vous accueillir au sein de Femmes Sans Limites.\n\nVotre adhésion est confirmée. Vous faites désormais partie d'une communauté de femmes inspirantes, déterminées et solidaires, réparties aux quatre coins du monde.\n\nVoici ce qui vous attend :\n• Accès prioritaire aux événements membres\n• Ressources exclusives (ebooks, guides pratiques)\n• Un réseau de femmes bienveillantes et engagées\n• Des opportunités de networking et de mentoring\n\nSi vous avez la moindre question, notre équipe est là pour vous.\n\nAvec fierté et affection,\nL'équipe Femmes Sans Limites",
                'cta_label' => null,
                'cta_url'   => null,
            ],
            [
                'name'    => 'Message de motivation',
                'subject' => '💪 Un message rien que pour toi',
                'type'    => 'text_cta',
                'body'    => "Chère membre,\n\nAujourd'hui, nous voulions juste te rappeler quelque chose d'essentiel :\n\nTu es capable. Tu es forte. Tu es exactement là où tu dois être.\n\n[Message personnalisé ou citation inspirante ici]\n\nChaque pas que tu fais, même le plus petit, te rapproche de la femme que tu deviens. Nous sommes fières de marcher à tes côtés dans cette aventure.\n\nContinue d'avancer ✨\n\nAvec tout notre amour,\nL'équipe FSL",
                'cta_label' => 'Rejoindre notre prochain événement',
                'cta_url'   => '',
            ],
        ];

        foreach ($templates as $data) {
            CampaignTemplate::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
