-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 13 fév. 2026 à 10:47
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

DELIMITER $$
--
-- Procédures
--
DROP PROCEDURE IF EXISTS `calculate_daily_stats`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `calculate_daily_stats` (IN `target_date` DATE)   BEGIN
    INSERT INTO daily_stats (
        stat_date,
        total_sessions,
        total_orders,
        total_revenue,
        avg_session_duration,
        avg_order_value
    )
    SELECT 
        target_date,
        COUNT(DISTINCT ts.id),
        COUNT(DISTINCT o.id),
        COALESCE(SUM(o.total_price), 0),
        COALESCE(AVG(TIMESTAMPDIFF(MINUTE, ts.opened_at, ts.closed_at)), 0),
        COALESCE(AVG(o.total_price), 0)
    FROM table_sessions ts
    LEFT JOIN orders o ON ts.session_token = o.session_token
    WHERE DATE(ts.opened_at) = target_date
    ON DUPLICATE KEY UPDATE
        total_sessions = VALUES(total_sessions),
        total_orders = VALUES(total_orders),
        total_revenue = VALUES(total_revenue),
        avg_session_duration = VALUES(avg_session_duration),
        avg_order_value = VALUES(avg_order_value);
END$$

DROP PROCEDURE IF EXISTS `cleanup_old_data`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `cleanup_old_data` ()   BEGIN
    DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    DELETE FROM dynamic_qr_codes WHERE status IN ('expired', 'revoked') AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    DELETE FROM pos_export_log WHERE exported_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    DELETE FROM admin_activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    SELECT 'Nettoyage terminé' as status;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `active_dynamic_qrs`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `active_dynamic_qrs`;
