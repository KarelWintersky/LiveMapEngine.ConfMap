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
```

