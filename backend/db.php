<?php
$host = 'localhost';
$db   = 'staffing';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database and select it
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    $pdo->exec("USE `$db`");

    // Drop old unified submissions table if exists
    $pdo->exec("DROP TABLE IF EXISTS submissions");

    // Jobs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        location VARCHAR(255),
        type VARCHAR(255),
        description TEXT
    )");

    // Contact Submissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(255),
        topic VARCHAR(255),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Staffing Submissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        org VARCHAR(255),
        ftype VARCHAR(255),
        name VARCHAR(255),
        title VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(255),
        loc VARCHAR(255),
        role VARCHAR(255),
        count VARCHAR(255),
        ctype VARCHAR(255),
        start VARCHAR(255),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Join Submissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS join_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(255),
        country VARCHAR(255),
        prof VARCHAR(255),
        specialty VARCHAR(255),
        exp VARCHAR(255),
        licence VARCHAR(255),
        regions TEXT,
        ctype VARCHAR(255),
        avail VARCHAR(255),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");



} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . " - Please make sure MySQL is running in XAMPP.");
}
?>
