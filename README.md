# WTF

```bash
export VERSION=8.3
apt install php${VERSION}-{fpm,gd}
```

# SQL

```
ALTER TABLE map_data_regions CHANGE content_json content_extra longtext CHARACTER NULL COMMENT 'JSON-content';

ALTER TABLE map_data_regions CHANGE have_json_content is_display_extra_content tinyint DEFAULT 0 NOT NULL COMMENT 'Display extra content at View Region?';
ALTER TABLE map_data_regions MODIFY COLUMN is_display_extra_content tinyint DEFAULT 0 NOT NULL COMMENT 'Display extra content at View Region?';

ALTER TABLE livemap_confmap.map_data_regions MODIFY COLUMN edit_whois int NULL COMMENT 'ID редактора (0 - root)';
ALTER TABLE livemap_confmap.map_data_regions MODIFY COLUMN edit_ipv4 int unsigned NULL COMMENT 'IPv4 редактора';
```


