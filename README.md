# WTF

```bash
export VERSION=8.3
apt install php${VERSION}-{fpm,gd}
```

# SQL Migrations

```sql
-- applied 2024-09-27 20:23:00
USE livemap_confmap;
ALTER TABLE map_data_regions CHANGE content_json content_extra longtext NULL COMMENT 'Extra content (JSONized для confmap)';
ALTER TABLE map_data_regions CHANGE have_json_content is_display_extra_content tinyint DEFAULT 0 NOT NULL COMMENT 'is Extra Content visible?';
ALTER TABLE map_data_regions MODIFY COLUMN edit_whois int NULL COMMENT 'ID редактора (0 - root)';
ALTER TABLE map_data_regions MODIFY COLUMN edit_ipv4 int unsigned NULL COMMENT 'IPv4 редактора';
```

