# Femme Sans Limites

Plateforme de gestion de la communauté **Femme Sans Limites** (FSL) : adhésions, cartes de membre, événements, e-books, newsletter et campagnes email — avec un back-office d'administration.

**Stack :** Laravel 13 · PHP 8.3+ · MySQL (ou SQLite) · Vite + Tailwind · Alpine.js

---

## Prérequis

- PHP ≥ 8.3 avec l'extension **GD** (génération des cartes de membre)
- Composer 2
- Node.js 20+ / npm
- MySQL 8 (ou SQLite pour le développement)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configurer la base et le mail dans .env, puis :
php artisan migrate
php artisan db:seed            # données de démo (optionnel)

php artisan storage:link       # expose storage/app/public (photos, cartes, QR)
npm install && npm run build
```

### Compte administrateur

L'inscription publique est **désactivée** (back-office privé). Les comptes admin se créent ainsi :

```bash
# via la commande dédiée (mot de passe généré si omis)
php artisan fsl:make-admin admin@fsl.com --name="Admin FSL"

# ou via le seeder, en définissant ADMIN_INITIAL_PASSWORD dans .env
php artisan db:seed --class=AdminUserSeeder
```

> ⚠️ Après une montée de version qui ajoute la colonne `is_admin`, pensez à promouvoir
> votre compte existant : `php artisan tinker --execute="App\Models\User::query()->update(['is_admin'=>true]);"`

## Développement

```bash
composer dev   # serve + queue:listen + pail (logs) + vite, en parallèle
```

## Envoi des emails

Par défaut, `QUEUE_CONNECTION=sync` : les emails (cartes, campagnes, newsletters,
confirmations) partent **immédiatement pendant la requête**. Aucun worker requis —
idéal en hébergement mutualisé.

**Pour de gros envois (campagnes de plusieurs centaines de membres)**, on peut basculer
en envoi asynchrone afin de ne pas bloquer la requête : mettre `QUEUE_CONNECTION=database`
puis faire tourner un worker (ou un cron qui vide la file chaque minute) :

```bash
# worker permanent (VPS) :
php artisan queue:work --queue=emails,default

# ou cron (mutualisé), chaque minute :
* * * * * cd /chemin/projet && php artisan queue:work --stop-when-empty --max-time=50 --queue=emails,default >> /dev/null 2>&1
```

## Tâches planifiées (cron)

Deux tâches sont planifiées (`routes/console.php`) :

- `campaigns:dispatch` — envoi des campagnes programmées (chaque minute)
- `members:expire` — expiration et relance des adhésions échues (quotidien)

Activez l'ordonnanceur Laravel côté serveur :

```cron
* * * * * cd /chemin/projet && php artisan schedule:run >> /dev/null 2>&1
```

## Qualité

```bash
vendor/bin/pint        # style de code (PSR-12 / preset Laravel)
php artisan test       # suite de tests
```

## Points de configuration importants (production)

| Variable | Valeur recommandée |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL HTTPS réelle (utilisée par les QR de vérification) |
| `SESSION_SECURE_COOKIE` | `true` |
| `QUEUE_CONNECTION` | `sync` (envoi immédiat) — ou `database` + worker pour les gros envois |
| `MAIL_*` | SMTP de l'expéditeur officiel |

## Architecture (aperçu)

- `app/Http/Controllers/Admin` — back-office (membres, événements, e-books, communication, scanner)
- `app/Services/MemberCardService` — génération des cartes (GD) avec QR de vérification
- `app/Services/CampaignDispatcher` + `app/Jobs/SendCampaignEmail` — envoi des campagnes en file
- `app/Console/Commands` — `campaigns:dispatch`, `members:expire`, `fsl:make-admin`
- Pages publiques : accueil, événements, e-books, adhésion, vérification de carte (`/membre/{token}`)
