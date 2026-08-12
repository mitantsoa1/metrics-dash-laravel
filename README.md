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

## 🧪 Tests

```bash
vendor/bin/phpunit
```

---

## 📜 Licence

Ce package est sous licence [MIT](LICENSE).
