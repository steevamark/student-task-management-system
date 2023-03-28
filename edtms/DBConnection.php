<?php
session_start();

if(!is_dir(__DIR__.'./db'))
    mkdir(__DIR__.'./db');
if(!defined('db_file')) define('db_file',__DIR__.'./db/edtms_db.db');
if(!defined('tZone')) define('tZone',"Asia/Manila");
if(!defined('dZone')) define('dZone',ini_get('date.timezone'));
function my_udf_md5($string) {
    return md5($string);
}

Class DBConnection extends SQLite3{
    protected $db;
    function __construct(){
        $this->open(db_file);
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        $this->exec("CREATE TABLE IF NOT EXISTS `admin_list` (
            `admin_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"); 
        $this->exec("CREATE TABLE IF NOT EXISTS `department_list` (
            `department_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` INTEGER NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `employee_list` (
            `employee_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `employee_code` INTEGER NOT NULL,
            `fullname` INTEGER NOT NULL,
            `email` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `gender` TEXT NOT NULL,
            `dob` DATE NOT NULL,
            `contact` TEXT NOT NULL,
            `address` TEXT NOT NULL,
            `department_id` INTEGER NOT NULL,
            `type` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Foreign key(`department_id`) references `department_list`(`department_id`) on DELETE CASCADE
        )"); 
        $this->exec("CREATE TABLE IF NOT EXISTS `task_list` (
            `task_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `task_code` TEXT NOT NULL,
            `title` TEXT NOT NULL,
            `description` TEXT NOT NULL,
            `department_id` INTEGER NOT NULL,
            `employee_id` INTEGER NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Foreign key(`department_id`) references `department_list`(`department_id`) on DELETE CASCADE,
            Foreign key(`employee_id`) references `employee_list`(`employee_id`) on DELETE SET NULL
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `task_assignees` (
            `task_id` INTEGER NOT NULL,
            `employee_id` INTEGER NOT NULL,
            Foreign key(`task_id`) references `task_list`(`task_id`) on DELETE CASCADE,
            Foreign key(`employee_id`) references `employee_list`(`employee_id`) on DELETE CASCADE
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `comment_list` (
            `comment_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `task_id` INTEGER NOT NULL,
            `employee_id` INTEGER NOT NULL,
            `message` INTEGER NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Foreign key(`task_id`) references `task_list`(`task_id`) on DELETE CASCADE,
            Foreign key(`employee_id`) references `employee_list`(`employee_id`) on DELETE CASCADE
        )");


        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_employee AFTER UPDATE on `employee_list`
        BEGIN
            UPDATE `employee_list` SET date_updated = CURRENT_TIMESTAMP where employee_id = employee_id;
        END
        ");
        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_task AFTER UPDATE on `task_list`
        BEGIN
            UPDATE `task_list` SET date_updated = CURRENT_TIMESTAMP where task_id = task_id;
        END
        ");

        $this->exec("INSERT or IGNORE INTO `admin_list` VALUES (1,'Administrator','admin',md5('admin123'),1, CURRENT_TIMESTAMP)");

    }
    function __destruct(){
         $this->close();
    }
}

$conn = new DBConnection();