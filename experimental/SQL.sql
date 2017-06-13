-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1
-- Généré le: Sam 01 Juin 2013 à 00:13
-- Version du serveur: 5.5.27
-- Version de PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `sms`
--

-- --------------------------------------------------------

--
-- Structure de la table `ac`
--

CREATE TABLE IF NOT EXISTS `ac` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(25) DEFAULT NULL,
  `prenom` varchar(25) DEFAULT NULL,
  `domicile` varchar(25) DEFAULT NULL,
  `ac_phone` varchar(25) DEFAULT NULL,
  `centreID` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Contenu de la table `ac`
--

INSERT INTO `ac` (`id`, `nom`, `prenom`, `domicile`, `ac_phone`, `centreID`) VALUES
(1, 'White', 'Patrick', 'Boke', '+224000000000', '1'),
(2, 'White', 'Patrick', 'Conakry', '+224000000000', '6'),
(3, 'Diallo', 'Bhoye', 'lansanaya', '+224000000000', '3'),
(5, 'CAMARA', 'Fatoumata', NULL, '+224000000000', '2'),
(6, 'TAMBASSA', 'Amara', NULL, '+224000000000', '2'),
(7, 'NIAISSA', 'Amadou', NULL, '+224000000000', '1'),
(8, 'NIAISSA', 'Alya', NULL, '+224000000000', '1'),
(9, 'SOW', 'Mamadou Yaya', NULL, '+224000000000', '2'),
(10, 'SALL', 'Abdoul Karim', NULL, '+224000000000', '2'),
(11, 'Niassa', 'Babadi', NULL, '+224000000000', '2'),
(12, 'Saa Abel', 'Tinguiano', NULL, '+224000000000', '2'),
(13, 'White', 'Patrick', NULL, '+224000000000', '8');

-- --------------------------------------------------------

--
-- Structure de la table `asaq`
--

CREATE TABLE IF NOT EXISTS `asaq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_envoyeur` varchar(25) DEFAULT NULL,
  `numero_envoyeur` varchar(25) DEFAULT NULL,
  `keyword` varchar(25) DEFAULT NULL,
  `nourrisson` varchar(3) DEFAULT NULL,
  `petit_enfant` varchar(3) DEFAULT NULL,
  `enfant` varchar(3) DEFAULT NULL,
  `adulte` varchar(3) DEFAULT NULL,
  `tdr` varchar(5) DEFAULT NULL,
  `huere_message` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Contenu de la table `asaq`
--

INSERT INTO `asaq` (`id`, `nom_envoyeur`, `numero_envoyeur`, `keyword`, `nourrisson`, `petit_enfant`, `enfant`, `adulte`, `tdr`, `huere_message`) VALUES
(7, 'Bhoye', '+224000000000', 'Asaq', '2', '4', '6', '8', '2', '2013-05-25 11:48:28'),
(8, 'Bhoye', '+224000000000', 'Asaq', '2', '20', '19', '2', '5', '2013-05-26 12:49:15'),
(9, 'Amadou Niassa', '+224000000000', 'ASAQ', '14', '25', '25', '15', '07', '2013-05-28 19:37:30'),
(10, 'Abdul karim Sall', '+224000000000', 'ASAQ', '10', '15', '2', '3', '40', '2013-05-28 19:46:27'),
(11, 'Yaya Sow', '+224000000000', 'ASAQ', '5', '5', '5', '5', '5', '2013-05-28 19:55:03'),
(12, 'Alya Niassa 1', '+224000000000', 'ASAQ', '14', '25', '25', '15', '7', '2013-05-28 20:34:09'),
(13, 'Sanoussi Mamadouba CAMARA', '+224000000000', 'ASAQ', '20', '15', '10', '05', '05', '2013-05-28 21:15:29'),
(14, 'Bhoye', '+224000000000', 'Asaq', '25', '25', '10', '5', '10', '2013-05-29 17:36:57'),
(15, '+224000000000', '+224000000000', 'ASAQ', '10', '14', '23', '32', '42', '2013-05-29 20:30:32'),
(16, 'Bhoye', '+224000000000', 'Asaq', '5', '8', '0', '2', '10', '2013-05-30 22:46:48'),
(17, 'Amara Coubassa', '+224000000000', 'Asaq', '5', '8', '0', '2', '10', '2013-05-30 22:53:11'),
(18, 'Nancy Camara', '+224000000000', 'Asaq', '8', '20', '99', '77', '55', '2013-05-30 23:05:30');

