-- approvers_schema.sql
-- Run this in phpMyAdmin to add approvers table and audit columns

USE `if0_41373306_contactbook`;

-- ── Approvers table ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `approvers` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL,
    `mobile`        VARCHAR(20)  DEFAULT '',
    `username`      VARCHAR(50)  NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_active`     TINYINT(1)   DEFAULT 1,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Add audit columns to contacts_pending ────────────────────────────────────
ALTER TABLE `contacts_pending`
    ADD COLUMN IF NOT EXISTS `reviewed_by`      VARCHAR(100) DEFAULT NULL AFTER `reviewed_at`,
    ADD COLUMN IF NOT EXISTS `reviewer_name`    VARCHAR(100) DEFAULT NULL AFTER `reviewed_by`,
    ADD COLUMN IF NOT EXISTS `review_action`    ENUM('approved','rejected') DEFAULT NULL AFTER `reviewer_name`;

-- ── Add audit columns to contacts ────────────────────────────────────────────
ALTER TABLE `contacts`
    ADD COLUMN IF NOT EXISTS `approved_by`      VARCHAR(100) DEFAULT NULL AFTER `approval_status`,
    ADD COLUMN IF NOT EXISTS `approved_by_name` VARCHAR(100) DEFAULT NULL AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `approved_at`      TIMESTAMP    NULL DEFAULT NULL AFTER `approved_by_name`;

-- ── Insert default super-admin approver ──────────────────────────────────────
INSERT IGNORE INTO `approvers` (`name`,`email`,`mobile`,`username`,`password_hash`,`is_active`)
VALUES ('Super Admin','admin@contactbook.com','','admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
-- Default password: password (change immediately!)
