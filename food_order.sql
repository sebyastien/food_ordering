-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 06 déc. 2025 à 10:50
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_login`
--

INSERT INTO `admin_login` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$roPOqeJCMbrlKyIgxDZhEeg4sSYmQ7AZHDN3IxFW0xvBVvcs9hrCC', 'admin'),
(3, 'Pape', '$2y$10$T/5U1yIpKeLE2mndKX6vFe79XgrSgN84sve7aMlwBgrIXnasG9lrG', 'patron'),
(4, 'SEB', '$2y$10$LJcxUJnzu.OGBJ/U6fdr5Ot4HSotMKaLp9gFVnqeZBPJA4qtP4W2e', 'gérant'),
(8, 'Arthur', '$2y$10$L/RDSIVYSNFzcxw9qB9C1OOdzUMDSKhPi2cQ8OZ5ubb.zDzIcxmUW', 'serveur'),
(9, 'Serveur1', '$2y$10$rdRJsm6cJ3VeKx6qWCNq1.V2/h2WtRZDIkhhO5wdvk8dMPJsby292', 'serveur');

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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `food`
--

INSERT INTO `food` (`id`, `food_name`, `food_category`, `food_description`, `food_original_price`, `food_discount_price`, `food_avaibility`, `food_veg_nonveg`, `food_ingredients`, `food_image`, `is_active`) VALUES
(5, 'Tiramitsu', 'Dessert', 'Magnifique', '10', '8 €', 'Yes', 'Veg', 'lait, café, chocolat\r\n', 'images/db3e3857806393de8a2b2870d8c59167.png', 1),
(4, 'Pasta  ', 'Plat', 'Des pates', '5', '4 €', 'Yes', 'NonVeg', 'pasta, cheese, ham\r\n', 'images/51a2295004eab3b2a862590e0cae17ca.png', 1),
(6, 'Margarita', 'Plat', 'Simple et efficace', '15', '10 €', 'Yes', 'Veg', 'tomatoes', 'images/9047db0a70b253265f9569c5d3a12a89.png', 1),
(7, 'Steak Haché', 'Plat', 'Magnifique steak servi avec des champignons le tout sur une salade\r\n', '10', '9 €', 'Yes', 'NonVeg', '', 'images/0ed64b6ac3f1d6b88198e7e740f9c980.png', 1),
(8, 'Salade caesar', 'Salade', 'tres bonne salade repli de vie', '10 ', '8 €', 'Yes', 'Veg', 'concombre,salade,tomatoes', 'images/77ff637d489bb0e180da33663d9c5ceb.png', 1),
(9, 'EAU', 'Boisson', 'Très belle bouteille fraiche qui permet de s\'hydrater', '1 ', '2', 'Yes', 'Veg', 'eau', 'images/e57f762d2317eb285b2d23cbe62eb26c.png', 1),
(10, 'Boeuf', 'Menu du jour', 'Bon bœuf francais', '5 ', '6 €', 'Yes', 'Veg', 'frite, salade, poivre, herbe\r\n', 'images/8f92ce3d4fd143f23fe993b31cd14470.png', 1);

-- --------------------------------------------------------

--
-- Structure de la table `food_categories`
--

DROP TABLE IF EXISTS `food_categories`;
CREATE TABLE IF NOT EXISTS `food_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_categories` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ordre` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=armscii8;

--
-- Déchargement des données de la table `food_categories`
--

INSERT INTO `food_categories` (`id`, `food_categories`, `ordre`) VALUES
(19, 'Menu du jour', 1),
(14, 'Salade', 3),
(25, 'Entrée', 2),
(18, 'Plat', 4),
(1, 'Dessert', 5),
(21, 'Boisson', 6);

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
  `ready_time` datetime DEFAULT NULL,
  `served_time` datetime DEFAULT NULL,
  `restaurant_id` int DEFAULT NULL,
  `table_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` varchar(255) DEFAULT NULL,
  `order_type` varchar(50) NOT NULL DEFAULT 'table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `payment_method`, `total_price`, `order_date`, `status`, `ready_time`, `served_time`, `restaurant_id`, `table_id`, `created_at`, `user_id`, `order_type`) VALUES
