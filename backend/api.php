<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendConfirmationEmail($to, $name, $formType) {
    $mail = new PHPMailer(true);
    try {
        // Use SMTP if configured in .env
        if (!empty($_ENV['SMTP_HOST']) && $_ENV['SMTP_HOST'] !== 'smtp.example.com') {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            // If port is 587 use STARTTLS, otherwise use SMTPS (usually 465)
            $mail->SMTPSecure = ($_ENV['SMTP_PORT'] == 587) ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $_ENV['SMTP_PORT'];
        }

        $fromEmail = !empty($_ENV['SMTP_FROM_EMAIL']) ? $_ENV['SMTP_FROM_EMAIL'] : 'no-reply@alfaglobal.example.com';
        $fromName  = !empty($_ENV['SMTP_FROM_NAME']) ? $_ENV['SMTP_FROM_NAME'] : 'Alfa Global Staffing';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);

        if ($formType === 'contact') {
            $mail->Subject = 'We have received your inquiry - Alfa Global';
            $mail->Body    = "Dear $name,<br><br>Thank you for reaching out. We have received your inquiry and our team will get back to you shortly.<br><br>Best regards,<br>Alfa Global Team";
        } elseif ($formType === 'staff') {
            $mail->Subject = 'Staffing Request Received - Alfa Global';
            $mail->Body    = "Dear $name,<br><br>Thank you for your staffing request. Our recruitment specialists are reviewing your requirements and will contact you soon.<br><br>Best regards,<br>Alfa Global Team";
        } elseif ($formType === 'join') {
            $mail->Subject = 'Application Received - Alfa Global';
            $mail->Body    = "Dear $name,<br><br>Thank you for applying to join Alfa Global Healthcare Staffing. Our team will review your application and be in touch shortly.<br><br>Best regards,<br>Alfa Global Team";
        }
        $mail->send();
    } catch (Exception $e) {
        // Suppress email errors so the form still submits successfully
    }
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (!$data) $data = $_POST;
    
    if ($action === 'contactForm') {
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, topic, message) VALUES (?, ?, ?, ?, ?)");
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $stmt->execute([
            $name, $email, $data['phone'] ?? '', 
            $data['topic'] ?? '', $data['msg'] ?? ''
        ]);
        if (!empty($email)) sendConfirmationEmail($email, $name, 'contact');
        echo json_encode(['success' => true]); exit;
    }

    if ($action === 'staffForm') {
        $stmt = $pdo->prepare("INSERT INTO staff_submissions (org, ftype, name, title, email, phone, loc, role, count, ctype, start, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $stmt->execute([
            $data['org'] ?? '', $data['ftype'] ?? '', $name, $data['title'] ?? '',
            $email, $data['phone'] ?? '', $data['loc'] ?? '', $data['role'] ?? '',
            $data['count'] ?? '', $data['ctype'] ?? '', $data['start'] ?? '', $data['msg'] ?? ''
        ]);
        if (!empty($email)) sendConfirmationEmail($email, $name, 'staff');
        echo json_encode(['success' => true]); exit;
    }

    if ($action === 'joinForm') {
        $stmt = $pdo->prepare("INSERT INTO join_submissions (name, email, phone, country, prof, specialty, exp, licence, regions, ctype, avail, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $regions = isset($data['regions']) ? (is_array($data['regions']) ? implode(', ', $data['regions']) : $data['regions']) : '';
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $stmt->execute([
            $name, $email, $data['phone'] ?? '', $data['country'] ?? '',
            $data['prof'] ?? '', $data['specialty'] ?? '', $data['exp'] ?? '', $data['licence'] ?? '',
            $regions, $data['ctype'] ?? '', $data['avail'] ?? '', $data['msg'] ?? ''
        ]);
        if (!empty($email)) sendConfirmationEmail($email, $name, 'join');
        echo json_encode(['success' => true]); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'jobs') {
        $stmt = $pdo->query("SELECT * FROM jobs ORDER BY id DESC");
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($jobs);
        exit;
    }
}

echo json_encode(['error' => 'Invalid action']);
?>
