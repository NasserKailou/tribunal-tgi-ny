# Mémo de Présentation — Système de Gestion Judiciaire TGI-NY

## Informations Générales

- **Titre de l'application** : Système de Gestion Judiciaire — TGI-NY
- **Sous-titre** : Tribunal de Grande Instance Hors Classe de Niamey
- **Pays** : République du Niger
- **Institution** : Ministère de la Justice — Pouvoir Judiciaire
- **Version actuelle** : 3.1
- **Date** : Avril 2026
- **Technologies** : PHP 8+, MySQL 8 / MariaDB 10.6, Bootstrap 5, Leaflet.js, Chart.js

---

## 1. Vue d'Ensemble

### Contexte & Objectifs

Le **TGI-NY** est une application web de gestion judiciaire conçue pour moderniser et dématérialiser les processus du Tribunal de Grande Instance Hors Classe de Niamey. Elle couvre l'ensemble du circuit judiciaire : de la réception du Procès-Verbal jusqu'au jugement final, en passant par l'instruction et les audiences.

### Bénéfices Clés

- 🏛️ **Dématérialisation complète** du dossier judiciaire
- ⚡ **Gain de temps** : automatisation des numérotations, alertes et délais
- 📊 **Pilotage en temps réel** avec tableaux de bord statistiques
- 🔒 **Sécurité renforcée** : droits par rôle, CSRF, mots de passe hachés
- 🗺️ **Cartographie antiterroriste** interactive avec 266 communes
- 📱 **Interface responsive** accessible sur tous les appareils
- ⚙️ **100 % configurable** pour réutilisation dans tout tribunal du Niger

---

## 2. Architecture Technique

### Stack Technologique

| Couche | Technologie |
|--------|-------------|
| Backend | PHP 8.2 (MVC maison, sans framework) |
| Base de données | MySQL 8.0 / MariaDB 10.6 |
| Frontend | Bootstrap 5.3, Bootstrap Icons |
| Cartographie | Leaflet.js 1.9, GeoJSON Niger |
| Graphiques | Chart.js 4, Highcharts |
| Sécurité | Tokens CSRF, PDO prepared statements, bcrypt |
| Serveur | Apache 2.4 / Nginx (mod_rewrite) |

### Structure MVC

```
tribunal-tgi-ny/
├── app/
│   ├── controllers/     (12 contrôleurs)
│   ├── views/           (60+ vues PHP/HTML)
│   ├── helpers/         (Auth, CSRF, Alerte, Numérotation)
│   └── core/            (Controller base, Router)
├── migrations/          (001-010 + COMPLET.sql)
├── public/              (assets CSS/JS, uploads)
└── index.php            (Routeur principal)
```

---

## 3. Modules de l'Application

### 3.1 🔐 Authentification & Gestion des Rôles

**Description** : Système de connexion sécurisé avec gestion fine des accès par rôle.

**Rôles disponibles** (9 rôles) :
- Administrateur Système
- Président du Tribunal
- Vice-Président
- Procureur de la République
- Substitut du Procureur (×21 postes)
- Juge d'Instruction
- Juge du Siège
- Greffier
- Avocat

**Fonctionnalités** :
- Connexion sécurisée (email + mot de passe bcrypt)
- Session PHP avec protection CSRF
- Accès différencié par rôle (menus, actions)
- Gestion des utilisateurs avec matriucles
- Attribution de fonctions de parquet (Procureur, Adjoint, Substitut N°1 à N°21)

---

### 3.2 📋 Module Procès-Verbaux (PV)

**Description** : Enregistrement et suivi de tous les procès-verbaux reçus par le parquet.

**Fonctionnalités** :
- Création de PV avec numérotation automatique (RG)
- Liaison aux unités d'enquête et primo-intervenants
- Géolocalisation par région / département / commune
- **Marquage antiterroriste** (affichage sur carte)
- Affectation automatique à un substitut
- Cycle de vie complet : Nouveau → En traitement → Classé / Transféré
- **Déclassement** : réouverture d'un PV classé avec motif et traçabilité
- Filtres avancés (statut, type, antiterro, période)
- **Transfert intelligent** : suggestion du substitut le moins chargé
- Pagination et export

