<?php
/**
 * niger_geo.php — Référentiel géographique du Niger
 * Régions, départements et communes (266 communes officielles)
 *
 * Utilisé par : CarteController, exports, validations
 */

// ─── Régions ──────────────────────────────────────────────────────────────────
$regions_niger = [
    'Agadez',
    'Diffa',
    'Dosso',
    'Maradi',
    'Niamey',
    'Tahoua',
    'Tillaberi',
    'Zinder',
];

// ─── Départements par région ──────────────────────────────────────────────────
$departements_par_region = [
    'Agadez' => [
        'Aderbisanat',
        'Agadez Ville',
        'Arlit',
        'Bilma',
        'Iferouane',
        'Ingall',
        'Tassara',
        'Tchintabaraden',
        'Tchirozerine',
    ],
    'Diffa' => [
        'Bosso',
        'Diffa',
        'Goudoumaria',
        'Maine Soroa',
        "N'Gourti",
        "N'Guigmi",
    ],
    'Dosso' => [
        'Boboye',
        'Dioundiou',
        'Dogondoutchi',
        'Dosso',
        'Falmey',
        'Gaya',
        'Loga',
        'Tibiri',
    ],
    'Maradi' => [
        'Aguie',
        'Dakoro',
        'Gazaoua',
        'Guidan-Roumdji',
        'Madarounfa',
        'Mayahi',
        'Tessaoua',
        'Ville De Maradi',
    ],
    'Niamey' => [
        'Niamey Ville',
    ],
    'Tahoua' => [
        'Abalak',
        'Bagaroua',
        "Birni N'Konni",
        'Bouza',
        'Illéla',
        'Keita',
        'Madaoua',
        'Malbaza',
        'Tahoua Departement',
        'Takeita',
        'Tillia',
        'Ville De Tahoua',
    ],
    'Tillaberi' => [
        'Abala',
        'Ayorou',
        'Balleyara',
        'Banibangou',
        'Bankilare',
        'Filingue',
        'Gotheye',
        'Kollo',
        'Ouallam',
        'Say',
        'Tera',
        'Tillaberi',
        'Torodi',
    ],
    'Zinder' => [
        'Belbedji',
        'Bermo',
        'Damagaram Takaya',
        'Dungass',
        'Goure',
        'Kantche',
        'Magaria',
        'Mirriah',
        'Tanout',
        'Tesker',
        'Ville De Zinder',
    ],
];

