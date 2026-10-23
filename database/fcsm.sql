-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 30 avr. 2026 à 12:46
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `fcsm`
--

-- --------------------------------------------------------

--
-- Structure de la table `domicile`
--

DROP TABLE IF EXISTS `domicile`;
CREATE TABLE IF NOT EXISTS `domicile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('principale','secondaire') COLLATE utf8mb4_unicode_ci DEFAULT 'principale',
  `id_etudiant` int NOT NULL,
  `id_lieu` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`),
  KEY `id_lieu` (`id_lieu`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `domicile`
--

INSERT INTO `domicile` (`id`, `type`, `id_etudiant`, `id_lieu`) VALUES
(1, 'principale', 1, 10),
(2, 'principale', 2, 11),
(3, 'principale', 3, 12),
(4, 'principale', 4, 13),
(5, 'principale', 5, 14),
(6, 'principale', 6, 15),
(7, 'principale', 7, 16);

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `groupe` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ex: Groupe A, Groupe B',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`id`, `nom`, `prenom`, `email`, `password`, `groupe`) VALUES
(1, 'Martin', 'Lucas', 'lucas.martin@ufc', 'butrt', 'GB1'),
(2, 'Bernard', 'Emma', 'emma.bernard@ufc', 'butrt', 'GB1'),
(3, 'Petit', 'Hugo', 'hugo.petit@ufc', 'butrt', 'GB1'),
(4, 'Durand', 'Léa', 'lea.durand@ufc', 'butrt', 'LK1'),
(5, 'Moreau', 'Nathan', 'nathan.moreau@ufc', 'butrt', 'GB2'),
(6, 'Laurent', 'Chloé', 'chloe.laurent@ufc', 'butrt', 'LK2'),
(7, 'Simon', 'Théo', 'theo.simon@ufc', 'butrt', 'GB2');

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

DROP TABLE IF EXISTS `horaire`;
CREATE TABLE IF NOT EXISTS `horaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_etudiant` int NOT NULL,
  `id_lieu` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'travail, loisir, courses',
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`),
  KEY `id_lieu` (`id_lieu`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `horaire`
--

INSERT INTO `horaire` (`id`, `jour_semaine`, `heure_debut`, `heure_fin`, `id_etudiant`, `id_lieu`, `type`) VALUES
(1, 'lundi', '08:00:00', '12:00:00', 1, 1, 'travail'),
(2, 'mardi', '10:00:00', '14:00:00', 1, 1, 'travail'),
(3, 'jeudi', '08:00:00', '18:00:00', 1, 1, 'travail'),
(4, 'vendredi', '08:00:00', '12:00:00', 1, 1, 'travail'),
(5, 'lundi', '08:00:00', '12:00:00', 2, 1, 'travail'),
(6, 'mardi', '10:00:00', '14:00:00', 2, 1, 'travail'),
(7, 'mercredi', '14:00:00', '18:00:00', 2, 1, 'travail'),
(8, 'lundi', '08:00:00', '12:00:00', 3, 1, 'travail'),
(9, 'mercredi', '08:00:00', '12:00:00', 3, 1, 'travail'),
(10, 'jeudi', '14:00:00', '18:00:00', 3, 1, 'travail'),
(11, 'lundi', '08:15:00', '12:00:00', 4, 1, 'travail'),
(12, 'jeudi', '08:00:00', '18:00:00', 4, 1, 'travail'),
(13, 'lundi', '08:00:00', '12:00:00', 5, 1, 'travail'),
(14, 'mardi', '08:00:00', '12:00:00', 5, 1, 'travail'),
(15, 'vendredi', '08:00:00', '12:00:00', 5, 1, 'travail'),
(16, 'lundi', '10:00:00', '14:00:00', 6, 1, 'travail'),
(17, 'mercredi', '08:00:00', '12:00:00', 6, 1, 'travail'),
(18, 'lundi', '08:00:00', '12:00:00', 7, 1, 'travail'),
(19, 'vendredi', '14:00:00', '18:00:00', 7, 1, 'travail'),
(20, 'vendredi', '20:00:00', '22:30:00', 1, 2, 'loisir'),
(21, 'vendredi', '20:00:00', '22:30:00', 2, 2, 'loisir'),
(22, 'vendredi', '20:00:00', '22:30:00', 5, 2, 'loisir'),
(23, 'vendredi', '20:00:00', '22:30:00', 7, 2, 'loisir');

-- --------------------------------------------------------

--
-- Structure de la table `lieu`
--

DROP TABLE IF EXISTS `lieu`;
CREATE TABLE IF NOT EXISTS `lieu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'domicile, universite, loisir, courses',
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `lieu`
--

INSERT INTO `lieu` (`id`, `nom`, `adresse`, `ville`, `type`, `code_postal`, `longitude`, `latitude`) VALUES
(1, 'Dpt R&T Montbéliard', 'Rue Engel-Gros', 'Montbéliard', 'universite', '25200', 6.79826000, 47.50558000),
(2, 'Stade Bonal (FCSM)', 'Route de Besançon', 'Sochaux', 'loisir', '25600', 6.78900000, 47.49420000),
(3, 'Carrefour Audincourt', 'Rue de Seloncourt', 'Audincourt', 'courses', '25400', 6.84210000, 47.48280000),
(4, 'E.Leclerc Montbéliard', 'Rue Jacques Foillet', 'Montbéliard', 'courses', '25200', 6.79340000, 47.51820000),
(10, 'Domicile Lucas Martin', '12 rue des Acacias', 'Montbéliard', 'domicile', '25200', 6.80120000, 47.51020000),
(11, 'Domicile Emma Bernard', '3 avenue Gambetta', 'Audincourt', 'domicile', '25400', 6.83710000, 47.48990000),
(12, 'Domicile Hugo Petit', '4 rue des Jardins', 'Valentigney', 'domicile', '25700', 6.83200000, 47.47650000),
(13, 'Domicile Léa Durand', '22 rue Victor Hugo', 'Montbéliard', 'domicile', '25200', 6.79500000, 47.50200000),
(14, 'Domicile Nathan Moreau', '5 impasse des Lilas', 'Exincourt', 'domicile', '25400', 6.81900000, 47.49500000),
(15, 'Domicile Chloé Laurent', '18 rue du Général', 'Bart', 'domicile', '25420', 6.78400000, 47.52100000),
(16, 'Domicile Théo Simon', '9 rue des Fleurs', 'Sochaux', 'domicile', '25600', 6.79100000, 47.49600000);

-- --------------------------------------------------------

--
-- Structure de la table `membre_equipage`
--

DROP TABLE IF EXISTS `membre_equipage`;
CREATE TABLE IF NOT EXISTS `membre_equipage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_trajet` int NOT NULL,
  `id_etudiant` int NOT NULL,
  `role` enum('conducteur','passager') COLLATE utf8mb4_unicode_ci DEFAULT 'passager',
  `valide_aller` tinyint(1) DEFAULT '0',
  `valide_retour` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_membre` (`id_trajet`,`id_etudiant`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `membre_equipage`
--

INSERT INTO `membre_equipage` (`id`, `id_trajet`, `id_etudiant`, `role`, `valide_aller`, `valide_retour`) VALUES
(1, 1, 3, 'conducteur', 1, 1),
(2, 1, 5, 'passager', 1, 0),
(3, 2, 2, 'conducteur', 1, 0),
(4, 3, 1, 'conducteur', 1, 1),
(5, 3, 2, 'passager', 1, 1),
(6, 3, 7, 'passager', 1, 1),
(7, 4, 5, 'conducteur', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `trajet`
--

DROP TABLE IF EXISTS `trajet`;
CREATE TABLE IF NOT EXISTS `trajet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_trajet` date NOT NULL,
  `heure_depart` time NOT NULL,
  `heure_arrivee_estimee` time DEFAULT NULL,
  `heure_retour_depart` time DEFAULT NULL,
  `heure_retour_arrivee` time DEFAULT NULL,
  `type` enum('aller','retour','aller-retour') COLLATE utf8mb4_unicode_ci DEFAULT 'aller',
  `estPlein` tinyint(1) DEFAULT '0',
  `id_createur` int NOT NULL,
  `id_lieu_depart` int NOT NULL,
  `id_lieu_arrivee` int NOT NULL,
  `id_vehicule` int DEFAULT NULL,
  `participation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ex: 1€/trajet ou libre',
  `remarques` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id_createur` (`id_createur`),
  KEY `id_lieu_depart` (`id_lieu_depart`),
  KEY `id_lieu_arrivee` (`id_lieu_arrivee`),
  KEY `id_vehicule` (`id_vehicule`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `trajet`
--

INSERT INTO `trajet` (`id`, `date_trajet`, `heure_depart`, `heure_arrivee_estimee`, `heure_retour_depart`, `heure_retour_arrivee`, `type`, `estPlein`, `id_createur`, `id_lieu_depart`, `id_lieu_arrivee`, `id_vehicule`, `participation`, `remarques`) VALUES
(1, '2026-11-02', '07:30:00', '07:45:00', '12:15:00', '12:30:00', 'aller-retour', 0, 3, 12, 1, 4, '2 €', 'Départ devant la boulangerie'),
(2, '2026-11-02', '07:35:00', '07:50:00', NULL, NULL, 'aller', 0, 2, 11, 1, 2, 'gratuit', NULL),
(3, '2026-11-06', '19:15:00', '19:30:00', '22:45:00', '23:00:00', 'aller-retour', 0, 1, 10, 2, 1, '1 €', 'Match du FCSM au stade Bonal'),
(4, '2026-11-04', '17:30:00', '17:45:00', '18:30:00', '18:45:00', 'aller-retour', 0, 5, 14, 3, 3, 'libre', 'Courses de la semaine');

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

DROP TABLE IF EXISTS `vehicule`;
CREATE TABLE IF NOT EXISTS `vehicule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `immatriculation` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nb_places` int NOT NULL DEFAULT '5',
  `id_etudiant` int NOT NULL,
  `marque` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modele` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_ct` date DEFAULT NULL COMMENT 'Date validité contrôle technique',
  `date_assurance` date DEFAULT NULL COMMENT 'Date échéance assurance',
  PRIMARY KEY (`id`),
  UNIQUE KEY `immatriculation` (`immatriculation`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`id`, `immatriculation`, `nb_places`, `id_etudiant`, `marque`, `modele`, `date_ct`, `date_assurance`) VALUES
(1, 'AB-123-CD', 5, 1, 'Peugeot', '208', '2026-03-15', '2026-01-01'),
(2, 'EF-456-GH', 5, 2, 'Renault', 'Clio', '2025-11-20', '2026-01-01'),
(3, 'IJ-789-KL', 4, 5, 'Citroën', 'C3', '2026-07-10', '2026-01-01'),
(4, 'AB-122-AB', 3, 3, 'Porsche', 'GT3 RS', '2026-04-09', '2026-04-04');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