**Statuts** :
```
Nouveau → En traitement → Transféré (vers dossier)
                       → Classé sans suite
                       ← Déclassé (si réouverture)
```

---

### 3.3 📁 Module Dossiers

**Description** : Gestion complète des dossiers judiciaires depuis leur création jusqu'au jugement.

**Fonctionnalités** :
- Création depuis un PV transféré ou directement
- Numérotation automatique (RG, RP, RI selon destination)
- Affectation au parquet (substitut) et aux cabinets d'instruction
- Suivi des parties (prévenus, victimes, avocats)
- Gestion des pièces jointes (PDF, images — 10 MB max)
- Classement sans suite avec motif
- **Déclassement** avec motif, traçabilité et notification
- Envoi en audience automatisé
- Calcul automatique des délais (instruction : 6 mois)
- Journal des mouvements horodaté

**Statuts** :
```
Nouveau → En instruction → En audience → Jugé
       → Classé ← Déclassé
       → Appel
```

---

### 3.4 📅 Module Audiences

**Description** : Planification et gestion des audiences du tribunal.

**Fonctionnalités** :
- Calendrier interactif des audiences
- Planification avec salle, date, type d'audience
- Composition du tribunal (président, greffier, assesseurs, avocats)
- Types : Correctionnelle, Criminelle, Civile, Commerciale, Instruction
- Statuts : Planifiée, En cours, Terminée, Reportée, Annulée
- Alertes automatiques 3 jours avant l'audience
- Vue calendrier et liste filtrée

---

### 3.5 ⚖️ Module Jugements

**Description** : Saisie et suivi des décisions de justice.

**Fonctionnalités** :
- Enregistrement du dispositif de jugement
- Types : Condamnation, Relaxe, Acquittement, Non-lieu
- Peine principale, amende, dommages et intérêts
- Calcul automatique du délai d'appel (30 jours)
- Enregistrement des appels
- Impression officielle du jugement
- Historique complet

---

### 3.6 🔒 Module Mandats de Justice

**Description** : Génération et suivi des mandats judiciaires officiels.

**Types de mandats** :
- Mandat d'Arrêt
- Mandat de Dépôt
- Mandat d'Amener
- Mandat de Comparution
- Mandat de Perquisition
- Mandat de Libération

**Fonctionnalités** :
- Numérotation automatique officielle
- Impression officielle avec :
  - En-tête République du Niger / Ministère de la Justice
  - Logo TGI-NY
  - **QR Code** d'authentification
  - Pied de page configurable
  - Trois blocs de signature
- Suivi du statut (Émis → Signifié → Exécuté)
- Lien vers dossier et détenu
- Alerte expiration

---

### 3.7 👥 Module Population Carcérale

**Description** : Gestion et suivi des personnes détenues dans les maisons d'arrêt.

**Fonctionnalités** :
- Enregistrement complet du détenu (identité, photo, famille)
- Liaison au dossier judiciaire
- Types de détention : Provisoire, Condamné
- Suivi des statuts : Incarcéré, Libéré, Transféré
- **Statistiques par genre** (hommes / femmes) par maison d'arrêt
- Graphique doughnut Chart.js de répartition
- Calcul du taux d'occupation
- Vue d'état global des maisons d'arrêt
- Alertes détention provisoire dépassée

**Maisons d'arrêt gérées** :
| Établissement | Ville | Capacité |
|---------------|-------|----------|
| Maison d'Arrêt de Niamey | Niamey | 600 |
| Maison d'Arrêt de Zinder | Zinder | 300 |
| Maison d'Arrêt de Maradi | Maradi | 250 |
| Maison d'Arrêt de Tahoua | Tahoua | 200 |
| Maison d'Arrêt de Dosso | Dosso | 150 |
| Centre de Détention de Kollo | Kollo | 100 |
| Maison d'Arrêt d'Agadez | Agadez | 100 |
| Maison d'Arrêt de Diffa | Diffa | 80 |