CREATE TABLE IF NOT EXISTS `active_dynamic_qrs` (
`id` int
,`qr_token` varchar(64)
,`table_id` int
,`session_id` int
,`status` enum('active','expired','revoked')
,`scanned_count` int
,`created_at` datetime
,`expires_at` datetime
,`last_scanned_at` datetime
,`table_number` varchar(20)
,`table_name` varchar(100)
,`minutes_remaining` bigint
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `active_table_sessions`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `active_table_sessions`;
CREATE TABLE IF NOT EXISTS `active_table_sessions` (
`id` int
,`table_id` int
,`table_number` varchar(20)
,`table_name` varchar(100)
,`session_token` varchar(64)
,`status` enum('OPEN','CLOSED')
,`opened_at` datetime
,`expires_at` datetime
,`opened_by` varchar(100)
,`total_orders` int
,`minutes_remaining` bigint
);

-- --------------------------------------------------------

--
-- Structure de la table `admin_activity_log`
--

DROP TABLE IF EXISTS `admin_activity_log`;
CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_user` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `details` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`admin_user`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_login`
--

INSERT INTO `admin_login` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$roPOqeJCMbrlKyIgxDZhEeg4sSYmQ7AZHDN3IxFW0xvBVvcs9hrCC', 'admin'),
(3, 'Pape', '$2y$10$T/5U1yIpKeLE2mndKX6vFe79XgrSgN84sve7aMlwBgrIXnasG9lrG', 'patron'),
(8, 'Arthur', '$2y$10$L/RDSIVYSNFzcxw9qB9C1OOdzUMDSKhPi2cQ8OZ5ubb.zDzIcxmUW', 'serveur');

-- --------------------------------------------------------

--
-- Structure de la table `daily_stats`
--

DROP TABLE IF EXISTS `daily_stats`;
CREATE TABLE IF NOT EXISTS `daily_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `total_sessions` int DEFAULT '0',
  `total_orders` int DEFAULT '0',
  `total_revenue` decimal(10,2) DEFAULT '0.00',
  `avg_session_duration` int DEFAULT '0',
  `avg_order_value` decimal(10,2) DEFAULT '0.00',
  `peak_hour` int DEFAULT NULL,
  `busiest_table_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_date` (`stat_date`),
  KEY `idx_date` (`stat_date`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `daily_stats`
--

INSERT INTO `daily_stats` (`id`, `stat_date`, `total_sessions`, `total_orders`, `total_revenue`, `avg_session_duration`, `avg_order_value`, `peak_hour`, `busiest_table_id`, `created_at`, `updated_at`) VALUES
(1, '2026-02-05', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-05 09:05:52', '2026-02-05 09:05:52'),
(2, '2026-02-06', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-06 08:39:40', '2026-02-06 08:39:40'),
(3, '2026-02-07', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-07 20:05:31', '2026-02-07 20:05:31'),
(4, '2026-02-08', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-08 13:23:14', '2026-02-08 13:23:14'),
(5, '2026-02-09', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-09 08:45:00', '2026-02-09 08:45:00'),
(6, '2026-02-10', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-10 09:23:02', '2026-02-10 09:23:02'),
(7, '2026-02-13', 0, 0, 0.00, 0, 0.00, NULL, NULL, '2026-02-13 08:44:15', '2026-02-13 08:44:15');

-- --------------------------------------------------------

--
-- Structure de la table `dynamic_qr_codes`
--

DROP TABLE IF EXISTS `dynamic_qr_codes`;
CREATE TABLE IF NOT EXISTS `dynamic_qr_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `qr_token` varchar(64) NOT NULL,
  `table_id` int NOT NULL,
  `session_id` int DEFAULT NULL,
  `status` enum('active','expired','revoked') DEFAULT 'active',
  `scanned_count` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `last_scanned_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `idx_token` (`qr_token`),
  KEY `idx_table` (`table_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `notification_id` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `priority` int DEFAULT '3',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_id` (`notification_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`),
  KEY `idx_type` (`type`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `notification_id`, `type`, `title`, `message`, `data`, `priority`, `is_read`, `created_at`, `read_at`) VALUES
(1, 'notif_welcome', 'info', 'Bienvenue', 'Système de notifications activé avec succès !', NULL, 3, 0, '2026-02-04 21:02:09', NULL),
(2, 'notif_test', 'info', 'Installation réussie', 'Toutes les fonctionnalités futures sont installées.', NULL, 3, 0, '2026-02-04 21:02:09', NULL),
(4, 'order_168_1770579010', 'new_order', 'Nouvelle commande', 'Commande CMD026032 - Table 1 - 16.00 €', '{\"total\": 16.00, \"order_id\": 168, \"table_name\": \"Table 1\", \"order_number\": \"CMD026032\"}', 1, 0, '2026-02-08 20:30:10', NULL),
(5, 'order_169_1770579459', 'new_order', 'Nouvelle commande', 'Commande CMD54EE9C - Table 1 - 16.00 €', '{\"total\": 16.00, \"order_id\": 169, \"table_name\": \"Table 1\", \"order_number\": \"CMD54EE9C\"}', 1, 0, '2026-02-08 20:37:39', NULL),
(6, 'order_170_1770579850', 'new_order', 'Nouvelle commande', 'Commande CMDBA73A9 - Table 1 - 37.00 €', '{\"total\": 37.00, \"order_id\": 170, \"table_name\": \"Table 1\", \"order_number\": \"CMDBA73A9\"}', 1, 0, '2026-02-08 20:44:10', NULL),
(7, 'order_171_1770625942', 'new_order', 'Nouvelle commande', 'Commande CMD7C9C52 - Table 1 - 45.00 €', '{\"total\": 45.00, \"order_id\": 171, \"table_name\": \"Table 1\", \"order_number\": \"CMD7C9C52\"}', 1, 0, '2026-02-09 09:32:22', NULL),
(8, 'order_172_1770626189', 'new_order', 'Nouvelle commande', 'Commande CMD42FB31 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 172, \"table_name\": \"Table 1\", \"order_number\": \"CMD42FB31\"}', 1, 0, '2026-02-09 09:36:29', NULL),
(9, 'order_173_1770629486', 'new_order', 'Nouvelle commande', 'Commande CMD5571EA - Table inconnue - 132.00 €', '{\"total\": 132.00, \"order_id\": 173, \"table_name\": \"\", \"order_number\": \"CMD5571EA\"}', 1, 0, '2026-02-09 10:31:26', NULL),
(10, 'order_174_1770629617', 'new_order', 'Nouvelle commande', 'Commande CMD96655A - Table inconnue - 45.00 €', '{\"total\": 45.00, \"order_id\": 174, \"table_name\": \"\", \"order_number\": \"CMD96655A\"}', 1, 0, '2026-02-09 10:33:37', NULL),
(11, 'order_175_1770644106', 'new_order', 'Nouvelle commande', 'Commande CMDFDF909 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 175, \"table_name\": \"Table 1\", \"order_number\": \"CMDFDF909\"}', 1, 0, '2026-02-09 14:35:06', NULL),
(12, 'order_176_1770644321', 'new_order', 'Nouvelle commande', 'Commande CMD68FA33 - Table 1 - 50.00 €', '{\"total\": 50.00, \"order_id\": 176, \"table_name\": \"Table 1\", \"order_number\": \"CMD68FA33\"}', 1, 0, '2026-02-09 14:38:41', NULL),
(13, 'order_177_1770644337', 'new_order', 'Nouvelle commande', 'Commande CMD6F4A77 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 177, \"table_name\": \"Table 1\", \"order_number\": \"CMD6F4A77\"}', 1, 0, '2026-02-09 14:38:57', NULL),
(14, 'order_178_1770800837', 'new_order', 'Nouvelle commande', 'Commande CMD71A5FB - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 178, \"table_name\": \"Table 1\", \"order_number\": \"CMD71A5FB\"}', 1, 0, '2026-02-11 10:07:17', NULL),
(15, 'order_179_1770800864', 'new_order', 'Nouvelle commande', 'Commande CMDF3ADA4 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 179, \"table_name\": \"Table 1\", \"order_number\": \"CMDF3ADA4\"}', 1, 0, '2026-02-11 10:07:44', NULL),
(16, 'order_180_1770800911', 'new_order', 'Nouvelle commande', 'Commande CMDA5914E - Table 1 - 35.00 €', '{\"total\": 35.00, \"order_id\": 180, \"table_name\": \"Table 1\", \"order_number\": \"CMDA5914E\"}', 1, 0, '2026-02-11 10:08:31', NULL),
(17, 'order_181_1770973500', 'new_order', 'Nouvelle commande', 'Commande CMDA007BB - Table 5 - 16.00 €', '{\"total\": 16.00, \"order_id\": 181, \"table_name\": \"Table 5\", \"order_number\": \"CMDA007BB\"}', 1, 0, '2026-02-13 10:05:00', NULL),
(18, 'order_182_1770977281', 'new_order', 'Nouvelle commande', 'Commande CMD4B0060 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 182, \"table_name\": \"Table 1\", \"order_number\": \"CMD4B0060\"}', 1, 0, '2026-02-13 11:08:01', NULL),
(19, 'order_183_1770977296', 'new_order', 'Nouvelle commande', 'Commande CMD74D6C9 - Table 1 - 21.00 €', '{\"total\": 21.00, \"order_id\": 183, \"table_name\": \"Table 1\", \"order_number\": \"CMD74D6C9\"}', 1, 0, '2026-02-13 11:08:16', NULL),
(20, 'order_184_1770978005', 'new_order', 'Nouvelle commande', 'Commande CMDCCC4E9 - Table 1 - 15.00 €', '{\"total\": 15.00, \"order_id\": 184, \"table_name\": \"Table 1\", \"order_number\": \"CMDCCC4E9\"}', 1, 0, '2026-02-13 11:20:05', NULL),
(21, 'order_185_1770978028', 'new_order', 'Nouvelle commande', 'Commande CMD2CDED6 - Table 1 - 90.00 €', '{\"total\": 90.00, \"order_id\": 185, \"table_name\": \"Table 1\", \"order_number\": \"CMD2CDED6\"}', 1, 0, '2026-02-13 11:20:28', NULL),
(22, 'order_186_1770978150', 'new_order', 'Nouvelle commande', 'Commande CMD1852A7 - Table 1 - 6.00 €', '{\"total\": 6.00, \"order_id\": 186, \"table_name\": \"Table 1\", \"order_number\": \"CMD1852A7\"}', 1, 0, '2026-02-13 11:22:30', NULL),
(23, 'order_187_1770978890', 'new_order', 'Nouvelle commande', 'Commande CMDC3280D - Table 10 - 21.00 €', '{\"total\": 21.00, \"order_id\": 187, \"table_name\": \"Table 10\", \"order_number\": \"CMDC3280D\"}', 1, 0, '2026-02-13 11:34:50', NULL);

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
  `session_token` varchar(64) DEFAULT NULL,
  `order_type` varchar(50) NOT NULL DEFAULT 'table',
  PRIMARY KEY (`id`),
  KEY `idx_session_token` (`session_token`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `payment_method`, `total_price`, `order_date`, `status`, `ready_time`, `served_time`, `restaurant_id`, `table_id`, `created_at`, `user_id`, `session_token`, `order_type`) VALUES
(1, 'CMD123456', 'Se', 'Especes', 8.00, '2025-08-04 16:05:39', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(3, 'CMD5DCBC2', 'A', 'Especes', 8.00, '2025-08-05 08:23:50', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(4, 'CMD363275', 'Boris', 'PayPal', 8.00, '2025-08-05 12:22:22', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(5, 'CMD85A82A', 'Salut', 'Carte', 42.00, '2025-08-05 12:23:05', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(6, 'CMD86F1B3', 'Bro', 'Carte', 22.00, '2025-08-05 12:23:40', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(7, 'CMD583E4D', 'Bonjour', 'Especes', 44.00, '2025-08-05 15:47:06', 'Terminée', NULL, NULL, 1, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(8, 'CMDEFFE05', 'Barbara', 'Especes', 4.00, '2025-08-05 15:50:10', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(9, 'CMD68ADC1', 'Nicos', 'Carte', 24.00, '2025-08-05 15:53:13', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(10, 'CMD980C34', 'SEBY', 'PayPal', 148.00, '2025-08-05 16:09:38', 'Terminée', NULL, NULL, 1, 20, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(11, 'CMD58D75E', 'Seby', 'Especes', 4.00, '2025-08-05 16:11:14', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(12, 'CMD5873FF', 'Seb', 'Especes', 8.00, '2025-08-05 21:40:37', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(13, 'CMDD0A990', 'Manon', 'Especes', 12.00, '2025-08-05 21:43:26', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(14, 'CMD9788A0', 'SEB', 'Especes', 14.00, '2025-08-05 21:46:47', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(15, 'CMD6D6C71', 'A', 'Especes', 4.00, '2025-08-05 21:47:21', 'Terminée', NULL, NULL, 1, 5, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(16, 'CMD9CAAE8', 'Seb', 'Especes', 4.00, '2025-08-06 12:59:11', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(17, 'CMD4AF6E1', 'Seb', 'Especes', 4.00, '2025-08-06 13:04:52', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(18, 'CMD5CF7C8', 'Seb', 'Especes', 20.00, '2025-08-06 13:12:59', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(19, 'CMDD57DAD', 'Logan', 'Carte', 20.00, '2025-08-07 11:16:43', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(20, 'CMD7891C5', 'SebY', 'Especes', 10.00, '2025-08-07 11:20:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(21, 'CMD6BDFA9', 'Marchal', 'Carte', 4.00, '2025-08-07 11:29:00', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(22, 'CMDD29FEC', 'BRO', 'Especes', 4.00, '2025-08-07 15:01:44', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(23, 'CMDAD54CD', 'Seb', 'Especes', 8.00, '2025-08-07 15:05:17', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(24, 'CMDC950E0', 'YES', 'Especes', 8.00, '2025-08-07 15:07:36', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(25, 'CMD9DE1F1', 'Gourmand', 'PayPal', 72.00, '2025-08-07 15:10:04', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(26, 'CMD5DCC5C', 'Pape', 'Carte', 120.00, '2025-08-07 15:14:26', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(27, 'CMD204DED', 'se', 'Especes', 4.00, '2025-08-07 15:19:31', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(28, 'CMD1C4A76', 'Manou', 'Carte', 22.00, '2025-08-07 22:51:26', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(29, 'CMDF86D6A', 'Seb', 'Especes', 4.00, '2025-08-07 22:53:10', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(30, 'CMD507969', 'Bro', 'PayPal', 4.00, '2025-08-07 22:55:09', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(31, 'CMD265CF2', 's', 'Especes', 4.00, '2025-08-07 23:02:22', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(32, 'CMD8850BD', 'Money', 'Carte', 28.00, '2025-08-07 23:04:16', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(33, 'CMD423712', 'Troll', 'Carte', 4.00, '2025-08-07 23:07:00', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(34, 'CMD23421C', 'e', 'Especes', 8.00, '2025-08-07 23:09:06', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(35, 'CMD1B306E', 'A', 'Especes', 4.00, '2025-08-07 23:15:10', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(36, 'CMDB0F422', 'ssss', 'Especes', 4.00, '2025-08-07 23:19:35', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(37, 'CMD5F0C9B', 'Seb', 'Especes', 36.00, '2025-08-10 10:45:02', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(38, 'CMD54CFB7', 'Seb', 'Especes', 44.00, '2025-08-10 14:16:57', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(39, 'CMD69C98E', 'SEB', 'Especes', 186.00, '2025-08-10 14:36:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(40, 'CMDD57BBD', 'SEB', 'Especes', 42.00, '2025-08-10 15:27:45', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(41, 'CMDD3542C', 'Seb', 'Carte', 4.00, '2025-08-10 15:29:49', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(42, 'CMDB2032F', 'seb', 'Especes', 8.00, '2025-08-10 15:32:35', 'Terminée', NULL, NULL, NULL, 0, '2025-08-13 11:29:38', NULL, NULL, 'table'),
(50, 'CMDDEC69E', 'Seb', 'Espèces', 4.00, '2025-08-13 23:47:51', 'Terminée', NULL, NULL, NULL, 5, '2025-08-13 23:47:51', NULL, NULL, 'table'),
(51, 'CMDA97C95', 'Seb', 'Espèces', 8.00, '2025-08-13 23:49:02', 'Terminée', NULL, NULL, NULL, 5, '2025-08-13 23:49:02', NULL, NULL, 'table'),
(52, 'CMDC5225D', 'Seb', 'Espèces', 4.00, '2025-08-15 19:28:06', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:28:06', NULL, NULL, 'table'),
(53, 'CMDD815C2', 'Bro', 'Espèces', 14.00, '2025-08-15 19:28:31', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:28:31', NULL, NULL, 'table'),
(54, 'CMD50FE94', 'Yanis', 'Espèces', 14.00, '2025-08-15 19:29:16', 'Terminée', NULL, NULL, NULL, 8, '2025-08-15 19:29:16', NULL, NULL, 'table'),
(55, 'CMD98A32B', 'Seb', 'Espèces', 32.00, '2025-08-15 19:32:29', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:32:29', NULL, NULL, 'table'),
(56, 'CMD9E74AD', 'Seby', 'Espèces', 4.00, '2025-08-15 19:42:59', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:42:59', '0', NULL, 'table'),
(57, 'CMD076518', 'Seby', 'Espèces', 28.00, '2025-08-15 19:44:01', 'Terminée', NULL, NULL, NULL, 6, '2025-08-15 19:44:01', NULL, NULL, 'table'),
(58, 'CMD954ECC', 'Seb', 'Espèces', 4.00, '2025-08-15 19:46:00', 'Terminée', NULL, NULL, NULL, 1, '2025-08-15 19:46:00', 'user_689f71965b7e82.37210285', NULL, 'table'),
(59, 'CMD224D72', 'Seb', 'Espèces', 14.00, '2025-08-15 19:49:09', 'Terminée', NULL, NULL, NULL, 8, '2025-08-15 19:49:09', 'user_689f71965b7e82.37210285', NULL, 'table'),
(60, 'CMDBD23E6', 'Jeff', 'Espèces', 8.00, '2025-08-15 19:49:47', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:49:47', 'user_689f71965b7e82.37210285', NULL, 'table'),
(61, 'CMD93B4CE', 'Brother', 'Carte bancaire', 38.00, '2025-08-15 19:54:40', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:54:40', 'user_689f71965b7e82.37210285', NULL, 'table'),
(62, 'CMD04CE05', 'Brother', 'Espèces', 4.00, '2025-08-15 19:55:12', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:55:12', 'user_689f71965b7e82.37210285', NULL, 'table'),
(63, 'CMDB2C522', 'Seb', 'Carte bancaire', 8.00, '2025-08-15 19:55:30', 'Terminée', NULL, NULL, NULL, 7, '2025-08-15 19:55:30', 'user_689f71965b7e82.37210285', NULL, 'table'),
(64, 'CMDACA340', 'Seb', 'Espèces', 10.00, '2025-08-16 09:36:31', 'Terminée', NULL, NULL, NULL, 8, '2025-08-16 09:36:31', 'user_68a0324b56b1c5.52618241', NULL, 'table'),
(65, 'CMD925F38', 'SEBY', 'Carte bancaire', 14.00, '2025-08-16 09:37:40', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 09:37:40', 'user_68a0324b56b1c5.52618241', NULL, 'table'),
(66, 'CMD7C4924', 'Seb', 'Espèces', 148.00, '2025-08-16 09:55:02', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 09:55:02', 'user_68a0324b56b1c5.52618241', NULL, 'table'),
(67, 'CMDC68205', 'Karim', 'Carte bancaire', 18.00, '2025-08-16 10:12:23', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 10:12:23', 'user_68a0324b56b1c5.52618241', NULL, 'table'),
(68, 'CMDD752BA', 'Seb', 'Espèces', 4.00, '2025-08-16 10:49:39', 'Terminée', NULL, NULL, NULL, 20, '2025-08-16 10:49:39', 'user_68a0461a2be149.40269937', NULL, 'table'),
(69, 'CMD91BE2E', 'Seb', 'Espèces', 4.00, '2025-08-17 10:28:09', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:28:09', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(70, 'CMD04557A', 'KARIM', 'Carte bancaire', 4.00, '2025-08-17 10:32:49', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:32:49', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(71, 'CMD5B798A', 'BROTHER', 'Espèces', 14.00, '2025-08-17 10:35:48', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:35:48', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(72, 'CMDFE4CFA', 'SLD', 'Carte bancaire', 256.00, '2025-08-17 10:43:48', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:43:48', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(73, 'CMD934357', 'Pizza', 'Espèces', 360.00, '2025-08-17 10:44:31', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:44:31', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(74, 'CMDACED28', 'vitto', 'Carte bancaire', 370.00, '2025-08-17 10:44:58', 'Terminée', NULL, NULL, NULL, 1, '2025-08-17 10:44:58', 'user_68a18fdbc785b3.50946943', NULL, 'table'),
(75, 'CMD6801D7', 'Pape', 'Espèces', 30.00, '2025-08-18 09:51:32', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 09:51:32', 'user_68a2d799e19b06.18219295', NULL, 'table'),
(76, 'CMDC833E9', 'Seb', 'Espèces', 44.00, '2025-08-18 10:18:46', 'Terminée', NULL, NULL, NULL, 5, '2025-08-18 10:18:46', 'user_68a2d799e19b06.18219295', NULL, 'table'),
(77, 'CMD404504', 'A', 'Espèces', 36.00, '2025-08-18 10:47:27', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 10:47:27', 'user_68a2d799e19b06.18219295', NULL, 'takeaway'),
(78, 'CMD52CA30', 'Seb', 'Espèces', 4.00, '2025-08-18 10:47:51', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 10:47:51', 'user_68a2d799e19b06.18219295', NULL, 'takeaway'),
(79, 'CMD69EEAA', 'Manon', 'Espèces', 72.00, '2025-08-18 11:12:01', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 11:12:01', 'user_68a2d799e19b06.18219295', NULL, 'takeaway'),
(80, 'CMDDA171A', 'Bro', 'PayPal', 8.00, '2025-08-18 11:15:54', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 11:15:54', 'user_68a2ee9bafb607.55408287', NULL, 'takeaway'),
(81, 'CMD84A409', 'Seb', 'Espèces', 14.00, '2025-08-18 18:03:06', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 18:03:06', 'user_68a34ea3a82fd1.51125907', NULL, 'takeaway'),
(82, 'CMD1AA193', 'Barbara', 'Carte bancaire', 4.00, '2025-08-18 18:10:17', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 18:10:17', 'user_68a34ea3a82fd1.51125907', NULL, 'takeaway'),
(83, 'CMD6CAF42', 'Seb', 'Espèces', 4.00, '2025-08-18 18:28:47', 'Terminée', NULL, NULL, NULL, 1, '2025-08-18 18:28:47', 'user_68a34ea3a82fd1.51125907', NULL, 'table'),
(84, 'CMD74CF30', 'A', 'Espèces', 4.00, '2025-08-18 18:29:34', 'Terminée', NULL, NULL, NULL, 4, '2025-08-18 18:29:34', 'user_68a34ea3a82fd1.51125907', NULL, 'table'),
(85, 'CMD5E34CB', 'Bro', 'Espèces', 8.00, '2025-08-18 19:24:26', 'Terminée', NULL, NULL, NULL, 4, '2025-08-18 19:24:26', 'user_68a34ea3a82fd1.51125907', NULL, 'table'),
(86, 'CMDED4D38', 'SEB', 'Espèces', 4.00, '2025-08-18 19:27:24', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:27:24', 'user_68a362677aa9f6.42476903', NULL, 'table'),
(87, 'CMDA525F5', 'nico', 'Carte bancaire', 4.00, '2025-08-18 19:31:59', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:31:59', 'user_68a362677aa9f6.42476903', NULL, 'table'),
(88, 'CMD5A1644', 'MANON', 'Carte bancaire', 4.00, '2025-08-18 19:34:12', 'Terminée', NULL, NULL, NULL, 8, '2025-08-18 19:34:12', 'user_68a362677aa9f6.42476903', NULL, 'table'),
(89, 'CMDE6A0D4', 'Seb', 'Espèces', 4.00, '2025-08-18 19:35:34', 'Terminée', NULL, NULL, NULL, 0, '2025-08-18 19:35:34', 'user_68a362677aa9f6.42476903', NULL, 'takeaway'),
(90, 'CMD73A5A8', 'Seb', 'Espèces', 22.00, '2025-08-19 13:25:58', 'Terminée', NULL, NULL, NULL, 8, '2025-08-19 13:25:58', 'user_68a45c0ff00190.66621759', NULL, 'table'),
(91, 'CMDBC442C', 'A', 'Carte bancaire', 18.00, '2025-08-19 13:26:12', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:26:12', 'user_68a45c0ff00190.66621759', NULL, 'takeaway'),
(92, 'CMDAB9B9F', 'Barbara', 'Espèces', 4.00, '2025-08-19 13:28:22', 'Terminée', NULL, NULL, NULL, 5, '2025-08-19 13:28:22', 'user_68a45c0ff00190.66621759', NULL, 'table'),
(93, 'CMD651C11', 'Barbara', 'Espèces', 20.00, '2025-08-19 13:28:55', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:28:55', 'user_68a45c0ff00190.66621759', NULL, 'takeaway'),
(94, 'CMDEC92A9', 'Seb', 'Espèces', 4.00, '2025-08-19 13:44:39', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:44:39', 'user_68a45c0ff00190.66621759', NULL, 'takeaway'),
(95, 'CMD98CDC8', 'SEB', 'Espèces', 4.00, '2025-08-19 13:54:43', 'Terminée', NULL, NULL, NULL, 0, '2025-08-19 13:54:43', 'user_68a45c0ff00190.66621759', NULL, 'takeaway'),
(96, 'CMDA564ED', 'Seb', 'Espèces', 4.00, '2025-08-28 20:45:36', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:45:36', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(97, 'CMDFB6E78', 'Seb', 'Espèces', 12.00, '2025-08-28 20:48:10', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:48:10', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(98, 'CMD717E73', 'A', 'Espèces', 4.00, '2025-08-28 20:50:18', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:50:18', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(99, 'CMD3D9E38', 'Barbara', 'Carte bancaire', 4.00, '2025-08-28 20:52:19', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:52:19', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(100, 'CMD38AD13', 'Barbara', 'Espèces', 4.00, '2025-08-28 20:53:24', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 20:53:24', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(101, 'CMD5E2787', 'Bro', 'Espèces', 16.00, '2025-08-28 21:08:52', 'Terminée', NULL, NULL, NULL, 8, '2025-08-28 21:08:52', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(102, 'CMD1CD850', 'Seb', 'Carte bancaire', 24.00, '2025-08-29 10:23:00', 'Terminée', NULL, NULL, NULL, 8, '2025-08-29 10:23:00', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(103, 'CMDC856ED', 'A', 'Espèces', 44.00, '2025-08-29 10:25:30', 'Terminée', NULL, NULL, NULL, 8, '2025-08-29 10:25:30', 'user_68aeb69cc2c9f1.29250474', NULL, 'table'),
(104, 'CMDD7FF15', 'Seb', 'Espèces', 10.00, '2025-08-31 10:43:23', 'Terminée', NULL, NULL, NULL, 0, '2025-08-31 10:43:23', 'user_68b40a48000e63.93993416', NULL, 'takeaway'),
(105, 'CMDAB0877', 'A', 'Espèces', 4.00, '2025-08-31 10:45:46', 'Terminée', NULL, NULL, NULL, 0, '2025-08-31 10:45:46', 'user_68b40a48000e63.93993416', NULL, 'takeaway'),
(106, 'CMD8202CF', 'baptiste', 'Espèces', 18.00, '2025-09-08 09:30:47', 'Terminée', NULL, NULL, NULL, 1, '2025-09-08 09:30:47', 'user_68be85e47fbe39.27996402', NULL, 'table'),
(107, 'CMD782E7C', 'Collet', 'Espèces', 24.00, '2025-09-16 09:27:27', 'Terminée', NULL, NULL, NULL, 1, '2025-09-16 09:27:27', 'user_68c90fe04f8d92.25986266', NULL, 'table'),
(108, 'CMDF6F36C', 'Client', 'Espèces', 56.00, '2025-10-23 16:14:36', 'Terminée', '2025-10-26 12:02:56', '2025-10-26 12:07:31', NULL, 1, '2025-10-23 16:14:36', 'user_68fa312f172d70.11086723', NULL, 'table'),
(109, 'CMD929B7C', 'Seb', 'Espèces', 4.00, '2025-10-23 16:18:49', 'Terminée', '2025-10-26 12:08:24', '2025-10-26 12:09:56', NULL, 1, '2025-10-23 16:18:49', 'user_68fa312f172d70.11086723', NULL, 'table'),
(110, 'CMDD253C2', 'A', 'Carte bancaire', 8.00, '2025-10-23 16:22:52', 'Terminée', '2025-10-26 12:08:26', '2025-10-26 12:10:00', NULL, 1, '2025-10-23 16:22:52', 'user_68fa3a2c0105e6.38169328', NULL, 'table'),
(111, 'CMD50BF11', 'Seb', 'Espèces', 16.00, '2025-10-23 16:32:10', 'Terminée', NULL, NULL, NULL, 1, '2025-10-23 16:32:10', 'user_68fa3a2c0105e6.38169328', NULL, 'table'),
(112, 'CMDD03A41', 'Seb', 'Carte bancaire', 8.00, '2025-10-26 11:12:26', 'Terminée', '2025-10-26 12:08:29', '2025-10-26 12:10:03', NULL, 1, '2025-10-26 11:12:26', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(113, 'CMD9CA0CB', 'Seb', 'Espèces', 4.00, '2025-10-26 11:12:55', 'Terminée', '2025-10-26 12:09:08', '2025-10-26 12:10:06', NULL, 1, '2025-10-26 11:12:55', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(114, 'CMD0CEFE0', 'A', 'Espèces', 12.00, '2025-10-26 11:15:53', 'Terminée', NULL, NULL, NULL, 1, '2025-10-26 11:15:53', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(115, 'CMD8A3466', 'Seb', 'Espèces', 4.00, '2025-10-26 12:10:23', 'Terminée', '2025-10-26 12:10:32', '2025-10-26 13:04:21', NULL, 1, '2025-10-26 12:10:23', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(116, 'CMD82D3B3', 'A', 'Espèces', 10.00, '2025-10-26 12:20:51', 'Terminée', '2025-10-26 12:21:27', '2025-10-26 13:04:16', NULL, 1, '2025-10-26 12:20:51', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(117, 'CMD28314A', 'Seb', 'Espèces', 8.00, '2025-10-26 13:02:41', 'Terminée', '2025-10-26 13:03:39', '2025-10-26 13:04:31', NULL, 1, '2025-10-26 13:02:41', 'user_68fdeab3e11309.58058228', NULL, 'table'),
(118, 'CMD555961', 'A', 'Espèces', 8.00, '2025-10-26 15:41:48', 'Terminée', NULL, NULL, NULL, 1, '2025-10-26 15:41:48', 'user_68fe330f6a7b51.05490179', NULL, 'table'),
(119, 'CMD8FB25E', 'Seb', 'Espèces', 4.00, '2025-10-26 15:46:38', 'Terminée', '2025-11-06 09:32:58', '2025-11-06 09:33:26', NULL, 1, '2025-10-26 15:46:38', 'user_68fe330f6a7b51.05490179', NULL, 'table'),
(120, 'CMD219A69', 'Seb', 'Espèces', 12.00, '2025-11-06 09:33:44', 'Terminée', '2025-11-06 09:33:59', '2025-11-06 09:34:09', NULL, 1, '2025-11-06 09:33:44', 'user_690c5d4bdc19f9.07975215', NULL, 'table'),
(121, 'CMD6EBE34', 'A', 'Espèces', 8.00, '2025-11-06 09:34:40', 'Terminée', '2025-11-06 09:34:52', '2025-11-06 09:35:12', NULL, 1, '2025-11-06 09:34:40', 'user_690c5d4bdc19f9.07975215', NULL, 'table'),
(122, 'CMDBE0DF8', 'Seb', 'Espèces', 12.00, '2025-11-06 09:38:47', 'Terminée', '2025-11-06 09:38:52', '2025-11-06 09:38:55', NULL, 1, '2025-11-06 09:38:47', 'user_690c5d4bdc19f9.07975215', NULL, 'table'),
(123, 'CMDB917CC', 'Barbara', 'Espèces', 30.00, '2025-11-06 14:22:03', 'Terminée', '2025-11-06 14:26:14', '2025-11-06 14:26:59', NULL, 1, '2025-11-06 14:22:03', 'user_690c9e991cf555.48432898', NULL, 'table'),
(124, 'CMD989C4F', 'Sébastien', 'Espèces', 60.00, '2025-11-06 14:43:32', 'Terminée', '2025-11-06 15:18:41', '2025-11-06 15:30:14', NULL, 1, '2025-11-06 14:43:32', 'user_690c9e991cf555.48432898', NULL, 'table'),
(125, 'CMD83A04F', 'Seb', 'Espèces', 4.00, '2025-11-06 14:50:37', 'Terminée', '2025-11-06 15:21:00', '2025-11-06 15:30:30', NULL, 1, '2025-11-06 14:50:37', 'user_690c9e991cf555.48432898', NULL, 'table'),
(126, 'CMD2DC84D', 'Seb', 'Espèces', 4.00, '2025-11-06 14:51:54', 'Terminée', '2025-11-06 15:33:14', '2025-11-06 15:38:01', NULL, 0, '2025-11-06 14:51:54', 'user_690c9e991cf555.48432898', NULL, 'takeaway'),
(127, 'CMDA81042', 'Bro', 'Espèces', 4.00, '2025-11-06 15:20:07', 'Terminée', '2025-11-06 15:37:58', '2025-11-06 15:38:03', NULL, 0, '2025-11-06 15:20:07', 'user_690c9e991cf555.48432898', NULL, 'takeaway'),
(128, 'CMD9E8A4C', 'Nicolas', 'Espèces', 20.00, '2025-11-06 15:43:31', 'Terminée', '2025-11-06 15:44:09', '2025-11-06 15:44:35', NULL, 5, '2025-11-06 15:43:31', 'user_690cb2d98ac0c5.78482544', NULL, 'table'),
(129, 'CMDD2199F', 'Seb', 'Espèces', 2.00, '2025-11-10 23:17:50', 'Terminée', '2025-12-15 10:22:37', '2025-12-15 10:24:11', NULL, 1, '2025-11-10 23:17:50', 'user_691263f98a5841.61372948', NULL, 'table'),
(130, 'CMD42AA57', 'A', 'Carte bancaire', 1.00, '2025-11-10 23:24:28', 'Terminée', '2025-11-11 22:53:59', '2025-11-11 22:54:08', NULL, 1, '2025-11-10 23:24:28', 'user_691263f98a5841.61372948', NULL, 'table'),
(131, 'CMDB35460', 'Seb', 'Carte bancaire', 40.00, '2025-11-11 13:21:52', 'Terminée', '2025-11-11 22:53:56', '2025-11-11 22:54:06', NULL, 1, '2025-11-11 13:21:52', 'user_691323b09ce9c4.50590639', NULL, 'table'),
(132, 'CMDDF4769', 'Seb', 'Carte bancaire', 40.00, '2025-11-11 13:22:39', 'Terminée', '2025-12-15 10:22:38', '2025-12-15 10:24:12', NULL, 1, '2025-11-11 13:22:39', 'user_691323b09ce9c4.50590639', NULL, 'table'),
(133, 'CMD6DECFC', 'A', 'Espèces', 5.00, '2025-11-11 22:10:25', 'Terminée', '2025-12-15 10:22:55', '2025-12-15 10:24:56', NULL, 1, '2025-11-11 22:10:25', 'user_6913a60dcfde69.53597725', NULL, 'table'),
(134, 'CMDB87451', 'Seb', 'Espèces', 5.00, '2025-11-11 22:26:26', 'Terminée', '2025-11-11 22:48:50', '2025-11-11 22:54:10', NULL, 1, '2025-11-11 22:26:26', 'user_6913a60dcfde69.53597725', NULL, 'table'),
(135, 'CMDC0E5DC', 'Seb', 'Espèces', 71.00, '2025-11-11 22:57:29', 'Terminée', '2025-11-11 22:57:55', '2025-11-11 22:58:02', NULL, 1, '2025-11-11 22:57:29', 'user_6913a60dcfde69.53597725', NULL, 'table'),
(136, 'CMDCE9055', 'Seb', 'Espèces', 25.00, '2025-11-11 23:15:38', 'Terminée', '2025-12-15 10:22:39', '2025-12-15 10:24:14', NULL, 0, '2025-11-11 23:15:38', 'user_6913a60dcfde69.53597725', NULL, 'takeaway'),
(137, 'CMD2B41CA', 'Seb', 'Espèces', 20.00, '2025-11-11 23:19:49', 'Terminée', '2025-12-15 10:22:56', '2025-12-15 10:24:51', NULL, 0, '2025-11-11 23:19:49', 'user_6913a60dcfde69.53597725', NULL, 'takeaway'),
(138, 'CMD6DDE4F', 'A', 'Espèces', 15.00, '2025-11-19 22:24:25', 'Terminée', '2025-11-22 14:21:43', '2025-12-15 10:24:10', NULL, 1, '2025-11-19 22:24:25', 'user_691e33514da205.03873101', NULL, 'table'),
(139, 'CMDAF3DAF', 'A', 'Espèces', 25.00, '2025-11-19 22:25:34', 'Terminée', '2025-11-22 14:21:40', '2025-12-15 10:24:08', NULL, 1, '2025-11-19 22:25:34', 'user_691e33514da205.03873101', NULL, 'table'),
(140, 'CMD149F5A', 'Seb', 'Espèces', 5.00, '2025-11-19 22:40:10', 'Terminée', '2025-11-22 14:21:32', '2025-12-15 10:24:07', NULL, 1, '2025-11-19 22:40:10', 'user_691e33514da205.03873101', NULL, 'table'),
(141, 'CMD1FFB41', 'Seb', 'Espèces', 1.00, '2025-11-19 22:48:23', 'Terminée', '2025-11-22 14:21:30', '2025-12-15 10:24:06', NULL, 0, '2025-11-19 22:48:23', 'user_691e33514da205.03873101', NULL, 'takeaway'),
(142, 'CMDA722E4', 'Seb', 'PayPal', 5.00, '2025-11-19 22:48:54', 'Terminée', '2025-11-22 14:21:19', '2025-12-15 10:24:04', NULL, 0, '2025-11-19 22:48:54', 'user_691e33514da205.03873101', NULL, 'takeaway'),
(143, 'CMDD2127E', 'A', 'Carte bancaire', 1.00, '2025-11-19 22:49:49', 'Terminée', '2025-11-22 14:09:51', '2025-12-15 10:24:03', NULL, 0, '2025-11-19 22:49:49', 'user_691e33514da205.03873101', NULL, 'takeaway'),
(144, 'CMDE0F402', 'Seb', 'Espèces', 15.00, '2025-11-22 14:28:03', 'Terminée', '2025-12-15 10:22:40', '2025-12-15 10:24:15', NULL, 1, '2025-11-22 14:28:03', 'user_6921b400c0d049.32031912', NULL, 'table'),
(145, 'CMD83895A', 'A', 'Espèces', 1.00, '2025-11-22 14:40:05', 'Terminée', '2025-12-15 10:22:58', '2025-12-15 10:24:52', NULL, 1, '2025-11-22 14:40:05', 'user_6921b400c0d049.32031912', NULL, 'table'),
(146, 'CMD2F3EE1', 'Seb', 'Espèces', 16.00, '2025-11-27 11:12:17', 'Terminée', '2025-12-15 10:22:41', '2025-12-15 10:24:16', NULL, 1, '2025-11-27 11:12:17', 'user_6928212de43388.06263856', NULL, 'table'),
(147, 'CMDBC0FE4', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:16:21', 'Terminée', '2025-12-15 10:22:59', '2025-12-15 10:24:53', NULL, 1, '2025-11-27 11:16:21', 'user_6928212de43388.06263856', NULL, 'table'),
(148, 'CMD5CF3E3', 'Nicolas', 'Espèces', 10.00, '2025-11-27 11:17:57', 'Terminée', '2025-12-15 10:23:00', '2025-12-15 10:24:47', NULL, 1, '2025-11-27 11:17:57', 'user_6928212de43388.06263856', NULL, 'table'),
(149, 'CMD0E202F', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:18:54', 'Terminée', '2025-12-15 10:23:01', '2025-12-15 10:24:48', NULL, 1, '2025-11-27 11:18:54', 'user_6928212de43388.06263856', NULL, 'table'),
(150, 'CMD89EE8B', 'Nicolas', 'Espèces', 5.00, '2025-11-27 11:20:49', 'Terminée', '2025-12-15 10:23:02', '2025-12-15 10:24:49', NULL, 1, '2025-11-27 11:20:49', 'user_6928212de43388.06263856', NULL, 'table'),
(151, 'CMDA538D5', 'Bro', 'Carte bancaire', 5.00, '2025-11-27 11:21:24', 'Terminée', '2025-12-15 10:23:04', '2025-12-15 10:24:43', NULL, 1, '2025-11-27 11:21:24', 'user_6928212de43388.06263856', NULL, 'table'),
(152, 'CMD54909F', 'Bro', 'Carte bancaire', 51.00, '2025-11-27 11:21:51', 'Terminée', '2025-12-15 10:23:06', '2025-12-15 10:24:44', NULL, 1, '2025-11-27 11:21:51', 'user_6928212de43388.06263856', NULL, 'table'),
(153, 'CMD4C1DC9', 'Bro', 'Carte bancaire', 38.00, '2025-11-27 11:23:44', 'Terminée', '2025-12-15 10:22:53', '2025-12-15 10:24:55', NULL, 1, '2025-11-27 11:23:44', 'user_6928212de43388.06263856', NULL, 'table'),
(154, 'CMD79ABED', 'Bro', 'Espèces', 10.00, '2025-11-27 11:30:12', 'Terminée', '2025-12-15 10:23:07', '2025-12-15 10:24:45', NULL, 1, '2025-11-27 11:30:12', 'user_6928212de43388.06263856', NULL, 'table'),
(155, 'CMD46BAEA', 'Bro', 'Espèces', 15.00, '2025-11-27 13:26:06', 'Terminée', '2025-12-15 10:22:51', '2025-12-15 10:24:54', NULL, 1, '2025-11-27 13:26:06', 'user_6928212de43388.06263856', NULL, 'table'),
(156, 'CMD6AF903', 'Barbara', 'Espèces', 10.00, '2025-11-27 14:20:12', 'Terminée', '2025-12-15 10:22:49', '2025-12-15 10:24:59', NULL, 1, '2025-11-27 14:20:12', 'user_6928212de43388.06263856', NULL, 'table'),
(157, 'CMDFAC737', 'Barbara', 'Espèces', 45.00, '2025-11-27 23:06:36', 'Terminée', '2025-12-15 10:22:48', '2025-12-15 10:24:57', NULL, 1, '2025-11-27 23:06:36', 'user_6928c104b1d142.55887884', NULL, 'table'),
(158, 'CMDD27FE2', 'A', 'Espèces', 40.00, '2025-11-27 23:12:31', 'Terminée', '2025-12-15 10:22:48', '2025-12-15 10:24:58', NULL, 1, '2025-11-27 23:12:31', 'user_6928c104b1d142.55887884', NULL, 'table'),
(159, 'CMD84442B', 'Seb', 'Carte bancaire', 10.00, '2025-11-29 11:48:10', 'Terminée', '2025-12-15 10:22:47', '2025-12-15 10:25:02', NULL, 1, '2025-11-29 11:48:10', 'user_692abf873cc954.26731801', NULL, 'table'),
(160, 'CMD224704', 'A', 'Espèces', 50.00, '2025-11-29 15:22:08', 'Terminée', '2025-12-15 10:22:46', '2025-12-15 10:25:01', NULL, 1, '2025-11-29 15:22:08', 'user_692abf873cc954.26731801', NULL, 'table'),
(161, 'CMD5125CB', 'Seb', 'Espèces', 5.00, '2025-12-05 13:50:31', 'Terminée', '2025-12-15 10:22:45', '2025-12-15 10:25:00', NULL, 0, '2025-12-05 13:50:31', 'user_6932d23ce18aa6.68834573', NULL, 'takeaway'),
(162, 'CMDDE9603', 'Seb', 'Espèces', 5.00, '2025-12-05 13:50:58', 'Terminée', '2025-12-15 10:22:44', '2025-12-15 10:24:20', NULL, 0, '2025-12-05 13:50:58', 'user_6932d23ce18aa6.68834573', NULL, 'takeaway'),
(163, 'CMD99CD83', 'A', 'Espèces', 5.00, '2025-12-05 13:51:18', 'Terminée', '2025-12-15 10:22:43', '2025-12-15 10:24:18', NULL, 0, '2025-12-05 13:51:18', 'user_6932d23ce18aa6.68834573', NULL, 'takeaway'),
(164, 'CMD95F937', 'A', 'Carte bancaire', 120.00, '2025-12-05 13:53:49', 'Terminée', '2025-12-15 10:23:08', '2025-12-15 10:24:40', NULL, 0, '2025-12-05 13:53:49', 'user_6932d23ce18aa6.68834573', NULL, 'takeaway'),
(165, 'CMD1FFFFC', 'A', 'Espèces', 20.00, '2025-12-05 14:11:38', 'Terminée', '2025-12-15 10:23:10', '2025-12-15 10:24:28', NULL, 1, '2025-12-05 14:11:38', 'user_6932d23ce18aa6.68834573', NULL, 'table'),
(166, 'CMDDDF90C', 'Seb', 'Espèces', 1.00, '2025-12-14 17:10:17', 'Terminée', '2025-12-15 10:23:36', '2025-12-15 10:24:42', NULL, 10, '2025-12-14 17:10:17', 'user_693ee14ee21495.43180142', NULL, 'table'),
(167, 'CMD338F24', 'Nico', 'Carte bancaire', 5.00, '2025-12-15 22:30:27', 'Prête', '2026-02-13 09:56:10', NULL, NULL, 1, '2025-12-15 22:30:27', 'user_693fd2c77b9f50.05871802', NULL, 'table'),
(168, 'CMD026032', 'Nico', 'Carte bancaire', 16.00, '2026-02-08 20:30:10', 'Prête', '2026-02-13 09:56:12', NULL, NULL, 1, '2026-02-08 20:30:10', 'user_6988dee57ea439.41819234', '3adf368180009c80346b9db429490b04017f4208bda4bcc19857e02a0469660d', 'table'),
(169, 'CMD54EE9C', 'Seb', 'Espèces', 16.00, '2026-02-08 20:37:39', 'Prête', '2026-02-13 09:56:11', NULL, NULL, 1, '2026-02-08 20:37:39', 'user_6988dee57ea439.41819234', 'bca9d827c475f61bcf99cf98c95b5ae86a3d259bec6d63054c61441ca6bbc9c6', 'table'),
(170, 'CMDBA73A9', 'Seb', 'Espèces', 37.00, '2026-02-08 20:44:10', 'Prête', '2026-02-13 09:56:14', NULL, NULL, 1, '2026-02-08 20:44:10', 'user_6988dee57ea439.41819234', '06f566739f34aa3985a6c9d15dd41ad3d6b941c32a27659ee49cf4b9b3b63586', 'table'),
(171, 'CMD7C9C52', 'Seb', 'Espèces', 45.00, '2026-02-09 09:32:22', 'Prête', '2026-02-13 09:56:13', NULL, NULL, 1, '2026-02-09 09:32:22', 'user_69899b745182b2.10059252', 'bc9cbc960992b6181d32282426ec2320d5ab669097257df58215f90a8e242983', 'table'),
(172, 'CMD42FB31', 'A', 'Carte bancaire', 15.00, '2026-02-09 09:36:29', 'Prête', '2026-02-13 09:56:15', NULL, NULL, 1, '2026-02-09 09:36:29', 'user_69899b745182b2.10059252', '14e8ed091b56855e9e232df93005ee1cf90b89cc380e78d174710348e81db5c3', 'table'),
(173, 'CMD5571EA', 'AAAAAAAAAAAAAAAAAAAAAA', 'Espèces', 132.00, '2026-02-09 10:31:26', 'Prête', '2026-02-13 09:56:16', NULL, NULL, 1, '2026-02-09 10:31:26', 'user_69899b745182b2.10059252', NULL, 'table'),
(174, 'CMD96655A', 'Seb', 'EspÃ¨ces', 45.00, '2026-02-09 10:33:37', 'En attente', NULL, NULL, NULL, 1, '2026-02-09 10:33:37', 'user_69899b745182b2.10059252', NULL, 'table'),
(175, 'CMDFDF909', 'Seb', 'Espèces', 15.00, '2026-02-09 14:35:06', 'En attente', NULL, NULL, NULL, 1, '2026-02-09 14:35:06', 'user_6989e27da1c721.52331055', '71da10f55265ee25c3898059f80f8779dd251337eadea652fbc0ca16a9555cd8', 'table'),
(176, 'CMD68FA33', 'Seb', 'Espèces', 50.00, '2026-02-09 14:38:41', 'En attente', NULL, NULL, NULL, 1, '2026-02-09 14:38:41', 'user_6989e27da1c721.52331055', '71da10f55265ee25c3898059f80f8779dd251337eadea652fbc0ca16a9555cd8', 'table'),
(177, 'CMD6F4A77', 'Seb', 'Carte bancaire', 15.00, '2026-02-09 14:38:57', 'En attente', NULL, NULL, NULL, 1, '2026-02-09 14:38:57', 'user_6989e27da1c721.52331055', '71da10f55265ee25c3898059f80f8779dd251337eadea652fbc0ca16a9555cd8', 'table'),
(178, 'CMD71A5FB', 'Seb', 'Espèces', 15.00, '2026-02-11 10:07:17', 'En attente', NULL, NULL, NULL, 1, '2026-02-11 10:07:17', 'user_698c46af981752.03960002', 'd3087ba84b76c496578de7b2b8061e67001d07c1ea3a838e92e1eab2507b84f6', 'table'),
(179, 'CMDF3ADA4', 'A', 'Espèces', 15.00, '2026-02-11 10:07:44', 'En attente', NULL, NULL, NULL, 1, '2026-02-11 10:07:44', 'user_698c46af981752.03960002', 'd3087ba84b76c496578de7b2b8061e67001d07c1ea3a838e92e1eab2507b84f6', 'table'),
(180, 'CMDA5914E', 'Seb', 'Carte bancaire', 35.00, '2026-02-11 10:08:31', 'En attente', NULL, NULL, NULL, 1, '2026-02-11 10:08:31', 'user_698c46af981752.03960002', 'd3087ba84b76c496578de7b2b8061e67001d07c1ea3a838e92e1eab2507b84f6', 'table'),
(181, 'CMDA007BB', 'Seb', 'Carte bancaire', 16.00, '2026-02-13 10:05:00', 'En attente', NULL, NULL, NULL, 5, '2026-02-13 10:05:00', 'user_698ee92fa2b119.50999022', '5fa4abbbf1313d6dbc4b1bc7f5a70a297daca4fc95772af55e9a5830b83e5c53', 'table'),
(182, 'CMD4B0060', 'Seb', 'Espèces', 15.00, '2026-02-13 11:08:01', 'En attente', NULL, NULL, NULL, 1, '2026-02-13 11:08:01', 'user_698ef5ef294ec4.92274534', 'dbb1b13e6b425b1be91fb3cb083c91f3918f53a81c1d161da7d268c1c97c07ed', 'table'),
(183, 'CMD74D6C9', 'A', 'Carte bancaire', 21.00, '2026-02-13 11:08:16', 'En attente', NULL, NULL, NULL, 1, '2026-02-13 11:08:16', 'user_698ef5ef294ec4.92274534', 'dbb1b13e6b425b1be91fb3cb083c91f3918f53a81c1d161da7d268c1c97c07ed', 'table'),
(184, 'CMDCCC4E9', 'A', 'Carte bancaire', 15.00, '2026-02-13 11:20:05', 'En attente', NULL, NULL, NULL, 1, '2026-02-13 11:20:05', 'user_698efacb96b987.43408389', '7c50523bbbde52a2df64a3578ea7939d3549720a6a0632d6cea48e647d1e86cf', 'table'),
(185, 'CMD2CDED6', 'Seb', 'Espèces', 90.00, '2026-02-13 11:20:28', 'En attente', NULL, NULL, NULL, 1, '2026-02-13 11:20:28', 'user_698efacb96b987.43408389', '7c50523bbbde52a2df64a3578ea7939d3549720a6a0632d6cea48e647d1e86cf', 'table'),
(186, 'CMD1852A7', 'Seb', 'Espèces', 6.00, '2026-02-13 11:22:30', 'En attente', NULL, NULL, NULL, 1, '2026-02-13 11:22:30', 'user_698efacb96b987.43408389', '7c50523bbbde52a2df64a3578ea7939d3549720a6a0632d6cea48e647d1e86cf', 'table'),
(187, 'CMDC3280D', 'Seb', 'PayPal', 21.00, '2026-02-13 11:34:50', 'En attente', NULL, NULL, NULL, 10, '2026-02-13 11:34:50', 'user_698efe3c75bc57.49795930', 'f9e4894c0cfa2ca8e4903d0242b904c93992e7bf578a1e75df4f262566189da9', 'table');

--
-- Déclencheurs `orders`
--
DROP TRIGGER IF EXISTS `after_order_complete`;
DELIMITER $$
CREATE TRIGGER `after_order_complete` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    DECLARE auto_export_val VARCHAR(10);
    
    -- Vérifier si l'export auto est activé
    SELECT config_value INTO auto_export_val
    FROM pos_config
    WHERE config_key = 'auto_export'
    LIMIT 1;
    
    -- Si activé et order_number existe, ajouter à la file de synchronisation
    IF auto_export_val = 'true' AND NEW.order_number IS NOT NULL THEN
        INSERT INTO pos_sync_queue (order_number, payload, status)
        VALUES (
            NEW.order_number,
            JSON_OBJECT(
                'order_id', NEW.id,
                'order_number', NEW.order_number,
                'customer_name', COALESCE(NEW.customer_name, ''),
                'total_price', NEW.total_price,
                'payment_method', COALESCE(NEW.payment_method, '')
            ),
            'pending'
        );
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `after_order_insert`;
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    DECLARE table_name_val VARCHAR(100);
    
    -- Récupérer le nom de la table seulement si session_token existe
    IF NEW.session_token IS NOT NULL THEN
        SELECT rt.table_name INTO table_name_val
        FROM table_sessions ts
        INNER JOIN restaurant_tables rt ON ts.table_id = rt.id
        WHERE ts.session_token = NEW.session_token
        LIMIT 1;
    END IF;
    
    -- Créer la notification
    INSERT INTO notifications (
        notification_id,
        type,
        title,
        message,
        data,
        priority
    ) VALUES (
        CONCAT('order_', NEW.id, '_', UNIX_TIMESTAMP()),
        'new_order',
        'Nouvelle commande',
        CONCAT('Commande ', COALESCE(NEW.order_number, NEW.id), ' - ', COALESCE(table_name_val, 'Table inconnue'), ' - ', NEW.total_price, ' €'),
        JSON_OBJECT(
            'order_id', NEW.id,
            'order_number', COALESCE(NEW.order_number, ''),
            'table_name', COALESCE(table_name_val, ''),
            'total', NEW.total_price
        ),
        1
    );
END
$$
DELIMITER ;

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
) ENGINE=InnoDB AUTO_INCREMENT=325 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
(271, 165, 0, 'Tiramitsu', 1, 10.00, ''),
(272, 166, 0, 'EAU', 1, 1.00, ''),
(273, 167, 0, 'Pasta  ', 1, 5.00, ''),
(274, 168, 0, 'Pasta  ', 1, 5.00, ''),
(275, 168, 0, 'Tiramitsu', 1, 10.00, ''),
(276, 168, 0, 'EAU', 1, 1.00, ''),
(277, 169, 0, 'EAU', 1, 1.00, ''),
(278, 169, 0, 'Pasta  ', 1, 5.00, ''),
(279, 169, 0, 'Tiramitsu', 1, 10.00, ''),
(280, 170, 0, 'Tiramitsu', 1, 10.00, ''),
(281, 170, 0, 'Salade caesar', 2, 10.00, ''),
(282, 170, 0, 'EAU', 2, 1.00, ''),
(283, 170, 0, 'Pasta  ', 1, 5.00, ''),
(284, 171, 0, 'Pasta  ', 5, 5.00, ''),
(285, 171, 0, 'Tiramitsu', 2, 10.00, ''),
(286, 172, 0, 'Pasta  ', 1, 5.00, ''),
(287, 172, 0, 'Tiramitsu', 1, 10.00, ''),
(288, 173, 0, 'Tiramitsu', 3, 10.00, NULL),
(289, 173, 0, 'Pasta  ', 4, 5.00, NULL),
(290, 173, 0, 'Margarita', 2, 15.00, NULL),
(291, 173, 0, 'Salade caesar', 5, 10.00, NULL),
(292, 173, 0, 'EAU', 2, 1.00, NULL),
(293, 174, 0, 'Steak Haché', 1, 10.00, ''),
(294, 174, 0, 'Margarita', 1, 15.00, ''),
(295, 174, 0, 'Pasta  ', 2, 5.00, ''),
(296, 174, 0, 'Salade caesar', 1, 10.00, ''),
(297, 175, 0, 'Margarita', 1, 15.00, ''),
(298, 176, 0, 'Salade caesar', 5, 10.00, ''),
(299, 177, 0, 'Tiramitsu', 1, 10.00, ''),
(300, 177, 0, 'Pasta  ', 1, 5.00, ''),
(301, 178, 0, 'Pasta  ', 1, 5.00, ''),
(302, 178, 0, 'Tiramitsu', 1, 10.00, ''),
(303, 179, 0, 'Pasta  ', 1, 5.00, ''),
(304, 179, 0, 'Tiramitsu', 1, 10.00, ''),
(305, 180, 0, 'Pasta  ', 1, 5.00, ''),
(306, 180, 0, 'Margarita', 1, 15.00, ''),
(307, 180, 0, 'Margarita', 1, 15.00, 'sans oignosn'),
(308, 181, 0, 'Tiramitsu', 1, 10.00, ''),
(309, 181, 0, 'Pasta  ', 1, 5.00, ''),
(310, 181, 0, 'EAU', 1, 1.00, ''),
(311, 182, 0, 'Tiramitsu', 1, 10.00, ''),
(312, 182, 0, 'Pasta  ', 1, 5.00, ''),
(313, 183, 0, 'Tiramitsu', 1, 10.00, ''),
(314, 183, 0, 'Salade caesar', 1, 10.00, ''),
(315, 183, 0, 'EAU', 1, 1.00, ''),
(316, 184, 0, 'Tiramitsu', 1, 10.00, ''),
(317, 184, 0, 'Pasta  ', 1, 5.00, ''),
(318, 185, 0, 'Tiramitsu', 5, 10.00, ''),
(319, 185, 0, 'Pasta  ', 8, 5.00, ''),
(320, 186, 0, 'Pasta  ', 1, 5.00, ''),
(321, 186, 0, 'EAU', 1, 1.00, ''),
(322, 187, 0, 'Margarita', 1, 15.00, ''),
(323, 187, 0, 'Pasta  ', 1, 5.00, ''),
(324, 187, 0, 'EAU', 1, 1.00, '');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `pending_pos_sync`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `pending_pos_sync`;
CREATE TABLE IF NOT EXISTS `pending_pos_sync` (
`sync_id` int
,`order_number` varchar(50)
,`payload` json
,`sync_status` enum('pending','synced','failed')
,`sync_created_at` datetime
,`synced_at` datetime
,`retry_count` int
,`last_error` text
,`order_id` int
,`total_price` decimal(10,2)
,`order_created_at` datetime
,`customer_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure de la table `pos_config`
--

DROP TABLE IF EXISTS `pos_config`;
CREATE TABLE IF NOT EXISTS `pos_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `pos_config`
--

INSERT INTO `pos_config` (`id`, `config_key`, `config_value`, `description`, `updated_at`) VALUES
(1, 'webhook_url', '', 'URL du webhook pour le système de caisse', '2026-02-04 21:00:29'),
(2, 'api_key', '', 'Clé API pour l\'authentification', '2026-02-04 21:00:29'),
(3, 'auto_export', 'true', 'Export automatique vers le POS', '2026-02-04 21:00:29'),
(4, 'sync_interval_minutes', '5', 'Intervalle de synchronisation (minutes)', '2026-02-04 21:00:29');

-- --------------------------------------------------------

--
-- Structure de la table `pos_export_log`
--

DROP TABLE IF EXISTS `pos_export_log`;
CREATE TABLE IF NOT EXISTS `pos_export_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` varchar(20) NOT NULL,
  `exported_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

-- --------------------------------------------------------

--
-- Structure de la table `pos_sync_queue`
--

DROP TABLE IF EXISTS `pos_sync_queue`;
CREATE TABLE IF NOT EXISTS `pos_sync_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `payload` json NOT NULL,
  `status` enum('pending','synced','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `synced_at` datetime DEFAULT NULL,
  `retry_count` int DEFAULT '0',
  `last_error` text,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_order` (`order_number`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `pos_sync_queue`
--

INSERT INTO `pos_sync_queue` (`id`, `order_number`, `payload`, `status`, `created_at`, `synced_at`, `retry_count`, `last_error`) VALUES
(1, 'CMD026032', '{\"order_id\": 168, \"total_price\": 16.00, \"order_number\": \"CMD026032\", \"customer_name\": \"Nico\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-08 20:30:10', NULL, 0, NULL),
(2, 'CMD54EE9C', '{\"order_id\": 169, \"total_price\": 16.00, \"order_number\": \"CMD54EE9C\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-08 20:37:39', NULL, 0, NULL),
(3, 'CMDBA73A9', '{\"order_id\": 170, \"total_price\": 37.00, \"order_number\": \"CMDBA73A9\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-08 20:44:10', NULL, 0, NULL),
(4, 'CMD7C9C52', '{\"order_id\": 171, \"total_price\": 45.00, \"order_number\": \"CMD7C9C52\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-09 09:32:22', NULL, 0, NULL),
(5, 'CMD42FB31', '{\"order_id\": 172, \"total_price\": 15.00, \"order_number\": \"CMD42FB31\", \"customer_name\": \"A\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-09 09:36:29', NULL, 0, NULL),
(6, 'CMD5571EA', '{\"order_id\": 173, \"total_price\": 132.00, \"order_number\": \"CMD5571EA\", \"customer_name\": \"AAAAAAAAAAAAAAAAAAAAAA\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-09 10:31:26', NULL, 0, NULL),
(7, 'CMD96655A', '{\"order_id\": 174, \"total_price\": 45.00, \"order_number\": \"CMD96655A\", \"customer_name\": \"Seb\", \"payment_method\": \"EspÃ¨ces\"}', 'pending', '2026-02-09 10:33:37', NULL, 0, NULL),
(8, 'CMDFDF909', '{\"order_id\": 175, \"total_price\": 15.00, \"order_number\": \"CMDFDF909\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-09 14:35:06', NULL, 0, NULL),
(9, 'CMD68FA33', '{\"order_id\": 176, \"total_price\": 50.00, \"order_number\": \"CMD68FA33\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-09 14:38:41', NULL, 0, NULL),
(10, 'CMD6F4A77', '{\"order_id\": 177, \"total_price\": 15.00, \"order_number\": \"CMD6F4A77\", \"customer_name\": \"Seb\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-09 14:38:57', NULL, 0, NULL),
(11, 'CMD71A5FB', '{\"order_id\": 178, \"total_price\": 15.00, \"order_number\": \"CMD71A5FB\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-11 10:07:17', NULL, 0, NULL),
(12, 'CMDF3ADA4', '{\"order_id\": 179, \"total_price\": 15.00, \"order_number\": \"CMDF3ADA4\", \"customer_name\": \"A\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-11 10:07:44', NULL, 0, NULL),
(13, 'CMDA5914E', '{\"order_id\": 180, \"total_price\": 35.00, \"order_number\": \"CMDA5914E\", \"customer_name\": \"Seb\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-11 10:08:31', NULL, 0, NULL),
(14, 'CMDA007BB', '{\"order_id\": 181, \"total_price\": 16.00, \"order_number\": \"CMDA007BB\", \"customer_name\": \"Seb\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-13 10:05:00', NULL, 0, NULL),
(15, 'CMD4B0060', '{\"order_id\": 182, \"total_price\": 15.00, \"order_number\": \"CMD4B0060\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-13 11:08:01', NULL, 0, NULL),
(16, 'CMD74D6C9', '{\"order_id\": 183, \"total_price\": 21.00, \"order_number\": \"CMD74D6C9\", \"customer_name\": \"A\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-13 11:08:16', NULL, 0, NULL),
(17, 'CMDCCC4E9', '{\"order_id\": 184, \"total_price\": 15.00, \"order_number\": \"CMDCCC4E9\", \"customer_name\": \"A\", \"payment_method\": \"Carte bancaire\"}', 'pending', '2026-02-13 11:20:05', NULL, 0, NULL),
(18, 'CMD2CDED6', '{\"order_id\": 185, \"total_price\": 90.00, \"order_number\": \"CMD2CDED6\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-13 11:20:28', NULL, 0, NULL),
(19, 'CMD1852A7', '{\"order_id\": 186, \"total_price\": 6.00, \"order_number\": \"CMD1852A7\", \"customer_name\": \"Seb\", \"payment_method\": \"Espèces\"}', 'pending', '2026-02-13 11:22:30', NULL, 0, NULL),
(20, 'CMDC3280D', '{\"order_id\": 187, \"total_price\": 21.00, \"order_number\": \"CMDC3280D\", \"customer_name\": \"Seb\", \"payment_method\": \"PayPal\"}', 'pending', '2026-02-13 11:34:50', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
CREATE TABLE IF NOT EXISTS `restaurant_tables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_number` varchar(20) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `qr_identifier` varchar(50) DEFAULT NULL,
  `capacity` int DEFAULT '4',
  `qr_code_identifier` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`),
  UNIQUE KEY `qr_code_identifier` (`qr_code_identifier`),
  UNIQUE KEY `idx_qr_identifier` (`qr_identifier`),
  KEY `idx_qr` (`qr_code_identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`id`, `table_number`, `table_name`, `qr_identifier`, `capacity`, `qr_code_identifier`, `is_active`, `created_at`) VALUES
(1, '1', 'Table 1', 'QR_TABLE_001', 4, 'QR_TABLE_001', 1, '2026-02-04 20:43:23'),
(2, '2', 'Table 2', 'QR_TABLE_002', 4, 'QR_TABLE_002', 1, '2026-02-04 20:43:23'),
(3, '3', 'Table 3', 'QR_TABLE_003', 6, 'QR_TABLE_003', 1, '2026-02-04 20:43:23'),
(4, '4', 'Table 4', 'QR_TABLE_004', 4, 'QR_TABLE_004', 1, '2026-02-04 20:43:23'),
(5, '5', 'Table 5', 'QR_TABLE_005', 2, 'QR_TABLE_005', 1, '2026-02-04 20:43:23'),
(6, '6', 'Table 6', 'QR_TABLE_006', 8, 'QR_TABLE_006', 1, '2026-02-04 20:43:23'),
(7, '7', 'Table 7', 'QR_TABLE_007', 4, 'QR_TABLE_007', 1, '2026-02-04 20:43:23'),
(8, '8', 'Table 8', 'QR_TABLE_008', 4, 'QR_TABLE_008', 1, '2026-02-04 20:43:23'),
(9, '9', 'Table 9', 'QR_TABLE_009', 6, 'QR_TABLE_009', 1, '2026-02-04 20:43:23'),
(10, '10', 'Table 10', 'QR_TABLE_010', 4, 'QR_TABLE_010', 1, '2026-02-04 20:43:23');

-- --------------------------------------------------------

--
-- Structure de la table `table_sessions`
--

DROP TABLE IF EXISTS `table_sessions`;
CREATE TABLE IF NOT EXISTS `table_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_id` int NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `status` enum('OPEN','CLOSED') DEFAULT 'OPEN',
  `opened_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `opened_by` varchar(100) DEFAULT NULL,
  `total_orders` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `idx_table_id` (`table_id`),
  KEY `idx_token` (`session_token`),
  KEY `idx_status` (`status`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Déchargement des données de la table `table_sessions`
--

INSERT INTO `table_sessions` (`id`, `table_id`, `session_token`, `status`, `opened_at`, `expires_at`, `closed_at`, `opened_by`, `total_orders`) VALUES
(1, 10, 'ea621fc3b22e451273fd0117cfd6c40b8efe978da9f3c26e53c33f77c0b2bd2a', 'CLOSED', '2026-02-04 20:44:47', '2026-02-04 21:14:47', '2026-02-04 20:44:49', 'Serveur', 0),
(2, 1, 'd04151dc4679995d4a72d2299e5fdbc154ac243ad71bb387445ae27492f1c737', 'CLOSED', '2026-02-04 20:48:07', '2026-02-04 21:48:07', '2026-02-05 16:36:17', 'Serveur', 0),
(3, 10, '615e64cc4077cf7d8fc820085948965623aa8db278a9a3164cd019867c11da95', 'CLOSED', '2026-02-04 20:48:08', '2026-02-04 21:48:08', '2026-02-05 16:36:17', 'Serveur', 0),
(4, 2, 'cff008ada8e47b85c86d3e090968b12ce809e383191ea4ad1129caf949c243b0', 'CLOSED', '2026-02-04 20:48:10', '2026-02-04 21:18:10', '2026-02-05 16:36:17', 'Serveur', 0),
(5, 3, '7fd24905b5628094ecc6a96e297ecca7634183fefdeba1a48db50b537d769ed5', 'CLOSED', '2026-02-04 20:48:11', '2026-02-04 21:18:11', '2026-02-05 16:36:17', 'Serveur', 0),
(6, 4, '4b84d0adaa8ef862b4a7b578512e0df7e6f553d2367916a5afe003e06a8cbd88', 'CLOSED', '2026-02-04 20:48:12', '2026-02-04 21:18:12', '2026-02-05 16:36:17', 'Serveur', 0),
(7, 5, '9e8122f0bf6dc026e30986dee8ee59bcea2f6be6fd731128a1efe16e623cc6b4', 'CLOSED', '2026-02-04 20:48:13', '2026-02-04 21:18:13', '2026-02-05 16:36:17', 'Serveur', 0),
(8, 8, '0e0203dbef7d6e2bda52349156715ccb54ad2bf9bab28ecaf0f4b3a685d840af', 'CLOSED', '2026-02-04 20:48:14', '2026-02-04 21:18:14', '2026-02-05 16:36:17', 'Serveur', 0),
(9, 7, 'feb6cf33a60a548f319f43ed6f23a2bdf32247110c1593b4cfaa345b7ac079f0', 'CLOSED', '2026-02-04 20:48:15', '2026-02-04 21:18:15', '2026-02-05 16:36:17', 'Serveur', 0),
(10, 6, '41f25d29a28b987d925bcf92f379dd0c8eb8d1c3f883188d3cc6fbd18869cc60', 'CLOSED', '2026-02-04 20:48:17', '2026-02-04 21:18:17', '2026-02-05 16:36:17', 'Serveur', 0),
(11, 1, '90de08f7d0425ac89dafbc380da0ac6971cf81dbe98c6fbbe8485a7c42929654', 'CLOSED', '2026-02-05 16:36:19', '2026-02-05 17:06:19', '2026-02-05 17:47:53', 'Serveur', 0),
(12, 10, '00f7f14786ce544d163c07e634eddc2f7a6a8800210fc4d889e42af91a5e57e4', 'CLOSED', '2026-02-05 16:36:24', '2026-02-05 17:06:24', '2026-02-05 17:47:53', 'Serveur', 0),
(13, 2, 'ec40d6a5576d30972d2d47ba561c2571fb79af433424ebca0ef98dce4b7a8bd2', 'CLOSED', '2026-02-05 16:36:25', '2026-02-05 17:06:25', '2026-02-05 17:47:53', 'Serveur', 0),
(14, 3, 'cc18d6389c838afaa8a7438e0f182d52a682ca97364ad9e8a0267f6abda40404', 'CLOSED', '2026-02-05 16:36:27', '2026-02-05 17:06:27', '2026-02-05 17:47:53', 'Serveur', 0),
(15, 4, '4dee43fe9fe45ab6a25d6528bb6d42cb46602b841ac92f58a146794c6a318581', 'CLOSED', '2026-02-05 16:36:29', '2026-02-05 17:06:29', '2026-02-05 17:47:53', 'Serveur', 0),
(16, 5, '0ad805214cb80247b4c11307098b39525bddabe6bf59ce50339f03ba3c2b0cd4', 'CLOSED', '2026-02-05 16:36:30', '2026-02-05 17:06:30', '2026-02-05 17:47:53', 'Serveur', 0),
(17, 6, '1579a0ac47996c8744636055a36d0b7a3de86f77d534a54ce959c2889fdd3f6a', 'CLOSED', '2026-02-05 16:36:32', '2026-02-05 17:06:32', '2026-02-05 17:47:53', 'Serveur', 0),
(18, 7, '2682abf715778d7947fcc94f8cf196a76e66bd41c799191d1b1013e2ae8d8ef9', 'CLOSED', '2026-02-05 16:36:34', '2026-02-05 17:06:34', '2026-02-05 17:47:53', 'Serveur', 0),
(19, 8, 'b6d05cf685f835036607a32c553cb3197f1edc91e4d97a9b9406da5f011310a3', 'CLOSED', '2026-02-05 16:36:36', '2026-02-05 17:06:36', '2026-02-05 17:47:53', 'Serveur', 0),
(20, 9, 'd40f5cde804aedbc0a8b92aac1c492f45bf44d5cbc6b5ae56c346efc565ad537', 'CLOSED', '2026-02-05 16:36:38', '2026-02-05 17:06:38', '2026-02-05 16:37:08', 'Serveur', 0),
(21, 9, '9b7dff7c383e9aa582e6086d6704bb7b4454807336206a0185a095a39091477e', 'CLOSED', '2026-02-05 16:37:08', '2026-02-05 17:07:08', '2026-02-05 16:37:39', 'Serveur', 0),
(22, 9, '0ede433199ef92877d60e81ede286717e442fa5e26aa1320dad67610a701a6e4', 'CLOSED', '2026-02-05 16:37:39', '2026-02-05 17:07:39', '2026-02-05 16:38:09', 'Serveur', 0),
(23, 9, '60db067c6e39bed401f89792dda4e5227b0b1aa8dc62b3e08bb4a73cc5826e5b', 'CLOSED', '2026-02-05 16:38:09', '2026-02-05 17:08:09', '2026-02-05 16:38:40', 'Serveur', 0),
(24, 9, 'f4675da3e91950e10d2c635d7f0a8cc88688d235b6964e3e068fcf67f503e915', 'CLOSED', '2026-02-05 16:38:40', '2026-02-05 17:08:40', '2026-02-05 16:39:11', 'Serveur', 0),
(25, 9, 'd7f50af86669be25d19e0a86780b2af89438cd222347d0467b5a30320c17deb9', 'CLOSED', '2026-02-05 16:39:11', '2026-02-05 17:09:11', '2026-02-05 16:39:42', 'Serveur', 0),
(26, 9, 'e384f0c86b66aeac007571548b30d236a09cac14807230fa621c37c6748e1380', 'CLOSED', '2026-02-05 16:39:42', '2026-02-05 17:09:42', '2026-02-05 16:40:13', 'Serveur', 0),
(27, 9, '17df4709c23e1f03a066c337b3786766f8272606cbd545c19ab217afff2b53a1', 'CLOSED', '2026-02-05 16:40:13', '2026-02-05 17:10:13', '2026-02-05 16:40:44', 'Serveur', 0),
(28, 9, '796c447891db9b0e7eebed1a6ca6e553b2da8f39352a342f1cae63dbc7f7e7db', 'CLOSED', '2026-02-05 16:40:44', '2026-02-05 17:10:44', '2026-02-05 16:41:15', 'Serveur', 0),
(29, 9, 'fbe656ccb6a77a1fe550aebf2e5a6c8df5aa7f5d5ae1f28cc028667bc212b30e', 'CLOSED', '2026-02-05 16:41:15', '2026-02-05 17:11:15', '2026-02-05 16:41:46', 'Serveur', 0),
(30, 9, 'cad99deab7e381bb8935d90268c687c774bbb3fc79e2be0aec5389b42fdf10bc', 'CLOSED', '2026-02-05 16:41:46', '2026-02-05 17:11:46', '2026-02-05 16:42:17', 'Serveur', 0),
(31, 9, 'c3c5dedbb8b8336799a8b4a8c31fbd0b625055cc18958c67565964a913ef5d25', 'CLOSED', '2026-02-05 16:42:17', '2026-02-05 17:12:17', '2026-02-05 16:54:44', 'Serveur', 0),
(32, 9, 'c2f27a97b61b11ff3ee054a5f421a2a2949b3184a20db7173e795e760ddaf657', 'CLOSED', '2026-02-05 16:54:44', '2026-02-05 17:24:44', '2026-02-05 17:47:53', 'Serveur', 0),
(33, 9, '4eda96d3e87086141cbcc66ca130a7c50f245e7ca92c76634e8a9d74a19e4e94', 'CLOSED', '2026-02-05 17:47:53', '2026-02-05 18:17:53', '2026-02-06 08:41:59', 'Serveur', 0),
(34, 9, 'b066805d786f3e901cdb8ea6ee51afc9519b8f9dc309dc7d7d901bc59fc9fff3', 'CLOSED', '2026-02-06 08:41:59', '2026-02-06 09:11:59', '2026-02-06 08:42:29', 'Serveur', 0),
(35, 9, 'b3d19126910667f23b6919dc3504d7070f0493152b3a944526e96cd8938b75a5', 'CLOSED', '2026-02-06 08:42:29', '2026-02-06 09:12:29', '2026-02-06 08:43:00', 'Serveur', 0),
(36, 9, '7bf80ceb75da32c87e83651089f3b53b0bddc40e78fa7294b338746a51114efb', 'CLOSED', '2026-02-06 08:43:00', '2026-02-06 09:13:00', '2026-02-06 08:43:31', 'Serveur', 0),
(37, 9, '90a2d9172f608364361cb7c760b52e78b8351c080b01cd43a234b1834769344e', 'CLOSED', '2026-02-06 08:43:31', '2026-02-06 09:13:31', '2026-02-06 08:44:02', 'Serveur', 0),
(38, 9, 'c3c2dab74a864b929c9c0adb12b4cf09f1ef53995a916c0bb2a026dad26936f3', 'CLOSED', '2026-02-06 08:44:02', '2026-02-06 09:14:02', '2026-02-06 08:44:33', 'Serveur', 0),
(39, 9, '4aca165c7476d2e826c056a2a8c634c046ee7bf8a457d8354e9067845121d944', 'CLOSED', '2026-02-06 08:44:33', '2026-02-06 09:14:33', '2026-02-06 08:45:04', 'Serveur', 0),
(40, 9, '244dbaa3aa106c3737807b3a4f692ca86a17789583a3fe283614f5d48724d848', 'CLOSED', '2026-02-06 08:45:04', '2026-02-06 09:15:04', '2026-02-06 08:45:35', 'Serveur', 0),
(41, 9, '5000d7923dbfe4d095935b28c9152de85854bb9ad40d35a1899a215099773b49', 'CLOSED', '2026-02-06 08:45:35', '2026-02-06 09:15:35', '2026-02-06 08:46:06', 'Serveur', 0),
(42, 9, 'aeb3eff6f22624dfdf259770734552c25886c1da189747a1d5813763147c28e4', 'CLOSED', '2026-02-06 08:46:06', '2026-02-06 09:16:06', '2026-02-06 08:46:37', 'Serveur', 0),
(43, 9, 'cd0ec12f5be8a3c922a50a249615c183120fe1943c7f231a76465e4568d820f6', 'CLOSED', '2026-02-06 08:46:37', '2026-02-06 09:16:37', '2026-02-06 08:47:08', 'Serveur', 0),
(44, 9, '2916dce3a9c68a6cfab44a9a9dbfb3b5c9c30d02f4d4c87a1785a313de66b6b6', 'CLOSED', '2026-02-06 08:47:08', '2026-02-06 09:17:08', '2026-02-06 08:47:39', 'Serveur', 0),
(45, 9, 'de08fbab203095f8ee3688372e714d6bc04cd7d4815f376d8445eda5d7550158', 'CLOSED', '2026-02-06 08:47:39', '2026-02-06 09:17:39', '2026-02-06 08:48:10', 'Serveur', 0),
(46, 9, '316d8f596e5fc781c904a3bebe7d1bcdc9a0e81de501cb02f95d47f84067d88f', 'CLOSED', '2026-02-06 08:48:10', '2026-02-06 09:18:10', '2026-02-06 08:48:41', 'Serveur', 0),
(47, 9, 'a69658545ed7f5912be218b86b884401ba9fd98a4dcd051d766f066dfe35439c', 'CLOSED', '2026-02-06 08:48:41', '2026-02-06 09:18:41', '2026-02-06 08:49:12', 'Serveur', 0),
(48, 9, 'aa67103179b4d676989b634b338f5e5275d995d640b675952c0997d370035a7a', 'CLOSED', '2026-02-06 08:49:12', '2026-02-06 09:19:12', '2026-02-06 08:49:43', 'Serveur', 0),
(49, 9, '205b816f954e9b37f505a9a06e23e356dbdb858f4ba6fb1f9ac9a769c1b25b7b', 'CLOSED', '2026-02-06 08:49:43', '2026-02-06 09:19:43', '2026-02-06 08:50:14', 'Serveur', 0),
(50, 9, '03febba5f506b0e7f7e399ae188094b5ae2f5bf56868d259f32959117ac019b9', 'CLOSED', '2026-02-06 08:50:14', '2026-02-06 09:20:14', '2026-02-06 08:50:45', 'Serveur', 0),
(51, 9, '61fc826018e6692605d074986cd868ccabfaa5c8901f09ab5fa3157405c4810e', 'CLOSED', '2026-02-06 08:50:45', '2026-02-06 09:20:45', '2026-02-06 08:51:16', 'Serveur', 0),
(52, 9, '47051c516bd2b6e10a14ccffaf456b1087771e3ff967c958532e417b077cfa08', 'CLOSED', '2026-02-06 08:51:16', '2026-02-06 09:21:16', '2026-02-06 08:51:47', 'Serveur', 0),
(53, 9, '44e71db0d23b3eaa4d8b6412f6399dfeadd219c65bc12d5ca0c3ede9bdb48682', 'CLOSED', '2026-02-06 08:51:47', '2026-02-06 09:21:47', '2026-02-06 08:52:18', 'Serveur', 0),
(54, 9, '06b73285772dd75e231804e1e97a204aaa33275709c08d530a539d16e01bf9aa', 'CLOSED', '2026-02-06 08:52:18', '2026-02-06 09:22:18', '2026-02-06 08:52:49', 'Serveur', 0),
(55, 9, '99a9ef03c9a6444bdfd4721b7a4833d3ad591de6a744c8254fd49ed55f3e009b', 'CLOSED', '2026-02-06 08:52:49', '2026-02-06 09:22:49', '2026-02-06 08:53:20', 'Serveur', 0),
(56, 9, '6fd8c37a0948e2a2be9e24c9797444ea09eb236ac49609d0b009f0b6d7ad7787', 'CLOSED', '2026-02-06 08:53:20', '2026-02-06 09:23:20', '2026-02-06 08:53:51', 'Serveur', 0),
(57, 9, '358e73de5b75d95c2121f2f74bc0a3dbc1a3ada0697bf559ee6e951e5aca12ee', 'CLOSED', '2026-02-06 08:53:51', '2026-02-06 09:23:51', '2026-02-06 08:54:22', 'Serveur', 0),
(58, 9, 'b1666b71876ecce77e6239667211dea9b6b6bdbbd2ccb769767bc716f721f6ed', 'CLOSED', '2026-02-06 08:54:22', '2026-02-06 09:24:22', '2026-02-06 08:54:53', 'Serveur', 0),
(59, 9, 'a85dda15261e12b12f887b25cd9778553dd0e15c3fb652b740192a1394ab1f45', 'CLOSED', '2026-02-06 08:54:53', '2026-02-06 09:24:53', '2026-02-06 08:55:24', 'Serveur', 0),
(60, 9, 'a56b282f7f8df697b8d7778b2d911bc052fbf45905adb7ac1aa9dd3f4f7aee4e', 'CLOSED', '2026-02-06 08:55:24', '2026-02-06 09:25:24', '2026-02-06 08:55:55', 'Serveur', 0),
(61, 9, '037c224c08f8ae8c45fbf73113af76ac2ddd07e0bb1819cf74162b58ea34241a', 'CLOSED', '2026-02-06 08:55:55', '2026-02-06 09:25:55', '2026-02-06 08:56:26', 'Serveur', 0),
(62, 9, '2ab9974de14c889e05d25a034f98d0dce86f1ef29f26e1650ab2bf286d6d940e', 'CLOSED', '2026-02-06 08:56:26', '2026-02-06 09:26:26', '2026-02-06 08:56:57', 'Serveur', 0),
(63, 9, '4eaecb53ea6a695e606b9f104c2997efbcc1c7535de2f8253fd230c072230460', 'CLOSED', '2026-02-06 08:56:57', '2026-02-06 09:26:57', '2026-02-06 08:57:28', 'Serveur', 0),
(64, 9, '3ab0d258f99ef83bf3cc078505d306c6f9b53ecd4f0b0b07797a941e22eab2a3', 'CLOSED', '2026-02-06 08:57:28', '2026-02-06 09:27:28', '2026-02-06 08:57:59', 'Serveur', 0),
(65, 9, '352db6dbeb053e6f6676eacb4243c76bb7c017acebca16890258ad426b828ed5', 'CLOSED', '2026-02-06 08:57:59', '2026-02-06 09:27:59', '2026-02-06 08:58:30', 'Serveur', 0),
(66, 9, '44a3969b7162edb0b75dc9d33c3c9beaae7e00247877621836e343e8512b2649', 'CLOSED', '2026-02-06 08:58:30', '2026-02-06 09:28:30', '2026-02-06 08:59:01', 'Serveur', 0),
(67, 9, 'd49ab5caa681fc433cb22d7fdce00324bcf4d91391d2705fb2340861e3334231', 'CLOSED', '2026-02-06 08:59:01', '2026-02-06 09:29:01', '2026-02-06 08:59:32', 'Serveur', 0),
(68, 9, 'a4f8bb1ed22f4fe36954ad667c5368afcb68a0685a856e1dbf3946148ed76a8d', 'CLOSED', '2026-02-06 08:59:32', '2026-02-06 09:29:32', '2026-02-06 09:00:03', 'Serveur', 0),
(69, 9, '9f1979df8a0a67925b1b43765d6f6be98fd99ec8e37af960d54dce300c3c5503', 'CLOSED', '2026-02-06 09:00:03', '2026-02-06 09:30:03', '2026-02-06 09:00:34', 'Serveur', 0),
(70, 9, '2e890e5582b0b4a01a430e92dab4a9a5b06cd2ae7801cc3ade50c7ff3c1beb81', 'CLOSED', '2026-02-06 09:00:34', '2026-02-06 09:30:34', '2026-02-06 09:01:05', 'Serveur', 0),
(71, 9, 'b4faf2db420d9e0f07e8527e286f8e4bc913f0d9e1382e577ead24a15ca4abda', 'CLOSED', '2026-02-06 09:01:05', '2026-02-06 09:31:05', '2026-02-06 09:01:36', 'Serveur', 0),
(72, 9, '44c179073b0a7397c248314d22f46703d86e4a8a1c6cc7db5c992955a0ac4ac4', 'CLOSED', '2026-02-06 09:01:36', '2026-02-06 09:31:36', '2026-02-06 09:02:07', 'Serveur', 0),
(73, 9, 'bde0e3a0870d40df6074d86c5bd5736f99186681cc2478b5e1c0778ceac012c1', 'CLOSED', '2026-02-06 09:02:07', '2026-02-06 09:32:07', '2026-02-06 09:02:38', 'Serveur', 0),
(74, 9, '470d868d96a25d1f54221b11c840ece48f1b2451e77c2468b52845779b36e5e2', 'CLOSED', '2026-02-06 09:02:38', '2026-02-06 09:32:38', '2026-02-06 09:03:09', 'Serveur', 0),
(75, 9, '6e3ced0acc399f0ca9ce81383ce8c3bb2cc7a0f403c624cd38b1f27041df19b2', 'CLOSED', '2026-02-06 09:03:09', '2026-02-06 09:33:09', '2026-02-06 09:03:40', 'Serveur', 0),
(76, 9, '201dfa359db78f1bfb9a8dfa4a26fb14b5eb7b69f2b232fd5041673f162414f9', 'CLOSED', '2026-02-06 09:03:40', '2026-02-06 09:33:40', '2026-02-06 09:04:11', 'Serveur', 0),
(77, 9, '3f4a503a051471e7d55fd3c9dec5a3bc8e617085c2f912c59b1af734267c0bb8', 'CLOSED', '2026-02-06 09:04:11', '2026-02-06 09:34:11', '2026-02-06 09:04:42', 'Serveur', 0),
(78, 9, '3e362ecfdc6471ea33e5d8282ccd63c4a925e2784be7a34ab894530c1ec57c91', 'CLOSED', '2026-02-06 09:04:42', '2026-02-06 09:34:42', '2026-02-06 09:05:13', 'Serveur', 0),
(79, 9, '35122b414890d9e2ea83244dbcfe97395ce2310c9a786e92665c4d6204e24e3b', 'CLOSED', '2026-02-06 09:05:13', '2026-02-06 09:35:13', '2026-02-06 09:05:44', 'Serveur', 0),
(80, 9, 'ba6e63309c90d65a5a65d8984d258698221defd44edc5e5415d8873d11bf7e18', 'CLOSED', '2026-02-06 09:05:44', '2026-02-06 09:35:44', '2026-02-06 09:06:15', 'Serveur', 0),
(81, 9, 'b2cd917a13c23f255abad8789ee5d87759f5f6773152cf34af703698ee6a70eb', 'CLOSED', '2026-02-06 09:06:15', '2026-02-06 09:36:15', '2026-02-06 09:06:46', 'Serveur', 0),
(82, 9, 'cf12ef9231ace80a29c50c84995df2cd9bbe393bf5fac758308b4e304f040704', 'CLOSED', '2026-02-06 09:06:46', '2026-02-06 09:36:46', '2026-02-06 09:07:17', 'Serveur', 0),
(83, 9, '1624b44024aebf354f764203ff782165ed882d55a3271eb230e3440ef35fbedf', 'CLOSED', '2026-02-06 09:07:17', '2026-02-06 09:37:17', '2026-02-08 20:11:23', 'Serveur', 0),
(84, 1, '51ecdd4726c94d16446fc5c47eb406d733a8c677d88f9c9ffc9a92131ee9d49d', 'CLOSED', '2026-02-08 20:11:26', '2026-02-09 13:11:26', '2026-02-08 20:27:36', 'Serveur', 0),
(85, 1, '24da63b329dc9758c4282ec2d22da4b940bc8ad0f552dae943ca02ee61cb2c75', 'CLOSED', '2026-02-08 20:27:41', '2026-02-08 20:27:41', '2026-02-08 20:27:43', 'Serveur', 0),
(86, 1, '23c1befe5d8a98faaa60b88d84b219953bfdc0ee7ba34b657b0cd68878731562', 'CLOSED', '2026-02-08 20:27:43', '2026-02-08 20:57:43', '2026-02-08 20:28:14', 'Serveur', 0),
(87, 1, '9da91ffde2e11bf815c1899b521a03e8ea3ac7afc67ea32772d8345a1273634b', 'CLOSED', '2026-02-08 20:28:14', '2026-02-08 20:58:14', '2026-02-08 20:28:26', 'Serveur', 0),
(88, 1, '58cd81526658e06cddcd92ab4e95b76843ba5a9663252d602bf38219c06e7dee', 'CLOSED', '2026-02-08 20:28:28', '2026-02-08 20:58:28', '2026-02-08 20:28:42', 'Serveur', 0),
(89, 1, '45de43223c200ded29ba4fb1fcc97db7e4696a18e4a0b90993e38d245b086617', 'CLOSED', '2026-02-08 20:28:42', '2026-02-08 20:58:42', '2026-02-08 20:29:12', 'Serveur', 0),
(90, 1, '5802fbbe7a1dffc8900d7d2cb66d280504929d153d7868509c1b4dccfb4ae53f', 'CLOSED', '2026-02-08 20:29:12', '2026-02-08 20:59:12', '2026-02-08 20:29:43', 'Serveur', 0),
(91, 1, '3adf368180009c80346b9db429490b04017f4208bda4bcc19857e02a0469660d', 'CLOSED', '2026-02-08 20:29:43', '2026-02-08 20:59:43', '2026-02-08 20:30:14', 'Serveur', 1),
(92, 1, '6e4dd625028f30a64b898a8300f7eb97789bc737b52266f61025f091934395f1', 'CLOSED', '2026-02-08 20:30:14', '2026-02-08 21:00:14', '2026-02-08 20:30:38', 'Serveur', 0),
(93, 1, '4483eb7d5c27c67477e0deb9c269d3512361703cdc0f7d6354cccfcde02e84db', 'CLOSED', '2026-02-08 20:30:38', '2026-02-08 21:00:38', '2026-02-08 20:31:09', 'Serveur', 0),
(94, 1, 'c57032e574d8a5a21deb53289261c4b7b4f88bd688f8c7609ce7a3b1c3b1dab1', 'CLOSED', '2026-02-08 20:31:09', '2026-02-08 21:01:09', '2026-02-08 20:31:40', 'Serveur', 0),
(95, 1, '4b6b5d2cd16bcaaf4f88185e28a27a6313882a7d6ccc0b7ba84693e4fba01b1d', 'CLOSED', '2026-02-08 20:31:40', '2026-02-08 21:01:40', '2026-02-08 20:32:11', 'Serveur', 0),
(96, 1, '409d08b97d40a4aa4b5bddb326fe76079f7efeff258f5d46b390f5536dbd8c05', 'CLOSED', '2026-02-08 20:32:11', '2026-02-08 21:02:11', '2026-02-08 20:32:42', 'Serveur', 0),
(97, 1, '8a22d525bd3883a84beaf2ef4ef9247e1e436325827a22893690ceea597916e8', 'CLOSED', '2026-02-08 20:32:42', '2026-02-08 21:02:42', '2026-02-08 20:33:13', 'Serveur', 0),
(98, 1, '1520f3ecd5c5facb95f2a65263f9ec1ec0f457671ffd3aed6f118ff70c695bee', 'CLOSED', '2026-02-08 20:33:13', '2026-02-08 21:03:13', '2026-02-08 20:33:44', 'Serveur', 0),
(99, 1, 'c129527d91038dd900d07e01c1dd1e9849728b6935560b8bc9921784c4cb7671', 'CLOSED', '2026-02-08 20:33:44', '2026-02-08 21:03:44', '2026-02-08 20:34:15', 'Serveur', 0),
(100, 1, 'a058600b4f0b1f829e8f3b66fed9d6378e51700a42fd24f4c87a0ef61d8ed1b5', 'CLOSED', '2026-02-08 20:34:15', '2026-02-08 21:04:15', '2026-02-08 20:34:46', 'Serveur', 0),
(101, 1, 'e51224084d114428ce1906eb8ac07392a3930aab4fbdff73144ff83012a226bc', 'CLOSED', '2026-02-08 20:34:46', '2026-02-08 21:04:46', '2026-02-08 20:35:17', 'Serveur', 0),
(102, 1, '87bee095bcbcda1732dfe83f414b4ea02f6fe586e1987848ef353be477ccd5f2', 'CLOSED', '2026-02-08 20:35:17', '2026-02-08 21:05:17', '2026-02-08 20:35:47', 'Serveur', 0),
(103, 1, 'f169bd07a46703bba1218f8e5a822bfb0a699f63278ccf1ebe076eb462022218', 'CLOSED', '2026-02-08 20:35:47', '2026-02-08 21:05:47', '2026-02-08 20:35:49', 'Serveur', 0),
(104, 1, '0d1bb004f12140f162612eabb3b7a123c94d851bd4b5c4971e01fbb1ed9d032d', 'CLOSED', '2026-02-08 20:35:52', '2026-02-08 20:35:52', '2026-02-08 20:35:55', 'Serveur', 0),
(105, 1, 'ddb7b76955ede2310d2f897448937703b9b7218c77f8de91b3d981b715e6068c', 'CLOSED', '2026-02-08 20:35:55', '2026-02-08 23:05:55', '2026-02-08 20:36:04', 'Serveur', 0),
(106, 1, 'd04dd1fdaaf41e401b09824e477763d00b3d770160812ec0a1e5f850c1f8afe0', 'CLOSED', '2026-02-08 20:36:08', '2026-02-08 22:36:08', '2026-02-08 20:36:10', 'Serveur', 0),
(107, 1, '3a0f5297afd916bc2e7e7e9c83388d8998822fd767f2ce073e52c491e716e699', 'CLOSED', '2026-02-08 20:36:18', '2026-02-08 21:06:18', '2026-02-08 20:36:48', 'HUGO', 0),
(108, 1, 'f6010c1292b028cd4d0e2059783b3a54596e766d21220c56110cf12e28a9d582', 'CLOSED', '2026-02-08 20:36:48', '2026-02-08 21:06:48', '2026-02-08 20:37:18', 'HUGO', 0),
(109, 1, 'bca9d827c475f61bcf99cf98c95b5ae86a3d259bec6d63054c61441ca6bbc9c6', 'CLOSED', '2026-02-08 20:37:18', '2026-02-08 21:07:18', '2026-02-08 20:37:46', 'HUGO', 1),
(110, 1, '88e1f506d3508581784a1e792699611f123a3a0ec710bb531ec42b218fabe901', 'CLOSED', '2026-02-08 20:37:46', '2026-02-08 21:07:46', '2026-02-08 20:38:16', 'HUGO', 0),
(111, 1, 'd2107eaec314a2927a12fa058642db13b3369ed99bc79589de6c5364122a8a8d', 'CLOSED', '2026-02-08 20:38:16', '2026-02-08 21:08:16', '2026-02-08 20:38:47', 'HUGO', 0),
(112, 1, '072cd2683bd0d24d77adad274d31d0acb40afd636efcf9d1a330f20dfca9933f', 'CLOSED', '2026-02-08 20:38:47', '2026-02-08 21:08:47', '2026-02-08 20:39:18', 'HUGO', 0),
(113, 1, 'c490c5a242e395c7a77971416f56d5264dd86c2b10262a7a9f336879e61b46a4', 'CLOSED', '2026-02-08 20:39:18', '2026-02-08 21:09:18', '2026-02-08 20:39:49', 'HUGO', 0),
(114, 1, '49ab425931b5fd57a63d84eb214ad9b1ef0ec1653d084ccc78662ac7dbf7a53c', 'CLOSED', '2026-02-08 20:39:49', '2026-02-08 21:09:49', '2026-02-08 20:40:20', 'HUGO', 0),
(115, 1, '708c98851750b7091593b007b8df2c8e30b83681b98d56a528007232f935743d', 'CLOSED', '2026-02-08 20:40:20', '2026-02-08 21:10:20', '2026-02-08 20:40:51', 'HUGO', 0),
(116, 1, 'aab0814bfa10273ea6a5ad19dd4d56ee8258558a8a8b07336a162ec8602ec19c', 'CLOSED', '2026-02-08 20:40:51', '2026-02-08 21:10:51', '2026-02-08 20:41:22', 'HUGO', 0),
(117, 1, 'cfc2597afceead29f413d64d3b06d4e3e83460091120d7b959c2caa4922483c9', 'CLOSED', '2026-02-08 20:41:22', '2026-02-08 21:11:22', '2026-02-08 20:41:53', 'HUGO', 0),
(118, 1, '4bce38aff9d0aeb9fffc8df05f1511aa28fe99dc07ed3094423b1b143ca3f156', 'CLOSED', '2026-02-08 20:41:53', '2026-02-08 21:11:53', '2026-02-08 20:42:24', 'HUGO', 0),
(119, 1, '76bea2b8a4366dd0f5b83af2340a87f6161b8409c17c302c9e399cac6a44a944', 'CLOSED', '2026-02-08 20:42:24', '2026-02-08 21:12:24', '2026-02-08 20:42:55', 'HUGO', 0),
(120, 1, '527c433aa5bd4dd169f7a877bb5d7a869f3b7a63e191aa7f32a06f00c1c0dd3e', 'CLOSED', '2026-02-08 20:42:55', '2026-02-08 21:12:55', '2026-02-08 20:43:26', 'HUGO', 0),
(121, 1, '343d6917791142ee46b6077ba07f612dc09cf783158659ad50d1c2301264013e', 'CLOSED', '2026-02-08 20:43:26', '2026-02-08 21:13:26', '2026-02-08 20:43:45', 'HUGO', 0),
(122, 1, '06f566739f34aa3985a6c9d15dd41ad3d6b941c32a27659ee49cf4b9b3b63586', 'CLOSED', '2026-02-08 20:43:45', '2026-02-08 21:13:45', '2026-02-08 20:44:15', 'HUGO', 1),
(123, 1, '303648dc7d25211323852ec10cd90702891700f5551b6542e1a10e54b455f83d', 'CLOSED', '2026-02-08 20:44:15', '2026-02-08 21:14:15', '2026-02-09 09:31:10', 'HUGO', 0),
(124, 1, 'd9147a8d34ee0f2583a3531ce6342392fdbe28ec71650b6b1194077fe3bd3dd9', 'CLOSED', '2026-02-09 09:31:24', '2026-02-09 10:01:24', '2026-02-09 09:31:55', 'Serveur', 0),
(125, 1, 'bc9cbc960992b6181d32282426ec2320d5ab669097257df58215f90a8e242983', 'CLOSED', '2026-02-09 09:31:55', '2026-02-09 10:01:55', '2026-02-09 09:32:26', 'Serveur', 1),
(126, 1, 'dacc548f900eb7d9488063b7e42bff443c045ccf55074cd7bbc1a0e5286ba138', 'CLOSED', '2026-02-09 09:32:26', '2026-02-09 10:02:26', '2026-02-09 09:32:57', 'Serveur', 0),
(127, 1, 'ab1a863def9c400b8f465534b8a4185e7b9481dff607fd9692c21d75993a60d4', 'CLOSED', '2026-02-09 09:32:57', '2026-02-09 10:02:57', '2026-02-09 09:33:28', 'Serveur', 0),
(128, 1, '7b942da8197fe58966549963abeea1609e9c5499b78decf47e6f4845564c8cd1', 'CLOSED', '2026-02-09 09:33:28', '2026-02-09 10:03:28', '2026-02-09 09:33:59', 'Serveur', 0),
(129, 1, 'db8cec2ca6ad48f94d73bdbd00188a77d04beaa0aae1217508336c906c2d9b3f', 'CLOSED', '2026-02-09 09:33:59', '2026-02-09 10:03:59', '2026-02-09 09:34:30', 'Serveur', 0),
(130, 1, '7f225c37fb75f822dc0fda64958c303e90057795b1d840fc5e516cf99d04ab0d', 'CLOSED', '2026-02-09 09:34:30', '2026-02-09 10:04:30', '2026-02-09 09:35:01', 'Serveur', 0),
(131, 1, '6d6f43c2330466729a902e41e055950e2bf9c76be7ffe5c77b76ac1cc24e3ee7', 'CLOSED', '2026-02-09 09:35:01', '2026-02-09 10:05:01', '2026-02-09 09:35:32', 'Serveur', 0),
(132, 1, 'd094c36c2a8b6b88d32edb0b54152ef74931d82b2a2457922be2c37a9c5daef3', 'CLOSED', '2026-02-09 09:35:32', '2026-02-09 10:05:32', '2026-02-09 09:36:01', 'Serveur', 0),
(133, 1, '14e8ed091b56855e9e232df93005ee1cf90b89cc380e78d174710348e81db5c3', 'CLOSED', '2026-02-09 09:36:01', '2026-02-09 10:06:01', '2026-02-09 09:36:32', 'Serveur', 1),
(134, 1, '55cc6d14fae93a808853fceb27ab50e4b6213403edbb9197e0d73bf9abbfbcc3', 'CLOSED', '2026-02-09 09:36:32', '2026-02-09 10:06:32', '2026-02-09 09:37:03', 'Serveur', 0),
(135, 1, '6d5de8a579c639ff268254c5aa59f1a92b2c9dd81530ca9f4ceea1513e5d4993', 'CLOSED', '2026-02-09 09:37:03', '2026-02-09 10:07:03', '2026-02-09 09:37:34', 'Serveur', 0),
(136, 1, 'ff1e1dc9cd50323b7de2d15b198fb4053ea3280d1d3fa220a1b7fd2a914c6183', 'CLOSED', '2026-02-09 09:37:34', '2026-02-09 10:07:34', '2026-02-09 09:38:05', 'Serveur', 0),
(137, 1, '7e65ee4ee42b251b3986cbb191021b605584002002314dcbb247bbe975d259a2', 'CLOSED', '2026-02-09 09:38:05', '2026-02-09 10:08:05', '2026-02-09 09:38:36', 'Serveur', 0),
(138, 1, '77c630fc4e4e947a2751d37e3753fd617487ea8461610ea3a472374a083cb35c', 'CLOSED', '2026-02-09 09:38:36', '2026-02-09 10:08:36', '2026-02-09 09:39:07', 'Serveur', 0),
(139, 1, 'd1d6c5421d1fb7bc886dc3ec612ceb565fb44c0bbd92dfaeb59873a95c76c12d', 'CLOSED', '2026-02-09 09:39:07', '2026-02-09 10:09:07', '2026-02-09 09:39:38', 'Serveur', 0),
(140, 1, 'c44341a6030b1651fd8f590f73892713de551ddcb3d0060bb4a9a883bf3ea45f', 'CLOSED', '2026-02-09 09:39:38', '2026-02-09 10:09:38', '2026-02-09 09:40:09', 'Serveur', 0),
(141, 1, 'd696887e4b86251355c0042306ad50c4cc1b761923632c64f887636a8ee770bf', 'CLOSED', '2026-02-09 09:40:09', '2026-02-09 10:10:09', '2026-02-09 09:40:40', 'Serveur', 0),
(142, 1, 'e3b99437d8e5dd539f8007d8c76ed6253512839acce4c3b1def150baaa7da298', 'CLOSED', '2026-02-09 09:40:40', '2026-02-09 10:10:40', '2026-02-09 09:41:11', 'Serveur', 0),
(143, 1, 'd9eef8111ba5a295732e5c0349f31789833f9656e2ff2e7673aaa74a4432e618', 'CLOSED', '2026-02-09 09:41:11', '2026-02-09 10:11:11', '2026-02-09 09:41:42', 'Serveur', 0),
(144, 1, '046bfffc37550de9ccc457c7171b528a717c403978532aacb01948b52b60160a', 'CLOSED', '2026-02-09 09:41:42', '2026-02-09 10:11:42', '2026-02-09 09:42:13', 'Serveur', 0),
(145, 1, '6253cb82837661ec417d575c822ad0a71bcb75ccc8b24d89c45837c676bccbbc', 'CLOSED', '2026-02-09 09:42:13', '2026-02-09 10:12:13', '2026-02-09 09:42:44', 'Serveur', 0),
(146, 1, '8df4ab53c3b5f94277aeb47589c9bff245cd7d905da4660d7b46700279f464bd', 'CLOSED', '2026-02-09 09:42:44', '2026-02-09 10:12:44', '2026-02-09 09:43:15', 'Serveur', 0),
(147, 1, '31d2745fff9412557dad2b536c4f9d17b9e7cd988b2d7365e1cb708028aae293', 'CLOSED', '2026-02-09 09:43:15', '2026-02-09 10:13:15', '2026-02-09 09:43:46', 'Serveur', 0),
(148, 1, 'bf826253227effc59ca812d28502c197b88a564ad939e27ad8046a1d2a098072', 'CLOSED', '2026-02-09 09:43:46', '2026-02-09 10:13:46', '2026-02-09 09:44:17', 'Serveur', 0),
(149, 1, 'a3721a42fcf53cf8e8ee3a3b0256bceccffb2547b26232c5fe29c2e7c7cc54fc', 'CLOSED', '2026-02-09 09:44:17', '2026-02-09 10:14:17', '2026-02-09 09:44:48', 'Serveur', 0),
(150, 1, '3ab48625a687e417800e96406e394dc8bbda7b98a0a8d3747c41f4c41106fedd', 'CLOSED', '2026-02-09 09:44:48', '2026-02-09 10:14:48', '2026-02-09 09:45:19', 'Serveur', 0),
(151, 1, 'e0df831c72cce559b82ddf34f4436fd1a25d9bb47f3dcb41a78031a3ec8c6eac', 'CLOSED', '2026-02-09 09:45:19', '2026-02-09 10:15:19', '2026-02-09 09:45:50', 'Serveur', 0),
(152, 1, 'e882cac1f16be3e4a5b77880fada2849f766fe786b7a6f27d491dded02e2c2af', 'CLOSED', '2026-02-09 09:45:50', '2026-02-09 10:15:50', '2026-02-09 09:46:21', 'Serveur', 0),
(153, 1, '7b609b8bb0d51fcf3dd7b45397341d719926d34f4aa1d4a20317b0fc41345bea', 'CLOSED', '2026-02-09 09:46:21', '2026-02-09 10:16:21', '2026-02-09 09:46:52', 'Serveur', 0),
(154, 1, '95113fec987e87836c57226ae77de1464c22b588be1aeeba8ab4006a7dafa70d', 'CLOSED', '2026-02-09 09:46:52', '2026-02-09 10:16:52', '2026-02-09 09:47:23', 'Serveur', 0),
(155, 1, '69374d80c9b1c30423c522ed977a639bdd212d9bc1ebe5ea2f19abf26f044075', 'CLOSED', '2026-02-09 09:47:23', '2026-02-09 10:17:23', '2026-02-09 09:47:54', 'Serveur', 0),
(156, 1, '6fff3b879e69b83e05860b237c2fab4a62c18e3844d0671d6ca91fb2d603a4f0', 'CLOSED', '2026-02-09 09:47:54', '2026-02-09 10:17:54', '2026-02-09 09:48:25', 'Serveur', 0),
(157, 1, '212faa72bcd8f108806c9b13642039bcf90747a1cc0e0dab8ed7094d92c7071f', 'CLOSED', '2026-02-09 09:48:25', '2026-02-09 10:18:25', '2026-02-09 09:48:56', 'Serveur', 0),
(158, 1, '8e5703bf8e88f077d138d075f9567aa62b7fb76da8784b8106f3d9acf406e970', 'CLOSED', '2026-02-09 09:48:56', '2026-02-09 10:18:56', '2026-02-09 09:49:27', 'Serveur', 0),
(159, 1, '21c0f508a618ab606dabc9708fdd54478761c0e856b08f490b766d61ed7d9da4', 'CLOSED', '2026-02-09 09:49:27', '2026-02-09 10:19:27', '2026-02-09 09:49:58', 'Serveur', 0),
(160, 1, '92bfb1dc3e1312e7b4647d9f4f933a624235a4b49fe2f9ef869f191a089b87a0', 'CLOSED', '2026-02-09 09:49:58', '2026-02-09 10:19:58', '2026-02-09 09:50:29', 'Serveur', 0),
(161, 1, '74e04c57e1c230c8f9f32d4872b687e3e577029a76e519aaf5726aaa455843df', 'CLOSED', '2026-02-09 09:50:29', '2026-02-09 10:20:29', '2026-02-09 09:51:00', 'Serveur', 0),
(162, 1, '7e84994c456df8922cbcfedbcf9c1cca662f88ccc8f3c37ad0859e361d655bbd', 'CLOSED', '2026-02-09 09:51:00', '2026-02-09 10:21:00', '2026-02-09 09:51:31', 'Serveur', 0),
(163, 1, '7a242d60dd3bc22fe110161ba7392aa115161aad6264384c856fa3b1ad60e7c5', 'CLOSED', '2026-02-09 09:51:31', '2026-02-09 10:21:31', '2026-02-09 09:52:02', 'Serveur', 0),
(164, 1, '81b41d7bcb2bac7d225ba6bd9318830dcc9f3cc2bd9fa0fa9a21ec859148465f', 'CLOSED', '2026-02-09 09:52:02', '2026-02-09 10:22:02', '2026-02-09 09:52:33', 'Serveur', 0),
(165, 1, 'c370d0d4e6056e2e038f510c0cc00460b7ad63a2618864a3267ffb519bdc3525', 'CLOSED', '2026-02-09 09:52:33', '2026-02-09 10:22:33', '2026-02-09 09:53:04', 'Serveur', 0),
(166, 1, 'd2e4c124f863a054ea957f29c5faa8477718e2400b17feb8c2225d9ab2c1d8b0', 'CLOSED', '2026-02-09 09:53:04', '2026-02-09 10:23:04', '2026-02-09 09:53:35', 'Serveur', 0),
(167, 1, '8866ff0fb84a00a72c05df66612efcbbbdd51e0780ff5239bbaff32c98350f0e', 'CLOSED', '2026-02-09 09:53:35', '2026-02-09 10:23:35', '2026-02-09 09:54:06', 'Serveur', 0),
(168, 1, 'ee240dc22caaa6cac30b60b8278a66eccc38a7803b2483a4d2fc564609dd0602', 'CLOSED', '2026-02-09 09:54:06', '2026-02-09 10:24:06', '2026-02-09 09:54:37', 'Serveur', 0),
(169, 1, 'ad4c85cc9e582c68af98230c9f17752ec96e9729f7a8c4f09e75bc8219786386', 'CLOSED', '2026-02-09 09:54:37', '2026-02-09 10:24:37', '2026-02-09 09:55:08', 'Serveur', 0),
(170, 1, 'a909fc19cec3200dbac57bdddad11576c779d508a569a114792fce8bd84dbf5b', 'CLOSED', '2026-02-09 09:55:08', '2026-02-09 10:25:08', '2026-02-09 09:55:39', 'Serveur', 0),
(171, 1, '2b74c0ea562b50fb13f98f48bc6cd6308ba2e845c2efb2c9e830eef3688c771e', 'CLOSED', '2026-02-09 09:55:39', '2026-02-09 10:25:39', '2026-02-09 09:56:10', 'Serveur', 0),
(172, 1, '07e9b3ea5f983ed796d36b0cfaf045bf1e3e48f5536fe4ad9891d2f8a2a14563', 'CLOSED', '2026-02-09 09:56:10', '2026-02-09 10:26:10', '2026-02-09 09:56:41', 'Serveur', 0),
(173, 1, '4770f367e1dd66160af5c9ee368ba634a544737921cc3a9a44a2101d4600a288', 'CLOSED', '2026-02-09 09:56:41', '2026-02-09 10:26:41', '2026-02-09 09:57:12', 'Serveur', 0),
(174, 1, '04a5fc4a3d892d7825d437c896e79ce47d9122eccc3319584e185659722782d2', 'CLOSED', '2026-02-09 09:57:12', '2026-02-09 10:27:12', '2026-02-09 09:57:43', 'Serveur', 0),
(175, 1, '6f43c392285019e0ffe3b97b37182b8de711169f9b36bc8781b1fbb7e0e5cdd0', 'CLOSED', '2026-02-09 09:57:43', '2026-02-09 10:27:43', '2026-02-09 09:58:14', 'Serveur', 0),
(176, 1, 'a7d189bc5e1642dcd5b2ab664f4f87fe75974d3173f6b36c63fa78952b638428', 'CLOSED', '2026-02-09 09:58:14', '2026-02-09 10:28:14', '2026-02-09 09:58:45', 'Serveur', 0),
(177, 1, '0fddd1ba5d744dc0339618d5fb8e8db564e401e6fbe1cabd70b620001f1bbad6', 'CLOSED', '2026-02-09 09:58:45', '2026-02-09 10:28:45', '2026-02-09 09:59:16', 'Serveur', 0),
(178, 1, '451600ebe0f034f849abb0d81b089ec35e02ad99dacedb0ddd095cfc362c6d6a', 'CLOSED', '2026-02-09 09:59:16', '2026-02-09 10:29:16', '2026-02-09 09:59:47', 'Serveur', 0),
(179, 1, '961a18f84deac3df5129f58c9e0826688bff319c6dab014a58e8ff3c2a807f5d', 'CLOSED', '2026-02-09 09:59:47', '2026-02-09 10:29:47', '2026-02-09 10:00:18', 'Serveur', 0),
(180, 1, 'a3107791cf0eb3cc6d16c5295b4271fdaa31b0c51fc93c6b6c12963290b4910e', 'CLOSED', '2026-02-09 10:00:18', '2026-02-09 10:30:18', '2026-02-09 10:00:49', 'Serveur', 0),
(181, 1, '515edab960845b88120f3ca7f8b26d7f4dc09e4df47995ecde6a75fa61a35572', 'CLOSED', '2026-02-09 10:00:49', '2026-02-09 10:30:49', '2026-02-09 10:01:20', 'Serveur', 0),
(182, 1, '419dfc775d1638d18adfa88bb8710b3f4fb9c87ea0872447fc7f4b2b1a35e5d8', 'CLOSED', '2026-02-09 10:01:20', '2026-02-09 10:31:20', '2026-02-09 10:01:51', 'Serveur', 0),
(183, 1, '99600dc9e9c0c16d186e62986fa063908b460b719da604e9788e8db90e52849c', 'CLOSED', '2026-02-09 10:01:51', '2026-02-09 10:31:51', '2026-02-09 10:02:22', 'Serveur', 0),
(184, 1, '3538dd749704904f5a04d8ce2679bb2bd5c36fcb9f31cc447584b6bd8fd88362', 'CLOSED', '2026-02-09 10:02:22', '2026-02-09 10:32:22', '2026-02-09 10:02:53', 'Serveur', 0),
(185, 1, '7b439de3ecdbc9b7d66cb08bad0a630b06dd303cd72fedfcc7335679323dff58', 'CLOSED', '2026-02-09 10:02:53', '2026-02-09 10:32:53', '2026-02-09 10:03:24', 'Serveur', 0),
(186, 1, '39a963ac0742437f0464985be011f844a5adeabb1927c73671fc370d4f44fb8b', 'CLOSED', '2026-02-09 10:03:24', '2026-02-09 10:33:24', '2026-02-09 10:03:55', 'Serveur', 0),
(187, 1, 'efd76c9a4721656707db7f6b046cc77bfdb810461017f4dde997e9126f76b1a0', 'CLOSED', '2026-02-09 10:03:55', '2026-02-09 10:33:55', '2026-02-09 10:04:26', 'Serveur', 0),
(188, 1, '27b4db4b75a3e230593a2ab1b3df1f11b0d9efb0a9df5f09f486ee447bcf7e16', 'CLOSED', '2026-02-09 10:04:26', '2026-02-09 10:34:26', '2026-02-09 10:04:57', 'Serveur', 0),
(189, 1, 'db2a508d1fb316177834d60d14af6f41df80932f7e7034f9795c8e03d95ea3f8', 'CLOSED', '2026-02-09 10:04:57', '2026-02-09 10:34:57', '2026-02-09 10:05:28', 'Serveur', 0),
(190, 1, '70ab28a4df33f1e206f4d83afec80abfed2d0f4b0a2147662874c8daaeef30ae', 'CLOSED', '2026-02-09 10:05:28', '2026-02-09 10:35:28', '2026-02-09 10:05:59', 'Serveur', 0),
(191, 1, '46f5d06c71a8072273dd6fcce194d21dc7f46d89fd5424dba9f86f776323e82b', 'CLOSED', '2026-02-09 10:05:59', '2026-02-09 10:35:59', '2026-02-09 10:06:30', 'Serveur', 0),
(192, 1, '5dd1ee9636fbc4ec403f8c8e481641e8cfec1c6e68654c36c3ab0c11a00c6591', 'CLOSED', '2026-02-09 10:06:30', '2026-02-09 10:36:30', '2026-02-09 10:07:01', 'Serveur', 0),
(193, 1, '8a4e1b8574b84890b9e481b325043537f920da6c711f6fa8fe10ee33bccce036', 'CLOSED', '2026-02-09 10:07:01', '2026-02-09 10:37:01', '2026-02-09 10:07:32', 'Serveur', 0),
(194, 1, '0f1c29d5cf841bc288a19ee91fdc703a53f6e1a2c4f0b09f74211d405694892d', 'CLOSED', '2026-02-09 10:07:32', '2026-02-09 10:37:32', '2026-02-09 10:08:03', 'Serveur', 0),
(195, 1, 'fc5bddd46089b5877a89105cc989b155177ff766d20196f60aea097fbaa946c9', 'CLOSED', '2026-02-09 10:08:03', '2026-02-09 10:38:03', '2026-02-09 10:08:34', 'Serveur', 0),
(196, 1, '2dd5ade4c6e246991206a6f401a004b34c335ced6a7c3d0c2ff49afcf8c1eeee', 'CLOSED', '2026-02-09 10:08:34', '2026-02-09 10:38:34', '2026-02-09 10:09:05', 'Serveur', 0),
(197, 1, '4098ef45484be3e9f440610d4afa34469e50e810672c4d3b421e278a08f23d0e', 'CLOSED', '2026-02-09 10:09:05', '2026-02-09 10:39:05', '2026-02-09 10:09:36', 'Serveur', 0),
(198, 1, '0e7042886fb23a5bf5bd0b48e7f99716ed7a7cd9c7cc2eabb3696ec7684eaecc', 'CLOSED', '2026-02-09 10:09:36', '2026-02-09 10:39:36', '2026-02-09 10:10:07', 'Serveur', 0),
(199, 1, '8d792565023232cbdbeb31a732f8495332a283e3042af96b3a358ce87f90fa25', 'CLOSED', '2026-02-09 10:10:07', '2026-02-09 10:40:07', '2026-02-09 10:10:38', 'Serveur', 0),
(200, 1, 'a982e19a9d5e03e809b9cde3667be857ce8691d65570cc327fb7c19dfc8bb46b', 'CLOSED', '2026-02-09 10:10:38', '2026-02-09 10:40:38', '2026-02-09 10:11:09', 'Serveur', 0),
(201, 1, 'e652baee46ea5e5061110a76789878e0fdf22cdb9b6fc634fd88d8ecd8a64aac', 'CLOSED', '2026-02-09 10:11:09', '2026-02-09 10:41:09', '2026-02-09 10:11:40', 'Serveur', 0),
(202, 1, '4a99bb87d5fb0a0d35cbc35adb957664b9c89faa74799304ef0bfae7b7bb820f', 'CLOSED', '2026-02-09 10:11:40', '2026-02-09 10:41:40', '2026-02-09 10:12:11', 'Serveur', 0),
(203, 1, 'fe7447a83cd86967163bab2239268f8c4e60c984ba7842275171fb94223d8de6', 'CLOSED', '2026-02-09 10:12:11', '2026-02-09 10:42:11', '2026-02-09 10:12:42', 'Serveur', 0),
(204, 1, '4f316640068faaf29eb989067e2d9048a4d7e5fe5f832e6642c1c59611446963', 'CLOSED', '2026-02-09 10:12:42', '2026-02-09 10:42:42', '2026-02-09 10:13:13', 'Serveur', 0),
(205, 1, '13e6db9795346ab49e56b580606f7d4ae756cb3f7a785af5b515189430da707a', 'CLOSED', '2026-02-09 10:13:13', '2026-02-09 10:43:13', '2026-02-09 10:13:44', 'Serveur', 0),
(206, 1, '6d0224ca318d82ff7c42dc68dc7916476115d3eb6473ccbc6a9d8cdc001a85e0', 'CLOSED', '2026-02-09 10:13:44', '2026-02-09 10:43:44', '2026-02-09 10:14:15', 'Serveur', 0),
(207, 1, '76363754d1390b7537a09fc52ce8c371536c8ce49c404d79dea378e6a08f297b', 'CLOSED', '2026-02-09 10:14:15', '2026-02-09 10:44:15', '2026-02-09 10:14:46', 'Serveur', 0),
(208, 1, '1dd06e5ae491024ac52e28ce750838bc1f3d452231df5bc78a1a2c5c33c8e77f', 'CLOSED', '2026-02-09 10:14:46', '2026-02-09 10:44:46', '2026-02-09 10:15:17', 'Serveur', 0),
(209, 1, 'f7855667941fcba41e3be7404110578da025bd1fdd23ad993e5147f5f00bf89b', 'CLOSED', '2026-02-09 10:15:17', '2026-02-09 10:45:17', '2026-02-09 10:15:48', 'Serveur', 0),
(210, 1, '99f7b3b0e105ed776f35c9b383d4ff7392082dde529bdf50d1fadf2a09a0a441', 'CLOSED', '2026-02-09 10:15:48', '2026-02-09 10:45:48', '2026-02-09 10:16:19', 'Serveur', 0),
(211, 1, 'efb743a57df8977749e7ff49f194fd2444689afbaa652f1fa418068cf03c379e', 'CLOSED', '2026-02-09 10:16:19', '2026-02-09 10:46:19', '2026-02-09 10:16:50', 'Serveur', 0),
(212, 1, 'ced1ec7d78fa628cadd7770acee841bb0bdf475b7c4f643b81f39f02a41742b5', 'CLOSED', '2026-02-09 10:16:50', '2026-02-09 10:46:50', '2026-02-09 10:17:21', 'Serveur', 0),
(213, 1, '8b12ac995a4fc3e1d7e363d99bd828891bae356d3b13fe716d708ad3f012981b', 'CLOSED', '2026-02-09 10:17:21', '2026-02-09 10:47:21', '2026-02-09 10:17:52', 'Serveur', 0),
(214, 1, 'eb185fe7841c6ec4d6474cff18635387fd2014e46f21d2d0b8e4b0d612ab6b52', 'CLOSED', '2026-02-09 10:17:52', '2026-02-09 10:47:52', '2026-02-09 10:18:23', 'Serveur', 0),
(215, 1, '3f41adfa8388c59529e8f744a0691ef76b22d1b644975ae3adb0a0389ad0cd74', 'CLOSED', '2026-02-09 10:18:23', '2026-02-09 10:48:23', '2026-02-09 10:18:54', 'Serveur', 0),
(216, 1, '6d611a051b0f82c69e0e4512bf9ca52ca6b1c2475893fb3a7c374de665a1c1a5', 'CLOSED', '2026-02-09 10:18:54', '2026-02-09 10:48:54', '2026-02-09 10:19:25', 'Serveur', 0),
(217, 1, '50bc7da57ead04d71375558a5fca91d3701890134b7f73750e616566911d4d4a', 'CLOSED', '2026-02-09 10:19:25', '2026-02-09 10:49:25', '2026-02-09 10:19:56', 'Serveur', 0),
(218, 1, 'ea87ba30316ced68a80c1a66e303797fa8fc7cf04f3642fca2122381078bee73', 'CLOSED', '2026-02-09 10:19:56', '2026-02-09 10:49:56', '2026-02-09 10:20:27', 'Serveur', 0),
(219, 1, 'd67d5d8ca013eb916724b1e6dbc95ed0374ec4ff2cedfd70ced0dcaf3bb8aadc', 'CLOSED', '2026-02-09 10:20:27', '2026-02-09 10:50:27', '2026-02-09 10:20:58', 'Serveur', 0),
(220, 1, '879aacdffee41c4e35d190f4019d6d26d60451d191475cdbd331bbbcf2d46529', 'CLOSED', '2026-02-09 10:20:58', '2026-02-09 10:50:58', '2026-02-09 10:21:29', 'Serveur', 0),
(221, 1, '8f1010093631232b5e2d3a98a989500db185b6213840c89c2f0a4aea547768c8', 'CLOSED', '2026-02-09 10:21:29', '2026-02-09 10:51:29', '2026-02-09 10:22:00', 'Serveur', 0),
(222, 1, '1667224b0ba400c98a71585d81c5f5d4bd7ebdfed3dd7ac66c9470ad3d9fedc9', 'CLOSED', '2026-02-09 10:22:00', '2026-02-09 10:52:00', '2026-02-09 10:22:31', 'Serveur', 0),
(223, 1, 'e378770f8688b4fdac068bf4a81649ca319f5860bed678d74900598b1e8094ee', 'CLOSED', '2026-02-09 10:22:31', '2026-02-09 10:52:31', '2026-02-09 10:23:28', 'Serveur', 0),
(224, 1, 'ff312ca9b2dac380454331bad03d609367e5b76a127372fb773f3eb7129336bd', 'CLOSED', '2026-02-09 10:23:28', '2026-02-09 10:53:28', '2026-02-09 10:23:58', 'Serveur', 0),
(225, 1, '251098daee1f1149f7b1f439acbc7f76c53cebf678be6a829c93919878f6b290', 'CLOSED', '2026-02-09 10:23:58', '2026-02-09 10:53:58', '2026-02-09 10:24:29', 'Serveur', 0),
(226, 1, 'b318e7ccaf13e4e90fe4de09d147eb0fa232268c49287b14cd9128745beb1d94', 'CLOSED', '2026-02-09 10:24:29', '2026-02-09 10:54:29', '2026-02-09 10:25:00', 'Serveur', 0),
(227, 1, '4e4cc6976a139ea76dc51c91cfb5a56538166968acfa5fe96d52c77a4b78e234', 'CLOSED', '2026-02-09 10:25:00', '2026-02-09 10:55:00', '2026-02-09 10:25:31', 'Serveur', 0),
(228, 1, '3819a52af027ed18028dbff68c132bc34d58192228bfee241124d82f7038a67a', 'CLOSED', '2026-02-09 10:25:31', '2026-02-09 10:55:31', '2026-02-09 10:26:02', 'Serveur', 0),
(229, 1, '391c053b2e10ce2321c2771d4aa0d065063fa2d5430ade793e32f79c3fe2540f', 'CLOSED', '2026-02-09 10:26:02', '2026-02-09 10:56:02', '2026-02-09 10:26:33', 'Serveur', 0),
(230, 1, 'eb456ba227ca1c2cdd53a4111d1f25999114c7c02b030db3e8731defc7127c62', 'CLOSED', '2026-02-09 10:26:33', '2026-02-09 10:56:33', '2026-02-09 10:27:04', 'Serveur', 0),
(231, 1, 'c97534c2af2d501897e6b7317cbc7dd0dcbfd6090ad597ef1d14c72615b5f474', 'CLOSED', '2026-02-09 10:27:04', '2026-02-09 10:57:04', '2026-02-09 10:27:35', 'Serveur', 0),
(232, 1, 'ad0c4508a90c8db09234dc7b5ac8ae9a6a5857d88be6bc907236931f77aa8fc4', 'CLOSED', '2026-02-09 10:27:35', '2026-02-09 10:57:35', '2026-02-09 10:28:06', 'Serveur', 0),
(233, 1, '2d30f1caee49db368fc4967c5a765095884babb3cb1b425abd51089afa791964', 'CLOSED', '2026-02-09 10:28:06', '2026-02-09 10:58:06', '2026-02-09 10:28:37', 'Serveur', 0),
(234, 1, 'bce393664d0d1465e0d269da2e104ee34dec7535a109ed4d536798628992a708', 'CLOSED', '2026-02-09 10:28:37', '2026-02-09 10:58:37', '2026-02-09 10:29:08', 'Serveur', 0),
(235, 1, '069a2526372d0c3a8b85b7364e3f009a5737820f228ceda5516905c0d33499bd', 'CLOSED', '2026-02-09 10:29:08', '2026-02-09 10:59:08', '2026-02-09 10:29:19', 'Serveur', 0),
(236, 1, 'd9ffc91520aa02850e5cb26a10223643f55a5e7ca0b4c86a78e3be9e34807cbc', 'CLOSED', '2026-02-09 10:29:37', '2026-02-09 09:44:37', '2026-02-09 10:29:40', 'Serveur', 0),
(237, 1, '8b077ce00e7a9ed0e6de658cf05f8784db852ebf4d09e98038c611526e0ff534', 'CLOSED', '2026-02-09 10:29:40', '2026-02-09 09:59:40', '2026-02-09 10:29:43', 'Serveur', 0),
(238, 1, '4d177f0d4c014c7a2544a4a16a4866253b5b249e8258b91d0ad35898aabe125e', 'CLOSED', '2026-02-09 10:29:43', '2026-02-09 10:29:43', '2026-02-09 10:29:46', 'Serveur', 0),
(239, 1, '3e6d5ab3d45ae0d1dcee589707144bf6f03fd1de0748637604c7a177a0e30d28', 'CLOSED', '2026-02-09 10:29:46', '2026-02-09 10:59:46', '2026-02-09 10:30:16', 'Serveur', 0),
(240, 1, 'ed8e384233d34d2705d3df1f3b12997dcb1c714a92c8301afb5a69f84b7f3f67', 'CLOSED', '2026-02-09 10:30:16', '2026-02-09 11:00:16', '2026-02-09 10:30:47', 'Serveur', 0),
(241, 1, '047fb318120afe73efb327c4cb76627c41e0142b01145abcfa69464fae4ba092', 'CLOSED', '2026-02-09 10:30:47', '2026-02-09 11:00:47', '2026-02-09 10:31:18', 'Serveur', 0),
(242, 1, '6bf49f3a76e73690966793c7121ee7097ed346dfd2ba670bfb108b098079cf29', 'CLOSED', '2026-02-09 10:31:18', '2026-02-09 11:01:18', '2026-02-09 10:31:49', 'Serveur', 0),
(243, 1, 'c0cd5037bc01732d8fddd27bbf75b566f7fbfac99b65cfc2e577c7631a98b1f8', 'CLOSED', '2026-02-09 10:31:49', '2026-02-09 11:01:49', '2026-02-09 10:32:19', 'Serveur', 0),
(244, 1, '1fcba7cc1376e389bdac208ca553cf213678d0e2fe0428d6e8591d920af82343', 'CLOSED', '2026-02-09 10:32:19', '2026-02-09 11:02:19', '2026-02-09 10:32:38', 'Serveur', 0),
(245, 1, 'c630211a14b82969d0fe890828df525b393f917c0c54dde35358088f15e917f4', 'CLOSED', '2026-02-09 10:33:06', '2026-02-09 11:03:06', '2026-02-09 10:33:36', 'Serveur', 0),
(246, 1, '4e232d8e23d3dada262c19a8ce0ab40a14718007d3db4912fde31a386ef48b7d', 'CLOSED', '2026-02-09 10:33:36', '2026-02-09 11:03:36', '2026-02-09 10:34:07', 'Serveur', 0),
(247, 1, '756dff3e155701c9d144425a86ee2a46f31ffcebdee7a1f90fe59b54c3682e46', 'CLOSED', '2026-02-09 10:34:07', '2026-02-09 11:04:07', '2026-02-09 10:34:38', 'Serveur', 0),
(248, 1, 'ae65edf41de72cb128d849e2ede8a12a3afd66b14e8d644760ce72903b801a50', 'CLOSED', '2026-02-09 10:34:38', '2026-02-09 11:04:38', '2026-02-09 10:35:09', 'Serveur', 0),
(249, 1, '756ba624b672b47ad45718f77aa0c4bef5d3040a6fd818d3619a1f9e0ee67041', 'CLOSED', '2026-02-09 10:35:09', '2026-02-09 11:05:09', '2026-02-09 10:35:40', 'Serveur', 0),
(250, 1, '3be18f2c34ea186d0091e1232dc1af5985a34f78e1947db1244b6cb142e7d617', 'CLOSED', '2026-02-09 10:35:40', '2026-02-09 11:05:40', '2026-02-09 10:36:11', 'Serveur', 0),
(251, 1, '47036fd9059881e9ec3b0bd58796b4c79daf2694cd76def66feea5e02310de98', 'CLOSED', '2026-02-09 10:36:11', '2026-02-09 11:06:11', '2026-02-09 10:36:42', 'Serveur', 0),
(252, 1, '606f41c88c927ce776ae6425c75c45fcfce5beff64d87524bb8ec777484d7501', 'CLOSED', '2026-02-09 10:36:42', '2026-02-09 11:06:42', '2026-02-09 10:37:13', 'Serveur', 0),
(253, 1, '43acc929de7ed7ecf2b7d64be69ab1fc95236a0118045ac7a1cc6319e650e91f', 'CLOSED', '2026-02-09 10:37:13', '2026-02-09 11:07:13', '2026-02-09 10:37:44', 'Serveur', 0),
(254, 1, '9f57b0c21f6d42c9923c97ffe8780987642a2588e298b0e3e61c7c710089b26a', 'CLOSED', '2026-02-09 10:37:44', '2026-02-09 11:07:44', '2026-02-09 10:38:15', 'Serveur', 0),
(255, 1, '597dc77d060596c34a25dc19625bcdef6de4acb06c2fa4a768a46d433f303f60', 'CLOSED', '2026-02-09 10:38:15', '2026-02-09 11:08:15', '2026-02-09 10:38:46', 'Serveur', 0),
(256, 1, '7d0c92bbd6762e81f5328fa955710eda24583aafe964c200326fa686ba6e3b9e', 'CLOSED', '2026-02-09 10:38:46', '2026-02-09 11:08:46', '2026-02-09 10:39:17', 'Serveur', 0),
(257, 1, '83658a4ace9568329951f9e26e5e1bf0b223d606f05e9b3b1fb8d75a7f6d633c', 'CLOSED', '2026-02-09 10:39:17', '2026-02-09 11:09:17', '2026-02-09 10:39:48', 'Serveur', 0),
(258, 1, 'fe8400e15494e051a3de9939de89f0489e1ba858ce1c1c0679462e61062b0b9d', 'CLOSED', '2026-02-09 10:39:48', '2026-02-09 11:09:48', '2026-02-09 10:40:19', 'Serveur', 0),
(259, 1, '5fe0d4b1ebd46dc502527bcff3710f422226beed031271de03866e245ecaa8af', 'CLOSED', '2026-02-09 10:40:19', '2026-02-09 11:10:19', '2026-02-09 10:40:50', 'Serveur', 0),
(260, 1, '4b0701ca89c4e3bc9ef035230d9dc703eb46d27ce9d2e85bea8948e3e2a7405a', 'CLOSED', '2026-02-09 10:40:50', '2026-02-09 11:10:50', '2026-02-09 10:41:21', 'Serveur', 0),
(261, 1, 'e3a2a653f95bd8d9318497b3eafafa894cbbdecff77f9185e083e04d65d1439c', 'CLOSED', '2026-02-09 10:41:21', '2026-02-09 11:11:21', '2026-02-09 10:41:52', 'Serveur', 0),
(262, 1, 'de2eadcf6337375cb4a6d975079d9ac63e27eceb422516ecf33d13e5b41b011f', 'CLOSED', '2026-02-09 10:41:52', '2026-02-09 11:11:52', '2026-02-09 10:42:23', 'Serveur', 0),
(263, 1, '6d3732499a0d40ab3a489b353cb00b26aa09724e41eb3c63586c23ef4a833786', 'CLOSED', '2026-02-09 10:42:23', '2026-02-09 11:12:23', '2026-02-09 10:42:54', 'Serveur', 0),
(264, 1, 'a978b2989f8ce1c5889661a80191be9a7c221e78cbd960ea1ce023d3e451e382', 'CLOSED', '2026-02-09 10:42:54', '2026-02-09 11:12:54', '2026-02-09 10:43:25', 'Serveur', 0),
(265, 1, '7479c460985c7b4a220fb48dc15fd0ebb37ed8a2a079a21cf2a9fb1ce7582e4d', 'CLOSED', '2026-02-09 10:43:25', '2026-02-09 11:13:25', '2026-02-09 10:43:56', 'Serveur', 0),
(266, 1, '5c98b8ee29adb881ad3e41b0f6f71ba8d25447bd6dbe2a9c7de3237ad234adb2', 'CLOSED', '2026-02-09 10:43:56', '2026-02-09 11:13:56', '2026-02-09 10:44:27', 'Serveur', 0),
(267, 1, '693b12a8dbcd0389903abc3423c32296bb5c1e8ac93f9be3dda38ec00eb23ad9', 'CLOSED', '2026-02-09 10:44:27', '2026-02-09 11:14:27', '2026-02-09 10:44:58', 'Serveur', 0),
(268, 1, 'd73d8b3b09f12f856bbc6c24beb58e0158e84b662bb531e787ceccbbe085044b', 'CLOSED', '2026-02-09 10:44:58', '2026-02-09 11:14:58', '2026-02-09 10:45:29', 'Serveur', 0),
(269, 1, 'd34e58aab4ffcfde026aec943b450d85a9e512753818d6e9393e63a75004c445', 'CLOSED', '2026-02-09 10:45:29', '2026-02-09 11:15:29', '2026-02-09 10:46:00', 'Serveur', 0),
(270, 1, '87cad15224390ec87bfcc570fc6bce52caf48102662d90eb435b25250f0fca95', 'CLOSED', '2026-02-09 10:46:00', '2026-02-09 11:16:00', '2026-02-09 10:46:31', 'Serveur', 0),
(271, 1, '2a774a9b54c940cedd330d1dbab76561aec55b96eea3c8148be3a002762267d1', 'CLOSED', '2026-02-09 10:46:31', '2026-02-09 11:16:31', '2026-02-09 10:47:02', 'Serveur', 0),
(272, 1, '29e3e4b3804fb9e1d8736348901acaaeb38707fa99f195fa63bfcdfb043d1f38', 'CLOSED', '2026-02-09 10:47:02', '2026-02-09 11:17:02', '2026-02-09 10:47:33', 'Serveur', 0),
(273, 1, '56a3215f50d56cc33dbe3b338d927133ea99316d75deb156c3a931dd2448eb9e', 'CLOSED', '2026-02-09 10:47:33', '2026-02-09 11:17:33', '2026-02-09 10:48:04', 'Serveur', 0),
(274, 1, '90198241fc0addaa780d97a558d4118fa226c8b6a8f7271ade4ae4afe9a583c6', 'CLOSED', '2026-02-09 10:48:04', '2026-02-09 11:18:04', '2026-02-09 10:48:35', 'Serveur', 0),
(275, 1, '6abefc7e338b0fd8082b1551d957996f8c89528294934da283de219ae5dd92a4', 'CLOSED', '2026-02-09 10:48:35', '2026-02-09 11:18:35', '2026-02-09 10:49:06', 'Serveur', 0),
(276, 1, '3f76f4724fce033a749c85c7986d38f6324995919482f12d66c95e909999683c', 'CLOSED', '2026-02-09 10:49:06', '2026-02-09 11:19:06', '2026-02-09 10:49:37', 'Serveur', 0),
(277, 1, '0c6423f6a4f00268d8fa6028f7c9f5b43ef9e0633ca1f3d8cdd56a32379b51f1', 'CLOSED', '2026-02-09 10:49:37', '2026-02-09 11:19:37', '2026-02-09 10:50:08', 'Serveur', 0),
(278, 1, '8ba5c9c68cccd0b3e6f270fa2d5a0dccf321f6150eb8aab189f79510a555cf72', 'CLOSED', '2026-02-09 10:50:08', '2026-02-09 11:20:08', '2026-02-09 10:50:39', 'Serveur', 0),
(279, 1, '5fcab6b4717f0398d219608eb140de0fb5bb5cf422b9d431cb4e5e4af9e7c5b2', 'CLOSED', '2026-02-09 10:50:39', '2026-02-09 11:20:39', '2026-02-09 10:51:10', 'Serveur', 0),
(280, 1, 'a452e8f4715901858a87d4f313eb5157938e59571c4dc98d396f2abd341cb389', 'CLOSED', '2026-02-09 10:51:10', '2026-02-09 11:21:10', '2026-02-09 10:51:41', 'Serveur', 0),
(281, 1, 'adde092b75924a709166264c4f5627ba9a3a35be17f02e9bf0249c5cecc30e5d', 'CLOSED', '2026-02-09 10:51:41', '2026-02-09 11:21:41', '2026-02-09 10:52:12', 'Serveur', 0),
(282, 1, 'da7185868c29b2ec8738f104a0058a9cda9623cd56d2c9af76b4811f299af24f', 'CLOSED', '2026-02-09 10:52:12', '2026-02-09 11:22:12', '2026-02-09 10:52:43', 'Serveur', 0),
(283, 1, '6c4d66618e127847472e8c1bac5257fa56a02e802d75e73c6dfb3ffe613228d8', 'CLOSED', '2026-02-09 10:52:43', '2026-02-09 11:22:43', '2026-02-09 10:53:14', 'Serveur', 0),
(284, 1, '03d2f404bac310f2bd8a24ad19db98227561aa8d525da0f151ba8ec78818f8f0', 'CLOSED', '2026-02-09 10:53:14', '2026-02-09 11:23:14', '2026-02-09 10:53:45', 'Serveur', 0),
(285, 1, '77ffc18a083b4d2d064b661d6194d2618666d3b408fe8911a444df3d5c595310', 'CLOSED', '2026-02-09 10:53:45', '2026-02-09 11:23:45', '2026-02-09 10:54:16', 'Serveur', 0),
(286, 1, 'fabbe59ddf6e74c0a978f10a71c40bc5a11f5daf2005f3c0a789433e82c39f91', 'CLOSED', '2026-02-09 10:54:16', '2026-02-09 11:24:16', '2026-02-09 10:54:47', 'Serveur', 0),
(287, 1, 'a379bb4a8b5ffb4517141a393fe419b4338c22f4075b2e62a4128930753fae3b', 'CLOSED', '2026-02-09 10:54:47', '2026-02-09 11:24:47', '2026-02-09 10:55:18', 'Serveur', 0),
(288, 1, '0f1dd1e37726b297b1e994c3ab7f977285c7d056815b40c470772b97dc72b200', 'CLOSED', '2026-02-09 10:55:18', '2026-02-09 11:25:18', '2026-02-09 10:55:49', 'Serveur', 0),
(289, 1, '10a619dcf6a7fbcb23d3812764afee61cf7813b0b19c14a0fba79070b37698b0', 'CLOSED', '2026-02-09 10:55:49', '2026-02-09 11:25:49', '2026-02-09 10:56:20', 'Serveur', 0),
(290, 1, '5d6fe8c884041718aa038a2267dfb18329b4b338a885aa850ff553bf31fb945b', 'CLOSED', '2026-02-09 10:56:20', '2026-02-09 11:26:20', '2026-02-09 10:56:51', 'Serveur', 0),
(291, 1, '1dd8d88409eee1d888d8b2d2b67950544de1f67295165c1b8b7f14a4ce723fe7', 'CLOSED', '2026-02-09 10:56:51', '2026-02-09 11:26:51', '2026-02-09 10:57:22', 'Serveur', 0),
(292, 1, '134381698269be64f1c0aa60b23f7b179cfc4a23201880832782ca988e83d14d', 'CLOSED', '2026-02-09 10:57:22', '2026-02-09 11:27:22', '2026-02-09 10:57:53', 'Serveur', 0),
(293, 1, 'f49537dbc20cbe4e50ea0cba2951df088e317beb004e55432c35dc6217da564a', 'CLOSED', '2026-02-09 10:57:53', '2026-02-09 11:27:53', '2026-02-09 10:58:24', 'Serveur', 0),
(294, 1, '97a2b1eb5c7900a8156e1361c72b6fe901704c31b20d65ccc352d22278107f56', 'CLOSED', '2026-02-09 10:58:24', '2026-02-09 11:28:24', '2026-02-09 10:58:55', 'Serveur', 0),
(295, 1, 'e98d1beb826bdb3917dd64b2624b98004648c3e19aa2bc0e4b3ada7450cac274', 'CLOSED', '2026-02-09 10:58:55', '2026-02-09 11:28:55', '2026-02-09 10:59:26', 'Serveur', 0);
INSERT INTO `table_sessions` (`id`, `table_id`, `session_token`, `status`, `opened_at`, `expires_at`, `closed_at`, `opened_by`, `total_orders`) VALUES
(296, 1, '09a866432d0e9d041a481600d826d4277446c31656c351a2fe6b524cd7cbc389', 'CLOSED', '2026-02-09 10:59:26', '2026-02-09 11:29:26', '2026-02-09 10:59:57', 'Serveur', 0),
(297, 1, 'c1bec84b5e04ab0c9d7f30c3593d5cf12ed893364b007655bc3826b59e531c72', 'CLOSED', '2026-02-09 10:59:57', '2026-02-09 11:29:57', '2026-02-09 11:00:28', 'Serveur', 0),
(298, 1, '20548cb01060f21d48502cbaef093e2823158d62a0c3c411731fb27f3d3683a4', 'CLOSED', '2026-02-09 11:00:28', '2026-02-09 11:30:28', '2026-02-09 11:00:59', 'Serveur', 0),
(299, 1, '75237684473a314b29b9ffe7eb3c02f70c74ef06b6431515b7dc71bc620898f5', 'CLOSED', '2026-02-09 11:00:59', '2026-02-09 11:30:59', '2026-02-09 11:01:30', 'Serveur', 0),
(300, 1, '8714f49cc1a647e22cd2c6c68f926577a4948a7ecea7e80b5b4a4f8e55809ac5', 'CLOSED', '2026-02-09 11:01:30', '2026-02-09 11:31:30', '2026-02-09 11:02:01', 'Serveur', 0),
(301, 1, 'f289919a94425d67f33700921ed9177612b3134e155670593ce2ab5129108049', 'CLOSED', '2026-02-09 11:02:01', '2026-02-09 11:32:01', '2026-02-09 11:02:32', 'Serveur', 0),
(302, 1, 'c3e3a992a41508f50d57c638074d0c7eec24168021330026f82aa2802daf4313', 'CLOSED', '2026-02-09 11:02:32', '2026-02-09 11:32:32', '2026-02-09 11:03:03', 'Serveur', 0),
(303, 1, '612e135d25f031908f0e8d07649916e8ac42bf8aa8cb4e64c91ca926d681af43', 'CLOSED', '2026-02-09 11:03:03', '2026-02-09 11:33:03', '2026-02-09 11:03:34', 'Serveur', 0),
(304, 1, '8550564eae596268ec78576d062f5de2d41a004d00f03f26e78a70d6cf8e0bfd', 'CLOSED', '2026-02-09 11:03:34', '2026-02-09 11:33:34', '2026-02-09 11:04:05', 'Serveur', 0),
(305, 1, '744651d4b237cbbfc618773b5a3c7137d2936b9992e3362977b925927b5c942a', 'CLOSED', '2026-02-09 11:04:05', '2026-02-09 11:34:05', '2026-02-09 11:04:36', 'Serveur', 0),
(306, 1, 'f98bfdf492817f469470ac865461582006b22ba4b77b19b4152e148479af9342', 'CLOSED', '2026-02-09 11:04:36', '2026-02-09 11:34:36', '2026-02-09 11:05:07', 'Serveur', 0),
(307, 1, '929665a1c45055c3e67650cc68e14ea76997cb19bfd3fc5a08da2139705f7b13', 'CLOSED', '2026-02-09 11:05:07', '2026-02-09 11:35:07', '2026-02-09 11:05:38', 'Serveur', 0),
(308, 1, '31e50b4dfb5a2612867690210609c20ebb4387d61c90a51dd4bf9f0d2868c810', 'CLOSED', '2026-02-09 11:05:38', '2026-02-09 11:35:38', '2026-02-09 11:06:09', 'Serveur', 0),
(309, 1, 'edd1f443a6024d851290537d4ca41171a1c7a7d3f4f91806c839a3a344265ebc', 'CLOSED', '2026-02-09 11:06:09', '2026-02-09 11:36:09', '2026-02-09 11:06:40', 'Serveur', 0),
(310, 1, '29cc0ec70b68b04fa273eeb408daa4a2dbc2bb3e792ccdd820115a9f2f35c881', 'CLOSED', '2026-02-09 11:06:40', '2026-02-09 11:36:40', '2026-02-09 11:07:11', 'Serveur', 0),
(311, 1, '0d4ed2c721724a75b29491150a016f1b5d28a4ad2b42385be5ec426adf4c742e', 'CLOSED', '2026-02-09 11:07:11', '2026-02-09 11:37:11', '2026-02-09 11:07:42', 'Serveur', 0),
(312, 1, '79986f58de3baa80ab612c323e90c86d410a27a498e4990f32c3c7dea61cff52', 'CLOSED', '2026-02-09 11:07:42', '2026-02-09 11:37:42', '2026-02-09 11:08:13', 'Serveur', 0),
(313, 1, 'f134559f9710db152a81e25b89ef8a159d5051c43f830ed58ceaeee6388e87a1', 'CLOSED', '2026-02-09 11:08:13', '2026-02-09 11:38:13', '2026-02-09 11:08:44', 'Serveur', 0),
(314, 1, 'dcf93588057715bd6f793a2776b8ea8334de63a372c31328e379aed006c985b4', 'CLOSED', '2026-02-09 11:08:44', '2026-02-09 11:38:44', '2026-02-09 11:09:15', 'Serveur', 0),
(315, 1, 'fdcd5d3993fb8a71421f52bec106e6f860133125600d668c3b429604e3da4c00', 'CLOSED', '2026-02-09 11:09:15', '2026-02-09 11:39:15', '2026-02-09 11:09:46', 'Serveur', 0),
(316, 1, '9b9cc7a81bc0d520dcfb77df2aeadedc8acb26f6bdbe82835d986322a76038c4', 'CLOSED', '2026-02-09 11:09:46', '2026-02-09 11:39:46', '2026-02-09 11:10:17', 'Serveur', 0),
(317, 1, '19ad54caef456e1637a78fa9d18dee422aec5537113fd9fdb36e68e133f7f2c4', 'CLOSED', '2026-02-09 11:10:17', '2026-02-09 11:40:17', '2026-02-09 11:10:48', 'Serveur', 0),
(318, 1, 'f31ed5f212b1197fcc21dc73fec4a1b8dcc0623bcdfb88251ab590df777270e7', 'CLOSED', '2026-02-09 11:10:48', '2026-02-09 11:40:48', '2026-02-09 11:11:19', 'Serveur', 0),
(319, 1, '45e230780847ac19f4413ec488b6e820a8566e0e9610831cc0a0018f3d2c6545', 'CLOSED', '2026-02-09 11:11:19', '2026-02-09 11:41:19', '2026-02-09 11:11:50', 'Serveur', 0),
(320, 1, '519b3a0a296658944e24fb02a42aab32a00acf8f38e31c5e2601ab000d438aee', 'CLOSED', '2026-02-09 11:11:50', '2026-02-09 11:41:50', '2026-02-09 11:12:21', 'Serveur', 0),
(321, 1, 'e0b8b589cd446f7dfae294a6c3e534999690aee47d86da9418114b28af7da15a', 'CLOSED', '2026-02-09 11:12:21', '2026-02-09 11:42:21', '2026-02-09 11:12:52', 'Serveur', 0),
(322, 1, '02fea6c5a29ab79c833c31442fbeb2cf5fe9f7be1079640fd3371ec29e485871', 'CLOSED', '2026-02-09 11:12:52', '2026-02-09 11:42:52', '2026-02-09 11:13:23', 'Serveur', 0),
(323, 1, '8e58760cbcea04d0393dd7ffd39e4f316a94fceb45790f397e7a6476774b4ddb', 'CLOSED', '2026-02-09 11:13:23', '2026-02-09 11:43:23', '2026-02-09 11:13:54', 'Serveur', 0),
(324, 1, 'c9eea7576c64b7041ab31cd668ff2646e01ba94ad305ae2e9f56543135be627c', 'CLOSED', '2026-02-09 11:13:54', '2026-02-09 11:43:54', '2026-02-09 11:14:25', 'Serveur', 0),
(325, 1, 'b7de71c715da257ed1c22886efafc902fc3bca4fabb4aff79616501f49a1c612', 'CLOSED', '2026-02-09 11:14:25', '2026-02-09 11:44:25', '2026-02-09 11:14:56', 'Serveur', 0),
(326, 1, 'b486e553bd9e82fb7e55e18d4f2e4c1abc9b27a8e97832cc95a9db3b4f99c0a0', 'CLOSED', '2026-02-09 11:14:56', '2026-02-09 11:44:56', '2026-02-09 11:15:27', 'Serveur', 0),
(327, 1, 'ef3bc56dd44855669699fddaf9cf67bb6dc336b368eab4fbd159ef2774ad6d3c', 'CLOSED', '2026-02-09 11:15:27', '2026-02-09 11:45:27', '2026-02-09 11:15:58', 'Serveur', 0),
(328, 1, 'd40726c7960234f6125a3380d9f953e92418d13dc085c294d9c13884111e28fa', 'CLOSED', '2026-02-09 11:15:58', '2026-02-09 11:45:58', '2026-02-09 11:16:29', 'Serveur', 0),
(329, 1, '06d34f9c779099429f99e21b60fa83cd94639a22536a6cb4c7c119c3223655a2', 'CLOSED', '2026-02-09 11:16:29', '2026-02-09 11:46:29', '2026-02-09 11:17:00', 'Serveur', 0),
(330, 1, '57a9da543284ac848c78ffaa2a51a5592f070f8d3fbfa485e0838fcba3ee90fb', 'CLOSED', '2026-02-09 11:17:00', '2026-02-09 11:47:00', '2026-02-09 11:17:31', 'Serveur', 0),
(331, 1, '7879af24040e5ed7952188f95d320b03940af0e63a4c3d7875cec45bb8843e4d', 'CLOSED', '2026-02-09 11:17:31', '2026-02-09 11:47:31', '2026-02-09 11:18:02', 'Serveur', 0),
(332, 1, '5bec5ef29ded8f8e3c92dd1e4a0a1141142c8b846e501cae3f4b083b97e1709d', 'CLOSED', '2026-02-09 11:18:02', '2026-02-09 11:48:02', '2026-02-09 11:18:33', 'Serveur', 0),
(333, 1, '4707a562239fba0aebdfd856a24ac46b96b29a2ceb76133982b4ab90b1d59ce7', 'CLOSED', '2026-02-09 11:18:33', '2026-02-09 11:48:33', '2026-02-09 11:19:04', 'Serveur', 0),
(334, 1, '06422f488cd29585b7b3b3d74fe200d3b2fc03fc78dc006e55cf4b78dbd84953', 'CLOSED', '2026-02-09 11:19:04', '2026-02-09 11:49:04', '2026-02-09 11:19:35', 'Serveur', 0),
(335, 1, 'fea525dca69b6bd9d4a46450deaad104a06fb81196dbef307adf30e4e2e4d9b3', 'CLOSED', '2026-02-09 11:19:35', '2026-02-09 11:49:35', '2026-02-09 11:20:06', 'Serveur', 0),
(336, 1, 'f9082517e20a82298a1092c41f7495a20f62438419d0673ab68013d3301b883b', 'CLOSED', '2026-02-09 11:20:06', '2026-02-09 11:50:06', '2026-02-09 11:20:37', 'Serveur', 0),
(337, 1, 'e28d45e19e85c830f7788da8e86d797c004179c1797e8a5f1522783e458b3319', 'CLOSED', '2026-02-09 11:20:37', '2026-02-09 11:50:37', '2026-02-09 11:21:08', 'Serveur', 0),
(338, 1, '2dac174ebb945acc398ca5119bf592e2c4ef9309b59e007e7a470e2f3a5139a3', 'CLOSED', '2026-02-09 11:21:08', '2026-02-09 11:51:08', '2026-02-09 11:21:39', 'Serveur', 0),
(339, 1, '660834ecc9671122129b40c75dbb5610ab92a4b6f4b5df8f6a1b4995465e8709', 'CLOSED', '2026-02-09 11:21:39', '2026-02-09 11:51:39', '2026-02-09 11:22:10', 'Serveur', 0),
(340, 1, '88a6835eb324c6133dfddde43d81d171695906d2fe838b7c153b3a9c7cb8e5fc', 'CLOSED', '2026-02-09 11:22:10', '2026-02-09 11:52:10', '2026-02-09 11:22:41', 'Serveur', 0),
(341, 1, '2d6b2820a15f69f58cd84038f95e891bcff77da9192e08de61f2dc20a384a724', 'CLOSED', '2026-02-09 11:22:41', '2026-02-09 11:52:41', '2026-02-09 11:23:12', 'Serveur', 0),
(342, 1, 'c20b36ba16367625726c43b85296353af2f708881c15adda0ff340901a1e063e', 'CLOSED', '2026-02-09 11:23:12', '2026-02-09 11:53:12', '2026-02-09 11:23:43', 'Serveur', 0),
(343, 1, '3e62a6786f87377a744f195e4b5ddf16f463f3d49b07631b9edb526addafd662', 'CLOSED', '2026-02-09 11:23:43', '2026-02-09 11:53:43', '2026-02-09 11:24:14', 'Serveur', 0),
(344, 1, '5fb7a113d03ab02b832422ef33907e43f477b4fb77ebd8ff96b765113fd4a407', 'CLOSED', '2026-02-09 11:24:14', '2026-02-09 11:54:14', '2026-02-09 11:24:45', 'Serveur', 0),
(345, 1, '1df168c6658fd8310ed9a2612ba4b5142a06d9375be66b9383dcf52206fc4100', 'CLOSED', '2026-02-09 11:24:45', '2026-02-09 11:54:45', '2026-02-09 11:25:16', 'Serveur', 0),
(346, 1, 'b31b6d5c7f3a1363966aba7ebccb588000c58245f148884e8f1b092f44aadb4c', 'CLOSED', '2026-02-09 11:25:16', '2026-02-09 11:55:16', '2026-02-09 11:25:47', 'Serveur', 0),
(347, 1, 'c2543e5c0e5077b3615ef16e8fbb8d689c762ef540c0fc9185ab763d98147808', 'CLOSED', '2026-02-09 11:25:47', '2026-02-09 11:55:47', '2026-02-09 11:26:18', 'Serveur', 0),
(348, 1, 'b7cccd656c3959719bde3a01d3968ba9341c432776b3eea25dee01339b995bc9', 'CLOSED', '2026-02-09 11:26:18', '2026-02-09 11:56:18', '2026-02-09 11:26:49', 'Serveur', 0),
(349, 1, '4a2fb3a73c6b9c98ad9f98bd39a201b688db342b92468e11159ee0359c34ce4c', 'CLOSED', '2026-02-09 11:26:49', '2026-02-09 11:56:49', '2026-02-09 11:27:20', 'Serveur', 0),
(350, 1, '646e9d48a8ccf48febfc7517465e30e66ad32c8073252405c1323996f4a40739', 'CLOSED', '2026-02-09 11:27:20', '2026-02-09 11:57:20', '2026-02-09 11:27:51', 'Serveur', 0),
(351, 1, '7cd476c964ffb685e81fa7f611e49ef7688fff030465717ba056a1d1b0c3dea5', 'CLOSED', '2026-02-09 11:27:51', '2026-02-09 11:57:51', '2026-02-09 11:28:22', 'Serveur', 0),
(352, 1, 'd1d54a5ac833d83de33e6b6de8c5d723ee5c140d2a0ba4ebfa27052489d3efdf', 'CLOSED', '2026-02-09 11:28:22', '2026-02-09 11:58:22', '2026-02-09 11:28:53', 'Serveur', 0),
(353, 1, 'd385ed6a01ce42193a021b1425caa28b38ea2eeda92b2741eb641ce72d1899d5', 'CLOSED', '2026-02-09 11:28:53', '2026-02-09 11:58:53', '2026-02-09 11:29:24', 'Serveur', 0),
(354, 1, '59920d909446da80721f82eb42ea35e3341fa82e17a1d6ff785b6c7f23c11aa1', 'CLOSED', '2026-02-09 11:29:24', '2026-02-09 11:59:24', '2026-02-09 11:29:55', 'Serveur', 0),
(355, 1, '0236f4c39bc9ce0a21a1a5a4110565f1c090dcc6d5fdb25596fac8e6497e3292', 'CLOSED', '2026-02-09 11:29:55', '2026-02-09 11:59:55', '2026-02-09 11:30:26', 'Serveur', 0),
(356, 1, '2441befb86012abd4a090c4be146925e4d6511c02a6a4d6e74476f1450dc0ca4', 'CLOSED', '2026-02-09 11:30:26', '2026-02-09 12:00:26', '2026-02-09 11:30:57', 'Serveur', 0),
(357, 1, '44e33f915a6be1f4fd5b927613f7b30e9be9ef8071d4eb104590b9dccf0952a9', 'CLOSED', '2026-02-09 11:30:57', '2026-02-09 12:00:57', '2026-02-09 11:31:28', 'Serveur', 0),
(358, 1, 'b5663c03165292d3bc827a4ce6701c82698b60ae83ba5734da16a0a17e4f8139', 'CLOSED', '2026-02-09 11:31:28', '2026-02-09 12:01:28', '2026-02-09 11:31:59', 'Serveur', 0),
(359, 1, 'eb6a18608165f1c522a0fae62132af5f7df1bcd0b93aeff53ea9b8db3ab6f90c', 'CLOSED', '2026-02-09 11:31:59', '2026-02-09 12:01:59', '2026-02-09 11:32:30', 'Serveur', 0),
(360, 1, 'ff3f346fcc41634d41105c19309dab8489f46f7666ffa5288b7c1e21c8c156ab', 'CLOSED', '2026-02-09 11:32:30', '2026-02-09 12:02:30', '2026-02-09 11:33:01', 'Serveur', 0),
(361, 1, 'db2cd489f064a0532677f398b27c2b9549d97f17b1ffa3a018ef870ce3b5be18', 'CLOSED', '2026-02-09 11:33:01', '2026-02-09 12:03:01', '2026-02-09 11:33:32', 'Serveur', 0),
(362, 1, 'd1a029ddb64e494b9b85517ceb97f80cf4b1cb6f6d62ffdc5a513a7066e0072a', 'CLOSED', '2026-02-09 11:33:32', '2026-02-09 12:03:32', '2026-02-09 11:34:03', 'Serveur', 0),
(363, 1, 'db0e2a67fc5bbd6d68145d6aa5fffee50539f51e68fae6293cb37a3fdd348d5f', 'CLOSED', '2026-02-09 11:34:03', '2026-02-09 12:04:03', '2026-02-09 11:37:34', 'Serveur', 0),
(364, 1, 'd5bc37a76b90fa773be00306b4e9fbeabf8a981465d7c102b3fa9e74c94daace', 'CLOSED', '2026-02-09 11:37:34', '2026-02-09 12:07:34', '2026-02-09 11:37:37', 'Serveur', 0),
(365, 1, '71da10f55265ee25c3898059f80f8779dd251337eadea652fbc0ca16a9555cd8', 'CLOSED', '2026-02-09 14:34:37', '2026-02-09 15:04:37', '2026-02-09 14:35:08', 'Serveur', 1),
(366, 1, '0ae3fe80f0027eb142e14f4b6d2b77d9ef8e14ec8553f53fb084de804565a28d', 'CLOSED', '2026-02-09 14:35:08', '2026-02-09 15:05:08', '2026-02-09 14:35:38', 'Serveur', 0),
(367, 1, 'f06b959d9fe3a5adb35c59e59182087ecde2527c3dfbec96a29ee507195d7702', 'CLOSED', '2026-02-09 14:35:38', '2026-02-09 15:05:38', '2026-02-09 14:36:08', 'Serveur', 0),
(368, 1, '098e4416a0a1d026b513da5464287b0ba7004d34d805737f752a2d92d8e00f21', 'CLOSED', '2026-02-09 14:36:08', '2026-02-09 15:06:08', '2026-02-09 14:36:38', 'Serveur', 0),
(369, 1, 'a8efa1991cd1b2471ca54def11438ed6fb288635a8aca8c0f08669e770084eb8', 'CLOSED', '2026-02-09 14:36:38', '2026-02-09 15:06:38', '2026-02-09 14:37:08', 'Serveur', 0),
(370, 1, '7fe5b6d89f5bf24fd515be2557f320c8cad9113c5f5d3ac24ad7e4ea1bdb4726', 'CLOSED', '2026-02-09 14:37:08', '2026-02-09 15:07:08', '2026-02-09 14:37:38', 'Serveur', 0),
(371, 1, '6aa1de2bc5e73f94e21296f1802dafabb9d1ce740e10ba7e357626aff03fadb0', 'CLOSED', '2026-02-09 14:37:38', '2026-02-09 15:07:38', '2026-02-09 14:38:08', 'Serveur', 0),
(372, 1, 'b5557c79500c91606fd32ecbc0205b827b1e92861c4d7a899e9ad1bc8869f994', 'CLOSED', '2026-02-09 14:38:08', '2026-02-09 15:08:08', '2026-02-09 14:38:38', 'Serveur', 0),
(373, 1, 'bf0bd0909fbe62ffdcc243c5ff2a66c411c89b620285ce9709eba8d42bb2a1c4', 'CLOSED', '2026-02-09 14:38:38', '2026-02-09 15:08:38', '2026-02-09 14:39:09', 'Serveur', 0),
(374, 1, '68b1df8577907984235152eaf7e4ed54452360031bf003e00bc82d66a2f5abba', 'CLOSED', '2026-02-09 14:39:09', '2026-02-09 15:09:09', '2026-02-09 14:39:40', 'Serveur', 0),
(375, 1, '9f71da8964e291c73f4db1b4bc95f89ce07d1617eb6bd73704d5325fff9ead71', 'CLOSED', '2026-02-09 14:39:40', '2026-02-09 15:09:40', '2026-02-09 14:40:11', 'Serveur', 0),
(376, 1, '0d3642dc4eebce4bb8a36eaea08ab4ce29acfcf49da0632b3b6644a8c67cede5', 'CLOSED', '2026-02-09 14:40:11', '2026-02-09 15:10:11', '2026-02-09 14:40:42', 'Serveur', 0),
(377, 1, 'aa2bbaa295ba4632054439c8e33e24b35de317bce6c499dae6d23768dd1a7e3b', 'CLOSED', '2026-02-09 14:40:42', '2026-02-09 15:10:42', '2026-02-09 14:41:13', 'Serveur', 0),
(378, 1, 'ae36c52701d7fd6a08d5571f915eb39ce9222d30bdc710c585749318f7864964', 'CLOSED', '2026-02-09 14:41:13', '2026-02-09 15:11:13', '2026-02-09 14:41:44', 'Serveur', 0),
(379, 1, 'a988161917526f87062af5e123e47cdd8e67184c23e963616263029bea1e1abb', 'CLOSED', '2026-02-09 14:41:44', '2026-02-09 15:11:44', '2026-02-09 14:42:15', 'Serveur', 0),
(380, 1, 'c878f6c12b4e760124243e6bf91f8cc5ee20284837a5ea4622f993c7c5b09241', 'CLOSED', '2026-02-09 14:42:15', '2026-02-09 15:12:15', '2026-02-09 14:42:46', 'Serveur', 0),
(381, 1, 'bfd731bdd6ee97d229b2f34bb949d9a0d0479d47853206ebcd2235bfb9007527', 'CLOSED', '2026-02-09 14:42:46', '2026-02-09 15:12:46', '2026-02-09 14:43:17', 'Serveur', 0),
(382, 1, '2f62c1e119b81c386c78c6c0fdcfd11b97a7615006b3ea894b44bd8ff9309a46', 'CLOSED', '2026-02-09 14:43:17', '2026-02-09 15:13:17', '2026-02-09 14:43:48', 'Serveur', 0),
(383, 1, '34ae1ddf3937951cd7f48e87514630abf173672408e7323aca9cb4f3ba4f9a79', 'CLOSED', '2026-02-09 14:43:48', '2026-02-09 15:13:48', '2026-02-09 14:44:19', 'Serveur', 0),
(384, 1, '06ad3d0a4315d3702c3546d48b3b5da7fd836d04d21a363e0e8444e6a05e3d84', 'CLOSED', '2026-02-09 14:44:19', '2026-02-09 15:14:19', '2026-02-09 14:44:50', 'Serveur', 0),
(385, 1, 'e7c74936678b65910f024fdf63548533eba202f4ea90c511a03d5857bc3e9b81', 'CLOSED', '2026-02-09 14:44:50', '2026-02-09 15:14:50', '2026-02-09 14:45:21', 'Serveur', 0),
(386, 1, '1f8a1ba93d63dc5d88193ea2eaf224ac1878eaee5c43ca6e40dedeb014dd2b1d', 'CLOSED', '2026-02-09 14:45:21', '2026-02-09 15:15:21', '2026-02-09 14:45:52', 'Serveur', 0),
(387, 1, '37ea469717284cb5cea62be8687922f076c6317b813ac38bbab2edd034c5a414', 'CLOSED', '2026-02-09 14:45:52', '2026-02-09 15:15:52', '2026-02-09 14:46:23', 'Serveur', 0),
(388, 1, '212493f803b9099de0489b1e6dcf1a410f4ca037f34010fbfddcaad96ce26873', 'CLOSED', '2026-02-09 14:46:23', '2026-02-09 15:16:23', '2026-02-09 14:46:54', 'Serveur', 0),
(389, 1, '3e60750b7f1578b28e393ca1505230c1262e8499b6886071226d6c87446950bb', 'CLOSED', '2026-02-09 14:46:54', '2026-02-09 15:16:54', '2026-02-09 14:47:25', 'Serveur', 0),
(390, 1, '3ef1a071233b73d54542e6c7a3a795a3813d422b8afa4080f55f05017099f59d', 'CLOSED', '2026-02-09 14:47:25', '2026-02-09 15:17:25', '2026-02-09 14:47:55', 'Serveur', 0),
(391, 1, '08d587202c217de11bc01e3000bd8aac11a1af88a2912ca6a3dae4ea5680d4ef', 'CLOSED', '2026-02-09 14:47:55', '2026-02-09 15:17:55', '2026-02-09 14:48:26', 'Serveur', 0),
(392, 1, 'feafe64be52671e2c9d517779e5ba8980b8934f4a16b587941e0eda826aa2145', 'CLOSED', '2026-02-09 14:48:26', '2026-02-09 15:18:26', '2026-02-09 14:48:57', 'Serveur', 0),
(393, 1, '3e6293431dff2b45f31e777b5f35575dbf03fe670cbe3d5f22bab50739801eab', 'CLOSED', '2026-02-09 14:48:57', '2026-02-09 15:18:57', '2026-02-09 14:49:28', 'Serveur', 0),
(394, 1, '1178b577fb0c547b8de8ea75c256ab2ec89ac1eba0a27e27888b54f49176fe03', 'CLOSED', '2026-02-09 14:49:28', '2026-02-09 15:19:28', '2026-02-09 14:49:59', 'Serveur', 0),
(395, 1, '05075637037d8c35dc03da87029d28deda8d359fab7c8f5dc089175e243b136b', 'CLOSED', '2026-02-09 14:49:59', '2026-02-09 15:19:59', '2026-02-09 14:50:30', 'Serveur', 0),
(396, 1, '712021b5c9ccc3227f281d7f0e69733b65457bb9114cec076b5a46ef0bfdc450', 'CLOSED', '2026-02-09 14:50:30', '2026-02-09 15:20:30', '2026-02-09 14:51:01', 'Serveur', 0),
(397, 1, 'fe01d77d129f2d96ff64abd932c1aad46b9a7a3fe7c45c3cc5ae475e20c2bcc7', 'CLOSED', '2026-02-09 14:51:01', '2026-02-09 15:21:01', '2026-02-09 14:51:32', 'Serveur', 0),
(398, 1, '8d3724356d78f5a802d4ec4b7a2eefd3c3cb966a4783d5a8e640ee4f8ea193a2', 'CLOSED', '2026-02-09 14:51:32', '2026-02-09 15:21:32', '2026-02-09 14:52:03', 'Serveur', 0),
(399, 1, '9f26d3d05b14a05bedbf383afeb31b1051103fbcb08170326546b63396ad7a59', 'CLOSED', '2026-02-09 14:52:03', '2026-02-09 15:22:03', '2026-02-09 14:52:34', 'Serveur', 0),
(400, 1, 'c60767e8e19b452cebe603172106aa556e5120e4bba1021b97ed95155246ddd1', 'CLOSED', '2026-02-09 14:52:34', '2026-02-09 15:22:34', '2026-02-09 14:53:05', 'Serveur', 0),
(401, 1, '79ef87687d613267bb2b3b80bbaab396c4353d162cb0db607eb27aa84172f912', 'CLOSED', '2026-02-09 14:53:05', '2026-02-09 15:23:05', '2026-02-09 14:53:36', 'Serveur', 0),
(402, 1, '9e723701241852514582d5ada361ff7be100ffc298768d4a1cc0d3a900018780', 'CLOSED', '2026-02-09 14:53:36', '2026-02-09 15:23:36', '2026-02-09 14:54:07', 'Serveur', 0),
(403, 1, 'e320e0c2377561ed3265849bc1d6c8b4e6e9fdbf192ed6524497293c2a711c9e', 'CLOSED', '2026-02-09 14:54:07', '2026-02-09 15:24:07', '2026-02-09 14:54:38', 'Serveur', 0),
(404, 1, '30f6cfabac123f5b51fd04f92f6dc099cdea53ed74a2653d7ce5bce5f60c833e', 'CLOSED', '2026-02-09 14:54:38', '2026-02-09 15:24:38', '2026-02-09 14:55:09', 'Serveur', 0),
(405, 1, 'e6805f3da018bb832fe7949888c6229076893f1c1ac5e448f20959c4be225a64', 'CLOSED', '2026-02-09 14:55:09', '2026-02-09 15:25:09', '2026-02-09 14:55:40', 'Serveur', 0),
(406, 1, '35ed708c9c54906774a72714916acde00a8485f7931a915757069929d9dfbf1f', 'CLOSED', '2026-02-09 14:55:40', '2026-02-09 15:25:40', '2026-02-09 14:56:11', 'Serveur', 0),
(407, 1, 'cf9153c871d46a955e3d7abe50274db0e6175545cd929a04b0b2205d296f87b1', 'CLOSED', '2026-02-09 14:56:11', '2026-02-09 15:26:11', '2026-02-09 14:56:42', 'Serveur', 0),
(408, 1, 'f59f8cc3ccd583a2132dbbb90e1064362e44a03b94ec068c7a60a147b7bb5bfe', 'CLOSED', '2026-02-09 14:56:42', '2026-02-09 15:26:42', '2026-02-09 14:57:13', 'Serveur', 0),
(409, 1, '9a9087eb9bdde3500a076a05799d22433980f310fcb4b4a6bb7f8f28249177af', 'CLOSED', '2026-02-09 14:57:13', '2026-02-09 15:27:13', '2026-02-09 14:57:44', 'Serveur', 0),
(410, 1, '12360011110809718a1a67c72aa8ec9c7fcb7bba3dc09caa43a0710335a41561', 'CLOSED', '2026-02-09 14:57:44', '2026-02-09 15:27:44', '2026-02-09 14:58:15', 'Serveur', 0),
(411, 1, '2800f49dade0bece9ce4092b3d7912522130e3fde2b2e2e9825211a390960936', 'CLOSED', '2026-02-09 14:58:15', '2026-02-09 15:28:15', '2026-02-09 14:58:46', 'Serveur', 0),
(412, 1, '273365c1c5f19f75f7e3c357c9d5133368de72ff27ee52d0302fcf0fb50fe301', 'CLOSED', '2026-02-09 14:58:46', '2026-02-09 15:28:46', '2026-02-09 14:59:17', 'Serveur', 0),
(413, 1, 'eeede9f2fc8a1d6e9e59df8cefb77e781d59bfebad35dd20bca17fdcdac9aa64', 'CLOSED', '2026-02-09 14:59:17', '2026-02-09 15:29:17', '2026-02-09 14:59:48', 'Serveur', 0),
(414, 1, 'e61793fa3e7cd6c0e7ce03fa00b41f431b65d31c3feedcbffe5b57492cd8924a', 'CLOSED', '2026-02-09 14:59:48', '2026-02-09 15:29:48', '2026-02-09 15:00:19', 'Serveur', 0),
(415, 1, 'b1ca813a9fa32ee038ca8bfc1303b8311027b81d9f6efd73bbecd8d10db0c194', 'CLOSED', '2026-02-09 15:00:19', '2026-02-09 15:30:19', '2026-02-09 15:00:50', 'Serveur', 0),
(416, 1, '0edab775988d4be4589d71e69e58c5b8bd67e901c251c33b1c71b74615be5133', 'CLOSED', '2026-02-09 15:00:50', '2026-02-09 15:30:50', '2026-02-09 15:01:21', 'Serveur', 0),
(417, 1, '3808a3419c8fbe06011cf7d27c585eff36f4d1e05286ccff57512e6edefa42ee', 'CLOSED', '2026-02-09 15:01:21', '2026-02-09 15:31:21', '2026-02-09 15:01:52', 'Serveur', 0),
(418, 1, 'c61453794d02e43d32a7f72977c6fef68d52c5269b43ddc1c3913680661e73f7', 'CLOSED', '2026-02-09 15:01:52', '2026-02-09 15:31:52', '2026-02-09 15:02:23', 'Serveur', 0),
(419, 1, '3c8d088b4ab9643687fd596c3f958c5b8c7ebb2a1fa77267eb5525ec85d1c78c', 'CLOSED', '2026-02-09 15:02:23', '2026-02-09 15:32:23', '2026-02-09 15:02:54', 'Serveur', 0),
(420, 1, '271dfda09ea7f0b6f351541bdf40e63db27c657fec480fb19772f65576bae129', 'CLOSED', '2026-02-09 15:02:54', '2026-02-09 15:32:54', '2026-02-09 15:03:25', 'Serveur', 0),
(421, 1, '93c0afc7098a612a2a88dd84249db1969208b10ac1ba4aca157ebaebc073cc8f', 'CLOSED', '2026-02-09 15:03:25', '2026-02-09 15:33:25', '2026-02-09 15:03:56', 'Serveur', 0),
(422, 1, 'e959e0e3c05b4061583c1d8b6e2dc9f0b206351804d19996e9b0a7e4940512c2', 'CLOSED', '2026-02-09 15:03:56', '2026-02-09 15:33:56', '2026-02-09 15:04:27', 'Serveur', 0),
(423, 1, '4f75b5409aeb1c3b00ae29dcbc84c02b4f263dcaf3ecf667559c75a5daeec02f', 'CLOSED', '2026-02-09 15:04:27', '2026-02-09 15:34:27', '2026-02-09 15:04:58', 'Serveur', 0),
(424, 1, 'f662c5271e212f4fbf79f57d8f02e947d315ea783786ddc60422303fbe9d1144', 'CLOSED', '2026-02-09 15:04:58', '2026-02-09 15:34:58', '2026-02-09 15:05:29', 'Serveur', 0),
(425, 1, '5ac820fc1232f69e04b56969e9431ee47747d9ab0f432524991e5e61618f418e', 'CLOSED', '2026-02-09 15:05:29', '2026-02-09 15:35:29', '2026-02-09 15:06:00', 'Serveur', 0),
(426, 1, '771526514699d4af27df90f11cc39e2c14e6aea389e2c394e84df2597f122778', 'CLOSED', '2026-02-09 15:06:00', '2026-02-09 15:36:00', '2026-02-09 15:06:31', 'Serveur', 0),
(427, 1, 'ab48c5e09965bffc37f8275d83d7cae5a48ec8f3079b610c8ee6dd0cda4a3d90', 'CLOSED', '2026-02-09 15:06:31', '2026-02-09 15:36:31', '2026-02-09 15:07:02', 'Serveur', 0),
(428, 1, 'c2f9cf839f5017fff8b2af447f543c5a9df773e7e835fd8559231f841f21d1a9', 'CLOSED', '2026-02-09 15:07:02', '2026-02-09 15:37:02', '2026-02-09 15:07:33', 'Serveur', 0),
(429, 1, '51cf2495a416b4b8d34b66d18854e8e8c8dd23dc7f125c1ea7549f956b5bb132', 'CLOSED', '2026-02-09 15:07:33', '2026-02-09 15:37:33', '2026-02-09 15:08:04', 'Serveur', 0),
(430, 1, 'f6326d61508b34de074db5eeee3569477dc908acd61ccb99c1c260605c6fed81', 'CLOSED', '2026-02-09 15:08:04', '2026-02-09 15:38:04', '2026-02-09 15:08:35', 'Serveur', 0),
(431, 1, '05eb5d482796a87ae16029dba4a9bdcf431867a3e5554e7f34ca416f7779ea16', 'CLOSED', '2026-02-09 15:08:35', '2026-02-09 15:38:35', '2026-02-09 15:09:06', 'Serveur', 0),
(432, 1, '9f1707574f60dc73a2e9bc333e21abad2ca65203f9320a88655a4adcb776a95f', 'CLOSED', '2026-02-09 15:09:06', '2026-02-09 15:39:06', '2026-02-09 15:09:37', 'Serveur', 0),
(433, 1, '86ae6f151a81d154b10c78dfafb5a1f8d69ffff88c2093bb4a8fe0da9a6461e6', 'CLOSED', '2026-02-09 15:09:37', '2026-02-09 15:39:37', '2026-02-09 15:10:08', 'Serveur', 0),
(434, 1, '29b05ce81294c71affebf1a1717ffe4c83601a522905d91a5dbb93d80747a8f4', 'CLOSED', '2026-02-09 15:10:08', '2026-02-09 15:40:08', '2026-02-09 15:10:39', 'Serveur', 0),
(435, 1, '036982be7a5518fd9253b6b605cd94428ba15bd3d29f6f4ec36fba7d6049bec5', 'CLOSED', '2026-02-09 15:10:39', '2026-02-09 15:40:39', '2026-02-09 15:11:10', 'Serveur', 0),
(436, 1, '4dfcc7df38628eec5c48d91f281cbaaa8de6613ea63aa8459e74eb1e0a7fb9d1', 'CLOSED', '2026-02-09 15:11:10', '2026-02-09 15:41:10', '2026-02-09 15:11:41', 'Serveur', 0),
(437, 1, '46ae03ed27b8a36a675724dfee11130504fafa8d4376b4730490ca894b117518', 'CLOSED', '2026-02-09 15:11:41', '2026-02-09 15:41:41', '2026-02-09 15:12:12', 'Serveur', 0),
(438, 1, '64695f37715cf970c5db35bcc07ca1a3d5e3b0651f2f116692d56259cf7e9f24', 'CLOSED', '2026-02-09 15:12:12', '2026-02-09 15:42:12', '2026-02-09 15:12:43', 'Serveur', 0),
(439, 1, 'bbe29b96f597df51d382e8845a9b8784b0732799966e54ec40a67dccbad70af4', 'CLOSED', '2026-02-09 15:12:43', '2026-02-09 15:42:43', '2026-02-09 15:13:14', 'Serveur', 0),
(440, 1, 'ded4201a330803ca9976140a274b740c44e72eb18db7f051882e4fa3b6b027e8', 'CLOSED', '2026-02-09 15:13:14', '2026-02-09 15:43:14', '2026-02-09 15:13:45', 'Serveur', 0),
(441, 1, 'bdeeae9b4125b114b0b385319055167a84ccc4db5268192affca440a028b0b7c', 'CLOSED', '2026-02-09 15:13:45', '2026-02-09 15:43:45', '2026-02-09 15:14:16', 'Serveur', 0),
(442, 1, '90bef9d8aff6e7089fb99cf362abf910bf91346d1b42858718b0793f8e6025ed', 'CLOSED', '2026-02-09 15:14:16', '2026-02-09 15:44:16', '2026-02-09 15:14:47', 'Serveur', 0),
(443, 1, '50e7d6a1e62969ce771db12bc491b5583d30ee04e7dd2a5b518ef0501a5c2240', 'CLOSED', '2026-02-09 15:14:47', '2026-02-09 15:44:47', '2026-02-09 15:15:18', 'Serveur', 0),
(444, 1, '5c24e2005e81a4130efac612479e750a6cba800ca8006fd4dc1796c4aa16db72', 'CLOSED', '2026-02-09 15:15:18', '2026-02-09 15:45:18', '2026-02-09 15:15:49', 'Serveur', 0),
(445, 1, '07f8c28e6bd7962eff7275a71c13d7498806de0828458296edefc4b8e32f62f8', 'CLOSED', '2026-02-09 15:15:49', '2026-02-09 15:45:49', '2026-02-09 15:16:20', 'Serveur', 0),
(446, 1, '1d5844b21f8e5c4c241fcbb0daa65e99388b973d50bb87de4162e49c417579b4', 'CLOSED', '2026-02-09 15:16:20', '2026-02-09 15:46:20', '2026-02-09 15:16:50', 'Serveur', 0),
(447, 1, '2f1bafe4c8e4293a7525010767bdb2c9ab18788fd85570d1f0d0b0caa6380e28', 'CLOSED', '2026-02-09 15:16:50', '2026-02-09 15:46:50', '2026-02-11 10:06:06', 'Serveur', 0),
(448, 1, '8bff5c9cc8723445071439f75f19008cf2a03871335261a8ef6e08a6e5290261', 'CLOSED', '2026-02-11 10:06:08', '2026-02-11 10:36:08', '2026-02-11 10:06:39', 'Serveur', 0),
(449, 1, 'd3087ba84b76c496578de7b2b8061e67001d07c1ea3a838e92e1eab2507b84f6', 'CLOSED', '2026-02-11 10:06:39', '2026-02-11 10:36:39', '2026-02-11 10:07:10', 'Serveur', 0),
(450, 1, '3e90cc8dc620d6e3ba63a98dd525d7208775bcc610a1118f4d05c9b66ed280c9', 'CLOSED', '2026-02-11 10:07:10', '2026-02-11 10:37:10', '2026-02-11 10:07:41', 'Serveur', 0),
(451, 1, '4cf64fe6712e908103e6c8b95669cde0894c4181d72f2cd60b8fbc36b1816535', 'CLOSED', '2026-02-11 10:07:41', '2026-02-11 10:37:41', '2026-02-11 10:08:12', 'Serveur', 0),
(452, 1, 'c49a0bb1145fb4f40e6830c7cefd94cbffd0bde5e55c82d290e1817ec3a9ec2b', 'CLOSED', '2026-02-11 10:08:12', '2026-02-11 10:38:12', '2026-02-11 10:08:43', 'Serveur', 0),
(453, 1, 'b504f3dfd1ecadb532ae55ee000ed910cb26ec7375e3f8eb444753d2601ee9df', 'CLOSED', '2026-02-11 10:08:43', '2026-02-11 10:38:43', '2026-02-11 10:09:14', 'Serveur', 0),
(454, 1, '9c0c1a80ae39d0cb221c487c113ec83b93c25d7456b567c7dd04a6e6c5f9c68d', 'CLOSED', '2026-02-11 10:09:14', '2026-02-11 10:39:14', '2026-02-12 10:04:45', 'Serveur', 0),
(455, 1, '08b638162b16920b3b3c03bfcfdf9840a12967ee1484f51e19658645cae64f73', 'CLOSED', '2026-02-12 10:04:48', '2026-02-12 09:19:48', '2026-02-12 10:04:54', 'Serveur', 0),
(456, 1, '1780e078dada08fa91e2c216f50d975d21c0cba3788814cdfdc96b6cde0b94e0', 'CLOSED', '2026-02-12 10:04:54', '2026-02-12 10:49:54', '2026-02-12 10:05:00', 'Serveur', 0),
(457, 1, '02988c9bbe911ef15273d9be9e3899d9a7d021a3f9eacf3e83398df8f9dd093a', 'CLOSED', '2026-02-12 10:05:03', '2026-02-12 10:05:03', '2026-02-12 10:05:07', 'Serveur', 0),
(458, 1, 'ebea94f8e45e146f153e355b77dc9d8619ba71c388d7ff7d0e78fd90910b437c', 'CLOSED', '2026-02-12 10:05:07', '2026-02-12 10:35:07', '2026-02-12 10:05:38', 'Serveur', 0),
(459, 1, '41876f808da7225300ad1fff5831c897b485685ddd11ed09a9600bee47327926', 'CLOSED', '2026-02-12 10:05:38', '2026-02-12 10:35:38', '2026-02-12 10:06:09', 'Serveur', 0),
(460, 1, '8874a8d64516f6912440457934e225f9d9bace82da41ce68ebf5f0a36f711a9d', 'CLOSED', '2026-02-12 10:06:09', '2026-02-12 10:36:09', '2026-02-12 10:06:40', 'Serveur', 0),
(461, 1, '5558080171828d1710038798ccb9ece2128bcf78ea934aa4cd82e7c6cd027195', 'CLOSED', '2026-02-12 10:06:40', '2026-02-12 10:36:40', '2026-02-12 10:07:11', 'Serveur', 0),
(462, 1, 'b19bcc693bd6e0e94fc82d698157fb7203987ced48ea87f71546fd194123df9f', 'CLOSED', '2026-02-12 10:07:11', '2026-02-12 10:37:11', '2026-02-12 10:09:07', 'Serveur', 0),
(463, 1, 'fa5a4ffc9c558fd7704bfa0539fd0e8f944300022ff5bcf262dc5ace3418db68', 'CLOSED', '2026-02-12 10:09:07', '2026-02-12 10:39:07', '2026-02-12 10:09:38', 'Serveur', 0),
(464, 1, '75bcb10658d1451cb4ec5b31c20590c2cf09d950907b5c866ba56f5f352be788', 'CLOSED', '2026-02-12 10:09:38', '2026-02-12 10:39:38', '2026-02-12 10:10:09', 'Serveur', 0),
(465, 1, 'e2a8f62efe19b8f22ac06d30ed5749e648d6ad6a112d287e5efe091035a1f2b6', 'CLOSED', '2026-02-12 10:10:09', '2026-02-12 10:40:09', '2026-02-12 10:10:40', 'Serveur', 0),
(466, 1, '7dca6d67bda2f1bd09ae447cf4f4e4e349d703fe671ab44f626eda1461d3cfec', 'CLOSED', '2026-02-12 10:10:40', '2026-02-12 10:40:40', '2026-02-12 10:11:11', 'Serveur', 0),
(467, 1, '65a52461c3cbe24f79b3d6644bbbfdf169d163e6efb783c27beb0070e0ae1e92', 'CLOSED', '2026-02-12 10:11:11', '2026-02-12 10:41:11', '2026-02-12 10:11:42', 'Serveur', 0),
(468, 1, 'f9547df526519da17a012a1a1d115bf01ed05cba71f4a2e94baa847424620b6e', 'CLOSED', '2026-02-12 10:11:42', '2026-02-12 10:41:42', '2026-02-12 10:12:13', 'Serveur', 0),
(469, 1, '3aef5031dab5d79934ee555f2e564bc3fe942e604b8e17c8efb2192349dff718', 'CLOSED', '2026-02-12 10:12:13', '2026-02-12 10:42:13', '2026-02-12 10:12:44', 'Serveur', 0),
(470, 1, '365460b686a487ff4e8d2653a8eee0096ae7f1982eaa96be63eaaf886f9dfb34', 'CLOSED', '2026-02-12 10:12:44', '2026-02-12 10:42:44', '2026-02-12 10:13:09', 'Serveur', 0),
(471, 1, 'd1db4ffda5ed5df2aa8340810d7bd04ff7b981f5aa6284584c38669e2481a74a', 'CLOSED', '2026-02-12 10:13:09', '2026-02-12 10:43:09', '2026-02-12 10:13:13', 'Serveur', 0),
(472, 1, 'ac69b8a2c8ddd3cfe918a1f72ee58a67a14f77adb2b78055d4223092ad06726f', 'CLOSED', '2026-02-12 10:13:16', '2026-02-12 11:28:16', '2026-02-12 10:14:14', 'Serveur', 0),
(473, 1, '097b330cfe714122c79e14e564ccbfe9cfe17e2ebb0d1f5663cac2800823cd10', 'CLOSED', '2026-02-12 10:14:16', '2026-02-12 11:59:16', '2026-02-12 10:15:36', 'Serveur', 0),
(474, 1, '8454ee240411e6f96ba824dadf1b076f1df4a99d4c2e5e43447e87d7af536ff6', 'CLOSED', '2026-02-12 10:15:43', '2026-02-12 09:30:43', '2026-02-12 10:15:46', 'Serveur', 0),
(475, 1, '8e01452b9cde1ee1f0f3e055a3c1317e94f13efac09c36d2e214a31d82f4d63b', 'CLOSED', '2026-02-12 10:15:46', '2026-02-12 11:15:46', '2026-02-12 10:16:16', 'Serveur', 0),
(476, 1, '3a7b068d49d6faf5106303b7553791a330784cc3458ead3dff54ee098ec4d22c', 'CLOSED', '2026-02-12 10:16:16', '2026-02-12 11:16:16', '2026-02-12 10:16:31', 'Serveur', 0),
(477, 1, '2b061459c9b0ee7b0c9d81818ee3a5325b3cfdd04a6da4b9b0aaa9f346548e30', 'CLOSED', '2026-02-12 10:17:56', '2026-02-12 14:17:56', '2026-02-12 10:18:10', 'Serveur', 0),
(478, 1, '0c6f9ab84015dbebfd52b43f5b7fd905b2f294bdcd9abbcd21939ab115a45414', 'CLOSED', '2026-02-12 10:20:03', '2026-02-12 10:20:03', '2026-02-12 10:20:33', 'Serveur', 0),
(479, 1, '45c1cb67367696ba4b40d055687f51645cb64c4abe533fdd7ed9372775dffd07', 'CLOSED', '2026-02-12 10:20:33', '2026-02-12 10:20:33', '2026-02-12 10:21:04', 'Serveur', 0),
(480, 1, '3919dc53f913e3b3e69d1bcce8718052d8edfc960d05ebbfa95c6b670641fd8a', 'CLOSED', '2026-02-12 10:21:04', '2026-02-12 10:21:04', '2026-02-12 10:21:35', 'Serveur', 0),
(481, 1, '68d2a7039ba1f0d5dec2617a5b8d7efbd47fb95181915383fde4f40e3f5b386d', 'CLOSED', '2026-02-12 10:21:35', '2026-02-12 10:21:35', '2026-02-12 10:22:06', 'Serveur', 0),
(482, 1, 'e22d4a222b35886507a341648525d120cb5e61512a882202e868a1976e53b8b9', 'CLOSED', '2026-02-12 10:22:06', '2026-02-12 10:22:06', '2026-02-12 10:22:37', 'Serveur', 0),
(483, 1, 'b8a3e6a28cc9b2ed53c84ee5d91e56c41f8368e6c8249c132d32e73f784f8f1e', 'CLOSED', '2026-02-12 10:22:37', '2026-02-12 10:22:37', '2026-02-12 10:23:08', 'Serveur', 0),
(484, 1, 'd1b919b9df7628a3be53a5681027afa725f723bd22866ec5425601758d0b11d2', 'CLOSED', '2026-02-12 10:23:08', '2026-02-12 10:23:08', '2026-02-12 10:23:39', 'Serveur', 0),
(485, 1, '2e39f7ceef3686c12daab74aa05278a5fd5dd9af8831f9ede49e1a1317f18b4d', 'CLOSED', '2026-02-12 10:23:39', '2026-02-12 10:23:39', '2026-02-12 10:24:10', 'Serveur', 0),
(486, 1, '6407a996498272a52310148ce6ea0fbebc046d30dd84d5193787def8c3c32a0b', 'CLOSED', '2026-02-12 10:24:10', '2026-02-12 10:24:10', '2026-02-12 10:24:39', 'Serveur', 0),
(487, 1, 'bf127582b8a8e5a9c221704aca9a7e7a787d7e42674ef05ad90a9120fb44dc01', 'CLOSED', '2026-02-12 10:24:39', '2026-02-12 11:24:39', '2026-02-12 10:24:41', 'Serveur', 0),
(488, 1, 'caee8754d817c99f0b6b0f7862d9bfb6c2266d79c4470259a7e60f1d1fae442c', 'CLOSED', '2026-02-12 10:24:43', '2026-02-12 12:09:43', '2026-02-12 10:24:54', 'Serveur', 0),
(489, 1, 'ca749c3b3cf5b88f38c8021e330076c61db818143f92ffa630e341888c6517f6', 'CLOSED', '2026-02-12 10:24:58', '2026-02-12 10:39:58', '2026-02-12 10:40:03', 'Serveur', 0),
(490, 10, '4ab9bc00ed2c806aa830efa66bca0a1025135de7154d9cec2e9fe13c2f939c3e', 'CLOSED', '2026-02-12 10:25:21', '2026-02-12 13:40:21', '2026-02-12 11:03:10', 'Serveur', 0),
(491, 2, 'a740949bda154a903d4d0482596922b42b3cf3e1bd873de30d5e12782157a985', 'CLOSED', '2026-02-12 10:26:31', '2026-02-12 13:26:31', '2026-02-13 09:59:24', 'Serveur5', 0),
(492, 1, '0eec2397f3bbbdeae5cda46f3b709419e3e85603255a6f3065b2f34521895d4f', 'CLOSED', '2026-02-12 10:49:47', '2026-02-12 13:49:47', '2026-02-12 10:49:54', 'Serveur', 0),
(493, 1, '65a1bf98b39b3fad57299b6fec00d4072b2106ab649d64fab155c028fdb382a3', 'CLOSED', '2026-02-13 10:01:57', '2026-02-13 10:16:57', '2026-02-13 10:04:07', 'Serveur', 0),
(494, 1, '972c2bbda8d11eaa02ca6ecccec7fd0d9336067cac091763d543657c0a7f06b5', 'CLOSED', '2026-02-13 10:04:07', '2026-02-13 10:19:07', '2026-02-13 10:19:13', 'Serveur', 0),
(495, 5, '5fa4abbbf1313d6dbc4b1bc7f5a70a297daca4fc95772af55e9a5830b83e5c53', 'CLOSED', '2026-02-13 10:04:25', '2026-02-13 12:04:25', '2026-02-13 11:02:22', 'Serveur', 1),
(496, 1, 'fad151078031d00a1dc8abd9576bd3b3efe78586b5f45e3b3da91404c74c4871', 'CLOSED', '2026-02-13 11:01:14', '2026-02-13 11:31:14', '2026-02-13 11:02:21', 'Serveur', 0),
(497, 10, '8c3a0e7d88ae2f109911b67cc3f66682ba401719bec3263efc0ed23d5b37ee1e', 'CLOSED', '2026-02-13 11:01:19', '2026-02-13 10:16:19', '2026-02-13 11:01:19', 'Serveur', 0),
(498, 10, 'a65eacb2e922399e0ebdf9102430d942785609bf4a43971297ca36ab749d4be8', 'CLOSED', '2026-02-13 11:01:22', '2026-02-13 10:31:22', '2026-02-13 11:01:22', 'Serveur', 0),
(499, 1, 'a62937b8492aa65db48227ea4ebb702537f7352fd1b3a6eaf62293588edaa752', 'CLOSED', '2026-02-13 11:02:24', '2026-02-13 12:32:24', '2026-02-13 11:06:34', 'Serveur', 0),
(500, 1, 'dbb1b13e6b425b1be91fb3cb083c91f3918f53a81c1d161da7d268c1c97c07ed', 'CLOSED', '2026-02-13 11:07:40', '2026-02-13 12:37:40', '2026-02-13 11:08:45', 'Serveur', 2),
(501, 1, '14a563d6b73a9b71d03fb9d289b574823dafb4d55d067f4f4ceef24b85361214', 'CLOSED', '2026-02-13 11:17:49', '2026-02-13 12:47:49', '2026-02-13 11:18:20', 'Serveur', 0),
(502, 1, '018f11c345ce055dd2d9d78b6d3a7fda5f400d3d1fd7ad65d5f6b47cdb7dc109', 'CLOSED', '2026-02-13 11:18:20', '2026-02-13 12:48:20', '2026-02-13 11:18:51', 'Serveur', 0),
(503, 1, 'f32d3d447fafa0e796d75336822569196b787c00fae165a28fcd54b346e15cd4', 'CLOSED', '2026-02-13 11:18:51', '2026-02-13 12:48:51', '2026-02-13 11:19:22', 'Serveur', 0),
(504, 1, '7c50523bbbde52a2df64a3578ea7939d3549720a6a0632d6cea48e647d1e86cf', 'CLOSED', '2026-02-13 11:19:22', '2026-02-13 13:04:22', '2026-02-13 11:20:20', 'Serveur', 1),
(505, 1, '0b10f082a11facfae0f49a03eb0eeb1522b77641e6b06e7c6db0077cb1b7e6e7', 'CLOSED', '2026-02-13 11:22:02', '2026-02-13 12:52:02', '2026-02-13 11:22:34', 'Serveur', 0),
(506, 1, 'b3dbf5c39df89077fde4c093abdc91dd3a2d07a459226c95747fcf7e436f563c', 'CLOSED', '2026-02-13 11:28:58', '2026-02-13 12:58:58', '2026-02-13 11:29:54', 'Serveur', 0),
(507, 1, '06838a5798ef4519284c20e462a0ce5dc38694d2b0d4186edc06d7bd3b673f17', 'CLOSED', '2026-02-13 11:30:47', '2026-02-13 13:00:47', '2026-02-13 11:30:59', 'Serveur', 0),
(508, 1, '01f060ecc54219ff79acd05094d581fd1624bd63b6eaf239e1c43eae8b87eeb4', 'OPEN', '2026-02-13 11:34:20', '2026-02-13 13:04:20', NULL, 'Serveur', 0),
(509, 10, 'f9e4894c0cfa2ca8e4903d0242b904c93992e7bf578a1e75df4f262566189da9', 'CLOSED', '2026-02-13 11:34:23', '2026-02-13 11:49:23', '2026-02-13 11:35:08', 'Serveur', 1);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `unread_notifications`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `unread_notifications`;
CREATE TABLE IF NOT EXISTS `unread_notifications` (
`id` int
,`notification_id` varchar(50)
,`type` varchar(50)
,`title` varchar(255)
,`message` text
,`data` json
,`priority` int
,`created_at` datetime
,`priority_label` varchar(7)
);

-- --------------------------------------------------------

--
-- Structure de la table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_pref` (`user_id`,`preference_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

-- --------------------------------------------------------

--
-- Structure de la vue `active_dynamic_qrs`
--
DROP TABLE IF EXISTS `active_dynamic_qrs`;

DROP VIEW IF EXISTS `active_dynamic_qrs`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_dynamic_qrs`  AS SELECT `dqr`.`id` AS `id`, `dqr`.`qr_token` AS `qr_token`, `dqr`.`table_id` AS `table_id`, `dqr`.`session_id` AS `session_id`, `dqr`.`status` AS `status`, `dqr`.`scanned_count` AS `scanned_count`, `dqr`.`created_at` AS `created_at`, `dqr`.`expires_at` AS `expires_at`, `dqr`.`last_scanned_at` AS `last_scanned_at`, `rt`.`table_number` AS `table_number`, `rt`.`table_name` AS `table_name`, timestampdiff(MINUTE,now(),`dqr`.`expires_at`) AS `minutes_remaining` FROM (`dynamic_qr_codes` `dqr` join `restaurant_tables` `rt` on((`dqr`.`table_id` = `rt`.`id`))) WHERE ((`dqr`.`status` = 'active') AND (`dqr`.`expires_at` > now())) ;

-- --------------------------------------------------------

--
-- Structure de la vue `active_table_sessions`
--
DROP TABLE IF EXISTS `active_table_sessions`;

DROP VIEW IF EXISTS `active_table_sessions`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_table_sessions`  AS SELECT `ts`.`id` AS `id`, `ts`.`table_id` AS `table_id`, `rt`.`table_number` AS `table_number`, `rt`.`table_name` AS `table_name`, `ts`.`session_token` AS `session_token`, `ts`.`status` AS `status`, `ts`.`opened_at` AS `opened_at`, `ts`.`expires_at` AS `expires_at`, `ts`.`opened_by` AS `opened_by`, `ts`.`total_orders` AS `total_orders`, greatest(0,timestampdiff(MINUTE,now(),`ts`.`expires_at`)) AS `minutes_remaining` FROM (`table_sessions` `ts` join `restaurant_tables` `rt` on((`ts`.`table_id` = `rt`.`id`))) WHERE ((`ts`.`status` = 'OPEN') AND (`ts`.`expires_at` > now()) AND (`rt`.`is_active` = 1)) ORDER BY `ts`.`opened_at` DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `pending_pos_sync`
--
DROP TABLE IF EXISTS `pending_pos_sync`;

DROP VIEW IF EXISTS `pending_pos_sync`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pending_pos_sync`  AS SELECT `psq`.`id` AS `sync_id`, `psq`.`order_number` AS `order_number`, `psq`.`payload` AS `payload`, `psq`.`status` AS `sync_status`, `psq`.`created_at` AS `sync_created_at`, `psq`.`synced_at` AS `synced_at`, `psq`.`retry_count` AS `retry_count`, `psq`.`last_error` AS `last_error`, `o`.`id` AS `order_id`, `o`.`total_price` AS `total_price`, `o`.`created_at` AS `order_created_at`, `o`.`customer_name` AS `customer_name` FROM (`pos_sync_queue` `psq` left join `orders` `o` on((`psq`.`order_number` = `o`.`order_number`))) WHERE (`psq`.`status` = 'pending') ORDER BY `psq`.`created_at` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `unread_notifications`
--
DROP TABLE IF EXISTS `unread_notifications`;

DROP VIEW IF EXISTS `unread_notifications`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `unread_notifications`  AS SELECT `n`.`id` AS `id`, `n`.`notification_id` AS `notification_id`, `n`.`type` AS `type`, `n`.`title` AS `title`, `n`.`message` AS `message`, `n`.`data` AS `data`, `n`.`priority` AS `priority`, `n`.`created_at` AS `created_at`, (case when (`n`.`priority` = 1) then 'Haute' when (`n`.`priority` = 2) then 'Moyenne' else 'Basse' end) AS `priority_label` FROM `notifications` AS `n` WHERE (`n`.`is_read` = false) ORDER BY `n`.`priority` ASC, `n`.`created_at` DESC ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Évènements
--
DROP EVENT IF EXISTS `daily_cleanup`$$
CREATE DEFINER=`root`@`localhost` EVENT `daily_cleanup` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-05 03:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL cleanup_old_data()$$

DROP EVENT IF EXISTS `daily_stats_calculation`$$
CREATE DEFINER=`root`@`localhost` EVENT `daily_stats_calculation` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-04 23:55:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL calculate_daily_stats(CURDATE())$$

DROP EVENT IF EXISTS `expire_dynamic_qrs`$$
CREATE DEFINER=`root`@`localhost` EVENT `expire_dynamic_qrs` ON SCHEDULE EVERY 5 MINUTE STARTS '2026-02-04 21:05:34' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE dynamic_qr_codes 
    SET status = 'expired' 
    WHERE status = 'active' AND expires_at <= NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