-- --------------------------------------------------------

--
-- Structure de la table `centre_sante`
--

CREATE TABLE IF NOT EXISTS `centre_sante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centre` varchar(25) DEFAULT NULL,
  `chef` varchar(25) DEFAULT NULL,
  `numero` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Contenu de la table `centre_sante`
--

INSERT INTO `centre_sante` (`id`, `centre`, `chef`, `numero`) VALUES
(1, 'Dibiya', 'Abdul Rakhman Bangoura', '+224000000000'),
(2, 'Koulefanyah', 'Tinguiano Saa Abel', '+224000000000'),
(3, 'Boulbinet', 'aissata fofana', '+224000000000'),
(4, 'cimenterie', 'mariama', '224621193616'),
(5, 'TEST', 'DIALLO IT', '+224000000000'),
(6, 'TEST2', 'DIALLO IT', '+224000000000'),
(7, 'Stage House', 'Patrick White', '+224000000000'),
(8, '400', 'Patrick White', '+224000000000');

-- --------------------------------------------------------

--
-- Structure de la table `pec`
--

CREATE TABLE IF NOT EXISTS `pec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_envoyeur` varchar(25) DEFAULT NULL,
  `numero_envoyeur` varchar(25) DEFAULT NULL,
  `keyword` varchar(25) DEFAULT NULL,
  `tdr_realise` varchar(4) DEFAULT NULL,
  `tdr_positif` varchar(4) DEFAULT NULL,
  `patient_traite` varchar(4) DEFAULT NULL,
  `patient_referee` varchar(4) DEFAULT NULL,
  `huere_message` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Contenu de la table `pec`
--

INSERT INTO `pec` (`id`, `nom_envoyeur`, `numero_envoyeur`, `keyword`, `tdr_realise`, `tdr_positif`, `patient_traite`, `patient_referee`, `huere_message`) VALUES
(1, 'Pat', '+224000000000', 'Pec', '1', '1', '1', '0', '2013-05-24 16:34:13'),
(2, 'Pat', '+224000000000', 'Pec', '9', '5', '1', '4', '2013-05-24 16:34:28'),
(3, 'Bhoye', '+224000000000', 'Pec', '1', '2', '3', '0', '2013-05-24 16:34:38'),
(4, 'Pat', '+224000000000', 'Pec', '1', '1', '1', '0', '2013-05-24 16:34:48'),
(5, 'Pat', '+224000000000', 'Pec', '1', '1', '1', '0', '2013-05-24 16:37:28'),
(6, 'Babadi Niassa', '+224000000000', 'PEC', '20', '14', '12', '2', '2013-05-28 21:10:01'),
(7, 'Yaya Sow', '+224000000000', 'PEC', '18', '12', '11', '1', '2013-05-28 21:12:27'),
(8, 'Amadou Niassa', '+224000000000', 'Pec', '20', '14', '12', '2', '2013-05-28 21:17:56'),
(9, 'Bhoye', '+224000000000', 'Pec', '12', '11', '6', '3', '2013-05-28 21:29:17'),
(10, '+224000000000', '+224000000000', 'PEC', '10', '8', '7', '1', '2013-05-28 21:30:51'),
(11, 'Alya Niassa 1', '+224000000000', 'PEC', '20', '14', '12', '2', '2013-05-28 21:32:10'),
(12, 'Patrick3', '+224000000000', 'PEC', '10', '8', '7', '1', '2013-05-29 16:31:56'),
(13, 'Nancy Camara', '+224000000000', 'Pec', '20', '14', '12', '2', '2013-05-30 23:00:29'),
(14, 'Amara Coubassa', '+224000000000', 'Pec', '3', '4', '5', '2', '2013-05-30 23:05:40'),
(15, 'Nancy Camara', '+224000000000', 'Pec', '20', '14', '12', '2', '2013-05-30 23:25:41'),
(16, 'Nancy Camara', '+224000000000', 'Pec', '20', '14', '12', '2', '2013-05-30 23:34:33'),
(17, 'Amara Coubassa', '+224000000000', 'Pec', '3', '4', '5', '2', '2013-05-31 17:54:42');

