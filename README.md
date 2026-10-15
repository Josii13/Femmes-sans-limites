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

Les emails de confirmation (QR d'accès, ebooks, cartes de membre) partent avec
`->queue()`. Deux modes possibles :

- `QUEUE_CONNECTION=sync` — envoi immédiat pendant la requête, aucun worker requis.
- `QUEUE_CONNECTION=database` — mise en file, vidée **par l'ordonnanceur** : la tâche
  `queue:work --stop-when-empty` est déclarée dans `routes/console.php`, donc le cron
  `schedule:run` ci-dessous suffit. Aucune tâche cron supplémentaire à créer.

> Historique : la file était en base **sans worker**. Les confirmations restaient
> dans la table `jobs`, le back-office affichait « Confirmée » et l'inscrite ne
> recevait jamais son QR. D'où la tâche planifiée, et la page de billet en ligne
> qui ne dépend plus de la bonne réception d'un email.

En cas de doute sur un envoi, en production :

```bash
php artisan fsl:diagnose-emails
```

Lecture seule. Affiche le transport, l'expéditeur, la connexion de file, le nombre
d'emails en attente et depuis combien de temps, les échecs, et les inscriptions
confirmées sans QR.

## Tâches planifiées (cron)

Déclarées dans `routes/console.php` :

- `queue:work --stop-when-empty` — vide la file des emails (chaque minute, si la file n'est pas `sync`)
- `campaigns:dispatch` — envoi des campagnes programmées (chaque minute)
- `registrations:expire-unpaid` — libère les places impayées (toutes les 10 min)
- `payments:reconcile` — rattrape les webhooks manqués (toutes les 10 min)
- `members:expire` — expiration et relance des adhésions échues (quotidien)
- `fsl:backup` — sauvegarde de la base et des fichiers (quotidien, 14 jours conservés)

Un seul cron à activer côté serveur, il déclenche tout le reste :

```cron
* * * * * cd /chemin/projet && php artisan schedule:run >> /dev/null 2>&1
```

## Déploiement

Toujours ces quatre commandes, dans cet ordre :

```bash
cd /chemin/projet
git pull origin main
php artisan migrate --force
php artisan optimize:clear     # vide config, routes, vues, événements
php artisan optimize           # les régénère toutes
```

> **`optimize` plutôt qu'une liste de `:cache`.** Les caches de configuration, de
> routes et de vues doivent être régénérés **ensemble**. En vider une partie
> seulement produit une incohérence silencieuse : une vue recompilée qui appelle
> `route('nouvelle.route')` alors que la table de routes en cache date d'avant
> fait tomber **tout le site** en `RouteNotFoundException`. `optimize` ne peut pas
> être fait à moitié ; une liste recopiée à la main, si.

Après un déploiement qui ajoute une dépendance : `composer install --no-dev --optimize-autoloader`.

Les assets (`public/build`) sont **versionnés** : ils se compilent en local avec
`npm run build` puis se committent. Ne pas lancer `npm` sur le serveur.

Vérification rapide après mise en ligne :

```bash
php artisan route:list | grep -c .   # la table de routes se reconstruit
php artisan fsl:diagnose-sales       # chaîne de vente (lecture seule)
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
