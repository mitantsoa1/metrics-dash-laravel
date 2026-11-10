# MetricsDash Laravel SDK ⚡

[![Latest Version on Packagist](https://img.shields.io/packagist/v/metrics-dash/laravel.svg?style=flat-square)](https://packagist.org/packages/metrics-dash/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/metrics-dash/laravel.svg?style=flat-square)](https://packagist.org/packages/metrics-dash/laravel)
[![License](https://img.shields.io/packagist/l/metrics-dash/laravel.svg?style=flat-square)](LICENSE)

**MetricsDash SDK** est l'extension officielle Laravel pour intégrer n'importe quelle application Laravel (SaaS, E-commerce, Marketplace) au moteur d'analyse et de tableau de bord **MetricsDash Enterprise**.

Il inclut une **interface d'administration embarquée de mappage dynamique BDD** accessible directement à l'adresse `/metrics-dash` sans nécessiter aucune migration ni modification de votre schéma de base de données existant.

---

## 💡 Caractéristiques Principales

- 🔌 **Intégration Sans Migration** : Fonctionne sur vos tables BDD existantes (`payments`, `orders`, `packs`, `plans`, `users`, etc.).
- 🎨 **Interface d'Admin Embarquée (`/metrics-dash`)** : Mappage visuel des tables et colonnes réelles en 3 clics.
- 👤 **Identification des Acheteurs** : Jointure automatique avec la table des utilisateurs pour faire remonter le **Nom** et l'**Email** de l'acheteur dans `/dashboard/analytics`.
- 📦 **Ventilation par Offres & Packages** : Analyse en temps réel du Chiffre d'Affaires (CA), MRR et Panier Moyen (AOV).
- ⚡ **Synchronisation Automatique** : Commande Artisan `php artisan metrics-dash:sync` et déclenchement programmable pour vos tâches planifiées.

---

## 🛠️ Installation

Installez le package via Composer dans votre projet Laravel client :

```bash
composer require mitantsoa1/metrics-dash-laravel
```

### 1. Publier la configuration (Optionnel)

```bash
php artisan vendor:publish --tag=metrics-dash-config
```

Cela créera le fichier de configuration `config/metrics-dash.php`.

---

## ⚙️ Configuration Environment (`.env`)

Ajoutez vos clés d'API dans votre fichier `.env` :

```env
METRICSDASH_API_URL=https://votre-instance-metricsdash.com
METRICSDASH_PUSH_KEY=votre_cle_secret_push
METRICSDASH_SDK_KEY=votre_cle_sdk_client
```

---

## 🖥️ Utilisation de l'Interface de Mappage (`/metrics-dash`)

1. Accédez à l'URL d'administration de votre application backend :

   ```
   http://votre-domaine-backend/metrics-dash
   ```

2. Sélectionnez vos 3 tables BDD réelles :
   - **Table 1 : Paiements & Transactions** (ex: `payments`, `amount`, `currency`, `status=paid, succeeded`, `user_id`, `pack_id`).
   - **Table 2 : Packages & Abonnements** (ex: `packs`, `id`, `name`, `price`).
   - **Table 3 : Utilisateurs & Clients** (ex: `users`, `id`, `name`, `email`).

3. Cliquez sur **"Sauvegarder & Prévisualiser"** puis **"Synchroniser les Ventes Vers MetricsDash"**.

---

## ⚡ Synchronisation Planifiée (Cron / Scheduler)

Vous pouvez exécuter la synchronisation des données financières en tâche de fond via la commande Artisan :

```bash
php artisan metrics-dash:sync
```

Ou l'ajouter dans `app/Console/Kernel.php` ou `routes/console.php` :

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('metrics-dash:sync')->hourly();
```

---

## 🧭 Guide d'installation pas à pas (site à tracker)

### 1. Prérequis

- Laravel 11, 12 ou 13, PHP 8.2 ou plus.
- Une instance créée dans MetricsDash pour ce site. Demandez à un OWNER ou ADMIN la **clé privée** `sk_live_…` (affichée une seule fois à la création) et, si le frontend est aussi suivi, la clé publique `pk_live_…`.
- L'URL publique du backend MetricsDash.
- Des tables existantes pour les paiements, les offres et les utilisateurs.

### 2. Installer

```bash
composer require mitantsoa1/metrics-dash-laravel
php artisan vendor:publish --tag=metrics-dash-config
```

Si le package n'est pas sur Packagist, ajoutez dans le `composer.json` du site :

```json
"repositories": [{ "type": "path", "url": "../chemin/vers/packages/metrics-dash-laravel" }]
```

### 3. Configurer le `.env`

```env
METRICSDASH_API_URL=https://api.votre-metricsdash.com
METRICSDASH_PUSH_KEY=sk_live_votre_cle_privee
METRICSDASH_SDK_KEY=pk_live_votre_cle_publique
```

Ne commitez jamais ces valeurs. Après modification : `php artisan config:clear`.

### 4. Protéger l'interface `/metrics-dash`

Les routes du package ne contiennent pas d'authentification. Avant la mise en production, limitez l'accès : réseau privé, liste d'IP, ou middleware d'authentification de votre site appliqué à ces routes. Ne l'exposez pas publiquement.

### 5. Mapper les tables

1. Ouvrez `https://votre-site/metrics-dash`.
2. Choisissez la table des paiements (montant, devise, statut, valeurs de statut valides comme `paid, succeeded`, clés vers l'utilisateur et l'offre).
3. Choisissez la table des offres (identifiant, nom, prix) et celle des utilisateurs (identifiant, nom, e-mail).
4. Cliquez sur « Sauvegarder & Prévisualiser » et contrôlez les chiffres.

Le mappage est enregistré dans `storage/app/metrics-dash/mapping.json` (chemin modifiable avec la clé `mapping_file` de `config/metrics-dash.php`). Il est relu à chaque chargement de `/metrics-dash` et par `metrics-dash:sync`. Pour revenir aux valeurs par défaut, supprimez ce fichier. Le dossier `storage/app` n'est pas versionné : refaites le mappage sur chaque environnement.

### 6. Synchroniser

Test manuel :

```bash
php artisan metrics-dash:sync
```

La commande affiche le nombre de transactions extraites et transmises. Les deux nombres doivent être égaux.

Automatisation dans `routes/console.php` :

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('metrics-dash:sync')->hourly();
```

Le site doit lancer son scheduler chaque minute : `* * * * * cd /chemin/site && php artisan schedule:run`.

### 7. Vérifier

Dans le tableau de bord MetricsDash, choisissez l'instance et la période : les ventes apparaissent sous 10 minutes.

### 8. Dépannage

| Symptôme | Cause probable | Action |
| --- | --- | --- |
| « Aucune table de paiement configurée » | Mappage non fait | Ouvrir `/metrics-dash` |
| 0 transaction extraite | Mauvais statut valide ou table vide | Vérifier les valeurs de statut du mappage |
| Extraites mais 0 transmise | Clé privée ou URL erronée, réseau bloqué | Vérifier `METRICSDASH_PUSH_KEY`, `METRICSDASH_API_URL` et le journal `storage/logs/laravel.log` |
| Configuration ignorée | Cache de config | `php artisan config:clear` |
| Transactions envoyées plusieurs fois | Synchronisation relancée | Contrôler les doublons dans MetricsDash avant d'augmenter la fréquence |

---

## 🧪 Tests

```bash
vendor/bin/phpunit
```

---

## 📜 Licence

Ce package est sous licence [MIT](LICENSE).