-- --------------------------------------------------------

--
-- Structure de la table `vad`
--

CREATE TABLE IF NOT EXISTS `vad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_envoyeur` varchar(25) DEFAULT NULL,
  `numero_envoyeur` varchar(25) DEFAULT NULL,
  `keyword` varchar(25) DEFAULT NULL,
  `vad_total` varchar(4) DEFAULT NULL,
  `hommes` varchar(4) DEFAULT NULL,
  `femmes` varchar(4) DEFAULT NULL,
  `total` varchar(4) DEFAULT NULL,
  `huere_message` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- Contenu de la table `vad`
--

INSERT INTO `vad` (`id`, `nom_envoyeur`, `numero_envoyeur`, `keyword`, `vad_total`, `hommes`, `femmes`, `total`, `huere_message`) VALUES
(7, 'Amadou Niassa', '+224000000000', 'VAD', '3', '8', '12', '20', '2013-05-28 20:34:24'),
(8, 'Babadi Niassa', '+224000000000', 'VAD', '2', '5', '13', '18', '2013-05-28 20:58:59'),
(9, 'Amadou Niassa', '+224000000000', 'VAD', '2', '9', '13', '22', '2013-05-28 21:08:58'),
(10, 'Yaya Sow', '+224000000000', 'VAD', '8', '3', '15', '18', '2013-05-28 21:10:43'),
(11, 'Bhoye', '+224000000000', 'Vad', '3', '2', '1', '3', '2013-05-28 21:21:24'),
(12, 'Bhoye', '+224000000000', 'Vad', '3', '14', '25', '39', '2013-05-28 21:24:05'),
(13, 'Bhoye', '+224000000000', 'vad', '3', '14', '25', '39', '2013-05-28 21:26:00'),
(14, 'Alya Niassa 1', '+224000000000', 'VAD', '2', '5', '13', '18', '2013-05-28 21:29:59'),
(15, 'Bhoye', '+224000000000', 'Vad', '3', '20', '33', '53', '2013-05-28 21:36:25'),
(16, 'Patrick3', '+224000000000', 'vad', '2', '14', '19', '33', '2013-05-29 16:31:20'),
(17, 'Patrick3', '+224000000000', 'VAD', '1', '3', '8', '11', '2013-05-29 16:31:30'),
(18, 'Patrick3', '+224000000000', 'VAD', '2', '5', '8', '13', '2013-05-29 16:32:06'),
(19, 'Bhoye', '+224000000000', 'Vad', '12', '4', '5', '9', '2013-05-29 17:39:29'),
(20, 'Fatoumata Camara', '+224000000000', 'VAD', '3', '11', '32', '43', '2013-05-29 17:53:32'),
(21, '+224000000000', '+224000000000', 'VAD', '4', '17', '24', '41', '2013-05-29 18:14:39'),
(22, 'Alya Niassa 1', '+224000000000', 'VAD', '4', '17', '18', '35', '2013-05-29 20:36:57'),
(23, 'Alya Niassa 1', '+224000000000', 'VAD', '4', '17', '18', '35', '2013-05-29 20:37:12'),
(24, 'Bhoye', '+224000000000', 'VAD', '3', '5', '8', '13', '2013-05-30 18:55:33'),
(25, 'Bhoye', '+224000000000', 'vad', '1', '13', '6', '19', '2013-05-30 19:58:16'),
(26, 'Bhoye', '+224000000000', 'Vad', '2', '6', '14', '20', '2013-05-30 20:45:57'),
(27, 'Bhoye', '+224000000000', 'VAD', '8', '2', '5', '7', '2013-05-30 22:47:56'),
(28, 'Patrick1', '+224000000000', 'Vad', '2', '5', '13', '18', '2013-05-30 22:48:06'),
(29, 'Amara Coubassa', '+224000000000', 'Vad', '8', '2', '5', '7', '2013-05-30 22:54:17'),
(30, 'Nancy Camara', '+224000000000', 'Vad', '6', '16', '50', '66', '2013-05-30 22:56:11'),
(31, 'Nancy Camara', '+224000000000', 'Vad', '1', '7', '3', '10', '2013-05-30 23:25:51');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
