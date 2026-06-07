# Guide de Déploiement — TGI Hors Classe Niamey

**URL de production :** `https://tgihc.ca-niamey.ne`  
**Répertoire racine de l'application :** `/var/www/tgihc/` (exemple recommandé)  
**Point d'entrée web :** `public/` → accessible à `https://tgihc.ca-niamey.ne/public/`  
**Version PHP requise :** PHP 8.1 minimum (PHP 8.2 recommandé)  
**Base de données :** MySQL 8.0 ou MariaDB 10.6+

---

## Sommaire

1. [Prérequis serveur](#1-prérequis-serveur)
2. [Déploiement des fichiers](#2-déploiement-des-fichiers)
3. [Configuration Apache](#3-configuration-apache)
4. [Configuration de la base de données](#4-configuration-de-la-base-de-données)
5. [Configuration BASE_URL (point critique)](#5-configuration-base_url--point-critique)
6. [Droits sur les dossiers d'upload](#6-droits-sur-les-dossiers-dupload)
7. [Migrations de base de données](#7-migrations-de-base-de-données)
8. [Vérification post-déploiement](#8-vérification-post-déploiement)
9. [Diagnostic de la carte (/carte)](#9-diagnostic-de-la-carte-carte)
10. [Sécurité complémentaire](#10-sécurité-complémentaire)
11. [Mise à jour de l'application](#11-mise-à-jour-de-lapplication)

---

## 1. Prérequis serveur

### PHP 8.1+

```bash
php -v  # Vérifier la version installée
```

Extensions PHP **obligatoires** :

| Extension | Utilisation |
|-----------|-------------|
| `pdo_mysql` | Connexion PDO à MySQL/MariaDB |
| `mbstring` | Traitement des chaînes Unicode |
| `fileinfo` | Détection du type MIME des fichiers uploadés |
| `gd` ou `imagick` | Traitement éventuel d'images |
| `openssl` | Chiffrement des sessions |
| `intl` | Tri alphabétique Unicode (optionnel mais recommandé) |
| `zip` | Export ZIP (optionnel) |

Vérifier les extensions actives :

```bash
php -m | grep -E 'pdo_mysql|mbstring|fileinfo|gd|openssl|intl'
```

Activer les extensions manquantes dans `php.ini` ou `/etc/php/8.x/apache2/conf.d/` :

```ini
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=gd
extension=openssl
```

### Apache 2.4+ avec `mod_rewrite`

```bash
a2enmod rewrite   # Activer mod_rewrite
service apache2 reload
```

---

## 2. Déploiement des fichiers

### Cloner ou transférer le dépôt

```bash
# Via Git (recommandé)
cd /var/www
git clone https://github.com/NasserKailou/tribunal-tgi-ny.git tgihc
cd tgihc
git checkout ak_main

# Ou via SFTP/SCP — transférer tous les fichiers dans /var/www/tgihc/
```

### Structure attendue

```
/var/www/tgihc/
├── app/                   ← Logique applicative (contrôleurs, vues, helpers…)
│   ├── config/
│   │   ├── config.php
│   │   └── database.php
│   ├── controllers/
│   ├── views/
│   └── helpers/
├── migrations/            ← Scripts SQL de migration
│   ├── global.sql
│   └── 015_pv_infractions_qualifications.sql
├── public/                ← Seul répertoire exposé au web
│   ├── index.php          ← Point d'entrée unique
│   ├── .htaccess          ← Réécriture d'URL
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── data/
│   │       └── niger_communes.geojson
│   └── uploads/           ← Fichiers uploadés (doit être accessible en écriture)
├── app_config.php         ← À CRÉER (configuration locale — voir section 5)
└── guide.md
```

---

## 3. Configuration Apache

### VirtualHost (recommandé)

Créer le fichier `/etc/apache2/sites-available/tgihc.conf` :

```apache
<VirtualHost *:80>
    ServerName tgihc.ca-niamey.ne
    Redirect permanent / https://tgihc.ca-niamey.ne/
</VirtualHost>

<VirtualHost *:443>
    ServerName tgihc.ca-niamey.ne

    # Pointer le DocumentRoot directement sur public/
    DocumentRoot /var/www/tgihc/public

    # Certificat SSL
    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/tgihc.ca-niamey.ne/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/tgihc.ca-niamey.ne/privkey.pem

    <Directory /var/www/tgihc/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Logs
    ErrorLog  /var/log/apache2/tgihc_error.log
    CustomLog /var/log/apache2/tgihc_access.log combined
</VirtualHost>
```

Activer le site et recharger Apache :

```bash
a2ensite tgihc.conf
a2dissite 000-default.conf  # Désactiver le site par défaut (optionnel)
service apache2 reload
```

> **Important** : Le `DocumentRoot` pointe sur `public/`, pas sur la racine du dépôt.  
> De cette façon l'URL de base est `https://tgihc.ca-niamey.ne/` (sans `/public/` dans l'URL).

### Si le DocumentRoot ne peut PAS pointer sur `public/`

Si pour des raisons administratives le DocumentRoot doit rester sur `/var/www/tgihc/`, ajouter un Alias :

```apache
Alias /public /var/www/tgihc/public
<Directory /var/www/tgihc/public>
    AllowOverride All
    Require all granted
</Directory>
```

Dans ce cas l'URL sera `https://tgihc.ca-niamey.ne/public/` — **définir `APP_BASE_URL` en conséquence** (voir section 5).

### Vérifier le `.htaccess`

Le fichier `public/.htaccess` est déjà présent dans le dépôt :

```apache
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Il renvoie toutes les requêtes vers `index.php` sauf les fichiers et dossiers existants.

---

## 4. Configuration de la base de données

### Créer la base et l'utilisateur

```sql
CREATE DATABASE tribunal_tgi_ny_maj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'tgi_user'@'localhost' IDENTIFIED BY 'MotDePasseFort!';
GRANT ALL PRIVILEGES ON tribunal_tgi_ny_maj.* TO 'tgi_user'@'localhost';
FLUSH PRIVILEGES;
```

### Importer le dump de production

```bash
# Importer le dump complet (structure + données)
mysql -u tgi_user -p tribunal_tgi_ny_maj < /var/www/tgihc/migrations/global.sql

# Puis appliquer les migrations incrémentales dans l'ordre
mysql -u tgi_user -p tribunal_tgi_ny_maj < /var/www/tgihc/migrations/014_fix_jugement_id_avocats.sql
mysql -u tgi_user -p tribunal_tgi_ny_maj < /var/www/tgihc/migrations/015_pv_infractions_qualifications.sql
```

> Les migrations 001–013 sont intégrées dans `global.sql`. N'exécuter que les nouvelles migrations numérotées 014+.

### Vérifier / modifier `app/config/database.php`

```php
// Ce fichier est déjà configuré pour :
'dbname'   => 'tribunal_tgi_ny_maj'
'username' => 'root'          // ← À changer en production
'password' => ''              // ← À définir
```

**En production**, modifier les identifiants directement dans `database.php` **ou** (recommandé) créer `app_config.php` à la racine avec :

```php
<?php
// app_config.php — Configuration locale (jamais committé dans git)
define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne/public');

define('DB_HOST',     'localhost');
define('DB_NAME',     'tribunal_tgi_ny_maj');
define('DB_USER',     'tgi_user');
define('DB_PASSWORD', 'MotDePasseFort!');
```

Et dans `app/config/database.php`, remplacer les valeurs codées en dur par :

```php
$host = defined('DB_HOST')     ? DB_HOST     : 'localhost';
$db   = defined('DB_NAME')     ? DB_NAME     : 'tribunal_tgi_ny_maj';
$user = defined('DB_USER')     ? DB_USER     : 'root';
$pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
```

---

## 5. Configuration BASE_URL — Point critique

### Problème local → production

| Environnement | URL d'accès | `BASE_URL` attendu |
|---|---|---|
| Développement local | `http://localhost:8085/app_tgi/public/` | `http://localhost:8085/app_tgi/public` |
| Production (DocumentRoot = `/public`) | `https://tgihc.ca-niamey.ne/` | `https://tgihc.ca-niamey.ne` |
| Production (DocumentRoot = racine) | `https://tgihc.ca-niamey.ne/public/` | `https://tgihc.ca-niamey.ne/public` |

### Solution : `app_config.php` à la racine

Créer le fichier `/var/www/tgihc/app_config.php` **sur le serveur de production uniquement** :

```php
<?php
/**
 * Configuration locale — Production TGI Niamey
 * Ce fichier est chargé automatiquement par app/config/config.php
 * NE PAS committer ce fichier dans Git (il est dans .gitignore ou doit l'être)
 */

// Si le DocumentRoot Apache pointe sur public/ (recommandé) :
define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne');

// Si le DocumentRoot est la racine et que public/ est un sous-dossier :
// define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne/public');
```

> Ce fichier est détecté et chargé automatiquement par `app/config/config.php`.  
> `BASE_URL` sera partout dans l'application égal à `'https://tgihc.ca-niamey.ne'`.  
> Tous les liens, redirections, chemins d'assets, API AJAX s'adapteront sans aucune autre modification.

### Ajouter `app_config.php` au `.gitignore`

```bash
echo "app_config.php" >> /var/www/tgihc/.gitignore
```

---

## 6. Droits sur les dossiers d'upload

Le serveur web (Apache/www-data) doit pouvoir écrire dans `public/uploads/` :

```bash
# Propriétaire et droits
chown -R www-data:www-data /var/www/tgihc/public/uploads
chmod -R 755 /var/www/tgihc/public/uploads

# Créer les sous-dossiers standard s'ils n'existent pas
mkdir -p /var/www/tgihc/public/uploads/documents
chmod 755 /var/www/tgihc/public/uploads/documents

# Le reste des fichiers de l'application doit être en lecture seule pour www-data
chown -R root:www-data /var/www/tgihc
chmod -R 750 /var/www/tgihc
chmod -R 755 /var/www/tgihc/public/uploads   # uploads : écriture OK
```

---

## 7. Migrations de base de données

### Ordre d'application

```bash
# Si déploiement initial (base vide) :
mysql -u tgi_user -p tribunal_tgi_ny_maj < migrations/global.sql

# Migrations additionnelles (v3.7+) :
mysql -u tgi_user -p tribunal_tgi_ny_maj < migrations/014_fix_jugement_id_avocats.sql
mysql -u tgi_user -p tribunal_tgi_ny_maj < migrations/015_pv_infractions_qualifications.sql
```

### Contenu de `015_pv_infractions_qualifications.sql`

Cette migration crée deux nouvelles tables sans toucher aux données existantes :

- **`pv_infractions_enquete`** : Infractions déclarées par l'unité d'enquête (multi-sélection)
- **`pv_qualifications_substitut`** : Qualifications retenues par le substitut du procureur

Elle migre automatiquement les infractions existantes (`pv.infraction_id`) vers la nouvelle table.

> Toutes les instructions sont **idempotentes** (`IF NOT EXISTS`, `INSERT IGNORE`) : on peut la rejouer sans risque.

---

## 8. Vérification post-déploiement

### Checklist

```bash
# 1. Apache répond
curl -I https://tgihc.ca-niamey.ne/

# 2. PHP s'exécute (doit afficher la page de login, pas du code source)
curl -s https://tgihc.ca-niamey.ne/ | grep -i "tribunal\|login\|connexion"

# 3. Assets chargés correctement (status 200)
curl -I https://tgihc.ca-niamey.ne/assets/css/app.css

# 4. Pas de mixed content dans les headers
curl -I https://tgihc.ca-niamey.ne/ | grep -i "content-security"

# 5. Base de données accessible
php -r "
  \$pdo = new PDO('mysql:host=localhost;dbname=tribunal_tgi_ny_maj', 'tgi_user', 'MotDePasseFort!');
  echo 'DB OK - ' . \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() . ' utilisateur(s)\n';
"

# 6. Dossier uploads accessible en écriture
php -r "echo is_writable('/var/www/tgihc/public/uploads') ? 'uploads: OK\n' : 'uploads: ERREUR\n';"
```

### En cas d'erreur 500

```bash
tail -50 /var/log/apache2/tgihc_error.log
tail -50 /var/log/php8.x/error.log      # Adapter la version PHP
```

Activer temporairement les erreurs PHP (dev uniquement) dans `public/index.php` :

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## 9. Diagnostic de la carte (`/carte`)

### Cause des problèmes observés

La carte antiterroriste (`/carte`) utilise :

| Ressource | Source | Protocol |
|---|---|---|
| Leaflet CSS + JS | `https://unpkg.com/leaflet@1.9.4/` | ✅ HTTPS |
| Highcharts | `https://code.highcharts.com/` | ✅ HTTPS |
| Tuiles CartoDB | `https://{s}.basemaps.cartocdn.com/` | ✅ HTTPS |
| GeoJSON communes | `BASE_URL + '/assets/data/niger_communes.geojson'` | ✅ BASE_URL |
| API données | `BASE_URL + '/api/carte-data'` | ✅ BASE_URL |

**Toutes les ressources utilisent déjà HTTPS** et s'appuient sur `BASE_URL`. **La carte ne s'affiche pas en production uniquement si `BASE_URL` est incorrect.**

### Cause probable : `BASE_URL` pointe encore vers `localhost`

Si `app_config.php` n'est pas créé sur le serveur de production, `BASE_URL` est auto-détecté depuis `$_SERVER['SCRIPT_NAME']`. Sur certains hébergements, `SCRIPT_NAME` peut retourner une valeur inattendue.

**Solution définitive** : créer `app_config.php` comme indiqué en section 5.

### Vérifier `BASE_URL` en production

Ajouter temporairement dans `public/index.php` (supprimer après vérification) :

```php
// Débogage temporaire — À SUPPRIMER après vérification
if (isset($_GET['debug_base']) && $_SERVER['REMOTE_ADDR'] === '::1') {
    echo 'BASE_URL = ' . BASE_URL;
    exit;
}
```

Ou via SSH :

```bash
php -r "
  define('ROOT_PATH', '/var/www/tgihc');
  require '/var/www/tgihc/app/config/config.php';
  echo 'BASE_URL = ' . BASE_URL . PHP_EOL;
"
```

### Vérifier que le GeoJSON est accessible

```bash
curl -I https://tgihc.ca-niamey.ne/assets/data/niger_communes.geojson
# Doit retourner : HTTP/2 200 et Content-Type: application/json (ou application/geo+json)
```

Si le fichier retourne 404 :

```bash
ls -la /var/www/tgihc/public/assets/data/niger_communes.geojson
```

Si absent, copier depuis le dépôt local ou S'assurer que git pull a été fait.

### Content-Security-Policy (HTTPS strict)

Si le serveur déploie un CSP strict, s'assurer que les domaines CDN sont autorisés :

```apache
# Dans le VirtualHost ou .htaccess
Header always set Content-Security-Policy "
    default-src 'self';
    script-src 'self' 'unsafe-inline' https://unpkg.com https://code.highcharts.com;
    style-src 'self' 'unsafe-inline' https://unpkg.com;
    img-src 'self' data: https://*.basemaps.cartocdn.com https://*.openstreetmap.org;
    connect-src 'self' https://*.basemaps.cartocdn.com;
    font-src 'self';
"
```

---

## 10. Sécurité complémentaire

### Headers HTTP recommandés (`.htaccess` ou VirtualHost)

```apache
# Sécurité des en-têtes HTTP
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# HTTPS strict (HSTS) — activer seulement si SSL est permanent
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

Activer `mod_headers` :

```bash
a2enmod headers
service apache2 reload
```

### Protéger les dossiers sensibles

```apache
# Bloquer l'accès direct aux répertoires hors public/
<DirectoryMatch "^/var/www/tgihc/(app|migrations|vendor)">
    Require all denied
</DirectoryMatch>
```

### Session PHP (`php.ini` ou `ini_set`)

```ini
session.cookie_httponly = 1
session.cookie_secure   = 1   ; HTTPS uniquement
session.use_strict_mode = 1
session.cookie_samesite = Lax
```

### Fichier `app_config.php` hors du dépôt Git

```bash
# Ne jamais committer les credentials en production
echo "app_config.php" >> /var/www/tgihc/.gitignore
git -C /var/www/tgihc rm --cached app_config.php 2>/dev/null; true
```

---

## 11. Mise à jour de l'application

```bash
cd /var/www/tgihc

# 1. Sauvegarder la base avant toute mise à jour
mysqldump -u tgi_user -p tribunal_tgi_ny_maj > /backup/tgi_$(date +%Y%m%d_%H%M%S).sql

# 2. Récupérer les dernières modifications
git pull origin ak_main

# 3. Appliquer les nouvelles migrations (si présentes)
# Vérifier les numéros de migration non encore appliqués
mysql -u tgi_user -p tribunal_tgi_ny_maj < migrations/015_pv_infractions_qualifications.sql

# 4. Ajuster les droits si de nouveaux dossiers d'upload ont été créés
chown -R www-data:www-data public/uploads
chmod -R 755 public/uploads

# 5. Vider le cache OPcache PHP si activé
php -r "if(function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; }"

# 6. Tester
curl -I https://tgihc.ca-niamey.ne/
```

---

## Récapitulatif des actions indispensables

| # | Action | Commande / Fichier |
|---|--------|--------------------|
| 1 | Créer `app_config.php` à la racine | `define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne')` |
| 2 | Pointer DocumentRoot sur `public/` | VirtualHost Apache |
| 3 | Activer `mod_rewrite` | `a2enmod rewrite` |
| 4 | Importer la base de données | `mysql < migrations/global.sql` puis 014, 015 |
| 5 | Droits `uploads/` | `chown www-data:www-data public/uploads && chmod 755` |
| 6 | PHP 8.1+ avec `pdo_mysql`, `mbstring`, `fileinfo` | `php -m` pour vérifier |
| 7 | SSL actif (`https://`) | Let's Encrypt ou certificat fourni |

---

*Document généré le 2026-06-07 — Version application : 3.8 — Branche : `ak_main`*
