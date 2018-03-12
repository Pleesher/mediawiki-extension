CREATE TABLE IF NOT EXISTS /*_*/pleesher_cache (
`user_id` VARCHAR(255) NOT NULL,
`key` VARCHAR(50) NOT NULL,
`id` VARCHAR(50) NOT NULL,
`data` MEDIUMTEXT NOT NULL,
`obsolete` BIT(1) NOT NULL,
PRIMARY KEY (`user_id`, `key`, `id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
DATA DIRECTORY='/var/lib/mysql/innodb_data';