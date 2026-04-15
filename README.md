# TGI-NY — Système de Gestion des Dossiers Judiciaires
## Tribunal de Grande Instance Hors Classe de Niamey

---

## Description

Application web complète de gestion de bout en bout des dossiers judiciaires pour le **Tribunal de Grande Instance Hors Classe de Niamey** (Niger).

Développée en **PHP 8 pur** (sans framework), avec MySQL, Bootstrap 5, JavaScript vanilla et AJAX.

---

## Fonctionnalités principales

| Module | Description |
|--------|-------------|
| 🔐 Auth & Rôles | 9 rôles : Admin, Président, Procureur, Substitut, Juge instruction, Juge siège, Greffier, Avocat |
| 📋 PV | Réception, numérotation RG, affectation substitut, classification, transfert |
| 🗂️ Dossiers | Numéros RG/RP/RI, workflow complet, parties, historique |
| 📅 Audiences | Planning, salles, membres, renvois, calendrier interactif |
| ⚖️ Jugements | Saisie, dispositif, peine, appel |
| 🔒 Population carcérale | Prévenus, inculpés, condamnés, libérations, alertes durée |
| 🗺️ Carte antiterroriste | Leaflet.js, choroplèthe par commune, filtres dates |
| 🔔 Alertes | Retards PV/instruction, audiences proches, appels expirés, détentions longues |
| 📊 Dashboard | Statistiques temps réel, graphiques Chart.js |
| 👥 Utilisateurs | CRUD complet (admin) |

---

## Prérequis

- PHP 8.0+ avec extensions : PDO, PDO_MySQL, mbstring, json
- MySQL 8.x / MariaDB 10.6+
- Serveur web Apache avec **mod_rewrite** activé (ou Nginx)
- Navigateur moderne

---

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-compte/tribunal-tgi-ny.git
cd tribunal-tgi-ny
```

### 2. Créer la base de données

```sql
CREATE DATABASE tribunal_tgi_ny CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer le schéma et les données

```bash
mysql -u root -p tribunal_tgi_ny < migrations/001_schema.sql
mysql -u root -p tribunal_tgi_ny < migrations/002_seed_data.sql
```

### 4. Configurer la connexion

Créer un fichier `.env` à la racine (ou modifier `app/config/database.php`) :

```bash
# Variables d'environnement (optionnel)
export DB_HOST=localhost
export DB_NAME=tribunal_tgi_ny
export DB_USER=root
export DB_PASS=votre_mot_de_passe
export DB_PORT=3306
```

Ou modifier directement les valeurs par défaut dans `app/config/database.php`.

### 5. Configurer le serveur web

#### Apache (VirtualHost)

```apache
<VirtualHost *:80>
    ServerName tgi-niamey.local
    DocumentRoot /var/www/tribunal-tgi-ny/public
    <Directory /var/www/tribunal-tgi-ny/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### WAMP/XAMPP (développement local)

Placer le projet dans `htdocs/tribunal-tgi-ny/`, puis accéder via :
`http://localhost/tribunal-tgi-ny/public/`

### 6. Permissions

```bash
chmod -R 755 .
chmod -R 777 public/uploads/
```

---

## Identifiants par défaut

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@tgi-niamey.ne | Admin@2026 |
| Président | president@tgi-niamey.ne | Admin@2026 |
| Procureur | procureur@tgi-niamey.ne | Admin@2026 |
| Substitut | substitut1@tgi-niamey.ne | Admin@2026 |
| Juge instruction | juge.instr1@tgi-niamey.ne | Admin@2026 |
| Greffier | greffier@tgi-niamey.ne | Admin@2026 |
| Avocat | avocat@barreau-niamey.ne | Admin@2026 |

> ⚠️ **Changer impérativement tous les mots de passe en production !**

---

## Numérotation des dossiers

| Type | Format | Exemple |
|------|--------|---------|
| PV entrant | `RG N°XXX/AAAA/TGI-NY` | `RG N°001/2026/TGI-NY` |
| Parquet | `RP N°XXX/AAAA/PARQUET` | `RP N°001/2026/PARQUET` |
| Instruction | `RI N°XXX/AAAA/INSTR` | `RI N°001/2026/INSTR` |
| Jugement | `JUG N°XXX/AAAA/TGI-NY` | `JUG N°001/2026/TGI-NY` |
| Écrou | `ECRXXXx/AAAA` | `ECR0001/2026` |

---

## Workflow principal

```
PV reçu (RG)
    │
    ▼
Affecté à substitut du procureur
    │
    ├──▶ Classé sans suite
    │
    ├──▶ Cabinet instruction (RG + RP + RI)
    │        │
    │        ▼
    │    Instruction ──▶ Audience ──▶ Jugement
    │
    └──▶ Audience directe (RG + RP)
                         ──▶ Jugement
```

---

## Structure du projet

```
tribunal-tgi-ny/
├── public/           → Point d'entrée (index.php + assets)
├── app/
│   ├── config/       → Base de données, constantes
│   ├── core/         → Router, Controller, View
│   ├── controllers/  → Logique métier (10 controllers)
│   ├── models/       → (à implémenter si besoin)
│   ├── views/        → Templates PHP/HTML
│   └── helpers/      → Auth, CSRF, Numerotation, Alerte
├── api/              → Endpoints AJAX JSON
└── migrations/       → Scripts SQL
```

---

## Sécurité

- Tokens CSRF sur tous les formulaires POST
- PDO Prepared Statements (zéro injection SQL)
- Contrôle des rôles sur toutes les routes sensibles
- `password_hash()` / `password_verify()` pour les mots de passe
- `htmlspecialchars()` sur toutes les sorties
- Upload de fichiers restreint (PDF, DOC, images, max 10MB)

---

## Déploiement GitHub

```bash
cd /chemin/vers/tribunal-tgi-ny
git init
git add .
git commit -m "Initial commit — TGI-NY v1.0"
git remote add origin https://github.com/votre-compte/tribunal-tgi-ny.git
git push -u origin main
```

---

## Auteur

Développé pour le **Tribunal de Grande Instance Hors Classe de Niamey** — Niger  
Version 1.0 — 2026