---

### 3.8 🗺️ Module Carte Antiterroriste

**Description** : Cartographie interactive des PV antiterroristes sur les 266 communes du Niger.

**Fonctionnalités** :
- Carte choroplèthe Leaflet.js sur fond CartoDB Positron
- **266 communes** avec polygones délimités (Voronoi géographique)
- **Colorisation par intensité** des PV antiterroristes :
  - 🔵 Bleu clair = 0 PV
  - 🟡 Jaune = 1 PV
  - 🟠 Orange = 2–4 PV
  - 🔴 Rouge = 5–9 PV
  - 🔴 Rouge foncé = 10+ PV
- Popup détaillé au survol/clic (commune, région, département, nb PV)
- Zoom automatique sur la sélection
- Filtres : commune, date début/fin
- **Tableau détaillé** par commune avec tri
- Graphique Top 10 communes (Highcharts)
- Graphique répartition par région (Highcharts pie)
- Statistiques rapides : total PV, communes touchées, régions concernées
- Mode impression

---

### 3.9 🔔 Module Alertes

**Description** : Système d'alertes automatiques pour les délais critiques.

**Types d'alertes** :
- ⏰ Délai PV dépassé (30 jours sans traitement)
- ⚖️ Délai instruction dépassé (6 mois)
- 📅 Audience dans les 3 jours
- 📋 Mandat expiré
- 🔒 Détention provisoire dépassée

**Fonctionnalités** :
- Badge de comptage dans la barre de navigation
- Liste filtrée par niveau (Info, Avertissement, Danger)
- Marquage lu/non lu
- Lien direct vers le dossier/PV concerné

---

### 3.10 📊 Tableau de Bord

**Description** : Vision d'ensemble en temps réel de l'activité judiciaire.

**Indicateurs affichés** :
- Total PV (+ PV antiterroristes)
- Dossiers en cours / en instruction
- Audiences planifiées
- Personnes détenues
- Mandats émis
- Graphiques d'évolution mensuelle
- Top communes antiterroristes
- Alertes en attente

---

### 3.11 ⚙️ Module Configuration

**Description** : Paramétrage complet de l'application pour adaptation à tout tribunal.

**Sections** :

#### Paramètres Système (25+ paramètres)
- Identité du tribunal (nom, ville, adresse, contact)
- En-têtes et pieds de page des documents officiels
- Délais métier configurables
- Préfixes et suffixes de numérotation
- Couleur primaire de l'interface

#### Gestion des Cabinets d'Instruction
- 8 cabinets officiels TGI-NY
- Assignation des juges d'instruction
- Vue des dossiers assignés par cabinet

#### Gestion des Substituts
- Liste des substituts du procureur
- Vue des PV et dossiers assignés
- Charge de travail par substitut

#### Autres configurations
- Primo-intervenants (7 organismes)
- Unités d'enquête (8 unités)
- Infractions (15 infractions avec peines)
- Maisons d'arrêt
- Salles d'audience
- Membres types des audiences

---

### 3.12 🛡️ Gestion des Droits

**Description** : Administration fine des permissions par utilisateur.

**Fonctionnalités** :
- Interface UI avec checkboxes par menu et fonctionnalité
- Attribution / révocation de droits par utilisateur
- Menus et fonctionnalités paramétrables
- Audit trail des modifications

---

## 4. Sécurité

| Mesure | Détail |
|--------|--------|
| Authentification | Sessions PHP + hachage bcrypt (coût 12) |
| Protection CSRF | Token double champ (\_csrf + csrf\_token) |
| Requêtes SQL | PDO Prepared Statements (100% paramétrées) |
| Upload fichiers | Validation MIME, taille max 10 MB, extension whitelist |
| Autorisation | Vérification rôle à chaque action sensible |
| XSS | htmlspecialchars() systématique en sortie |

---

## 5. Installation & Déploiement

