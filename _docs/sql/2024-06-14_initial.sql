-- массив регионов
CREATE TABLE `map_data_regions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_map` int DEFAULT NULL,
    `alias_map` varchar(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
    `edit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `edit_whois` int DEFAULT NULL,
    `edit_ipv4` int unsigned DEFAULT NULL,
    `id_region` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
    `title` varchar(80) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
    `content` longtext COLLATE utf8mb3_unicode_ci COMMENT 'текстовый контент',
    -- и другие поля данных
    `content_restricted` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '' COMMENT 'показывается если доступ к информации ограничен',
    `edit_comment` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL COMMENT 'комментарий редактора',
    `region_stroke` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Draw region border?',
    `region_border_color` char(7) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'Region border color',
    `region_border_width` tinyint DEFAULT NULL COMMENT 'Region border width',
    `region_border_opacity` decimal(2,1) DEFAULT NULL COMMENT 'Region border opacity',
    `region_fill` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Draw region fill?',
    `region_fill_color` char(7) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'Region fill color',
    `region_fill_opacity` decimal(2,1) DEFAULT NULL COMMENT 'Region fill opacity',
    `is_excludelists` enum('A','F','N') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'N' COMMENT 'exclude from lists',
    `is_publicity` enum('ANYONE','VISITOR','EDITOR','OWNER','ROOT') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ANYONE' COMMENT 'visibility of region',
    PRIMARY KEY (`id`,`edit_date`),
    KEY `id_region` (`id_region`),
    KEY `alias_map` (`alias_map`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- DelightAuth: users

CREATE TABLE `users` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(249) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `status` tinyint unsigned NOT NULL DEFAULT '0',
    `verified` tinyint unsigned NOT NULL DEFAULT '0',
    `resettable` tinyint unsigned NOT NULL DEFAULT '1',
    `roles_mask` int unsigned NOT NULL DEFAULT '0',
    `registered` int unsigned NOT NULL,
    `last_login` int unsigned DEFAULT NULL,
    `force_logout` mediumint unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_confirmations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL,
    `email` varchar(249) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `expires` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `email_expires` (`email`,`expires`),
    KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_remembered` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user` int unsigned NOT NULL,
    `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `expires` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `user` (`user`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_resets` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user` int unsigned NOT NULL,
    `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `expires` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `user_expires` (`user`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_throttling` (
    `bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `tokens` float unsigned NOT NULL,
    `replenished_at` int unsigned NOT NULL,
    `expires_at` int unsigned NOT NULL,
    PRIMARY KEY (`bucket`),
    KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

