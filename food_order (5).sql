-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 18 oct. 2025 à 09:58
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `food_order`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_login`
--

DROP TABLE IF EXISTS `admin_login`;
CREATE TABLE IF NOT EXISTS `admin_login` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_login`
--

INSERT INTO `admin_login` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$roPOqeJCMbrlKyIgxDZhEeg4sSYmQ7AZHDN3IxFW0xvBVvcs9hrCC', 'admin'),
(3, 'Pape', '$2y$10$T/5U1yIpKeLE2mndKX6vFe79XgrSgN84sve7aMlwBgrIXnasG9lrG', 'patron'),
(4, 'SEB', '$2y$10$LJcxUJnzu.OGBJ/U6fdr5Ot4HSotMKaLp9gFVnqeZBPJA4qtP4W2e', 'gérant'),
(5, 'papep', '$2y$10$lBCo9Z6LvifcLSjkv4x91O8.rwZxy9pHGH/aG38V5SUaJKd/Xh70C', 'patron'),
(6, 'Collet', '$2y$10$4dMgujCpiTvGN/OLp0Leu.L7zTvk80JQ72QRBnBFELmKyCJrmuHK2', 'patron');

-- --------------------------------------------------------

--
-- Structure de la table `food`
--

DROP TABLE IF EXISTS `food`;
CREATE TABLE IF NOT EXISTS `food` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_original_price` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_discount_price` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_avaibility` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_veg_nonveg` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_ingredients` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_image` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `food`
--

INSERT INTO `food` (`id`, `food_name`, `food_category`, `food_description`, `food_original_price`, `food_discount_price`, `food_avaibility`, `food_veg_nonveg`, `food_ingredients`, `food_image`, `is_active`) VALUES
(5, 'Tiramitsu', 'Dessert', 'Magnifique', '10 €', '8 €', 'Yes', 'Veg', 'lait, café, chocolat\r\n', 'images/db3e3857806393de8a2b2870d8c59167.png', 1),
(4, 'Pasta  ', 'Plat', 'Des pates', '5 €', '4 €', 'Yes', 'NonVeg', 'tomatoes', 'images/51a2295004eab3b2a862590e0cae17ca.png', 1),
(6, 'Margarita', 'Plat', 'Simple et efficace', '15 €', '10 €', 'Yes', 'Veg', 'tomatoes', 'images/9047db0a70b253265f9569c5d3a12a89.png', 1),
(7, 'Steak Haché', 'Plat', 'Magnifique steak servi avec des champignons le tout sur une salade\r\n', '10 €', '8 €', 'Yes', 'NonVeg', '', 'images/c17d9f0268d2fc2319e6d1b1f87143f9.png', 1),
(8, 'Salade caesar', 'Salade', 'tres bonne salade repli de vie', '10 €', '8 €', 'Yes', 'Veg', 'concombre,salade,tomatoes', 'images/77ff637d489bb0e180da33663d9c5ceb.png', 1);

-- --------------------------------------------------------

--
-- Structure de la table `food_categories`
--

DROP TABLE IF EXISTS `food_categories`;
CREATE TABLE IF NOT EXISTS `food_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_categories` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=armscii8;

--
-- Déchargement des données de la table `food_categories`
--

INSERT INTO `food_categories` (`id`, `food_categories`) VALUES
(20, 'Menu du jour'),
(19, 'Salade'),
(14, 'Entree'),
(17, 'Plat'),
(18, 'Dessert');

-- --------------------------------------------------------

--
-- Structure de la table `food_ingredients`
--

DROP TABLE IF EXISTS `food_ingredients`;
CREATE TABLE IF NOT EXISTS `food_ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_ingredients` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=armscii8;

--
-- Déchargement des données de la table `food_ingredients`
--

INSERT INTO `food_ingredients` (`id`, `food_ingredients`) VALUES
(1, 'tomatoes'),
(2, 'cake'),
(4, 'concombre'),
(5, 'salade');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT 'En attente',
  `restaurant_id` int DEFAULT NULL,
  `table_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` varchar(255) DEFAULT NULL,
  `order_type` varchar(50) NOT NULL DEFAULT 'table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `payment_method`, `total_price`, `order_date`, `status`, `restaurant_id`, `table_id`, `created_at`, `user_id`, `order_type`) VALUES
