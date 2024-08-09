-- --------------------------------------------------------
-- Host:                         bottleuproduction.c6sthmahhial.eu-west-1.rds.amazonaws.com
-- Versione server:              10.4.32-MariaDB-log - Source distribution
-- S.O. server:                  Linux
-- HeidiSQL Versione:            12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dump della struttura di tabella bottleup_clear.brands
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL DEFAULT '0',
  `status` enum('draft','hidden','published') NOT NULL DEFAULT 'draft',
  `evidence` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  `cover` varchar(200) DEFAULT NULL,
  `logo_svg` mediumtext DEFAULT NULL,
  `logo_png` varchar(200) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.brands_lang
CREATE TABLE IF NOT EXISTS `brands_lang` (
  `id_brands` int(11) unsigned DEFAULT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `indexable` tinyint(3) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `id_brands_language` (`id_brands`,`language`),
  UNIQUE KEY `language_slug` (`language`,`slug`),
  CONSTRAINT `FK_brands_lang_brands` FOREIGN KEY (`id_brands`) REFERENCES `brands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) unsigned NOT NULL,
  `id_categories_main` int(11) unsigned DEFAULT NULL,
  `status` enum('draft','hidden','published') NOT NULL DEFAULT 'draft',
  `evidence` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  `cover` varchar(200) DEFAULT NULL,
  `icon_svg` mediumtext DEFAULT NULL,
  `icon_png` varchar(200) DEFAULT NULL,
  `date_last_sync` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `id` (`id`),
  KEY `status` (`status`),
  KEY `FK_store_categories_store_categories` (`id_categories_main`),
  CONSTRAINT `FK_store_categories_store_categories` FOREIGN KEY (`id_categories_main`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.categories_lang
CREATE TABLE IF NOT EXISTS `categories_lang` (
  `id_categories` int(11) unsigned NOT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `indexable` tinyint(3) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `id_ftstore_categories_language` (`id_categories`,`language`),
  UNIQUE KEY `language_slug` (`language`,`slug`),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `FK_ftstore_categories_lang_store_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.countries
CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iso` tinytext NOT NULL,
  `iso3` tinytext NOT NULL,
  `fips` tinytext DEFAULT NULL,
  `country` tinytext NOT NULL,
  `community` tinytext NOT NULL,
  `continent` tinytext NOT NULL,
  `currency_code` tinytext DEFAULT NULL,
  `currency_name` tinytext DEFAULT NULL,
  `phone_prefix` tinytext DEFAULT NULL,
  `zip_code_regexp` tinytext DEFAULT NULL,
  `languages` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=895 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.countries_regions
CREATE TABLE IF NOT EXISTS `countries_regions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `region` varchar(100) NOT NULL,
  `id_countries` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_geo_regions_geo_countries` (`id_countries`),
  CONSTRAINT `FK_geo_regions_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.countries_states
CREATE TABLE IF NOT EXISTS `countries_states` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_countries` int(11) unsigned NOT NULL,
  `state` tinytext NOT NULL,
  `state_short` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_regione` (`id_countries`) USING BTREE,
  CONSTRAINT `FK_countries_states_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=667 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.data
CREATE TABLE IF NOT EXISTS `data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_countries` int(10) unsigned DEFAULT NULL,
  `id_countries_states` int(10) unsigned DEFAULT NULL,
  `table_name` varchar(50) NOT NULL DEFAULT 'users',
  `table_id` int(10) unsigned NOT NULL,
  `main` tinyint(4) NOT NULL DEFAULT 0,
  `header` text NOT NULL,
  `address` text DEFAULT NULL,
  `address_note` text DEFAULT NULL,
  `co` text DEFAULT NULL,
  `ca` text DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `phone_prefix` varchar(10) DEFAULT NULL,
  `formatted_address` text DEFAULT NULL,
  `iban` text DEFAULT NULL,
  `swift` text DEFAULT NULL,
  `bank` text DEFAULT NULL,
  `account` text DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_expeditions_data_geo_countries` (`id_countries`),
  KEY `table_id` (`table_id`),
  KEY `table_name` (`table_name`),
  KEY `FK_expeditions_data_geo_provinces` (`id_countries_states`) USING BTREE,
  KEY `main` (`main`) USING BTREE,
  CONSTRAINT `FK_expeditions_data_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_shipping_data_countries_states` FOREIGN KEY (`id_countries_states`) REFERENCES `countries_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39818 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.data_history
CREATE TABLE IF NOT EXISTS `data_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_countries` int(10) unsigned DEFAULT NULL,
  `id_countries_states` int(10) unsigned DEFAULT NULL,
  `header` text DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address_note` mediumtext DEFAULT NULL,
  `co` text DEFAULT NULL,
  `ca` text DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `phone_prefix` varchar(10) DEFAULT NULL,
  `formatted_address` text DEFAULT NULL,
  `sdi_code` varchar(40) DEFAULT NULL,
  `fiscal_code` text DEFAULT NULL,
  `vat_number` text DEFAULT NULL,
  `pec` varchar(120) DEFAULT NULL,
  `pa` tinyint(4) DEFAULT 0,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_expeditions_data_history_geo_countries` (`id_countries`),
  KEY `FK_expeditions_data_history_geo_provinces` (`id_countries_states`) USING BTREE,
  CONSTRAINT `FK_expeditions_data_history_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_shipping_data_history_countries_states` FOREIGN KEY (`id_countries_states`) REFERENCES `countries_states` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95819 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.import_errors
CREATE TABLE IF NOT EXISTS `import_errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(50) DEFAULT NULL,
  `ref` varchar(50) DEFAULT NULL,
  `error` text DEFAULT NULL,
  `query` text DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.oauth_clients
CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` varchar(200) DEFAULT NULL,
  `client_secret` varchar(200) DEFAULT NULL,
  `id_stores` int(10) unsigned DEFAULT NULL,
  `kid` varchar(50) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `client_secret` (`client_secret`) USING BTREE,
  UNIQUE KEY `client_id` (`client_id`) USING BTREE,
  UNIQUE KEY `kid` (`kid`),
  KEY `FK_oauth_clients_stores` (`id_stores`),
  CONSTRAINT `FK_oauth_clients_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.oauth_tokens
CREATE TABLE IF NOT EXISTS `oauth_tokens` (
  `jti` varchar(40) NOT NULL,
  `client_id` varchar(200) DEFAULT NULL,
  `id_users` int(11) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `ip` varchar(60) DEFAULT NULL,
  `ua` text DEFAULT NULL,
  `scope` varchar(20) DEFAULT NULL,
  `issuer` longtext DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `jti` (`jti`),
  KEY `client_id` (`client_id`),
  KEY `id_users` (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `family` set('english_riding','western_riding','others_riding','stable','pet') NOT NULL,
  `gender` set('male','female') DEFAULT NULL,
  `age` set('adult','young','child') DEFAULT NULL,
  `type` set('horse','rider') DEFAULT NULL,
  `a0` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling') DEFAULT NULL,
  `a1` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling') DEFAULT NULL,
  `a4` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling') DEFAULT NULL,
  `split` enum('a0','a1','a4') DEFAULT NULL,
  `cover` text DEFAULT NULL,
  `discount_class` varchar(10) NOT NULL,
  `client_type` set('b2b','b2c') NOT NULL DEFAULT 'b2b,b2c',
  `date_last_sync` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `type` (`family`) USING BTREE,
  KEY `client_type` (`client_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6940 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.products_categories
CREATE TABLE IF NOT EXISTS `products_categories` (
  `id_products` int(10) unsigned NOT NULL,
  `id_categories` int(10) unsigned NOT NULL,
  `main` tinyint(3) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `id_products_id_categories` (`id_products`,`id_categories`),
  KEY `id_products` (`id_products`),
  KEY `FK_products_categories_categories` (`id_categories`),
  CONSTRAINT `FK__products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_products_categories_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.products_lang
CREATE TABLE IF NOT EXISTS `products_lang` (
  `id_products` int(11) unsigned NOT NULL,
  `language` char(2) NOT NULL,
  `title` text DEFAULT NULL,
  UNIQUE KEY `id_products_language` (`id_products`,`language`) USING BTREE,
  CONSTRAINT `FK_products_lang_products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.stores
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_resellers` int(10) unsigned DEFAULT NULL,
  `id_agents` int(10) unsigned DEFAULT NULL,
  `id_countries` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `visibility` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `type` enum('b2c','b2b','horeca') NOT NULL DEFAULT 'b2c',
  `locale` enum('it-IT','it-CH','de-CH','en-US') NOT NULL DEFAULT 'it-IT',
  `payment_method` set('cash','bank_check','bank_transfer','paypal','cash_on_delivery_check','cash_on_delivery_cash','stripe','usa') DEFAULT NULL,
  `pickup` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `delivery` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `price_display_taxes_excluded` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `price_recharge_percentage` int(10) unsigned DEFAULT NULL,
  `payment_commission_percentage` float(10,4) unsigned DEFAULT 0.0000,
  `minimum_order_price` float(10,2) unsigned DEFAULT NULL,
  `minimum_order_quantity` int(10) unsigned DEFAULT NULL,
  `maximum_order_quantity` int(10) unsigned DEFAULT NULL,
  `shipping_delay` int(10) unsigned NOT NULL DEFAULT 1,
  `shipping_max_hour` char(50) NOT NULL DEFAULT '12:00',
  `checkout_mode` enum('site','configurator') DEFAULT 'configurator',
  `zip_list` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_store_vegetables_cities_store_vegetables` (`id_resellers`) USING BTREE,
  KEY `FK_resellers_stores_countries` (`id_countries`),
  KEY `FK_stores_agents` (`id_agents`),
  FULLTEXT KEY `zip_code` (`zip_list`),
  CONSTRAINT `FK_resellers_stores_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_vegetables_cities_store_vegetables` FOREIGN KEY (`id_resellers`) REFERENCES `resellers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_stores_agents` FOREIGN KEY (`id_agents`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.stores_images
CREATE TABLE IF NOT EXISTS `stores_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stores` int(11) unsigned DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_manufacturers_pictures_manufacturers` (`id_stores`) USING BTREE,
  CONSTRAINT `FK_stores_images_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.stores_lang
CREATE TABLE IF NOT EXISTS `stores_lang` (
  `id_stores` int(11) unsigned NOT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(200) DEFAULT NULL,
  `indexable` tinyint(3) unsigned DEFAULT 0,
  UNIQUE KEY `id_store_products_language` (`id_stores`,`language`) USING BTREE,
  UNIQUE KEY `language_slug` (`language`,`slug`) USING BTREE,
  CONSTRAINT `FK_resellers_stores_lang_resellers_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='BottleUp';

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_orders
CREATE TABLE IF NOT EXISTS `store_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned DEFAULT NULL,
  `id_tracking` int(10) unsigned DEFAULT NULL,
  `id_agents` int(10) unsigned DEFAULT NULL,
  `id_resellers` int(10) unsigned DEFAULT NULL,
  `id_stores` int(10) unsigned DEFAULT NULL,
  `id_countries` int(10) unsigned DEFAULT NULL,
  `oauth_tokens_jti` varchar(40) DEFAULT NULL,
  `number` int(10) unsigned DEFAULT NULL,
  `type` enum('b2b','b2c','horeca') DEFAULT 'b2c',
  `currency` varchar(5) NOT NULL DEFAULT 'EUR',
  `currency_conversion_rate` float(10,2) NOT NULL DEFAULT 1.00,
  `email` varchar(250) DEFAULT NULL,
  `discount_code` varchar(50) DEFAULT NULL,
  `discount_percentage` float(10,2) DEFAULT NULL,
  `discount_price` float(10,2) DEFAULT NULL,
  `discount_cumulable` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` enum('deleted','pending','confirmed','quote','cart','credit_note','completed','abandoned') NOT NULL DEFAULT 'pending',
  `notified` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `payable` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `gift` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `offline` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `comment` longtext DEFAULT NULL,
  `comment_alert` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `availability_alert` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `final_tax` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `total_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_vendor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_marketing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_shipping_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_agent_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_to_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_excise` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_duties` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_discount` float(10,2) NOT NULL DEFAULT 0.00,
  `total_discount_products` float(10,2) NOT NULL DEFAULT 0.00,
  `total_weight` float(16,2) NOT NULL DEFAULT 0.00,
  `total_volume` float(12,2) NOT NULL DEFAULT 0.00,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `total_products` int(11) NOT NULL DEFAULT 0,
  `total_products_shipping` int(11) NOT NULL DEFAULT 0,
  `date_last_refresh` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `FK_store_orders_users` (`id_users`),
  KEY `FK_store_orders_tracking` (`id_tracking`),
  KEY `FK_store_orders_agents` (`id_agents`),
  KEY `FK_store_orders_resellers` (`id_resellers`),
  KEY `FK_store_orders_countries` (`id_countries`),
  KEY `FK_store_orders_resellers_stores` (`id_stores`) USING BTREE,
  KEY `FK_store_orders_oauth_tokens` (`oauth_tokens_jti`),
  CONSTRAINT `FK_store_orders_agents` FOREIGN KEY (`id_agents`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_oauth_tokens` FOREIGN KEY (`oauth_tokens_jti`) REFERENCES `oauth_tokens` (`jti`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_resellers` FOREIGN KEY (`id_resellers`) REFERENCES `resellers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_resellers_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_tracking` FOREIGN KEY (`id_tracking`) REFERENCES `tracking` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74533 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_orders_products
CREATE TABLE IF NOT EXISTS `store_orders_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_orders` int(11) unsigned NOT NULL,
  `id_store_orders_products_parent` int(11) unsigned DEFAULT NULL,
  `id_shipping` int(11) unsigned DEFAULT NULL,
  `id_products` int(11) unsigned DEFAULT NULL,
  `id_store_products` int(11) unsigned DEFAULT NULL,
  `id_configurator` int(11) unsigned DEFAULT NULL,
  `id_printing_front` int(11) unsigned DEFAULT NULL,
  `id_printing_back` int(11) unsigned DEFAULT NULL,
  `id_labels` int(11) unsigned DEFAULT NULL,
  `id_store_tax` int(11) unsigned DEFAULT NULL,
  `id_templates` int(11) unsigned DEFAULT NULL,
  `listing` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `currency` varchar(5) NOT NULL DEFAULT 'EUR',
  `currency_conversion_rate` float(10,2) NOT NULL DEFAULT 1.00,
  `format` enum('single','package') NOT NULL DEFAULT 'single',
  `quantity` int(11) unsigned NOT NULL DEFAULT 0,
  `standard` int(11) unsigned NOT NULL DEFAULT 1,
  `availability` varchar(10) NOT NULL DEFAULT 'warehouse',
  `minimum_order` smallint(6) DEFAULT NULL,
  `multiples_available` smallint(5) unsigned DEFAULT 1,
  `code` varchar(50) NOT NULL DEFAULT 'CUSTOM',
  `weight` int(11) DEFAULT NULL,
  `count_on_shipping` tinyint(4) DEFAULT 1,
  `forced` tinyint(4) DEFAULT 0,
  `follow_quantity` tinyint(4) DEFAULT 1,
  `add_price` tinyint(4) DEFAULT 1,
  `fixed_component` tinyint(4) DEFAULT 0,
  `title` text DEFAULT NULL,
  `free_port` enum('manufacturer','warehouse','reseller') NOT NULL DEFAULT 'warehouse',
  `packaging` varchar(50) DEFAULT NULL,
  `typology` varchar(50) DEFAULT NULL,
  `duties_goods_type` varchar(50) DEFAULT NULL,
  `timing_supply` smallint(6) DEFAULT NULL,
  `shipping_delay` smallint(6) DEFAULT NULL,
  `label_included` smallint(6) DEFAULT NULL,
  `only_front_label` smallint(6) DEFAULT NULL,
  `processing_included` smallint(6) DEFAULT NULL,
  `total_weight` float(16,2) NOT NULL DEFAULT 0.00,
  `total_volume` float(10,2) NOT NULL DEFAULT 0.00,
  `total_price_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_final_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price_vendor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_marketing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_reseller_shipping_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_store_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_agent_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_to_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_buy` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_capsule` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_shipping_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_free_port` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_front_label` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_retro_label` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_packaging` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_processing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_end_chain` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_fee_fixed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_fee_percentege` float NOT NULL DEFAULT 0,
  `price_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_recharge_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_vendor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_recharge_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_recharge_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_recharge_percentage_product` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_recharge_percentage_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_store_recharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_store_recharge_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_store_recharge_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_fee` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `price_reseller_fee_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_marketing_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_marketing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_shipping_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_reseller_shipping_adjustment_endchain` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_agent_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_agent_commission_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `tax_multiplier` decimal(10,2) DEFAULT NULL,
  `taxation_type` varchar(50) DEFAULT NULL,
  `price_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_payment_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_final_taxes_excluded` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_final_taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_to_pay` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `discounts_global_percentage` float(10,2) NOT NULL DEFAULT 0.00,
  `discounts_product_percentage` float(10,2) NOT NULL DEFAULT 0.00,
  `discounts_total_percentage` float(10,2) NOT NULL DEFAULT 0.00,
  `custom_text` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `preview` varchar(200) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_store_orders_products_store_orders` (`id_store_orders`) USING BTREE,
  KEY `id_printing_front` (`id_printing_front`) USING BTREE,
  KEY `id_printing_back` (`id_printing_back`) USING BTREE,
  KEY `id_store_tax` (`id_store_tax`) USING BTREE,
  KEY `id_labels` (`id_labels`) USING BTREE,
  KEY `FK_store_orders_products_store_products` (`id_store_products`) USING BTREE,
  KEY `FK_store_orders_products_templates` (`id_templates`) USING BTREE,
  KEY `id_store_products_availability` (`id_configurator`) USING BTREE,
  KEY `FK_store_orders_products_products` (`id_products`),
  KEY `FK_store_orders_products_shipping` (`id_shipping`),
  KEY `FK_store_orders_products_store_orders_products` (`id_store_orders_products_parent`),
  CONSTRAINT `FK_store_orders_products_configurator` FOREIGN KEY (`id_configurator`) REFERENCES `configurator` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_labels` FOREIGN KEY (`id_labels`) REFERENCES `labels` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_printing` FOREIGN KEY (`id_printing_front`) REFERENCES `printing` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_printing_2` FOREIGN KEY (`id_printing_back`) REFERENCES `printing` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_shipping` FOREIGN KEY (`id_shipping`) REFERENCES `shipping` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_store_orders` FOREIGN KEY (`id_store_orders`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_store_orders_products` FOREIGN KEY (`id_store_orders_products_parent`) REFERENCES `store_orders_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_store_tax` FOREIGN KEY (`id_store_tax`) REFERENCES `store_tax` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_store_orders_products_templates` FOREIGN KEY (`id_templates`) REFERENCES `templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=128512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products
CREATE TABLE IF NOT EXISTS `store_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_products` int(11) unsigned DEFAULT NULL,
  `variant` varchar(30) DEFAULT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(13) DEFAULT NULL,
  `status` enum('deleted','on_sale','not_on_sale','low_stock') NOT NULL DEFAULT 'not_on_sale',
  `collection` set('cont','fw23','fw24','fw25') DEFAULT NULL,
  `availability_type` enum('warehouse','order') NOT NULL DEFAULT 'warehouse',
  `a0_code` varchar(8) DEFAULT NULL,
  `a0_description` varchar(50) DEFAULT NULL,
  `a0_order` varchar(50) DEFAULT NULL,
  `a1_code` varchar(8) DEFAULT NULL,
  `a1_description` varchar(50) DEFAULT NULL,
  `a1_order` varchar(50) DEFAULT NULL,
  `a4_code` varchar(8) DEFAULT NULL,
  `a4_description` varchar(50) DEFAULT NULL,
  `a4_order` varchar(50) DEFAULT NULL,
  `date_last_sync` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`) USING BTREE,
  UNIQUE KEY `barcode` (`barcode`),
  KEY `FK_store_products_store_products_contents` (`id_products`) USING BTREE,
  KEY `status` (`status`),
  KEY `variant` (`variant`),
  CONSTRAINT `FK_store_products_products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1095 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_availability
CREATE TABLE IF NOT EXISTS `store_products_availability` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(11) unsigned DEFAULT NULL,
  `id_stores` int(11) unsigned DEFAULT NULL,
  `availability` int(11) DEFAULT NULL,
  `status` enum('out_of_stock','on_sale','not_on_sale') NOT NULL DEFAULT 'on_sale',
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_store_products_availability_store_products` (`id_store_products`),
  KEY `FK_store_products_availability_stores` (`id_stores`),
  CONSTRAINT `FK_store_products_availability_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_products_availability_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_components
CREATE TABLE IF NOT EXISTS `store_products_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(10) unsigned NOT NULL,
  `id_store_products_component` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `follow_quantity` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `add_price` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_store_products_components_store_products` (`id_store_products`),
  KEY `FK_store_products_components_store_products_2` (`id_store_products_component`),
  CONSTRAINT `FK_store_products_components_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_products_components_store_products_2` FOREIGN KEY (`id_store_products_component`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_discounts
CREATE TABLE IF NOT EXISTS `store_products_discounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(11) unsigned NOT NULL,
  `id_countries` int(11) unsigned DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `quantity_min` int(10) DEFAULT NULL,
  `quantity_max` int(10) DEFAULT NULL,
  `discount_percentage` float(10,2) DEFAULT NULL,
  `client_type` set('b2b','b2c','horeca') DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_ftstore_products_discounts_store_products` (`id_store_products`),
  KEY `FK_store_products_discounts_geo_countries` (`id_countries`),
  CONSTRAINT `FK_ftstore_products_discounts_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_products_discounts_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=707 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_images
CREATE TABLE IF NOT EXISTS `store_products_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(11) unsigned DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_ftstore_products_images_store_products` (`id_store_products`),
  CONSTRAINT `FK_ftstore_products_images_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_lang
CREATE TABLE IF NOT EXISTS `store_products_lang` (
  `id_store_products` int(11) unsigned NOT NULL,
  `language` char(2) NOT NULL,
  `slug` varchar(180) DEFAULT NULL,
  `title` text DEFAULT NULL,
  `title_image` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `indexable` tinyint(3) unsigned DEFAULT 0,
  `merchant` tinyint(3) unsigned DEFAULT 1,
  `merchant_title` text DEFAULT NULL,
  `merchant_description` text DEFAULT NULL,
  UNIQUE KEY `id_store_products_language` (`id_store_products`,`language`),
  UNIQUE KEY `language_slug` (`language`,`slug`),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `FK_ftstore_products_lang_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_resellers
CREATE TABLE IF NOT EXISTS `store_products_resellers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(10) unsigned NOT NULL,
  `id_resellers` int(10) unsigned NOT NULL,
  `price_recharge_percentage` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_store_products_landing_store_products` (`id_store_products`) USING BTREE,
  KEY `FK_store_products_resellers_resellers` (`id_resellers`),
  CONSTRAINT `FK_store_products_resellers_resellers` FOREIGN KEY (`id_resellers`) REFERENCES `resellers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `store_products_resellers_ibfk_1` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1978 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_agents` int(10) unsigned DEFAULT NULL,
  `id_resellers` int(10) unsigned DEFAULT NULL,
  `id_tracking` int(10) unsigned DEFAULT NULL,
  `username` char(180) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(200) NOT NULL,
  `hash` varchar(12) NOT NULL,
  `auth` smallint(4) unsigned NOT NULL DEFAULT 3000,
  `type` enum('b2c','b2b','horeca') NOT NULL DEFAULT 'b2c',
  `language` char(10) NOT NULL DEFAULT 'en' COMMENT 'Standard language iso code',
  `first_name` text DEFAULT NULL,
  `last_name` text DEFAULT NULL,
  `business_name` text DEFAULT NULL,
  `business_type` enum('company','hotel','restaurant','wine_shop','wedding','catering','pub','tavern','pizzeria','bar','club','resort','agritourism','event_planner','b&b','other') DEFAULT NULL,
  `header` text DEFAULT NULL,
  `sdi_code` varchar(40) DEFAULT NULL,
  `fiscal_code` text DEFAULT NULL,
  `vat_number` text DEFAULT NULL,
  `pec` varchar(120) DEFAULT NULL,
  `pa` tinyint(4) DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL COMMENT 'internal profile image or external by social login',
  `birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `id_countries` int(10) unsigned DEFAULT NULL COMMENT 'Region code standard from https://sites.google.com/site/tomihasa/google-language-codes',
  `id_countries_states` int(10) unsigned DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `phone_prefix` varchar(6) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `password` (`password`(191)),
  KEY `hash` (`hash`),
  KEY `FK_users_countries` (`id_countries`),
  KEY `FK_users_tracking` (`id_tracking`),
  KEY `FK_users_countries_states` (`id_countries_states`),
  KEY `FK_users_resellers` (`id_agents`) USING BTREE,
  KEY `FK_users_resellers_2` (`id_resellers`),
  CONSTRAINT `FK_users_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_countries_states` FOREIGN KEY (`id_countries_states`) REFERENCES `countries_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_resellers` FOREIGN KEY (`id_agents`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_resellers_2` FOREIGN KEY (`id_resellers`) REFERENCES `resellers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_tracking` FOREIGN KEY (`id_tracking`) REFERENCES `tracking` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.users_keys
CREATE TABLE IF NOT EXISTS `users_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned DEFAULT NULL,
  `key_value` varchar(64) DEFAULT NULL,
  `key_type` char(20) DEFAULT NULL,
  `key_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `key_expiry` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_user_key_type` (`id_users`,`key_type`),
  KEY `key_value` (`key_value`),
  CONSTRAINT `FK_users_keys_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10532 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di trigger bottleup_clear.oauth_tokens_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `bottleup_clear`.`oauth_tokens_after_update` AFTER UPDATE ON `oauth_tokens` FOR EACH ROW BEGIN
	IF OLD.id_users IS NULL AND NEW.id_users IS NOT NULL THEN
		UPDATE store_orders SET id_users = NEW.id_users, oauth_tokens_jti = NULL WHERE store_orders.oauth_tokens_jti = OLD.jti;
	END IF; 
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dump della struttura di trigger bottleup_clear.store_products_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `bottleup_clear`.`store_products_before_update` BEFORE UPDATE ON `store_products` FOR EACH ROW BEGIN

	IF (NEW.availability_virtual IS NOT NULL AND NEW.availability_virtual < 0)
	
	THEN
	
		SET NEW.availability_virtual = 0;
		
	END IF;
	
		IF (NEW.availability_warehouse IS NOT NULL AND NEW.availability_warehouse < 0)
	
	THEN
	
		SET NEW.availability_warehouse = 0;
		
	END IF;
	

	IF (NEW.availability_warehouse IS NOT NULL AND NEW.availability_warehouse <= 0 AND NEW.availability_virtual IS NOT NULL AND NEW.availability_virtual <= 0 AND (NEW.status = 'on_sale' OR OLD.status = 'on_sale'))
	
	THEN
	
		SET NEW.status = 'out_of_stock';
	
	END IF;
	

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
