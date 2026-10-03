<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (!$data) $data = $_POST;
    
    if ($action === 'contactForm') {
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, topic, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'] ?? '', $data['email'] ?? '', $data['phone'] ?? '', 
            $data['topic'] ?? '', $data['msg'] ?? ''
        ]);
        echo json_encode(['success' => true]); exit;
    }

    if ($action === 'staffForm') {
        $stmt = $pdo->prepare("INSERT INTO staff_submissions (org, ftype, name, title, email, phone, loc, role, count, ctype, start, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['org'] ?? '', $data['ftype'] ?? '', $data['name'] ?? '', $data['title'] ?? '',
            $data['email'] ?? '', $data['phone'] ?? '', $data['loc'] ?? '', $data['role'] ?? '',
            $data['count'] ?? '', $data['ctype'] ?? '', $data['start'] ?? '', $data['msg'] ?? ''
        ]);
        echo json_encode(['success' => true]); exit;
    }

    if ($action === 'joinForm') {
        $stmt = $pdo->prepare("INSERT INTO join_submissions (name, email, phone, country, prof, specialty, exp, licence, regions, ctype, avail, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $regions = isset($data['regions']) ? (is_array($data['regions']) ? implode(', ', $data['regions']) : $data['regions']) : '';
        $stmt->execute([
            $data['name'] ?? '', $data['email'] ?? '', $data['phone'] ?? '', $data['country'] ?? '',
            $data['prof'] ?? '', $data['specialty'] ?? '', $data['exp'] ?? '', $data['licence'] ?? '',
            $regions, $data['ctype'] ?? '', $data['avail'] ?? '', $data['msg'] ?? ''
        ]);
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