(1, 'CMD123456', 'Se', 'Especes', 8.00, '2025-08-04 16:05:39', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(3, 'CMD5DCBC2', 'A', 'Especes', 8.00, '2025-08-05 08:23:50', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(4, 'CMD363275', 'Boris', 'PayPal', 8.00, '2025-08-05 12:22:22', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(5, 'CMD85A82A', 'Salut', 'Carte', 42.00, '2025-08-05 12:23:05', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(6, 'CMD86F1B3', 'Bro', 'Carte', 22.00, '2025-08-05 12:23:40', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(7, 'CMD583E4D', 'Bonjour', 'Especes', 44.00, '2025-08-05 15:47:06', 'Terminée', 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(8, 'CMDEFFE05', 'Barbara', 'Especes', 4.00, '2025-08-05 15:50:10', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(9, 'CMD68ADC1', 'Nicos', 'Carte', 24.00, '2025-08-05 15:53:13', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(10, 'CMD980C34', 'SEBY', 'PayPal', 148.00, '2025-08-05 16:09:38', 'Terminée', 1, 20, '2025-08-13 11:29:38', NULL, 'table'),
(11, 'CMD58D75E', 'Seby', 'Especes', 4.00, '2025-08-05 16:11:14', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(12, 'CMD5873FF', 'Seb', 'Especes', 8.00, '2025-08-05 21:40:37', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(13, 'CMDD0A990', 'Manon', 'Especes', 12.00, '2025-08-05 21:43:26', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(14, 'CMD9788A0', 'SEB', 'Especes', 14.00, '2025-08-05 21:46:47', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(15, 'CMD6D6C71', 'A', 'Especes', 4.00, '2025-08-05 21:47:21', 'Terminée', 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(16, 'CMD9CAAE8', 'Seb', 'Especes', 4.00, '2025-08-06 12:59:11', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(17, 'CMD4AF6E1', 'Seb', 'Especes', 4.00, '2025-08-06 13:04:52', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(18, 'CMD5CF7C8', 'Seb', 'Especes', 20.00, '2025-08-06 13:12:59', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(19, 'CMDD57DAD', 'Logan', 'Carte', 20.00, '2025-08-07 11:16:43', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(20, 'CMD7891C5', 'SebY', 'Especes', 10.00, '2025-08-07 11:20:51', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(21, 'CMD6BDFA9', 'Marchal', 'Carte', 4.00, '2025-08-07 11:29:00', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(22, 'CMDD29FEC', 'BRO', 'Especes', 4.00, '2025-08-07 15:01:44', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(23, 'CMDAD54CD', 'Seb', 'Especes', 8.00, '2025-08-07 15:05:17', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(24, 'CMDC950E0', 'YES', 'Especes', 8.00, '2025-08-07 15:07:36', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(25, 'CMD9DE1F1', 'Gourmand', 'PayPal', 72.00, '2025-08-07 15:10:04', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(26, 'CMD5DCC5C', 'Pape', 'Carte', 120.00, '2025-08-07 15:14:26', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(27, 'CMD204DED', 'se', 'Especes', 4.00, '2025-08-07 15:19:31', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(28, 'CMD1C4A76', 'Manou', 'Carte', 22.00, '2025-08-07 22:51:26', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(29, 'CMDF86D6A', 'Seb', 'Especes', 4.00, '2025-08-07 22:53:10', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(30, 'CMD507969', 'Bro', 'PayPal', 4.00, '2025-08-07 22:55:09', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(31, 'CMD265CF2', 's', 'Especes', 4.00, '2025-08-07 23:02:22', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(32, 'CMD8850BD', 'Money', 'Carte', 28.00, '2025-08-07 23:04:16', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(33, 'CMD423712', 'Troll', 'Carte', 4.00, '2025-08-07 23:07:00', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(34, 'CMD23421C', 'e', 'Especes', 8.00, '2025-08-07 23:09:06', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(35, 'CMD1B306E', 'A', 'Especes', 4.00, '2025-08-07 23:15:10', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(36, 'CMDB0F422', 'ssss', 'Especes', 4.00, '2025-08-07 23:19:35', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(37, 'CMD5F0C9B', 'Seb', 'Especes', 36.00, '2025-08-10 10:45:02', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(38, 'CMD54CFB7', 'Seb', 'Especes', 44.00, '2025-08-10 14:16:57', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(39, 'CMD69C98E', 'SEB', 'Especes', 186.00, '2025-08-10 14:36:51', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(40, 'CMDD57BBD', 'SEB', 'Especes', 42.00, '2025-08-10 15:27:45', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(41, 'CMDD3542C', 'Seb', 'Carte', 4.00, '2025-08-10 15:29:49', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(42, 'CMDB2032F', 'seb', 'Especes', 8.00, '2025-08-10 15:32:35', 'Terminée', NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(50, 'CMDDEC69E', 'Seb', 'Espèces', 4.00, '2025-08-13 23:47:51', 'Terminée', NULL, 5, '2025-08-13 23:47:51', NULL, 'table'),
(51, 'CMDA97C95', 'Seb', 'Espèces', 8.00, '2025-08-13 23:49:02', 'Terminée', NULL, 5, '2025-08-13 23:49:02', NULL, 'table'),
(52, 'CMDC5225D', 'Seb', 'Espèces', 4.00, '2025-08-15 19:28:06', 'Terminée', NULL, 1, '2025-08-15 19:28:06', NULL, 'table'),
(53, 'CMDD815C2', 'Bro', 'Espèces', 14.00, '2025-08-15 19:28:31', 'Terminée', NULL, 1, '2025-08-15 19:28:31', NULL, 'table'),
(54, 'CMD50FE94', 'Yanis', 'Espèces', 14.00, '2025-08-15 19:29:16', 'Terminée', NULL, 8, '2025-08-15 19:29:16', NULL, 'table'),
(55, 'CMD98A32B', 'Seb', 'Espèces', 32.00, '2025-08-15 19:32:29', 'Terminée', NULL, 6, '2025-08-15 19:32:29', NULL, 'table'),
(56, 'CMD9E74AD', 'Seby', 'Espèces', 4.00, '2025-08-15 19:42:59', 'Terminée', NULL, 6, '2025-08-15 19:42:59', '0', 'table'),
(57, 'CMD076518', 'Seby', 'Espèces', 28.00, '2025-08-15 19:44:01', 'Terminée', NULL, 6, '2025-08-15 19:44:01', NULL, 'table'),
(58, 'CMD954ECC', 'Seb', 'Espèces', 4.00, '2025-08-15 19:46:00', 'Terminée', NULL, 1, '2025-08-15 19:46:00', 'user_689f71965b7e82.37210285', 'table'),
(59, 'CMD224D72', 'Seb', 'Espèces', 14.00, '2025-08-15 19:49:09', 'Terminée', NULL, 8, '2025-08-15 19:49:09', 'user_689f71965b7e82.37210285', 'table'),
(60, 'CMDBD23E6', 'Jeff', 'Espèces', 8.00, '2025-08-15 19:49:47', 'Terminée', NULL, 7, '2025-08-15 19:49:47', 'user_689f71965b7e82.37210285', 'table'),
(61, 'CMD93B4CE', 'Brother', 'Carte bancaire', 38.00, '2025-08-15 19:54:40', 'Terminée', NULL, 7, '2025-08-15 19:54:40', 'user_689f71965b7e82.37210285', 'table'),
(62, 'CMD04CE05', 'Brother', 'Espèces', 4.00, '2025-08-15 19:55:12', 'Terminée', NULL, 7, '2025-08-15 19:55:12', 'user_689f71965b7e82.37210285', 'table'),
(63, 'CMDB2C522', 'Seb', 'Carte bancaire', 8.00, '2025-08-15 19:55:30', 'Terminée', NULL, 7, '2025-08-15 19:55:30', 'user_689f71965b7e82.37210285', 'table'),
(64, 'CMDACA340', 'Seb', 'Espèces', 10.00, '2025-08-16 09:36:31', 'Terminée', NULL, 8, '2025-08-16 09:36:31', 'user_68a0324b56b1c5.52618241', 'table'),
(65, 'CMD925F38', 'SEBY', 'Carte bancaire', 14.00, '2025-08-16 09:37:40', 'Terminée', NULL, 20, '2025-08-16 09:37:40', 'user_68a0324b56b1c5.52618241', 'table'),
(66, 'CMD7C4924', 'Seb', 'Espèces', 148.00, '2025-08-16 09:55:02', 'Terminée', NULL, 20, '2025-08-16 09:55:02', 'user_68a0324b56b1c5.52618241', 'table'),
(67, 'CMDC68205', 'Karim', 'Carte bancaire', 18.00, '2025-08-16 10:12:23', 'Terminée', NULL, 20, '2025-08-16 10:12:23', 'user_68a0324b56b1c5.52618241', 'table'),
(68, 'CMDD752BA', 'Seb', 'Espèces', 4.00, '2025-08-16 10:49:39', 'Terminée', NULL, 20, '2025-08-16 10:49:39', 'user_68a0461a2be149.40269937', 'table'),
(69, 'CMD91BE2E', 'Seb', 'Espèces', 4.00, '2025-08-17 10:28:09', 'Terminée', NULL, 1, '2025-08-17 10:28:09', 'user_68a18fdbc785b3.50946943', 'table'),
(70, 'CMD04557A', 'KARIM', 'Carte bancaire', 4.00, '2025-08-17 10:32:49', 'Terminée', NULL, 1, '2025-08-17 10:32:49', 'user_68a18fdbc785b3.50946943', 'table'),
(71, 'CMD5B798A', 'BROTHER', 'Espèces', 14.00, '2025-08-17 10:35:48', 'Terminée', NULL, 1, '2025-08-17 10:35:48', 'user_68a18fdbc785b3.50946943', 'table'),
(72, 'CMDFE4CFA', 'SLD', 'Carte bancaire', 256.00, '2025-08-17 10:43:48', 'Terminée', NULL, 1, '2025-08-17 10:43:48', 'user_68a18fdbc785b3.50946943', 'table'),
(73, 'CMD934357', 'Pizza', 'Espèces', 360.00, '2025-08-17 10:44:31', 'Terminée', NULL, 1, '2025-08-17 10:44:31', 'user_68a18fdbc785b3.50946943', 'table'),
(74, 'CMDACED28', 'vitto', 'Carte bancaire', 370.00, '2025-08-17 10:44:58', 'Terminée', NULL, 1, '2025-08-17 10:44:58', 'user_68a18fdbc785b3.50946943', 'table'),
(75, 'CMD6801D7', 'Pape', 'Espèces', 30.00, '2025-08-18 09:51:32', 'Terminée', NULL, 8, '2025-08-18 09:51:32', 'user_68a2d799e19b06.18219295', 'table'),
(76, 'CMDC833E9', 'Seb', 'Espèces', 44.00, '2025-08-18 10:18:46', 'Terminée', NULL, 5, '2025-08-18 10:18:46', 'user_68a2d799e19b06.18219295', 'table'),
(77, 'CMD404504', 'A', 'Espèces', 36.00, '2025-08-18 10:47:27', 'Terminée', NULL, 0, '2025-08-18 10:47:27', 'user_68a2d799e19b06.18219295', 'takeaway'),
(78, 'CMD52CA30', 'Seb', 'Espèces', 4.00, '2025-08-18 10:47:51', 'Terminée', NULL, 0, '2025-08-18 10:47:51', 'user_68a2d799e19b06.18219295', 'takeaway'),
(79, 'CMD69EEAA', 'Manon', 'Espèces', 72.00, '2025-08-18 11:12:01', 'Terminée', NULL, 0, '2025-08-18 11:12:01', 'user_68a2d799e19b06.18219295', 'takeaway'),
(80, 'CMDDA171A', 'Bro', 'PayPal', 8.00, '2025-08-18 11:15:54', 'Terminée', NULL, 0, '2025-08-18 11:15:54', 'user_68a2ee9bafb607.55408287', 'takeaway'),
(81, 'CMD84A409', 'Seb', 'Espèces', 14.00, '2025-08-18 18:03:06', 'Terminée', NULL, 0, '2025-08-18 18:03:06', 'user_68a34ea3a82fd1.51125907', 'takeaway'),
(82, 'CMD1AA193', 'Barbara', 'Carte bancaire', 4.00, '2025-08-18 18:10:17', 'Terminée', NULL, 0, '2025-08-18 18:10:17', 'user_68a34ea3a82fd1.51125907', 'takeaway'),
(83, 'CMD6CAF42', 'Seb', 'Espèces', 4.00, '2025-08-18 18:28:47', 'Terminée', NULL, 1, '2025-08-18 18:28:47', 'user_68a34ea3a82fd1.51125907', 'table'),
(84, 'CMD74CF30', 'A', 'Espèces', 4.00, '2025-08-18 18:29:34', 'Terminée', NULL, 4, '2025-08-18 18:29:34', 'user_68a34ea3a82fd1.51125907', 'table'),
(85, 'CMD5E34CB', 'Bro', 'Espèces', 8.00, '2025-08-18 19:24:26', 'Terminée', NULL, 4, '2025-08-18 19:24:26', 'user_68a34ea3a82fd1.51125907', 'table'),
(86, 'CMDED4D38', 'SEB', 'Espèces', 4.00, '2025-08-18 19:27:24', 'Terminée', NULL, 8, '2025-08-18 19:27:24', 'user_68a362677aa9f6.42476903', 'table'),
(87, 'CMDA525F5', 'nico', 'Carte bancaire', 4.00, '2025-08-18 19:31:59', 'Terminée', NULL, 8, '2025-08-18 19:31:59', 'user_68a362677aa9f6.42476903', 'table'),
(88, 'CMD5A1644', 'MANON', 'Carte bancaire', 4.00, '2025-08-18 19:34:12', 'Terminée', NULL, 8, '2025-08-18 19:34:12', 'user_68a362677aa9f6.42476903', 'table'),
(89, 'CMDE6A0D4', 'Seb', 'Espèces', 4.00, '2025-08-18 19:35:34', 'Terminée', NULL, 0, '2025-08-18 19:35:34', 'user_68a362677aa9f6.42476903', 'takeaway'),
(90, 'CMD73A5A8', 'Seb', 'Espèces', 22.00, '2025-08-19 13:25:58', 'Terminée', NULL, 8, '2025-08-19 13:25:58', 'user_68a45c0ff00190.66621759', 'table'),
(91, 'CMDBC442C', 'A', 'Carte bancaire', 18.00, '2025-08-19 13:26:12', 'Terminée', NULL, 0, '2025-08-19 13:26:12', 'user_68a45c0ff00190.66621759', 'takeaway'),
(92, 'CMDAB9B9F', 'Barbara', 'Espèces', 4.00, '2025-08-19 13:28:22', 'Terminée', NULL, 5, '2025-08-19 13:28:22', 'user_68a45c0ff00190.66621759', 'table'),
(93, 'CMD651C11', 'Barbara', 'Espèces', 20.00, '2025-08-19 13:28:55', 'Terminée', NULL, 0, '2025-08-19 13:28:55', 'user_68a45c0ff00190.66621759', 'takeaway'),
(94, 'CMDEC92A9', 'Seb', 'Espèces', 4.00, '2025-08-19 13:44:39', 'Terminée', NULL, 0, '2025-08-19 13:44:39', 'user_68a45c0ff00190.66621759', 'takeaway'),
(95, 'CMD98CDC8', 'SEB', 'Espèces', 4.00, '2025-08-19 13:54:43', 'Terminée', NULL, 0, '2025-08-19 13:54:43', 'user_68a45c0ff00190.66621759', 'takeaway'),
(96, 'CMDA564ED', 'Seb', 'Espèces', 4.00, '2025-08-28 20:45:36', 'Terminée', NULL, 8, '2025-08-28 20:45:36', 'user_68aeb69cc2c9f1.29250474', 'table'),
(97, 'CMDFB6E78', 'Seb', 'Espèces', 12.00, '2025-08-28 20:48:10', 'Terminée', NULL, 8, '2025-08-28 20:48:10', 'user_68aeb69cc2c9f1.29250474', 'table'),
(98, 'CMD717E73', 'A', 'Espèces', 4.00, '2025-08-28 20:50:18', 'Terminée', NULL, 8, '2025-08-28 20:50:18', 'user_68aeb69cc2c9f1.29250474', 'table'),
(99, 'CMD3D9E38', 'Barbara', 'Carte bancaire', 4.00, '2025-08-28 20:52:19', 'Terminée', NULL, 8, '2025-08-28 20:52:19', 'user_68aeb69cc2c9f1.29250474', 'table'),
(100, 'CMD38AD13', 'Barbara', 'Espèces', 4.00, '2025-08-28 20:53:24', 'Terminée', NULL, 8, '2025-08-28 20:53:24', 'user_68aeb69cc2c9f1.29250474', 'table'),
(101, 'CMD5E2787', 'Bro', 'Espèces', 16.00, '2025-08-28 21:08:52', 'Terminée', NULL, 8, '2025-08-28 21:08:52', 'user_68aeb69cc2c9f1.29250474', 'table'),
(102, 'CMD1CD850', 'Seb', 'Carte bancaire', 24.00, '2025-08-29 10:23:00', 'Terminée', NULL, 8, '2025-08-29 10:23:00', 'user_68aeb69cc2c9f1.29250474', 'table'),
(103, 'CMDC856ED', 'A', 'Espèces', 44.00, '2025-08-29 10:25:30', 'Terminée', NULL, 8, '2025-08-29 10:25:30', 'user_68aeb69cc2c9f1.29250474', 'table'),
(104, 'CMDD7FF15', 'Seb', 'Espèces', 10.00, '2025-08-31 10:43:23', 'Terminée', NULL, 0, '2025-08-31 10:43:23', 'user_68b40a48000e63.93993416', 'takeaway'),
(105, 'CMDAB0877', 'A', 'Espèces', 4.00, '2025-08-31 10:45:46', 'Terminée', NULL, 0, '2025-08-31 10:45:46', 'user_68b40a48000e63.93993416', 'takeaway'),
(106, 'CMD8202CF', 'baptiste', 'Espèces', 18.00, '2025-09-08 09:30:47', 'Terminée', NULL, 1, '2025-09-08 09:30:47', 'user_68be85e47fbe39.27996402', 'table'),
(107, 'CMD782E7C', 'Collet', 'Espèces', 24.00, '2025-09-16 09:27:27', 'Terminée', NULL, 1, '2025-09-16 09:27:27', 'user_68c90fe04f8d92.25986266', 'table');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `food_id` int NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_id`, `food_name`, `quantity`, `price`) VALUES
(1, 1, 0, 'Tiramitsu', 1, 8.00),
(3, 3, 0, 'Tiramitsu', 1, 8.00),
(4, 4, 0, 'Tiramitsu', 1, 8.00),
(5, 5, 0, 'Tiramitsu', 4, 8.00),
(6, 5, 0, 'Margarita', 1, 10.00),
(7, 6, 0, 'Margarita', 1, 10.00),
(8, 6, 0, 'Steak Haché', 1, 8.00),
(9, 6, 0, 'pasta  ', 1, 4.00),
(10, 7, 0, 'Tiramitsu', 4, 8.00),
(11, 7, 0, 'pasta  ', 1, 4.00),
(12, 7, 0, 'Steak Haché', 1, 8.00),
(13, 8, 0, 'pasta  ', 1, 4.00),
(14, 9, 0, 'pasta  ', 6, 4.00),
(15, 10, 0, 'pasta  ', 11, 4.00),
(16, 10, 0, 'Steak Haché', 10, 8.00),
(17, 10, 0, 'Tiramitsu', 3, 8.00),
(18, 11, 0, 'pasta  ', 1, 4.00),
(19, 12, 0, 'Tiramitsu', 1, 8.00),
(20, 13, 0, 'pasta  ', 1, 4.00),
(21, 13, 0, 'Steak Haché', 1, 8.00),
(22, 14, 0, 'pasta  ', 1, 4.00),
(23, 14, 0, 'Margarita', 1, 10.00),
(24, 15, 0, 'pasta  ', 1, 4.00),
(25, 16, 0, 'Pasta  ', 1, 4.00),
(26, 17, 0, 'Pasta  ', 1, 4.00),
(27, 18, 0, 'Pasta  ', 5, 4.00),
(28, 19, 0, 'Pasta  ', 5, 4.00),
(29, 20, 0, 'Margarita', 1, 10.00),
(30, 21, 0, 'Pasta  ', 1, 4.00),
(31, 22, 0, 'Pasta  ', 1, 4.00),
(32, 23, 0, 'Tiramitsu', 1, 8.00),
(33, 24, 0, 'Tiramitsu', 1, 8.00),
(34, 25, 0, 'Tiramitsu', 9, 8.00),
(35, 26, 0, 'Tiramitsu', 15, 8.00),
(36, 27, 0, 'Pasta  ', 1, 4.00),
(37, 28, 0, 'Pasta  ', 1, 4.00),
(38, 28, 0, 'Margarita', 1, 10.00),
(39, 28, 0, 'Steak Haché', 1, 8.00),
(40, 29, 0, 'Pasta  ', 1, 4.00),
(41, 30, 0, 'Pasta  ', 1, 4.00),
(42, 31, 0, 'Pasta  ', 1, 4.00),
(43, 32, 0, 'Pasta  ', 7, 4.00),
(44, 33, 0, 'Pasta  ', 1, 4.00),
(45, 34, 0, 'Pasta  ', 2, 4.00),
(46, 35, 0, 'Pasta  ', 1, 4.00),
(47, 36, 0, 'Pasta  ', 1, 4.00),
(48, 37, 0, 'Tiramitsu', 2, 8.00),
(49, 37, 0, 'Pasta  ', 5, 4.00),
(50, 38, 0, 'Pasta  ', 3, 4.00),
(51, 38, 0, 'Tiramitsu', 4, 8.00),
(52, 39, 0, 'Tiramitsu', 8, 8.00),
(53, 39, 0, 'Pasta  ', 10, 4.00),
(54, 39, 0, 'Margarita', 1, 10.00),
(55, 39, 0, 'Steak Haché', 3, 8.00),
(56, 39, 0, 'salade caesar', 6, 8.00),
(57, 40, 0, 'Steak Haché', 1, 8.00),
(58, 40, 0, 'Margarita', 3, 10.00),
(59, 40, 0, 'Pasta  ', 1, 4.00),
(60, 41, 0, 'Pasta  ', 1, 4.00),
(61, 42, 0, 'salade caesar', 1, 8.00),
(83, 50, 0, 'Pasta  ', 1, 4.00),
(84, 51, 0, 'Tiramitsu', 1, 8.00),
(85, 52, 0, 'Pasta  ', 1, 4.00),
(86, 53, 0, 'Margarita', 1, 10.00),
(87, 53, 0, 'Pasta  ', 1, 4.00),
(88, 54, 0, 'Margarita', 1, 10.00),
(89, 54, 0, 'Pasta  ', 1, 4.00),
(90, 55, 0, 'Pasta  ', 8, 4.00),
(91, 56, 0, 'Pasta  ', 1, 4.00),
(92, 57, 0, 'Pasta  ', 7, 4.00),
(93, 58, 0, 'Pasta  ', 1, 4.00),
(94, 59, 0, 'Pasta  ', 1, 4.00),
(95, 59, 0, 'Margarita', 1, 10.00),
(96, 60, 0, 'salade caesar', 1, 8.00),
(97, 61, 0, 'Pasta  ', 1, 4.00),
(98, 61, 0, 'Tiramitsu', 1, 8.00),
(99, 61, 0, 'salade caesar', 1, 8.00),
(100, 61, 0, 'Margarita', 1, 10.00),
(101, 61, 0, 'Steak Haché', 1, 8.00),
(102, 62, 0, 'Pasta  ', 1, 4.00),
(103, 63, 0, 'salade caesar', 1, 8.00),
(104, 64, 0, 'Margarita', 1, 10.00),
(105, 65, 0, 'Margarita', 1, 10.00),
(106, 65, 0, 'Pasta  ', 1, 4.00),
(107, 66, 0, 'salade caesar', 10, 8.00),
(108, 66, 0, 'Margarita', 6, 10.00),
(109, 66, 0, 'Pasta  ', 2, 4.00),
(110, 67, 0, 'Margarita', 1, 10.00),
(111, 67, 0, 'Steak Haché', 1, 8.00),
(112, 68, 0, 'Pasta  ', 1, 4.00),
(113, 69, 0, 'Pasta  ', 1, 4.00),
(114, 70, 0, 'Pasta  ', 1, 4.00),
(115, 71, 0, 'Pasta  ', 1, 4.00),
(116, 71, 0, 'Margarita', 1, 10.00),
(117, 72, 0, 'Salade caesar', 32, 8.00),
(118, 73, 0, 'Steak Haché', 30, 8.00),
(119, 73, 0, 'Margarita', 12, 10.00),
(120, 74, 0, 'Margarita', 37, 10.00),
(121, 75, 0, 'Pasta  ', 1, 4.00),
(122, 75, 0, 'Tiramitsu', 1, 8.00),
(123, 75, 0, 'Margarita', 1, 10.00),
(124, 75, 0, 'Steak Haché', 1, 8.00),
(125, 76, 0, 'Pasta  ', 9, 4.00),
(126, 76, 0, 'Salade caesar', 1, 8.00),
(127, 77, 0, 'Salade caesar', 4, 8.00),
(128, 77, 0, 'Pasta  ', 1, 4.00),
(129, 78, 0, 'Pasta  ', 1, 4.00),
(130, 79, 0, 'Tiramitsu', 4, 8.00),
(131, 79, 0, 'Pasta  ', 10, 4.00),
(132, 80, 0, 'Salade caesar', 1, 8.00),
(133, 81, 0, 'Pasta  ', 1, 4.00),
(134, 81, 0, 'Margarita', 1, 10.00),
(135, 82, 0, 'Pasta  ', 1, 4.00),
(136, 83, 0, 'Pasta  ', 1, 4.00),
(137, 84, 0, 'Pasta  ', 1, 4.00),
(138, 85, 0, 'Pasta  ', 2, 4.00),
(139, 86, 0, 'Pasta  ', 1, 4.00),
(140, 87, 0, 'Pasta  ', 1, 4.00),
(141, 88, 0, 'Pasta  ', 1, 4.00),
(142, 89, 0, 'Pasta  ', 1, 4.00),
(143, 90, 0, 'Pasta  ', 3, 4.00),
(144, 90, 0, 'Margarita', 1, 10.00),
(145, 91, 0, 'Pasta  ', 2, 4.00),
(146, 91, 0, 'Margarita', 1, 10.00),
(147, 92, 0, 'Pasta  ', 1, 4.00),
(148, 93, 0, 'Pasta  ', 1, 4.00),
(149, 93, 0, 'Tiramitsu', 1, 8.00),
(150, 93, 0, 'Salade caesar', 1, 8.00),
(151, 94, 0, 'Pasta  ', 1, 4.00),
(152, 95, 0, 'Pasta  ', 1, 4.00),
(153, 96, 0, 'Pasta  ', 1, 4.00),
(154, 97, 0, 'Pasta  ', 1, 4.00),
(155, 97, 0, 'Salade caesar', 1, 8.00),
(156, 98, 0, 'Pasta  ', 1, 4.00),
(157, 99, 0, 'Pasta  ', 1, 4.00),
(158, 100, 0, 'Pasta  ', 1, 4.00),
(159, 101, 0, 'Tiramitsu', 2, 8.00),
(160, 102, 0, 'Pasta  ', 4, 4.00),
(161, 102, 0, 'Salade caesar', 1, 8.00),
(162, 103, 0, 'Pasta  ', 1, 4.00),
(163, 103, 0, 'Salade caesar', 5, 8.00),
(164, 104, 0, 'Margarita', 1, 10.00),
(165, 105, 0, 'Pasta  ', 1, 4.00),
(166, 106, 0, 'Pasta  ', 2, 4.00),
(167, 106, 0, 'Margarita', 1, 10.00),
(168, 107, 0, 'Tiramitsu', 2, 8.00),
(169, 107, 0, 'Pasta  ', 2, 4.00);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