(1, 'CMD123456', 'Se', 'Especes', 8.00, '2025-08-04 16:05:39', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(3, 'CMD5DCBC2', 'A', 'Especes', 8.00, '2025-08-05 08:23:50', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(4, 'CMD363275', 'Boris', 'PayPal', 8.00, '2025-08-05 12:22:22', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(5, 'CMD85A82A', 'Salut', 'Carte', 42.00, '2025-08-05 12:23:05', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(6, 'CMD86F1B3', 'Bro', 'Carte', 22.00, '2025-08-05 12:23:40', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(7, 'CMD583E4D', 'Bonjour', 'Especes', 44.00, '2025-08-05 15:47:06', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, 'table'),
(8, 'CMDEFFE05', 'Barbara', 'Especes', 4.00, '2025-08-05 15:50:10', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(9, 'CMD68ADC1', 'Nicos', 'Carte', 24.00, '2025-08-05 15:53:13', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(10, 'CMD980C34', 'SEBY', 'PayPal', 148.00, '2025-08-05 16:09:38', 'Terminée', NULL, NULL, 1, 20, '2025-08-13 11:29:38', NULL, 'table'),
(11, 'CMD58D75E', 'Seby', 'Especes', 4.00, '2025-08-05 16:11:14', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(12, 'CMD5873FF', 'Seb', 'Especes', 8.00, '2025-08-05 21:40:37', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(13, 'CMDD0A990', 'Manon', 'Especes', 12.00, '2025-08-05 21:43:26', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(14, 'CMD9788A0', 'SEB', 'Especes', 14.00, '2025-08-05 21:46:47', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(15, 'CMD6D6C71', 'A', 'Especes', 4.00, '2025-08-05 21:47:21', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, 'table'),
(16, 'CMD9CAAE8', 'Seb', 'Especes', 4.00, '2025-08-06 12:59:11', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(17, 'CMD4AF6E1', 'Seb', 'Especes', 4.00, '2025-08-06 13:04:52', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(18, 'CMD5CF7C8', 'Seb', 'Especes', 20.00, '2025-08-06 13:12:59', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(19, 'CMDD57DAD', 'Logan', 'Carte', 20.00, '2025-08-07 11:16:43', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(20, 'CMD7891C5', 'SebY', 'Especes', 10.00, '2025-08-07 11:20:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(21, 'CMD6BDFA9', 'Marchal', 'Carte', 4.00, '2025-08-07 11:29:00', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(22, 'CMDD29FEC', 'BRO', 'Especes', 4.00, '2025-08-07 15:01:44', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(23, 'CMDAD54CD', 'Seb', 'Especes', 8.00, '2025-08-07 15:05:17', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(24, 'CMDC950E0', 'YES', 'Especes', 8.00, '2025-08-07 15:07:36', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(25, 'CMD9DE1F1', 'Gourmand', 'PayPal', 72.00, '2025-08-07 15:10:04', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(26, 'CMD5DCC5C', 'Pape', 'Carte', 120.00, '2025-08-07 15:14:26', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(27, 'CMD204DED', 'se', 'Especes', 4.00, '2025-08-07 15:19:31', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(28, 'CMD1C4A76', 'Manou', 'Carte', 22.00, '2025-08-07 22:51:26', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(29, 'CMDF86D6A', 'Seb', 'Especes', 4.00, '2025-08-07 22:53:10', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(30, 'CMD507969', 'Bro', 'PayPal', 4.00, '2025-08-07 22:55:09', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(31, 'CMD265CF2', 's', 'Especes', 4.00, '2025-08-07 23:02:22', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(32, 'CMD8850BD', 'Money', 'Carte', 28.00, '2025-08-07 23:04:16', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(33, 'CMD423712', 'Troll', 'Carte', 4.00, '2025-08-07 23:07:00', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(34, 'CMD23421C', 'e', 'Especes', 8.00, '2025-08-07 23:09:06', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(35, 'CMD1B306E', 'A', 'Especes', 4.00, '2025-08-07 23:15:10', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(36, 'CMDB0F422', 'ssss', 'Especes', 4.00, '2025-08-07 23:19:35', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(37, 'CMD5F0C9B', 'Seb', 'Especes', 36.00, '2025-08-10 10:45:02', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(38, 'CMD54CFB7', 'Seb', 'Especes', 44.00, '2025-08-10 14:16:57', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(39, 'CMD69C98E', 'SEB', 'Especes', 186.00, '2025-08-10 14:36:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(40, 'CMDD57BBD', 'SEB', 'Especes', 42.00, '2025-08-10 15:27:45', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(41, 'CMDD3542C', 'Seb', 'Carte', 4.00, '2025-08-10 15:29:49', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(42, 'CMDB2032F', 'seb', 'Especes', 8.00, '2025-08-10 15:32:35', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, 'table'),
(50, 'CMDDEC69E', 'Seb', 'Espèces', 4.00, '2025-08-13 23:47:51', 'Terminée', NULL, NULL, NULL, 5, '2025-08-13 23:47:51', NULL, 'table'),
(51, 'CMDA97C95', 'Seb', 'Espèces', 8.00, '2025-08-13 23:49:02', 'Terminée', NULL, NULL, NULL, 5, '2025-08-13 23:49:02', NULL, 'table'),
(52, 'CMDC5225D', 'Seb', 'Espèces', 4.00, '2025-08-15 19:28:06', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:28:06', NULL, 'table'),
(53, 'CMDD815C2', 'Bro', 'Espèces', 14.00, '2025-08-15 19:28:31', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:28:31', NULL, 'table'),
(54, 'CMD50FE94', 'Yanis', 'Espèces', 14.00, '2025-08-15 19:29:16', 'Terminée', NULL, NULL, NULL, 8, '2025-08-15 19:29:16', NULL, 'table'),
(55, 'CMD98A32B', 'Seb', 'Espèces', 32.00, '2025-08-15 19:32:29', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:32:29', NULL, 'table'),
(56, 'CMD9E74AD', 'Seby', 'Espèces', 4.00, '2025-08-15 19:42:59', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:42:59', '0', 'table'),
(57, 'CMD076518', 'Seby', 'Espèces', 28.00, '2025-08-15 19:44:01', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:44:01', NULL, 'table'),
(58, 'CMD954ECC', 'Seb', 'Espèces', 4.00, '2025-08-15 19:46:00', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:46:00', 'user_689f71965b7e82.37210285', 'table'),
(59, 'CMD224D72', 'Seb', 'Espèces', 14.00, '2025-08-15 19:49:09', 'Terminée', NULL, NULL, NULL, 8, '2025-08-15 19:49:09', 'user_689f71965b7e82.37210285', 'table'),
(60, 'CMDBD23E6', 'Jeff', 'Espèces', 8.00, '2025-08-15 19:49:47', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:49:47', 'user_689f71965b7e82.37210285', 'table'),
(61, 'CMD93B4CE', 'Brother', 'Carte bancaire', 38.00, '2025-08-15 19:54:40', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:54:40', 'user_689f71965b7e82.37210285', 'table'),
(62, 'CMD04CE05', 'Brother', 'Espèces', 4.00, '2025-08-15 19:55:12', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:55:12', 'user_689f71965b7e82.37210285', 'table'),
(63, 'CMDB2C522', 'Seb', 'Carte bancaire', 8.00, '2025-08-15 19:55:30', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:55:30', 'user_689f71965b7e82.37210285', 'table'),
(64, 'CMDACA340', 'Seb', 'Espèces', 10.00, '2025-08-16 09:36:31', 'Terminée', NULL, NULL, NULL, 8, '2025-08-16 09:36:31', 'user_68a0324b56b1c5.52618241', 'table'),
(65, 'CMD925F38', 'SEBY', 'Carte bancaire', 14.00, '2025-08-16 09:37:40', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 09:37:40', 'user_68a0324b56b1c5.52618241', 'table'),
(66, 'CMD7C4924', 'Seb', 'Espèces', 148.00, '2025-08-16 09:55:02', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 09:55:02', 'user_68a0324b56b1c5.52618241', 'table'),
(67, 'CMDC68205', 'Karim', 'Carte bancaire', 18.00, '2025-08-16 10:12:23', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 10:12:23', 'user_68a0324b56b1c5.52618241', 'table'),
(68, 'CMDD752BA', 'Seb', 'Espèces', 4.00, '2025-08-16 10:49:39', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 10:49:39', 'user_68a0461a2be149.40269937', 'table'),
(69, 'CMD91BE2E', 'Seb', 'Espèces', 4.00, '2025-08-17 10:28:09', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:28:09', 'user_68a18fdbc785b3.50946943', 'table'),
(70, 'CMD04557A', 'KARIM', 'Carte bancaire', 4.00, '2025-08-17 10:32:49', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:32:49', 'user_68a18fdbc785b3.50946943', 'table'),
(71, 'CMD5B798A', 'BROTHER', 'Espèces', 14.00, '2025-08-17 10:35:48', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:35:48', 'user_68a18fdbc785b3.50946943', 'table'),
(72, 'CMDFE4CFA', 'SLD', 'Carte bancaire', 256.00, '2025-08-17 10:43:48', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:43:48', 'user_68a18fdbc785b3.50946943', 'table'),
(73, 'CMD934357', 'Pizza', 'Espèces', 360.00, '2025-08-17 10:44:31', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:44:31', 'user_68a18fdbc785b3.50946943', 'table'),
(74, 'CMDACED28', 'vitto', 'Carte bancaire', 370.00, '2025-08-17 10:44:58', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:44:58', 'user_68a18fdbc785b3.50946943', 'table'),
(75, 'CMD6801D7', 'Pape', 'Espèces', 30.00, '2025-08-18 09:51:32', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 09:51:32', 'user_68a2d799e19b06.18219295', 'table'),
(76, 'CMDC833E9', 'Seb', 'Espèces', 44.00, '2025-08-18 10:18:46', 'Terminée', NULL, NULL, NULL, 5, '2025-08-18 10:18:46', 'user_68a2d799e19b06.18219295', 'table'),
(77, 'CMD404504', 'A', 'Espèces', 36.00, '2025-08-18 10:47:27', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 10:47:27', 'user_68a2d799e19b06.18219295', 'takeaway'),
(78, 'CMD52CA30', 'Seb', 'Espèces', 4.00, '2025-08-18 10:47:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 10:47:51', 'user_68a2d799e19b06.18219295', 'takeaway'),
(79, 'CMD69EEAA', 'Manon', 'Espèces', 72.00, '2025-08-18 11:12:01', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 11:12:01', 'user_68a2d799e19b06.18219295', 'takeaway'),
(80, 'CMDDA171A', 'Bro', 'PayPal', 8.00, '2025-08-18 11:15:54', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 11:15:54', 'user_68a2ee9bafb607.55408287', 'takeaway'),
(81, 'CMD84A409', 'Seb', 'Espèces', 14.00, '2025-08-18 18:03:06', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 18:03:06', 'user_68a34ea3a82fd1.51125907', 'takeaway'),
(82, 'CMD1AA193', 'Barbara', 'Carte bancaire', 4.00, '2025-08-18 18:10:17', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 18:10:17', 'user_68a34ea3a82fd1.51125907', 'takeaway'),
(83, 'CMD6CAF42', 'Seb', 'Espèces', 4.00, '2025-08-18 18:28:47', 'Terminée', NULL, NULL, NULL, 1, '2025-08-18 18:28:47', 'user_68a34ea3a82fd1.51125907', 'table'),
(84, 'CMD74CF30', 'A', 'Espèces', 4.00, '2025-08-18 18:29:34', 'Terminée', NULL, NULL, NULL, 4, '2025-08-18 18:29:34', 'user_68a34ea3a82fd1.51125907', 'table'),
(85, 'CMD5E34CB', 'Bro', 'Espèces', 8.00, '2025-08-18 19:24:26', 'Terminée', NULL, NULL, NULL, 4, '2025-08-18 19:24:26', 'user_68a34ea3a82fd1.51125907', 'table'),
(86, 'CMDED4D38', 'SEB', 'Espèces', 4.00, '2025-08-18 19:27:24', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:27:24', 'user_68a362677aa9f6.42476903', 'table'),
(87, 'CMDA525F5', 'nico', 'Carte bancaire', 4.00, '2025-08-18 19:31:59', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:31:59', 'user_68a362677aa9f6.42476903', 'table'),
(88, 'CMD5A1644', 'MANON', 'Carte bancaire', 4.00, '2025-08-18 19:34:12', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:34:12', 'user_68a362677aa9f6.42476903', 'table'),
(89, 'CMDE6A0D4', 'Seb', 'Espèces', 4.00, '2025-08-18 19:35:34', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 19:35:34', 'user_68a362677aa9f6.42476903', 'takeaway'),
(90, 'CMD73A5A8', 'Seb', 'Espèces', 22.00, '2025-08-19 13:25:58', 'Terminée', NULL, NULL, NULL, 8, '2025-08-19 13:25:58', 'user_68a45c0ff00190.66621759', 'table'),
(91, 'CMDBC442C', 'A', 'Carte bancaire', 18.00, '2025-08-19 13:26:12', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:26:12', 'user_68a45c0ff00190.66621759', 'takeaway'),
(92, 'CMDAB9B9F', 'Barbara', 'Espèces', 4.00, '2025-08-19 13:28:22', 'Terminée', NULL, NULL, NULL, 5, '2025-08-19 13:28:22', 'user_68a45c0ff00190.66621759', 'table'),
(93, 'CMD651C11', 'Barbara', 'Espèces', 20.00, '2025-08-19 13:28:55', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:28:55', 'user_68a45c0ff00190.66621759', 'takeaway'),
(94, 'CMDEC92A9', 'Seb', 'Espèces', 4.00, '2025-08-19 13:44:39', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:44:39', 'user_68a45c0ff00190.66621759', 'takeaway'),
(95, 'CMD98CDC8', 'SEB', 'Espèces', 4.00, '2025-08-19 13:54:43', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:54:43', 'user_68a45c0ff00190.66621759', 'takeaway'),
(96, 'CMDA564ED', 'Seb', 'Espèces', 4.00, '2025-08-28 20:45:36', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:45:36', 'user_68aeb69cc2c9f1.29250474', 'table'),
(97, 'CMDFB6E78', 'Seb', 'Espèces', 12.00, '2025-08-28 20:48:10', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:48:10', 'user_68aeb69cc2c9f1.29250474', 'table'),
(98, 'CMD717E73', 'A', 'Espèces', 4.00, '2025-08-28 20:50:18', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:50:18', 'user_68aeb69cc2c9f1.29250474', 'table'),
(99, 'CMD3D9E38', 'Barbara', 'Carte bancaire', 4.00, '2025-08-28 20:52:19', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:52:19', 'user_68aeb69cc2c9f1.29250474', 'table'),
(100, 'CMD38AD13', 'Barbara', 'Espèces', 4.00, '2025-08-28 20:53:24', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:53:24', 'user_68aeb69cc2c9f1.29250474', 'table'),
(101, 'CMD5E2787', 'Bro', 'Espèces', 16.00, '2025-08-28 21:08:52', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 21:08:52', 'user_68aeb69cc2c9f1.29250474', 'table'),
(102, 'CMD1CD850', 'Seb', 'Carte bancaire', 24.00, '2025-08-29 10:23:00', 'Terminée', NULL, NULL, NULL, 8, '2025-08-29 10:23:00', 'user_68aeb69cc2c9f1.29250474', 'table'),
(103, 'CMDC856ED', 'A', 'Espèces', 44.00, '2025-08-29 10:25:30', 'Terminée', NULL, NULL, NULL, 8, '2025-08-29 10:25:30', 'user_68aeb69cc2c9f1.29250474', 'table'),
(104, 'CMDD7FF15', 'Seb', 'Espèces', 10.00, '2025-08-31 10:43:23', 'Terminée', NULL, NULL, NULL, 0, '2025-08-31 10:43:23', 'user_68b40a48000e63.93993416', 'takeaway'),
(105, 'CMDAB0877', 'A', 'Espèces', 4.00, '2025-08-31 10:45:46', 'Terminée', NULL, NULL, NULL, 0, '2025-08-31 10:45:46', 'user_68b40a48000e63.93993416', 'takeaway'),
(106, 'CMD8202CF', 'baptiste', 'Espèces', 18.00, '2025-09-08 09:30:47', 'Terminée', NULL, NULL, NULL, 1, '2025-09-08 09:30:47', 'user_68be85e47fbe39.27996402', 'table'),
(107, 'CMD782E7C', 'Collet', 'Espèces', 24.00, '2025-09-16 09:27:27', 'Terminée', NULL, NULL, NULL, 1, '2025-09-16 09:27:27', 'user_68c90fe04f8d92.25986266', 'table'),
(108, 'CMDF6F36C', 'Client', 'Espèces', 56.00, '2025-10-23 16:14:36', 'Terminée', '2025-10-26 12:02:56', '2025-10-26 12:07:31', NULL, 1, '2025-10-23 16:14:36', 'user_68fa312f172d70.11086723', 'table'),
(109, 'CMD929B7C', 'Seb', 'Espèces', 4.00, '2025-10-23 16:18:49', 'Terminée', '2025-10-26 12:08:24', '2025-10-26 12:09:56', NULL, 1, '2025-10-23 16:18:49', 'user_68fa312f172d70.11086723', 'table'),
(110, 'CMDD253C2', 'A', 'Carte bancaire', 8.00, '2025-10-23 16:22:52', 'Terminée', '2025-10-26 12:08:26', '2025-10-26 12:10:00', NULL, 1, '2025-10-23 16:22:52', 'user_68fa3a2c0105e6.38169328', 'table'),
(111, 'CMD50BF11', 'Seb', 'Espèces', 16.00, '2025-10-23 16:32:10', 'Terminée', NULL, NULL, NULL, 1, '2025-10-23 16:32:10', 'user_68fa3a2c0105e6.38169328', 'table'),
(112, 'CMDD03A41', 'Seb', 'Carte bancaire', 8.00, '2025-10-26 11:12:26', 'Terminée', '2025-10-26 12:08:29', '2025-10-26 12:10:03', NULL, 1, '2025-10-26 11:12:26', 'user_68fdeab3e11309.58058228', 'table'),
(113, 'CMD9CA0CB', 'Seb', 'Espèces', 4.00, '2025-10-26 11:12:55', 'Terminée', '2025-10-26 12:09:08', '2025-10-26 12:10:06', NULL, 1, '2025-10-26 11:12:55', 'user_68fdeab3e11309.58058228', 'table'),
(114, 'CMD0CEFE0', 'A', 'Espèces', 12.00, '2025-10-26 11:15:53', 'Terminée', NULL, NULL, NULL, 1, '2025-10-26 11:15:53', 'user_68fdeab3e11309.58058228', 'table'),
(115, 'CMD8A3466', 'Seb', 'Espèces', 4.00, '2025-10-26 12:10:23', 'Terminée', '2025-10-26 12:10:32', '2025-10-26 13:04:21', NULL, 1, '2025-10-26 12:10:23', 'user_68fdeab3e11309.58058228', 'table'),
(116, 'CMD82D3B3', 'A', 'Espèces', 10.00, '2025-10-26 12:20:51', 'Terminée', '2025-10-26 12:21:27', '2025-10-26 13:04:16', NULL, 1, '2025-10-26 12:20:51', 'user_68fdeab3e11309.58058228', 'table'),
(117, 'CMD28314A', 'Seb', 'Espèces', 8.00, '2025-10-26 13:02:41', 'Terminée', '2025-10-26 13:03:39', '2025-10-26 13:04:31', NULL, 1, '2025-10-26 13:02:41', 'user_68fdeab3e11309.58058228', 'table'),
(118, 'CMD555961', 'A', 'Espèces', 8.00, '2025-10-26 15:41:48', 'Terminée', NULL, NULL, NULL, 1, '2025-10-26 15:41:48', 'user_68fe330f6a7b51.05490179', 'table'),
(119, 'CMD8FB25E', 'Seb', 'Espèces', 4.00, '2025-10-26 15:46:38', 'Terminée', '2025-11-06 09:32:58', '2025-11-06 09:33:26', NULL, 1, '2025-10-26 15:46:38', 'user_68fe330f6a7b51.05490179', 'table'),
(120, 'CMD219A69', 'Seb', 'Espèces', 12.00, '2025-11-06 09:33:44', 'Terminée', '2025-11-06 09:33:59', '2025-11-06 09:34:09', NULL, 1, '2025-11-06 09:33:44', 'user_690c5d4bdc19f9.07975215', 'table'),
(121, 'CMD6EBE34', 'A', 'Espèces', 8.00, '2025-11-06 09:34:40', 'Terminée', '2025-11-06 09:34:52', '2025-11-06 09:35:12', NULL, 1, '2025-11-06 09:34:40', 'user_690c5d4bdc19f9.07975215', 'table'),
(122, 'CMDBE0DF8', 'Seb', 'Espèces', 12.00, '2025-11-06 09:38:47', 'Terminée', '2025-11-06 09:38:52', '2025-11-06 09:38:55', NULL, 1, '2025-11-06 09:38:47', 'user_690c5d4bdc19f9.07975215', 'table'),
(123, 'CMDB917CC', 'Barbara', 'Espèces', 30.00, '2025-11-06 14:22:03', 'Terminée', '2025-11-06 14:26:14', '2025-11-06 14:26:59', NULL, 1, '2025-11-06 14:22:03', 'user_690c9e991cf555.48432898', 'table'),
(124, 'CMD989C4F', 'Sébastien', 'Espèces', 60.00, '2025-11-06 14:43:32', 'Terminée', '2025-11-06 15:18:41', '2025-11-06 15:30:14', NULL, 1, '2025-11-06 14:43:32', 'user_690c9e991cf555.48432898', 'table'),
(125, 'CMD83A04F', 'Seb', 'Espèces', 4.00, '2025-11-06 14:50:37', 'Terminée', '2025-11-06 15:21:00', '2025-11-06 15:30:30', NULL, 1, '2025-11-06 14:50:37', 'user_690c9e991cf555.48432898', 'table'),
(126, 'CMD2DC84D', 'Seb', 'Espèces', 4.00, '2025-11-06 14:51:54', 'Terminée', '2025-11-06 15:33:14', '2025-11-06 15:38:01', NULL, 0, '2025-11-06 14:51:54', 'user_690c9e991cf555.48432898', 'takeaway'),
(127, 'CMDA81042', 'Bro', 'Espèces', 4.00, '2025-11-06 15:20:07', 'Terminée', '2025-11-06 15:37:58', '2025-11-06 15:38:03', NULL, 0, '2025-11-06 15:20:07', 'user_690c9e991cf555.48432898', 'takeaway'),
(128, 'CMD9E8A4C', 'Nicolas', 'Espèces', 20.00, '2025-11-06 15:43:31', 'Terminée', '2025-11-06 15:44:09', '2025-11-06 15:44:35', NULL, 5, '2025-11-06 15:43:31', 'user_690cb2d98ac0c5.78482544', 'table'),
(129, 'CMDD2199F', 'Seb', 'Espèces', 2.00, '2025-11-10 23:17:50', 'En attente', NULL, NULL, NULL, 1, '2025-11-10 23:17:50', 'user_691263f98a5841.61372948', 'table'),
(130, 'CMD42AA57', 'A', 'Carte bancaire', 1.00, '2025-11-10 23:24:28', 'Terminée', '2025-11-11 22:53:59', '2025-11-11 22:54:08', NULL, 1, '2025-11-10 23:24:28', 'user_691263f98a5841.61372948', 'table'),
(131, 'CMDB35460', 'Seb', 'Carte bancaire', 40.00, '2025-11-11 13:21:52', 'Terminée', '2025-11-11 22:53:56', '2025-11-11 22:54:06', NULL, 1, '2025-11-11 13:21:52', 'user_691323b09ce9c4.50590639', 'table'),
(132, 'CMDDF4769', 'Seb', 'Carte bancaire', 40.00, '2025-11-11 13:22:39', 'En attente', NULL, NULL, NULL, 1, '2025-11-11 13:22:39', 'user_691323b09ce9c4.50590639', 'table'),
(133, 'CMD6DECFC', 'A', 'Espèces', 5.00, '2025-11-11 22:10:25', 'En attente', NULL, NULL, NULL, 1, '2025-11-11 22:10:25', 'user_6913a60dcfde69.53597725', 'table'),
(134, 'CMDB87451', 'Seb', 'Espèces', 5.00, '2025-11-11 22:26:26', 'Terminée', '2025-11-11 22:48:50', '2025-11-11 22:54:10', NULL, 1, '2025-11-11 22:26:26', 'user_6913a60dcfde69.53597725', 'table'),
(135, 'CMDC0E5DC', 'Seb', 'Espèces', 71.00, '2025-11-11 22:57:29', 'Terminée', '2025-11-11 22:57:55', '2025-11-11 22:58:02', NULL, 1, '2025-11-11 22:57:29', 'user_6913a60dcfde69.53597725', 'table'),
(136, 'CMDCE9055', 'Seb', 'Espèces', 25.00, '2025-11-11 23:15:38', 'En attente', NULL, NULL, NULL, 0, '2025-11-11 23:15:38', 'user_6913a60dcfde69.53597725', 'takeaway'),
(137, 'CMD2B41CA', 'Seb', 'Espèces', 20.00, '2025-11-11 23:19:49', 'En attente', NULL, NULL, NULL, 0, '2025-11-11 23:19:49', 'user_6913a60dcfde69.53597725', 'takeaway'),
(138, 'CMD6DDE4F', 'A', 'Espèces', 15.00, '2025-11-19 22:24:25', 'Prête', '2025-11-22 14:21:43', NULL, NULL, 1, '2025-11-19 22:24:25', 'user_691e33514da205.03873101', 'table'),
(139, 'CMDAF3DAF', 'A', 'Espèces', 25.00, '2025-11-19 22:25:34', 'Prête', '2025-11-22 14:21:40', NULL, NULL, 1, '2025-11-19 22:25:34', 'user_691e33514da205.03873101', 'table'),
(140, 'CMD149F5A', 'Seb', 'Espèces', 5.00, '2025-11-19 22:40:10', 'Prête', '2025-11-22 14:21:32', NULL, NULL, 1, '2025-11-19 22:40:10', 'user_691e33514da205.03873101', 'table'),
(141, 'CMD1FFB41', 'Seb', 'Espèces', 1.00, '2025-11-19 22:48:23', 'Prête', '2025-11-22 14:21:30', NULL, NULL, 0, '2025-11-19 22:48:23', 'user_691e33514da205.03873101', 'takeaway'),
(142, 'CMDA722E4', 'Seb', 'PayPal', 5.00, '2025-11-19 22:48:54', 'Prête', '2025-11-22 14:21:19', NULL, NULL, 0, '2025-11-19 22:48:54', 'user_691e33514da205.03873101', 'takeaway'),
(143, 'CMDD2127E', 'A', 'Carte bancaire', 1.00, '2025-11-19 22:49:49', 'Prête', '2025-11-22 14:09:51', NULL, NULL, 0, '2025-11-19 22:49:49', 'user_691e33514da205.03873101', 'takeaway'),
(144, 'CMDE0F402', 'Seb', 'Espèces', 15.00, '2025-11-22 14:28:03', 'En attente', NULL, NULL, NULL, 1, '2025-11-22 14:28:03', 'user_6921b400c0d049.32031912', 'table'),
(145, 'CMD83895A', 'A', 'Espèces', 1.00, '2025-11-22 14:40:05', 'En attente', NULL, NULL, NULL, 1, '2025-11-22 14:40:05', 'user_6921b400c0d049.32031912', 'table'),
(146, 'CMD2F3EE1', 'Seb', 'Espèces', 16.00, '2025-11-27 11:12:17', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:12:17', 'user_6928212de43388.06263856', 'table'),
(147, 'CMDBC0FE4', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:16:21', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:16:21', 'user_6928212de43388.06263856', 'table'),
(148, 'CMD5CF3E3', 'Nicolas', 'Espèces', 10.00, '2025-11-27 11:17:57', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:17:57', 'user_6928212de43388.06263856', 'table'),
(149, 'CMD0E202F', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:18:54', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:18:54', 'user_6928212de43388.06263856', 'table'),
(150, 'CMD89EE8B', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:20:49', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:20:49', 'user_6928212de43388.06263856', 'table'),
(151, 'CMDA538D5', 'Bro', 'Carte bancaire', 5.00, '2025-11-27 11:21:24', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:21:24', 'user_6928212de43388.06263856', 'table'),
(152, 'CMD54909F', 'Bro', 'Carte bancaire', 51.00, '2025-11-27 11:21:51', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:21:51', 'user_6928212de43388.06263856', 'table'),
(153, 'CMD4C1DC9', 'Bro', 'Carte bancaire', 38.00, '2025-11-27 11:23:44', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:23:44', 'user_6928212de43388.06263856', 'table'),
(154, 'CMD79ABED', 'Bro', 'Espèces', 10.00, '2025-11-27 11:30:12', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 11:30:12', 'user_6928212de43388.06263856', 'table'),
(155, 'CMD46BAEA', 'Bro', 'Espèces', 15.00, '2025-11-27 13:26:06', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 13:26:06', 'user_6928212de43388.06263856', 'table'),
(156, 'CMD6AF903', 'Barbara', 'Espèces', 10.00, '2025-11-27 14:20:12', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 14:20:12', 'user_6928212de43388.06263856', 'table'),
(157, 'CMDFAC737', 'Barbara', 'Espèces', 45.00, '2025-11-27 23:06:36', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 23:06:36', 'user_6928c104b1d142.55887884', 'table'),
(158, 'CMDD27FE2', 'A', 'Espèces', 40.00, '2025-11-27 23:12:31', 'En attente', NULL, NULL, NULL, 1, '2025-11-27 23:12:31', 'user_6928c104b1d142.55887884', 'table'),
(159, 'CMD84442B', 'Seb', 'Carte bancaire', 10.00, '2025-11-29 11:48:10', 'En attente', NULL, NULL, NULL, 1, '2025-11-29 11:48:10', 'user_692abf873cc954.26731801', 'table'),
(160, 'CMD224704', 'A', 'Espèces', 50.00, '2025-11-29 15:22:08', 'En attente', NULL, NULL, NULL, 1, '2025-11-29 15:22:08', 'user_692abf873cc954.26731801', 'table'),
(161, 'CMD5125CB', 'Seb', 'Espèces', 5.00, '2025-12-05 13:50:31', 'En attente', NULL, NULL, NULL, 0, '2025-12-05 13:50:31', 'user_6932d23ce18aa6.68834573', 'takeaway'),
(162, 'CMDDE9603', 'Seb', 'Espèces', 5.00, '2025-12-05 13:50:58', 'En attente', NULL, NULL, NULL, 0, '2025-12-05 13:50:58', 'user_6932d23ce18aa6.68834573', 'takeaway'),
(163, 'CMD99CD83', 'A', 'Espèces', 5.00, '2025-12-05 13:51:18', 'En attente', NULL, NULL, NULL, 0, '2025-12-05 13:51:18', 'user_6932d23ce18aa6.68834573', 'takeaway'),
(164, 'CMD95F937', 'A', 'Carte bancaire', 120.00, '2025-12-05 13:53:49', 'En attente', NULL, NULL, NULL, 0, '2025-12-05 13:53:49', 'user_6932d23ce18aa6.68834573', 'takeaway'),
(165, 'CMD1FFFFC', 'A', 'Espèces', 20.00, '2025-12-05 14:11:38', 'En attente', NULL, NULL, NULL, 1, '2025-12-05 14:11:38', 'user_6932d23ce18aa6.68834573', 'table');

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
  `item_comment` text,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_id`, `food_name`, `quantity`, `price`, `item_comment`) VALUES
(1, 1, 0, 'Tiramitsu', 1, 8.00, NULL),
(3, 3, 0, 'Tiramitsu', 1, 8.00, NULL),
(4, 4, 0, 'Tiramitsu', 1, 8.00, NULL),
(5, 5, 0, 'Tiramitsu', 4, 8.00, NULL),
(6, 5, 0, 'Margarita', 1, 10.00, NULL),
(7, 6, 0, 'Margarita', 1, 10.00, NULL),
(8, 6, 0, 'Steak Haché', 1, 8.00, NULL),
(9, 6, 0, 'pasta  ', 1, 4.00, NULL),
(10, 7, 0, 'Tiramitsu', 4, 8.00, NULL),
(11, 7, 0, 'pasta  ', 1, 4.00, NULL),
(12, 7, 0, 'Steak Haché', 1, 8.00, NULL),
(13, 8, 0, 'pasta  ', 1, 4.00, NULL),
(14, 9, 0, 'pasta  ', 6, 4.00, NULL),
(15, 10, 0, 'pasta  ', 11, 4.00, NULL),
(16, 10, 0, 'Steak Haché', 10, 8.00, NULL),
(17, 10, 0, 'Tiramitsu', 3, 8.00, NULL),
(18, 11, 0, 'pasta  ', 1, 4.00, NULL),
(19, 12, 0, 'Tiramitsu', 1, 8.00, NULL),
(20, 13, 0, 'pasta  ', 1, 4.00, NULL),
(21, 13, 0, 'Steak Haché', 1, 8.00, NULL),
(22, 14, 0, 'pasta  ', 1, 4.00, NULL),
(23, 14, 0, 'Margarita', 1, 10.00, NULL),
(24, 15, 0, 'pasta  ', 1, 4.00, NULL),
(25, 16, 0, 'Pasta  ', 1, 4.00, NULL),
(26, 17, 0, 'Pasta  ', 1, 4.00, NULL),
(27, 18, 0, 'Pasta  ', 5, 4.00, NULL),
(28, 19, 0, 'Pasta  ', 5, 4.00, NULL),
(29, 20, 0, 'Margarita', 1, 10.00, NULL),
(30, 21, 0, 'Pasta  ', 1, 4.00, NULL),
(31, 22, 0, 'Pasta  ', 1, 4.00, NULL),
(32, 23, 0, 'Tiramitsu', 1, 8.00, NULL),
(33, 24, 0, 'Tiramitsu', 1, 8.00, NULL),
(34, 25, 0, 'Tiramitsu', 9, 8.00, NULL),
(35, 26, 0, 'Tiramitsu', 15, 8.00, NULL),
(36, 27, 0, 'Pasta  ', 1, 4.00, NULL),
(37, 28, 0, 'Pasta  ', 1, 4.00, NULL),
(38, 28, 0, 'Margarita', 1, 10.00, NULL),
(39, 28, 0, 'Steak Haché', 1, 8.00, NULL),
(40, 29, 0, 'Pasta  ', 1, 4.00, NULL),
(41, 30, 0, 'Pasta  ', 1, 4.00, NULL),
(42, 31, 0, 'Pasta  ', 1, 4.00, NULL),
(43, 32, 0, 'Pasta  ', 7, 4.00, NULL),
(44, 33, 0, 'Pasta  ', 1, 4.00, NULL),
(45, 34, 0, 'Pasta  ', 2, 4.00, NULL),
(46, 35, 0, 'Pasta  ', 1, 4.00, NULL),
(47, 36, 0, 'Pasta  ', 1, 4.00, NULL),
(48, 37, 0, 'Tiramitsu', 2, 8.00, NULL),
(49, 37, 0, 'Pasta  ', 5, 4.00, NULL),
(50, 38, 0, 'Pasta  ', 3, 4.00, NULL),
(51, 38, 0, 'Tiramitsu', 4, 8.00, NULL),
(52, 39, 0, 'Tiramitsu', 8, 8.00, NULL),
(53, 39, 0, 'Pasta  ', 10, 4.00, NULL),
(54, 39, 0, 'Margarita', 1, 10.00, NULL),
(55, 39, 0, 'Steak Haché', 3, 8.00, NULL),
(56, 39, 0, 'salade caesar', 6, 8.00, NULL),
(57, 40, 0, 'Steak Haché', 1, 8.00, NULL),
(58, 40, 0, 'Margarita', 3, 10.00, NULL),
(59, 40, 0, 'Pasta  ', 1, 4.00, NULL),
(60, 41, 0, 'Pasta  ', 1, 4.00, NULL),
(61, 42, 0, 'salade caesar', 1, 8.00, NULL),
(83, 50, 0, 'Pasta  ', 1, 4.00, NULL),
(84, 51, 0, 'Tiramitsu', 1, 8.00, NULL),
(85, 52, 0, 'Pasta  ', 1, 4.00, NULL),
(86, 53, 0, 'Margarita', 1, 10.00, NULL),
(87, 53, 0, 'Pasta  ', 1, 4.00, NULL),
(88, 54, 0, 'Margarita', 1, 10.00, NULL),
(89, 54, 0, 'Pasta  ', 1, 4.00, NULL),
(90, 55, 0, 'Pasta  ', 8, 4.00, NULL),
(91, 56, 0, 'Pasta  ', 1, 4.00, NULL),
(92, 57, 0, 'Pasta  ', 7, 4.00, NULL),
(93, 58, 0, 'Pasta  ', 1, 4.00, NULL),
(94, 59, 0, 'Pasta  ', 1, 4.00, NULL),
(95, 59, 0, 'Margarita', 1, 10.00, NULL),
(96, 60, 0, 'salade caesar', 1, 8.00, NULL),
(97, 61, 0, 'Pasta  ', 1, 4.00, NULL),
(98, 61, 0, 'Tiramitsu', 1, 8.00, NULL),
(99, 61, 0, 'salade caesar', 1, 8.00, NULL),
(100, 61, 0, 'Margarita', 1, 10.00, NULL),
(101, 61, 0, 'Steak Haché', 1, 8.00, NULL),
(102, 62, 0, 'Pasta  ', 1, 4.00, NULL),
(103, 63, 0, 'salade caesar', 1, 8.00, NULL),
(104, 64, 0, 'Margarita', 1, 10.00, NULL),
(105, 65, 0, 'Margarita', 1, 10.00, NULL),
(106, 65, 0, 'Pasta  ', 1, 4.00, NULL),
(107, 66, 0, 'salade caesar', 10, 8.00, NULL),
(108, 66, 0, 'Margarita', 6, 10.00, NULL),
(109, 66, 0, 'Pasta  ', 2, 4.00, NULL),
(110, 67, 0, 'Margarita', 1, 10.00, NULL),
(111, 67, 0, 'Steak Haché', 1, 8.00, NULL),
(112, 68, 0, 'Pasta  ', 1, 4.00, NULL),
(113, 69, 0, 'Pasta  ', 1, 4.00, NULL),
(114, 70, 0, 'Pasta  ', 1, 4.00, NULL),
(115, 71, 0, 'Pasta  ', 1, 4.00, NULL),
(116, 71, 0, 'Margarita', 1, 10.00, NULL),
(117, 72, 0, 'Salade caesar', 32, 8.00, NULL),
(118, 73, 0, 'Steak Haché', 30, 8.00, NULL),
(119, 73, 0, 'Margarita', 12, 10.00, NULL),
(120, 74, 0, 'Margarita', 37, 10.00, NULL),
(121, 75, 0, 'Pasta  ', 1, 4.00, NULL),
(122, 75, 0, 'Tiramitsu', 1, 8.00, NULL),
(123, 75, 0, 'Margarita', 1, 10.00, NULL),
(124, 75, 0, 'Steak Haché', 1, 8.00, NULL),
(125, 76, 0, 'Pasta  ', 9, 4.00, NULL),
(126, 76, 0, 'Salade caesar', 1, 8.00, NULL),
(127, 77, 0, 'Salade caesar', 4, 8.00, NULL),
(128, 77, 0, 'Pasta  ', 1, 4.00, NULL),
(129, 78, 0, 'Pasta  ', 1, 4.00, NULL),
(130, 79, 0, 'Tiramitsu', 4, 8.00, NULL),
(131, 79, 0, 'Pasta  ', 10, 4.00, NULL),
(132, 80, 0, 'Salade caesar', 1, 8.00, NULL),
(133, 81, 0, 'Pasta  ', 1, 4.00, NULL),
(134, 81, 0, 'Margarita', 1, 10.00, NULL),
(135, 82, 0, 'Pasta  ', 1, 4.00, NULL),
(136, 83, 0, 'Pasta  ', 1, 4.00, NULL),
(137, 84, 0, 'Pasta  ', 1, 4.00, NULL),
(138, 85, 0, 'Pasta  ', 2, 4.00, NULL),
(139, 86, 0, 'Pasta  ', 1, 4.00, NULL),
(140, 87, 0, 'Pasta  ', 1, 4.00, NULL),
(141, 88, 0, 'Pasta  ', 1, 4.00, NULL),
(142, 89, 0, 'Pasta  ', 1, 4.00, NULL),
(143, 90, 0, 'Pasta  ', 3, 4.00, NULL),
(144, 90, 0, 'Margarita', 1, 10.00, NULL),
(145, 91, 0, 'Pasta  ', 2, 4.00, NULL),
(146, 91, 0, 'Margarita', 1, 10.00, NULL),
(147, 92, 0, 'Pasta  ', 1, 4.00, NULL),
(148, 93, 0, 'Pasta  ', 1, 4.00, NULL),
(149, 93, 0, 'Tiramitsu', 1, 8.00, NULL),
(150, 93, 0, 'Salade caesar', 1, 8.00, NULL),
(151, 94, 0, 'Pasta  ', 1, 4.00, NULL),
(152, 95, 0, 'Pasta  ', 1, 4.00, NULL),
(153, 96, 0, 'Pasta  ', 1, 4.00, NULL),
(154, 97, 0, 'Pasta  ', 1, 4.00, NULL),
(155, 97, 0, 'Salade caesar', 1, 8.00, NULL),
(156, 98, 0, 'Pasta  ', 1, 4.00, NULL),
(157, 99, 0, 'Pasta  ', 1, 4.00, NULL),
(158, 100, 0, 'Pasta  ', 1, 4.00, NULL),
(159, 101, 0, 'Tiramitsu', 2, 8.00, NULL),
(160, 102, 0, 'Pasta  ', 4, 4.00, NULL),
(161, 102, 0, 'Salade caesar', 1, 8.00, NULL),
(162, 103, 0, 'Pasta  ', 1, 4.00, NULL),
(163, 103, 0, 'Salade caesar', 5, 8.00, NULL),
(164, 104, 0, 'Margarita', 1, 10.00, NULL),
(165, 105, 0, 'Pasta  ', 1, 4.00, NULL),
(166, 106, 0, 'Pasta  ', 2, 4.00, NULL),
(167, 106, 0, 'Margarita', 1, 10.00, NULL),
(168, 107, 0, 'Tiramitsu', 2, 8.00, NULL),
(169, 107, 0, 'Pasta  ', 2, 4.00, NULL),
(170, 108, 0, 'Tiramitsu', 1, 8.00, NULL),
(171, 108, 0, 'Pasta  ', 10, 4.00, NULL),
(172, 108, 0, 'Steak Haché', 1, 8.00, NULL),
(173, 109, 0, 'Pasta  ', 1, 4.00, NULL),
(174, 110, 0, 'Tiramitsu', 1, 8.00, NULL),
(175, 111, 0, 'Tiramitsu', 1, 8.00, NULL),
(176, 111, 0, 'Pasta  ', 2, 4.00, NULL),
(177, 112, 0, 'Salade caesar', 1, 8.00, NULL),
(178, 113, 0, 'Pasta  ', 1, 4.00, NULL),
(179, 114, 0, 'Pasta  ', 1, 4.00, NULL),
(180, 114, 0, 'Tiramitsu', 1, 8.00, NULL),
(181, 115, 0, 'Pasta  ', 1, 4.00, NULL),
(182, 116, 0, 'Margarita', 1, 10.00, NULL),
(183, 117, 0, 'Tiramitsu', 1, 8.00, NULL),
(184, 118, 0, 'Tiramitsu', 1, 8.00, NULL),
(185, 119, 0, 'Pasta  ', 1, 4.00, NULL),
(186, 120, 0, 'Pasta  ', 1, 4.00, NULL),
(187, 120, 0, 'Tiramitsu', 1, 8.00, NULL),
(188, 121, 0, 'Salade caesar', 1, 8.00, NULL),
(189, 122, 0, 'Salade caesar', 1, 8.00, NULL),
(190, 122, 0, 'Pasta  ', 1, 4.00, NULL),
(191, 123, 0, 'Pasta  ', 1, 4.00, NULL),
(192, 123, 0, 'Margarita', 1, 10.00, NULL),
(193, 123, 0, 'Steak Haché', 1, 8.00, NULL),
(194, 123, 0, 'Tiramitsu', 1, 8.00, NULL),
(195, 124, 0, 'Pasta  ', 13, 4.00, NULL),
(196, 124, 0, 'Tiramitsu', 1, 8.00, NULL),
(197, 125, 0, 'Pasta  ', 1, 4.00, NULL),
(198, 126, 0, 'Pasta  ', 1, 4.00, NULL),
(199, 127, 0, 'Pasta  ', 1, 4.00, NULL),
(200, 128, 0, 'Pasta  ', 1, 4.00, NULL),
(201, 128, 0, 'Tiramitsu', 1, 8.00, NULL),
(202, 128, 0, 'Salade caesar', 1, 8.00, NULL),
(203, 129, 0, 'EAU', 1, 2.00, NULL),
(204, 130, 0, 'EAU', 1, 1.00, NULL),
(205, 132, 0, 'Tiramitsu', 3, 10.00, 'sans lait'),
(206, 132, 0, 'Tiramitsu', 1, 10.00, ''),
(207, 133, 0, 'Boeuf', 1, 5.00, 'cuit à point svp'),
(208, 134, 0, 'Pasta  ', 1, 5.00, 'sans oignons'),
(209, 135, 0, 'EAU', 1, 1.00, 'avec glacon stp'),
(210, 135, 0, 'Tiramitsu', 5, 10.00, 'leger stp sans crème svp'),
(211, 135, 0, 'Salade caesar', 2, 10.00, 'Sans sauce svp, ou la mettre à côté plutot merci'),
(212, 136, 0, 'Margarita', 1, 15.00, ''),
(213, 136, 0, 'Tiramitsu', 1, 10.00, ''),
(214, 137, 0, 'Pasta  ', 1, 5.00, 'sans rien'),
(215, 137, 0, 'Margarita', 1, 15.00, ''),
(216, 138, 0, 'Pasta  ', 3, 5.00, 'Sans parmesans'),
(217, 139, 0, 'Pasta  ', 1, 5.00, 'sans parmesans'),
(218, 139, 0, 'Pasta  ', 2, 5.00, ''),
(219, 139, 0, 'Salade caesar', 1, 10.00, 'Svp, la sauce à côté'),
(220, 140, 0, 'Pasta  ', 1, 5.00, ''),
(221, 141, 0, 'EAU', 1, 1.00, ''),
(222, 142, 0, 'Boeuf', 1, 5.00, ''),
(223, 143, 0, 'EAU', 1, 1.00, ''),
(224, 144, 0, 'Margarita', 1, 15.00, 'sans oignons'),
(225, 145, 0, 'EAU', 1, 1.00, ''),
(226, 146, 0, 'Boeuf', 1, 5.00, ''),
(227, 146, 0, 'EAU', 1, 1.00, ''),
(228, 146, 0, 'Salade caesar', 1, 10.00, ''),
(229, 147, 0, 'Pasta  ', 1, 5.00, ''),
(230, 148, 0, 'Salade caesar', 1, 10.00, ''),
(231, 149, 0, 'Pasta  ', 1, 5.00, ''),
(232, 150, 0, 'Pasta  ', 1, 5.00, ''),
(233, 151, 0, 'Pasta  ', 1, 5.00, 'Sans oignons NI SAUCE PIQUANTE SVP'),
(234, 152, 0, 'Pasta  ', 1, 5.00, ''),
(235, 152, 0, 'Tiramitsu', 1, 10.00, ''),
(236, 152, 0, 'Salade caesar', 1, 10.00, ''),
(237, 152, 0, 'EAU', 1, 1.00, ''),
(238, 152, 0, 'Boeuf', 1, 5.00, ''),
(239, 152, 0, 'Steak Haché', 1, 10.00, ''),
(240, 152, 0, 'Steak Haché', 1, 10.00, 'jhiihzakgjioaerjzgokdjqjkljgbhjkdsqhq'),
(241, 153, 0, 'Pasta  ', 1, 5.00, ''),
(242, 153, 0, 'Tiramitsu', 1, 10.00, ''),
(243, 153, 0, 'Salade caesar', 1, 10.00, ''),
(244, 153, 0, 'EAU', 3, 1.00, ''),
(245, 153, 0, 'Boeuf', 2, 5.00, ''),
(246, 154, 0, 'Tiramitsu', 1, 10.00, 'je veux du chocolat et non du café'),
(247, 155, 0, 'Boeuf', 3, 5.00, 'uguihjkhgjghhj'),
(248, 156, 0, 'Steak Haché', 1, 10.00, ''),
(249, 157, 0, 'Pasta  ', 1, 5.00, ''),
(250, 157, 0, 'Tiramitsu', 1, 10.00, ''),
(251, 157, 0, 'Margarita', 1, 15.00, ''),
(252, 157, 0, 'Steak Haché', 1, 10.00, ''),
(253, 157, 0, 'Boeuf', 1, 5.00, 'jioogejhvuinqfdbkljkqb'),
(254, 158, 0, 'Pasta  ', 1, 5.00, ''),
(255, 158, 0, 'Tiramitsu', 1, 10.00, ''),
(256, 158, 0, 'Margarita', 1, 15.00, ''),
(257, 158, 0, 'Steak Haché', 1, 10.00, 'vsqddsVSvbqb'),
(258, 159, 0, 'Tiramitsu', 1, 10.00, ''),
(259, 160, 0, 'Pasta  ', 1, 5.00, ''),
(260, 160, 0, 'Tiramitsu', 3, 10.00, ''),
(261, 160, 0, 'Pasta  ', 3, 5.00, 'ogaqgqsdgq'),
(262, 161, 0, 'Pasta  ', 1, 5.00, ''),
(263, 162, 0, 'Pasta  ', 1, 5.00, ''),
(264, 163, 0, 'Pasta  ', 1, 5.00, ''),
(265, 164, 0, 'Pasta  ', 1, 5.00, ''),
(266, 164, 0, 'Tiramitsu', 7, 10.00, ''),
(267, 164, 0, 'Margarita', 1, 15.00, ''),
(268, 164, 0, 'Steak Haché', 1, 10.00, ''),
(269, 164, 0, 'Boeuf', 4, 5.00, ''),
(270, 165, 0, 'Pasta  ', 2, 5.00, ''),
(271, 165, 0, 'Tiramitsu', 1, 10.00, '');

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