### Prérequis
- PHP 8.0+ avec PDO, PDO_MySQL, fileinfo, mbstring
- MySQL 8.0+ ou MariaDB 10.6+
- Apache 2.4+ (mod_rewrite activé) ou Nginx
- 512 MB RAM minimum

### Étapes d'installation
```bash
# 1. Cloner le dépôt
git clone https://github.com/NasserKailou/tribunal-tgi-ny.git
cd tribunal-tgi-ny

# 2. Créer la base de données
mysql -u root -p -e "CREATE DATABASE tribunal_tgi_ny CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Importer la base complète
mysql -u root -p tribunal_tgi_ny < migrations/tribunal_tgi_ny_COMPLET.sql

# 4. Configurer la connexion
cp app/config/database.example.php app/config/database.php
# Éditer app/config/database.php avec les identifiants DB

# 5. Permissions
chmod -R 755 public/uploads/

# 6. Configurer le serveur web
# Pointer le DocumentRoot vers /public
```

### Comptes par défaut (mot de passe : `Admin@2026`)
| Email | Rôle |
|-------|------|
| admin@tgi-niamey.ne | Administrateur |
| procureur@tgi-niamey.ne | Procureur |
| president@tgi-niamey.ne | Président |
| substitut1@tgi-niamey.ne | Substitut 1 |
| greffier@tgi-niamey.ne | Greffier |

---

## 6. Workflow Judiciaire

```
RÉCEPTION PV
     │
     ▼
[Parquet — Substitut]
     │
     ├─── CLASSEMENT SANS SUITE (→ Archivé)
     │         └── Déclassement possible
     │
     └─── TRANSFERT EN DOSSIER
                │
                ▼
         [Dossier créé]
                │
                ├─── INSTRUCTION
                │         │
                │         ▼
                │    [Cabinet d'instruction]
                │         │
                │         └─ Ordonnance de renvoi
                │
                ├─── AUDIENCE DIRECTE
                │
                ▼
         [Audience planifiée]
                │
                ▼
         [JUGEMENT rendu]
                │
                ├─── Condamnation → Mandat de dépôt
                ├─── Relaxe / Acquittement
                └─── Appel (délai 30 jours)
```

---

## 7. Points Différenciants

### Versus gestion papier
| Aspect | Papier | TGI-NY |
|--------|--------|--------|
| Numérotation | Manuelle, erreurs | Automatique, unique |
| Recherche dossier | Fastidieuse | Instantanée |
| Alertes délais | Non | Automatiques |
| Statistiques | Impossibles | Temps réel |
| Mandats | Manuscrits | Imprimés + QR Code |
| Carte sécurité | Non | Interactive |

### Fonctionnalités innovantes
1. **Carte antiterroriste choroplèthe** avec 266 communes et colorisation par intensité PV
2. **QR Code** sur chaque mandat pour authentification rapide
3. **Transfert intelligent** : suggestion automatique du substitut le moins chargé
4. **Déclassement traçable** : réouverture PV/dossier avec motif obligatoire et journal
5. **100 % configurable** : tribunal, numérotation, délais, couleurs

---

## 8. Évolutions Prévues

- [ ] Application mobile Android/iOS (PWA)
- [ ] Génération PDF automatique des actes
- [ ] Signature électronique des documents
- [ ] API REST pour interconnexion Ministère
- [ ] Module statistiques avancé (export Excel)
- [ ] Sauvegarde automatique vers cloud
- [ ] Module casier judiciaire

---

## 9. Équipe & Contact

| Rôle | Information |
|------|-------------|
| Maîtrise d'ouvrage | Tribunal de Grande Instance H.C. de Niamey |
| Développement | Équipe TGI-NY — Branche `nasser` |
| Repository | https://github.com/NasserKailou/tribunal-tgi-ny |
| Contact technique | admin@tgi-niamey.ne |
| Licence | Propriétaire — Usage judiciaire Niger |

---

*Mémo préparé pour génération de présentation PowerPoint — Avril 2026*
*Toutes les captures d'écran et maquettes sont disponibles sur la branche `nasser`*
