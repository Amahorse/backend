-- --------------------------------------------------------
-- Host:                         bottleuproduction.c6sthmahhial.eu-west-1.rds.amazonaws.com
-- Versione server:              10.4.34-MariaDB-log - Source distribution
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
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `evidence` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  `cover` varchar(200) DEFAULT NULL,
  `logo_svg` mediumtext DEFAULT NULL,
  `logo_png` varchar(200) DEFAULT NULL,
  `date_last_sync` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.brands_lang
CREATE TABLE IF NOT EXISTS `brands_lang` (
  `id_brands` int(11) unsigned DEFAULT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_categories_main` int(11) unsigned DEFAULT NULL,
  `uniqid` varchar(50) NOT NULL DEFAULT '',
  `status` enum('draft','hidden','published') NOT NULL DEFAULT 'draft',
  `evidence` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  `cover` varchar(200) DEFAULT NULL,
  `icon_svg` mediumtext DEFAULT NULL,
  `icon_png` varchar(200) DEFAULT NULL,
  `date_last_sync` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`uniqid`) USING BTREE,
  KEY `status` (`status`),
  KEY `FK_store_categories_store_categories` (`id_categories_main`),
  CONSTRAINT `FK_categories_categories` FOREIGN KEY (`id_categories_main`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

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
  CONSTRAINT `FK_categories_lang_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.cms_menu
CREATE TABLE IF NOT EXISTS `cms_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(4) NOT NULL DEFAULT '0',
  `domain` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '0',
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_domain_name` (`language`,`domain`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.cms_menu_points
CREATE TABLE IF NOT EXISTS `cms_menu_points` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cms_menu` int(11) unsigned DEFAULT NULL,
  `id_cms_pages` int(11) unsigned DEFAULT NULL,
  `id_cms_menu_child` int(11) unsigned DEFAULT NULL,
  `id_categories` int(11) unsigned DEFAULT NULL,
  `title` text NOT NULL,
  `url` text DEFAULT NULL,
  `link_type` enum('page','url') NOT NULL DEFAULT 'page',
  `target` enum('_self','_blank') NOT NULL DEFAULT '_self',
  `sort_order` int(2) unsigned zerofill NOT NULL DEFAULT 00,
  `family` set('english_riding','western_riding','others_riding','stable','pet') NOT NULL,
  `gender` set('male','female') DEFAULT NULL,
  `age` set('adult','child') DEFAULT NULL,
  `type` set('horse','rider') DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_cms_menu_points_cms_pages` (`id_cms_pages`),
  KEY `FK_cms_menu_points_cms_menu` (`id_cms_menu`),
  KEY `FK_cms_menu_points_cms_menu_2` (`id_cms_menu_child`),
  KEY `FK_cms_menu_points_categories` (`id_categories`),
  CONSTRAINT `FK_cms_menu_points_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_cms_menu_points_cms_menu` FOREIGN KEY (`id_cms_menu`) REFERENCES `cms_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_cms_menu_points_cms_menu_2` FOREIGN KEY (`id_cms_menu_child`) REFERENCES `cms_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_cms_menu_points_cms_pages` FOREIGN KEY (`id_cms_pages`) REFERENCES `cms_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.cms_pages
CREATE TABLE IF NOT EXISTS `cms_pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_resellers` int(10) unsigned DEFAULT NULL,
  `page` varchar(250) DEFAULT NULL,
  `domain` varchar(50) DEFAULT NULL,
  `language` varchar(4) DEFAULT NULL,
  `slug` varchar(250) DEFAULT NULL,
  `status` enum('published','hidden','draft') NOT NULL DEFAULT 'published',
  `mode` enum('category','product','manual') NOT NULL DEFAULT 'manual',
  `type` enum('b2b','b2c','horeca') NOT NULL DEFAULT 'b2c',
  `cover` varchar(200) DEFAULT NULL,
  `model` varchar(200) DEFAULT NULL,
  `json` varchar(200) DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `product_type` enum('wine','beer','oil','spirits','drinks','food','boxes','packages','printing','cards','labels','accessories','other') DEFAULT NULL,
  `id_categories` int(11) unsigned DEFAULT NULL,
  `indexable` tinyint(3) unsigned DEFAULT NULL,
  `priority` varchar(4) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_domain_language` (`page`,`domain`,`language`),
  UNIQUE KEY `domain_language_slug` (`domain`,`language`,`slug`),
  KEY `FK_cms_pages_categories` (`id_categories`),
  KEY `FK_cms_pages_resellers` (`id_resellers`),
  CONSTRAINT `FK_cms_pages_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_cms_pages_resellers` FOREIGN KEY (`id_resellers`) REFERENCES `resellers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `id_users` int(10) unsigned NOT NULL,
  `id_countries` int(10) unsigned DEFAULT NULL,
  `header` text NOT NULL,
  `type` enum('contacts','newsletter','invoice','shipping') NOT NULL DEFAULT 'contacts',
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
  KEY `id_users` (`id_users`),
  CONSTRAINT `FK_expeditions_data_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39818 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=13533 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.import_logs
CREATE TABLE IF NOT EXISTS `import_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `import` varchar(10) NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `id_brands` int(11) unsigned DEFAULT NULL,
  `code` varchar(45) NOT NULL,
  `family` set('english_riding','western_riding','others_riding','stable','pet','display') NOT NULL,
  `gender` set('male','female') DEFAULT NULL,
  `age` set('adult','child') DEFAULT NULL,
  `type` set('horse','rider') DEFAULT NULL,
  `material` set('bamboo','dry-lex','eco-fur','eva','faux-sheepskin','felt','gel','leather','memory-foam','micropile','sheepskin','3d-spacer','cotton','louvre-bamboo','louvre','lycra','suede','wool') DEFAULT NULL,
  `tech` set('anallergic-antibacterial','antistatic','back-riser','breathable','carbon-finish','not-dry-clean','not-tumble-dry','double-riser','easy-care','eco-friendly','extra-grip-tread','extra-wide-tread','flexible-arch','flexible-tread','fly-protect','front-riser','grip-system','hand-wash','high-quality','high-resistance','hypoallergenic','inclined-tread','insulating','just-use-30','lightweight','light-aluminium','low-iron','low-knee-impact','made-in-italy','mag-system','max-ld-150','max-ld-200','max-ld-500','max-ld-80','middle-riser','natural-deodorize','patented-design','pocket-configuration','quick-dry','reflective','shock-absorbing','softness','spine-free','stirrup-orientation','stretchable','synthetic','thermal-insulator','touch','twin-side','water-repellent','waterproof','windproof','withers-free','ac-grip-system','classic-withers-3d','classic-withers','dressage-shape','gel-grip','half-pad-shape','high quality','jumping-close','shaped-withers-3d') DEFAULT NULL,
  `season` set('summer','winter') DEFAULT NULL,
  `discipline` set('dressage','jump','pony') DEFAULT NULL,
  `a0` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling','grip-color') DEFAULT NULL,
  `a1` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling','grip-color') DEFAULT NULL,
  `a4` enum('size','height','handle','calf-size','color','type','taste','chest-size','eco-wool-color','tie','gel-color','main-color','fabric-color','flag','seat-color','buckle','print','crystal-color','dri-lex-color','fitting-color','fork-size','headstall-size','lenght','manufacturing','material','micropile-color','quarter-size','rope-color','sheepskin-color','tooling','grip-color') DEFAULT NULL,
  `split` enum('a0','a1','a4') DEFAULT NULL,
  `market_sale` text DEFAULT NULL,
  `discount_product_percentage` float(10,2) DEFAULT NULL,
  `available_b2c` tinyint(1) DEFAULT NULL,
  `available_b2b` tinyint(1) NOT NULL DEFAULT 0,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `type` (`family`) USING BTREE,
  KEY `FK_products_brands` (`id_brands`),
  CONSTRAINT `FK_products_brands` FOREIGN KEY (`id_brands`) REFERENCES `brands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7077 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

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
  `slug` varchar(180) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `indexable` tinyint(3) unsigned DEFAULT 0,
  `size_fit` text DEFAULT NULL,
  `tech_spec` text DEFAULT NULL,
  `composition` text DEFAULT NULL,
  `info_care` text DEFAULT NULL,
  UNIQUE KEY `id_store_products_language` (`id_products`,`language`) USING BTREE,
  UNIQUE KEY `language_slug` (`language`,`slug`),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `FK_products_lang_products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.stores
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `type` enum('b2c','b2b') NOT NULL DEFAULT 'b2c',
  `currency` enum('USD','EUR','CAD') NOT NULL DEFAULT 'EUR',
  `taxes_included` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `collection` set('CONT','ETS22','ETW22','ETS23','ETW23','ETS24','ETW24','ETS25','ETW25') DEFAULT NULL,
  `season` set('summer','winter') DEFAULT NULL,
  `color_primary` set('red','blue','multicolor','grey','green','fucsia','black','purple','transparent','white','natural','brown','beige','silver','bronze','rose-gold','yellow','burgundy','orange','pink','royal-blue','lime','carbon','cognac','gold','titanium','royal blue','rose gold') DEFAULT NULL,
  `color_secondary` set('red','blue','multicolor','grey','green','fucsia','black','purple','transparent','white','natural','brown','beige','silver','bronze','rose-gold','yellow','burgundy','orange','pink','royal-blue','lime','carbon','cognac','gold','titanium','royal blue','rose gold') DEFAULT NULL,
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
  `cover` varchar(50) DEFAULT NULL,
  `cover_url` text DEFAULT NULL,
  `minimum_order` int(11) unsigned DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`) USING BTREE,
  UNIQUE KEY `barcode` (`barcode`),
  KEY `FK_store_products_store_products_contents` (`id_products`) USING BTREE,
  KEY `status` (`status`),
  KEY `variant` (`variant`),
  CONSTRAINT `FK_store_products_products` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65721 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_availability
CREATE TABLE IF NOT EXISTS `store_products_availability` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(11) unsigned DEFAULT NULL,
  `availability_b2c` int(11) DEFAULT NULL,
  `availability_b2b` int(11) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_store_products_availability_store_products` (`id_store_products`),
  CONSTRAINT `FK_store_products_availability_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29588 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `imported` tinyint(4) DEFAULT NULL,
  `id_countries` int(11) unsigned DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `quantity_min` int(10) DEFAULT NULL,
  `quantity_max` int(10) DEFAULT NULL,
  `discount_offer_percentage` float(10,2) DEFAULT NULL,
  `client_type` set('b2b','b2c','horeca') DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_store_products_imported` (`id_store_products`,`imported`),
  KEY `FK_ftstore_products_discounts_store_products` (`id_store_products`),
  KEY `FK_store_products_discounts_geo_countries` (`id_countries`),
  CONSTRAINT `FK_ftstore_products_discounts_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_store_products_discounts_geo_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6019 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_images
CREATE TABLE IF NOT EXISTS `store_products_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_store_products` int(11) unsigned DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `sorting` tinyint(3) unsigned DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_ftstore_products_images_store_products` (`id_store_products`),
  CONSTRAINT `FK_ftstore_products_images_store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.store_products_prices
CREATE TABLE IF NOT EXISTS `store_products_prices` (
  `id_store_products` int(10) unsigned NOT NULL,
  `id_stores` int(10) unsigned NOT NULL,
  `price` float(10,2) NOT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `id_store_products_id_stores` (`id_store_products`,`id_stores`),
  KEY `FK__stores` (`id_stores`),
  KEY `id_store_products` (`id_store_products`),
  CONSTRAINT `FK__store_products` FOREIGN KEY (`id_store_products`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella bottleup_clear.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_gamma` int(11) unsigned DEFAULT NULL,
  `id_countries` int(10) unsigned DEFAULT NULL COMMENT 'Region code standard from https://sites.google.com/site/tomihasa/google-language-codes',
  `id_stores` int(10) unsigned DEFAULT NULL,
  `username` char(180) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(200) NOT NULL,
  `hash` varchar(12) NOT NULL,
  `auth` smallint(4) unsigned NOT NULL DEFAULT 3000,
  `type` enum('b2b') NOT NULL DEFAULT 'b2b',
  `status` enum('pending','approved','confirmed','deleted') NOT NULL DEFAULT 'pending',
  `language` char(10) NOT NULL DEFAULT 'en' COMMENT 'Standard language iso code',
  `business_name` text DEFAULT NULL,
  `website` text DEFAULT NULL,
  `website_ecommerce` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `website_type` enum('wordpress','prestashop','shopify','magento','custom','other') DEFAULT NULL,
  `business_type` enum('wholesaler','saddlery','sport_association','online store','buying_group') DEFAULT NULL,
  `discount_client_percentage` float(10,2) DEFAULT NULL,
  `discount_contract_percentage` float(10,2) DEFAULT NULL,
  `discount_final_percentage` float(10,2) DEFAULT NULL,
  `date_ins` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `id_gamma` (`id_gamma`),
  KEY `password` (`password`(191)),
  KEY `hash` (`hash`),
  KEY `FK_users_countries` (`id_countries`),
  KEY `FK_users_stores` (`id_stores`),
  CONSTRAINT `FK_users_countries` FOREIGN KEY (`id_countries`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_stores` FOREIGN KEY (`id_stores`) REFERENCES `stores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
