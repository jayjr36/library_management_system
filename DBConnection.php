<?php
$db_dir = __DIR__ . '/db';
if (!is_dir($db_dir)) {
    mkdir($db_dir);
}
if (!defined('db_file')) {
    define('db_file', $db_dir . '/opac_db.db');
}
if (!defined('tZone')) {
    define('tZone', "Asia/Manila");
}
if (!defined('dZone')) {
    define('dZone', ini_get('date.timezone'));
}

function my_udf_md5($string) {
    return md5($string);
}

class DBConnection extends mysqli {
    function __construct() {
        parent::__construct('localhost', 'root', '', 'opac');
       // $this->createFunction('md5', 'my_udf_md5');

       // $this->query("SET time_zone = '".tZone."';");
        $this->query("SET FOREIGN_KEY_CHECKS = 1;");

        $this->query("CREATE TABLE IF NOT EXISTS `admin_list` (
            `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
            `fullname` VARCHAR(255) NOT NULL,
            `username` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `status` INT NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INT AUTO_INCREMENT PRIMARY KEY,
            `fullname` VARCHAR(255) NOT NULL,
            `username` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `contact` VARCHAR(255) NOT NULL,
            `address` VARCHAR(255) NOT NULL,
            `department` VARCHAR(255) NOT NULL,
            `type` VARCHAR(255) NOT NULL,
            `level_section` VARCHAR(255) DEFAULT NULL,
            `status` INT NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `category_list` (
            `category_id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `status` INT NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `sub_category_list` (
            `sub_category_id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `status` INT NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `category_list`(`category_id`) ON DELETE CASCADE
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `book_list` (
            `book_id` INT AUTO_INCREMENT PRIMARY KEY,
            `sub_category_id` INT NOT NULL,
            `isbn` VARCHAR(255) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `author` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `status` INT NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`sub_category_id`) REFERENCES `sub_category_list`(`sub_category_id`) ON DELETE CASCADE
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `cart_list` (
            `user_id` INT NOT NULL,
            `book_id` INT NOT NULL,
            FOREIGN KEY (`user_id`) REFERENCES `user_list`(`user_id`) ON DELETE CASCADE,
            FOREIGN KEY (`book_id`) REFERENCES `book_list`(`book_id`) ON DELETE CASCADE
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `borrowed_list` (
            `borrowed_id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `transaction_code` VARCHAR(255) NOT NULL,
            `due_date` DATE NULL DEFAULT NULL,
            `status` INT NOT NULL DEFAULT 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `user_list`(`user_id`) ON DELETE CASCADE
        )");

        $this->query("CREATE TABLE IF NOT EXISTS `borrowed_items` (
            `borrowed_id` INT NOT NULL,
            `book_id` INT NOT NULL,
            FOREIGN KEY (`borrowed_id`) REFERENCES `borrowed_list`(`borrowed_id`) ON DELETE CASCADE,
            FOREIGN KEY (`book_id`) REFERENCES `book_list`(`book_id`) ON DELETE CASCADE
        )");

        $this->query("CREATE TRIGGER IF NOT EXISTS updatedTime_book AFTER UPDATE ON `book_list`
            FOR EACH ROW BEGIN
                UPDATE `book_list` SET `date_updated` = CURRENT_TIMESTAMP WHERE `book_id` = OLD.`book_id`;
            END;
        ");

        $this->query("INSERT IGNORE INTO `admin_list` VALUES (1, 'Administrator', 'admin', md5('admin123'), 1, CURRENT_TIMESTAMP)");
    }

    function __destruct() {
        $this->close();
    }
}

$conn = new DBConnection();
