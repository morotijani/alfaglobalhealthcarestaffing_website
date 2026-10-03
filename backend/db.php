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

    // Insert sample jobs if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM jobs");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO jobs (title, location, type, description) VALUES 
            ('Registered Nurse (RN) - Med/Surg', 'New York, NY', 'Full-time', 'Looking for an experienced RN for a busy Med/Surg unit.'),
            ('Travel ICU Nurse', 'Los Angeles, CA', 'Contract (13 weeks)', 'High-paying travel assignment for ICU experienced RNs.'),
            ('Certified Nursing Assistant (CNA)', 'Chicago, IL', 'Part-time', 'Flexible shifts available in long-term care facilities.')
        ");
    }

    // Insert sample contacts if empty
    if ($pdo->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO contact_submissions (name, email, phone, topic, message) VALUES 
            ('John Doe', 'john.doe@example.com', '+1 (555) 123-4567', 'General Inquiry', 'I would like to know more about your international staffing capabilities and how long the process typically takes.'),
            ('Jane Smith', 'jane.smith@example.com', '+44 20 7123 4567', 'Partnership', 'Can we discuss a potential partnership between our organizations for placing medical professionals in the UK?')
        ");
    }

    // Insert sample staffing requests if empty
    if ($pdo->query("SELECT COUNT(*) FROM staff_submissions")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO staff_submissions (org, ftype, name, title, email, phone, loc, role, count, ctype, start, message) VALUES 
            ('City General Hospital', 'Hospital', 'Dr. Alice', 'HR Director', 'alice@citygen.example.com', '555-0101', 'New York, NY', 'Registered Nurse (RN)', '5-10', 'Contract', 'Next month', 'We are urgently looking for experienced RNs for our intensive care unit to cover a seasonal shortage.'),
            ('Sunset Care Home', 'Care Home', 'Bob Miller', 'Facility Manager', 'bob@sunsetcare.example.com', '555-0202', 'Miami, FL', 'Care Assistant', '1-4', 'Permanent', 'Immediately', 'Looking for caring and dedicated staff for elderly residents. Immediate start required.')
        ");
    }

    // Insert sample join applications if empty
    if ($pdo->query("SELECT COUNT(*) FROM join_submissions")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO join_submissions (name, email, phone, country, prof, specialty, exp, licence, regions, ctype, avail, message) VALUES 
            ('Emily Chen', 'emily.chen@example.com', '555-0303', 'Canada', 'Nurse', 'Pediatrics', '3-5 years', 'Registered, Active', 'North America, UK', 'Permanent', 'In 3 months', 'I am relocating and looking for pediatric nursing opportunities. My current licence is in good standing.'),
            ('Michael Brown', 'michael.b@example.com', '555-0404', 'UK', 'Doctor', 'Cardiology', '10+ years', 'GMC Registered', 'Middle East', 'Contract', 'Immediately', 'Looking for short-term contracts in the Middle East. I have previous experience working in Dubai.')
        ");
    }

} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . " - Please make sure MySQL is running in XAMPP.");
}
?>
