-- Run this in phpMyAdmin on database: if0_41373306_contactbook

USE `if0_41373306_contactbook`;

ALTER TABLE `contacts`
    ADD COLUMN IF NOT EXISTS `approval_status` ENUM('pending','approved','rejected') DEFAULT 'pending' AFTER `statuz`,
    ADD COLUMN IF NOT EXISTS `dob`             VARCHAR(10)  DEFAULT '' AFTER `owner_id`,
    ADD COLUMN IF NOT EXISTS `gender`          VARCHAR(10)  DEFAULT '' AFTER `dob`,
    ADD COLUMN IF NOT EXISTS `father_name`     VARCHAR(150) DEFAULT '' AFTER `gender`,
    ADD COLUMN IF NOT EXISTS `mother_name`     VARCHAR(150) DEFAULT '' AFTER `father_name`;

-- Also create contacts_pending table if not exists
CREATE TABLE IF NOT EXISTS `contacts_pending` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `contact_id`   INT NOT NULL,
    `change_type`  ENUM('create','update','delete') NOT NULL,
    `change_data`  LONGTEXT NOT NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at`  TIMESTAMP NULL DEFAULT NULL,
    `review_note`  VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing rows to approved so they show on main page
UPDATE `contacts` SET `approval_status` = 'approved' WHERE `approval_status` = 'pending' OR `approval_status` IS NULL OR `approval_status` = '';

-- Verify columns added
DESCRIBE `contacts`;
