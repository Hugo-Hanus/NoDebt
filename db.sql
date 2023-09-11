-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : Dim 07 août 2022 à 14:07
-- Version du serveur :  5.7.31
-- Version de PHP : 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nodebt`
--

-- --------------------------------------------------------

--
-- Structure de la table `caracteriser`
--

DROP TABLE IF EXISTS `caracteriser`;
CREATE TABLE IF NOT EXISTS `caracteriser` (
  `did` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  PRIMARY KEY (`did`,`tid`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `caracteriser`
--

INSERT INTO `caracteriser` (`did`, `tid`) VALUES
(1, 2),
(2, 2),
(3, 2),
(5, 3),
(6, 3);

-- --------------------------------------------------------

--
-- Structure de la table `depense`
--

DROP TABLE IF EXISTS `depense`;
CREATE TABLE IF NOT EXISTS `depense` (
  `did` int(11) NOT NULL AUTO_INCREMENT,
  `dateHeure` datetime DEFAULT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `libelle` varchar(50) DEFAULT NULL,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`did`),
  KEY `gid` (`gid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `depense`
--

INSERT INTO `depense` (`did`, `dateHeure`, `montant`, `libelle`, `gid`, `uid`) VALUES
(1, '2022-05-19 15:18:02', '100.00', 'code', 1, 2),
(2, '2022-05-20 15:18:15', '20.00', 'Hébergement', 1, 2),
(3, '2022-05-19 15:18:45', '30.00', 'Supplément', 1, 1),
(5, '2022-07-25 17:10:59', '50.00', 'Repas', 1, 10),
(6, '2022-08-05 13:53:54', '60.00', 'Boisson', 1, 10),
(12, '2022-08-07 15:26:39', '40.00', 'Location', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `facture`
--

DROP TABLE IF EXISTS `facture`;
CREATE TABLE IF NOT EXISTS `facture` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `scan` varchar(200) DEFAULT NULL,
  `did` int(11) NOT NULL,
  PRIMARY KEY (`fid`),
  KEY `did` (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

DROP TABLE IF EXISTS `groupe`;
CREATE TABLE IF NOT EXISTS `groupe` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) DEFAULT NULL,
  `devise` varchar(50) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `estSolder` tinyint(1) NOT NULL,
  PRIMARY KEY (`gid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`gid`, `nom`, `devise`, `uid`, `estSolder`) VALUES
(1, 'Site 1', '$', 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `groupement`
--

DROP TABLE IF EXISTS `groupement`;
CREATE TABLE IF NOT EXISTS `groupement` (
  `ggid` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) NOT NULL,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  PRIMARY KEY (`ggid`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `groupement`
--

INSERT INTO `groupement` (`ggid`, `libelle`, `uid`, `gid`) VALUES
(1, 'Moi-même', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `grouper`
--

DROP TABLE IF EXISTS `grouper`;
CREATE TABLE IF NOT EXISTS `grouper` (
  `ggid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`ggid`,`uid`),
  KEY `ggid` (`ggid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `grouper`
--

INSERT INTO `grouper` (`ggid`, `uid`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `participer`
--

DROP TABLE IF EXISTS `participer`;
CREATE TABLE IF NOT EXISTS `participer` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `estConfirmer` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`uid`,`gid`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `participer`
--

INSERT INTO `participer` (`uid`, `gid`, `estConfirmer`) VALUES
(1, 1, 1),
(2, 1, 1),
(10, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) DEFAULT NULL,
  `gid` int(11) NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `tag`
--

INSERT INTO `tag` (`tid`, `tag`, `gid`) VALUES
(2, 'Développement', 1),
(3, 'Alimentation', 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `courriel` varchar(100) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `motPasse` varchar(200) DEFAULT NULL,
  `estActif` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `courriel` (`courriel`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`uid`, `courriel`, `nom`, `prenom`, `motPasse`, `estActif`) VALUES
(1, 'h.hanus@student.helmo.be', 'HanusHelmo', 'HugoHelmo', '$2y$10$UQLJunkgZuD0JqTt5G0kGuiaCmArYIxIudcY0erABn0hQaiomkDTe', 1),
(2, 'hanushugo@gmail.com', 'Hanus', 'Hugo', '$2y$10$om5oLGgXTol9dil8yfu4NehaqFuPUrCInI/CAMvEYy1ZDSLbntO6e', 1),
(10, 'monkikTV@gmail.Com', 'Lapierre', 'Tchan', '$2y$10$vTnJyeswsELNF4OcuZyTGeryhRmSSjdbsdAJFm8L4xYc/.N9bhX8S', 1);

-- --------------------------------------------------------

--
-- Structure de la table `versement`
--

DROP TABLE IF EXISTS `versement`;
CREATE TABLE IF NOT EXISTS `versement` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `uid_1` int(11) NOT NULL,
  `dateHeure` datetime NOT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `estConfirme` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`gid`,`uid`,`uid_1`,`dateHeure`),
  KEY `uid` (`uid`),
  KEY `uid_1` (`uid_1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `caracteriser`
--
ALTER TABLE `caracteriser`
  ADD CONSTRAINT `caracteriser_ibfk_1` FOREIGN KEY (`did`) REFERENCES `depense` (`did`),
  ADD CONSTRAINT `caracteriser_ibfk_2` FOREIGN KEY (`tid`) REFERENCES `tag` (`tid`);

--
-- Contraintes pour la table `depense`
--
ALTER TABLE `depense`
  ADD CONSTRAINT `depense_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groupe` (`gid`),
  ADD CONSTRAINT `depense_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`);

--
-- Contraintes pour la table `facture`
--
ALTER TABLE `facture`
  ADD CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`did`) REFERENCES `depense` (`did`);

--
-- Contraintes pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD CONSTRAINT `groupe_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`);

--
-- Contraintes pour la table `groupement`
--
ALTER TABLE `groupement`
  ADD CONSTRAINT `groupement_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`),
  ADD CONSTRAINT `groupement_ibfk_2` FOREIGN KEY (`gid`) REFERENCES `groupe` (`gid`);

--
-- Contraintes pour la table `grouper`
--
ALTER TABLE `grouper`
  ADD CONSTRAINT `grouper_ibfk_1` FOREIGN KEY (`ggid`) REFERENCES `groupement` (`ggid`),
  ADD CONSTRAINT `grouper_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`);

--
-- Contraintes pour la table `participer`
--
ALTER TABLE `participer`
  ADD CONSTRAINT `participer_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`),
  ADD CONSTRAINT `participer_ibfk_2` FOREIGN KEY (`gid`) REFERENCES `groupe` (`gid`);

--
-- Contraintes pour la table `tag`
--
ALTER TABLE `tag`
  ADD CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groupe` (`gid`);

--
-- Contraintes pour la table `versement`
--
ALTER TABLE `versement`
  ADD CONSTRAINT `versement_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groupe` (`gid`),
  ADD CONSTRAINT `versement_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`uid`),
  ADD CONSTRAINT `versement_ibfk_3` FOREIGN KEY (`uid_1`) REFERENCES `utilisateur` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
