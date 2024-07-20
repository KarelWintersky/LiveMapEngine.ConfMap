-- массив регионов
CREATE TABLE `map_data_regions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_map` varchar(80) CHARACTER SET latin1 DEFAULT NULL COMMENT 'ID карты, строка латиницей',
    `id_region` varchar(64) DEFAULT NULL COMMENT 'ID региона, path id из SVG-файла. Рекомендуется латиница.',
    `edit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `edit_whois` int DEFAULT NULL,
    `edit_ipv4` int unsigned DEFAULT NULL,
    -- поля данных контента
    `title` varchar(120)  DEFAULT NULL,
    `content` longtext COMMENT 'текстовый контент',
    -- и другие поля данных
    `content_restricted` varchar(250) DEFAULT '' COMMENT 'показывается если доступ к информации ограничен',
    `edit_comment` varchar(120) DEFAULT NULL COMMENT 'комментарий редактора',
    `region_stroke` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Draw region border ?',
    `region_border_color` char(7) CHARACTER SET latin1 DEFAULT '' COMMENT 'Region border color: #000000',
    `region_border_width` tinyint DEFAULT '0' COMMENT 'Region border width',
    `region_border_opacity` decimal(2,1) DEFAULT  NULL COMMENT 'Region border opacity',
    `region_fill` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Draw region fill ?',
    `region_fill_color` char(7) CHARACTER SET latin1 DEFAULT '' COMMENT 'Region fill color',
    `region_fill_opacity` decimal(2,1) DEFAULT NULL COMMENT 'Region fill opacity',
    `is_excludelists` enum('A','F','N') CHARACTER SET latin1 NOT NULL DEFAULT 'N' COMMENT 'exclude from lists',
    `is_publicity` enum('ANYONE','VISITOR','EDITOR','OWNER','ROOT') CHARACTER SET latin1 NOT NULL DEFAULT 'ANYONE' COMMENT 'visibility of region',
    -- экстра-контент
    `content_json` longtext COMMENT 'JSON-структура с прочим контентом',

    PRIMARY KEY (`id`,`edit_date`),
    KEY `id_region` (`id_region`),
    KEY `id_map` (`id_map`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_remembered` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user` int unsigned NOT NULL,
    `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `expires` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_resets` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user` int unsigned NOT NULL,
    `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `expires` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `user_expires` (`user`,`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_throttling` (
    `bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
    `tokens` float unsigned NOT NULL,
    `replenished_at` int unsigned NOT NULL,
    `expires_at` int unsigned NOT NULL,
    PRIMARY KEY (`bucket`),
    KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

