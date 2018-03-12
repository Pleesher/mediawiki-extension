CREATE TABLE IF NOT EXISTS /*_*/pleesher_setting (
 `key` VARCHAR(50) NOT NULL,
 `value` VARCHAR(255) NULL DEFAULT NULL,
 PRIMARY KEY (`key`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
DATA DIRECTORY='/var/lib/mysql/innodb_data';