// ─── Communes par département ─────────────────────────────────────────────────
$communes_par_departement = [
    // AGADEZ
    'Aderbisanat'        => ['Adebissanat'],
    'Agadez Ville'       => ['Agadez Commune'],
    'Arlit'              => ['Arlit', 'Dannet', 'Gougaram'],
    'Bilma'              => ['Bilma', 'Dirkou', 'Djado', 'Fachi'],
    'Iferouane'          => ['Iferouane', 'Tmia'],
    'Ingall'             => ['Ingall'],
    'Tassara'            => ['Tassara'],
    'Tchintabaraden'     => ['Kao', 'Tchintabaraden'],
    'Tchirozerine'       => ['Dabaga', 'Tabelot', 'Tchirozerine'],

    // DIFFA
    'Bosso'              => ['Bosso', 'Toumour'],
    'Diffa'              => ['Chetimari', 'Diffa Commune', 'Gueskerou'],
    'Goudoumaria'        => ['Goudoumaria'],
    'Maine Soroa'        => ['Foulateri', 'Maine Soroa', "N'Guelbeyli"],
    "N'Gourti"           => ["N'Gourti"],
    "N'Guigmi"           => ['Kabelewa', "N'Guigmi"],

    // DOSSO
    'Boboye'             => ["Birni N'Gaoure", 'Fabidji', 'Fakara', 'Harika-Nassou', 'Kankandi', 'Kiota', 'Koygolo', "N'Gonga"],
    'Dioundiou'          => ['Dioundiou', 'Kara Kara', 'Zabori'],
    'Dogondoutchi'       => ['Dan Kassari', 'Dogon Kiria', 'Dogondoutchi', 'Kieche', 'Matankari', 'Soucoucoutane'],
    'Dosso'              => ['Dosso Commune', 'Farrey', 'Garankedeye', 'Golle', 'Gorouban Kassam', 'Kargui Bangou', 'Mokko', 'Sakadamna', 'Sambera', 'Tessa', 'Tombo Koarey'],
    'Falmey'             => ['Falmey', 'Guilladje'],
    'Gaya'               => ['Bana', 'Bengou', 'Gaya', 'Tanda', 'Tounouga', 'Yelou'],
    'Loga'               => ['Falwel', 'Loga', 'Sokorbe'],
    'Tibiri'             => ['Doumega', 'Guecheme', 'Kore Mairoua', 'Tibiri (Dogondoutchi)'],

    // MARADI
    'Aguie'              => ['Aguie', 'Tchadoua'],
    'Dakoro'             => ['Adjiekoria', 'Azagor', 'Bader Goula', 'Birnin Lalle', 'Dakoro', 'Dan Goulbi', 'Korahane', 'Kornaka', 'Maiyara', 'Roumbou', 'Sabonmachi', 'Tagriss'],
    'Gazaoua'            => ['Gangara', 'Gazaoua'],
    'Guidan-Roumdji'     => ['Chadakori', 'Guidan Roumdji', 'Guidan Sori', 'Sae Saboua', 'Tibiri (Maradi)'],
    'Madarounfa'         => ['Dan Issa', 'Djirataoua', 'Gabi', 'Madarounfa', 'Safo', 'Serki Yama'],
    'Mayahi'             => ['Attantane', 'El Allassan Mairerey', 'Guidan Amoumoune', 'Issawane', 'Kanambakache', 'Mayahi', 'Serkin Haoussa', 'Tchake'],
    'Tessaoua'           => ['Baoudeta', 'Hawandawaki', 'Koona', 'Korgom', 'Maijirgui', 'Ourafane', 'Tessaoua'],
    'Ville De Maradi'    => ['Maradi 1', 'Maradi 2', 'Maradi 3'],

    // NIAMEY
    'Niamey Ville'       => ['Niamey I', 'Niamey II', 'Niamey III', 'Niamey IV', 'Niamey V'],

    // TAHOUA
    'Abalak'             => ['Abalak', 'Akoubounou', 'Azeye', 'Tabalak', 'Tamaya'],
    'Bagaroua'           => ['Bagaroua'],
    "Birni N'Konni"      => ['Allela', 'Bazaga', "Birni N'Konni", 'Tsernaoua'],
    'Bouza'              => ['Allakeye', 'Baban Katami', 'Bouza', 'Deoule', 'Karofane', 'Tabotaki', 'Tama'],
    'Illéla'             => ['Badaguichiri', 'Illela', 'Tajae'],
    'Keita'              => ['Garhanga', 'Ibohamane', 'Keita', 'Tamaske'],
    'Madaoua'            => ['Azarori', 'Bangui', 'Galma Koudawatche', 'Madaoua', 'Ourno', 'Sabon Guida'],
    'Malbaza'            => ['Dogueraoua', 'Malbaza'],
    'Tahoua Departement' => ['Afala', 'Bambeye', 'Barmou', 'Kalfou', 'Takanamatt', 'Tebaram'],
    'Takeita'            => ['Dakoussa', 'Garagoumsa', 'Tirmini'],
    'Tillia'             => ['Tillia'],
    'Ville De Tahoua'    => ['Tahoua Commune 1', 'Tahoua Commune 2'],

    // TILLABERI
    'Abala'              => ['Abala', 'Sanam'],
    'Ayorou'             => ['Ayorou', 'Inattes'],
    'Balleyara'          => ['Tagazar'],
    'Banibangou'         => ['Banibangou'],
    'Bankilare'          => ['Bankilare'],
    'Filingue'           => ['Damana', 'Filingue', 'Imanan', 'Kourfeye Centre'],
    'Gotheye'            => ['Dargol', 'Gotheye'],
    'Kollo'              => ['Bitinkodji', 'Dantchandou', 'Hamdallaye', 'Karma', 'Kirtachi', 'Kollo', 'Koure', 'Libore', "N'Dounga", 'Namaro', 'Youri'],
    'Ouallam'            => ['Dingazi Banda', 'Ouallam', 'Simiri', 'Tondikiwindi'],
    'Say'                => ['Ouro Gueladio', 'Say', 'Tamou'],
    'Tera'               => ['Diagourou', 'Goroual', 'Kokorou', 'Mehana', 'Tera'],
    'Tillaberi'          => ['Anzourou', 'Bibiyergou', 'Dessa', 'Kourteye', 'Sakoira', 'Sinder', 'Tillaberi'],
    'Torodi'             => ['Makalondi', 'Torodi'],

    // ZINDER
    'Belbedji'           => ['Tarka'],
    'Bermo'              => ['Bermo', 'Gadabedji'],
    'Damagaram Takaya'   => ['Alberkaram', 'Damagaram Takaya', 'Guidimouni', 'Kagna Wame', 'Mazamni', 'Moa'],
    'Dungass'            => ['Dogo Dogo', 'Dungass', 'Gouchi', 'Mallaoua'],
    'Goure'              => ['Alakos', 'Boune', 'Gamou', 'Goure', 'Guidiguir', 'Kelle'],
    'Kantche'            => ['Dan Barto', 'Daoutche', 'Doungou', 'Ichernaoua', 'Kantche', 'Kourni', 'Matameye', 'Tsouni', 'Yaouri'],
    'Magaria'            => ['Bande', 'Dan Tchio', 'Kouaya', 'Magaria', 'Sassoumbroum', 'Wacha', 'Yekoua'],
    'Mirriah'            => ['Dala Koleram', 'Dogo', 'Droum', 'Gaffati', 'Gouna', 'Hamdara', 'Mirriah', 'Zermou'],
    'Tanout'             => ['Falenco', 'Gangara', 'Ollelewa', 'Tanout', 'Tenhya'],
    'Tesker'             => ['Tesker'],
    'Ville De Zinder'    => ['Zinder I', 'Zinder II', 'Zinder III', 'Zinder IV', 'Zinder V'],
];

// ─── Centres approximatifs des régions (longitude, latitude) ─────────────────
$region_centers = [
    'Agadez'    => ['lon' =>  8.0,  'lat' => 20.0],
    'Diffa'     => ['lon' => 13.3,  'lat' => 13.3],
    'Dosso'     => ['lon' =>  3.2,  'lat' => 13.0],
    'Maradi'    => ['lon' =>  7.1,  'lat' => 13.5],
    'Niamey'    => ['lon' =>  2.1,  'lat' => 13.5],
    'Tahoua'    => ['lon' =>  5.3,  'lat' => 14.9],
    'Tillaberi' => ['lon' =>  2.5,  'lat' => 14.2],
    'Zinder'    => ['lon' =>  8.9,  'lat' => 13.8],
];
