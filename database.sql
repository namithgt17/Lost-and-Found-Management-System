-- Run in phpMyAdmin (select your database first), or: mysql -u USER -p DBNAME < database.sql
-- Lost and found entries live in separate tables so no "type"/category column is needed.
-- `id` is only an internal row key (a USN can submit many items).

CREATE TABLE IF NOT EXISTS `lost_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usn` VARCHAR(20) NOT NULL,
  `object_name` VARCHAR(150) NOT NULL,
  `location_found` VARCHAR(200) NOT NULL,
  `collect_location` VARCHAR(200) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lost_usn` (`usn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `found_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usn` VARCHAR(20) NOT NULL,
  `object_name` VARCHAR(150) NOT NULL,
  `location_found` VARCHAR(200) NOT NULL,
  `collect_location` VARCHAR(200) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_found_usn` (`usn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
