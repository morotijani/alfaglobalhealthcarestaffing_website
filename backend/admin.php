<?php
session_start();
$password = 'admin123'; // Simple hardcoded password for demo

if (isset($_POST['login'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Admin Login - Alfa Global</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f3f4f6; margin: 0; }
            .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; width: 100%; max-width: 340px; border: 1px solid #e5e7eb;}
            .logo-icon { width: 48px; height: 48px; color: #2563eb; margin-bottom: 16px; }
            h2 { margin: 0 0 8px; color: #111827; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;}
            p { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
            input[type="password"] { padding: 12px 16px; width: 100%; margin-bottom: 16px; display: block; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 15px; background: #f9fafb; transition: all 0.2s;}
            input[type="password"]:focus { outline: none; border-color: #2563eb; background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
            button { padding: 12px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 15px; font-weight: 600; transition: all 0.2s;}
            button:hover { background: #1d4ed8; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
            .error { color: #ef4444; font-size: 14px; margin-bottom: 16px; font-weight: 500; background: #fee2e2; padding: 8px; border-radius: 6px;}
        </style>
    </head>
    <body>
        <div class="login-box">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            <h2>Welcome Back</h2>
            <p>Enter your password to access the dashboard. (Hint: admin123)</p>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Password" required autofocus>
                <button type="submit" name="login">Log In</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Admin Panel Logic
require_once __DIR__ . '/db.php';

// Handle Job Deletion
if (isset($_GET['delete_job'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['delete_job']]);
    header("Location: admin.php#jobs");
    exit;
}

// Handle Job Addition
if (isset($_POST['add_job'])) {
    $stmt = $pdo->prepare("INSERT INTO jobs (title, location, type, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['title'], $_POST['location'], $_POST['type'], $_POST['description']]);
    header("Location: admin.php#jobs");
    exit;
}

// Handle Submission Deletions
if (isset($_GET['delete_contact'])) {
    $stmt = $pdo->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->execute([$_GET['delete_contact']]);
    header("Location: admin.php#contact");
    exit;
}
if (isset($_GET['delete_staff'])) {
    $stmt = $pdo->prepare("DELETE FROM staff_submissions WHERE id = ?");
    $stmt->execute([$_GET['delete_staff']]);
    header("Location: admin.php#staff");
    exit;
}
if (isset($_GET['delete_join'])) {
    $stmt = $pdo->prepare("DELETE FROM join_submissions WHERE id = ?");
    $stmt->execute([$_GET['delete_join']]);
    header("Location: admin.php#join");
    exit;
}

// Fetch Data
$jobs = $pdo->query("SELECT * FROM jobs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$contact_subs = $pdo->query("SELECT * FROM contact_submissions ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$staff_subs = $pdo->query("SELECT * FROM staff_submissions ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$join_subs = $pdo->query("SELECT * FROM join_submissions ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_subs = count($contact_subs) + count($staff_subs) + count($join_subs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alfa Global</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --danger-hover: #b91c1c;
            --bg: #f3f4f6;
            --surface: #ffffff;
            --border: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --sidebar-width: 260px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: var(--text-main); display: flex; height: 100vh; overflow: hidden;}
        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .sidebar-header svg { width: 32px; height: 32px; color: var(--primary); }
        .sidebar-header h2 { margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main); letter-spacing: -0.5px; }
        .nav-menu { flex: 1; padding: 20px 0; overflow-y: auto; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.2s; }
        .nav-item:hover, .nav-item.active { background: #f0fdf4; color: var(--primary); border-right: 3px solid var(--primary); }
        .nav-item svg { width: 20px; height: 20px; }
        .sidebar-footer { padding: 20px 24px; border-top: 1px solid var(--border); }
        .logout { color: var(--danger); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px;}
        .logout:hover { color: var(--danger-hover); }
        .logout svg { width: 20px; height: 20px; }
        
        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: var(--surface); height: 70px; border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 32px; justify-content: space-between;}
        .topbar h1 { font-size: 20px; font-weight: 600; margin: 0; }
        .content-scroll { flex: 1; overflow-y: auto; padding: 32px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Cards */
        .card { background: var(--surface); border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 32px; overflow: hidden;}
        .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); background: #f9fafb; display: flex; justify-content: space-between; align-items: center;}
        .card-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: var(--text-main); }
        .card-body { padding: 24px; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px 24px; text-align: left; border-bottom: 1px solid var(--border); font-size: 14px; }
        th { background: var(--surface); font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fafb; }
        
        /* Forms & Buttons */
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;}
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
        .btn-danger { background: #fee2e2; color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: white; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text-muted); }
        input, textarea, select { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 14px; box-sizing: border-box; background: #f9fafb; transition: all 0.2s;}
        input:focus, textarea:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-contact { background: #dbeafe; color: #1e40af; }
        .badge-staff { background: #dcfce7; color: #166534; }
        .badge-join { background: #fef9c3; color: #854d0e; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state svg { width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5; }
        /* Tabs */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            <h2>Alfa Global</h2>
        </div>
        <div class="nav-menu">
            <a href="#dashboard" class="nav-item active" onclick="showTab('dashboard')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Dashboard
            </a>
            <a href="#jobs" class="nav-item" onclick="showTab('jobs')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                Live Vacancies
            </a>
            <a href="#contact" class="nav-item" onclick="showTab('contact')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                Contact Inbox
            </a>
            <a href="#staff" class="nav-item" onclick="showTab('staff')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Staffing Requests
            </a>
            <a href="#join" class="nav-item" onclick="showTab('join')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
                Clinician Join
            </a>
        </div>
        <div class="sidebar-footer">
            <a href="admin.php?logout=1" class="logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Log Out
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
        </div>
        
        <div class="content-scroll">
            <div class="container">
                
                <div id="tab-dashboard" class="tab-content active">
                    <!-- Stats Row -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
                        <div class="card" style="margin:0;">
                            <div class="card-body">
                                <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">Total Vacancies</div>
                                <div style="font-size: 28px; font-weight: 700; color: var(--text-main); margin-top: 8px;"><?= count($jobs) ?></div>
                            </div>
                        </div>
                        <div class="card" style="margin:0; cursor:pointer;" onclick="showTab('contact')">
                            <div class="card-body">
                                <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">Contact Inbox</div>
                                <div style="font-size: 28px; font-weight: 700; color: #1e40af; margin-top: 8px;"><?= count($contact_subs) ?></div>
                            </div>
                        </div>
                        <div class="card" style="margin:0; cursor:pointer;" onclick="showTab('staff')">
                            <div class="card-body">
                                <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">Staffing Requests</div>
                                <div style="font-size: 28px; font-weight: 700; color: #166534; margin-top: 8px;"><?= count($staff_subs) ?></div>
                            </div>
                        </div>
                        <div class="card" style="margin:0; cursor:pointer;" onclick="showTab('join')">
                            <div class="card-body">
                                <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">Clinician Join</div>
                                <div style="font-size: 28px; font-weight: 700; color: #854d0e; margin-top: 8px;"><?= count($join_subs) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-jobs" class="tab-content">
                    <!-- Add Job Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Publish New Vacancy</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Job Title</label>
                                        <input type="text" name="title" placeholder="e.g. ICU Registered Nurse" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Category / Specialty</label>
                                        <input type="text" name="type" placeholder="e.g. Nursing" required>
                                    </div>
                                    <div class="form-group full">
                                        <label>Location</label>
                                        <input type="text" name="location" placeholder="e.g. Dubai, UAE" required>
                                    </div>
                                    <div class="form-group full">
                                        <label>Job Description / Contract Type</label>
                                        <textarea name="description" rows="3" placeholder="e.g. Contract, 12 months" required></textarea>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; text-align: right;">
                                    <button type="submit" name="add_job" class="btn btn-primary">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        Publish Job
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Jobs Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Active Job Listings</h3>
                        </div>
                        <table style="margin:0;">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Details</th>
                                <th width="80"></th>
                            </tr>
                            <?php foreach($jobs as $job): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                <td><span class="badge badge-gray"><?= htmlspecialchars($job['type']) ?></span></td>
                                <td><?= htmlspecialchars($job['location']) ?></td>
                                <td style="color: var(--text-muted);"><?= htmlspecialchars($job['description']) ?></td>
                                <td>
                                    <a href="admin.php?delete_job=<?= $job['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this job?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($jobs)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                                        <p>No active job listings.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div id="tab-contact" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Contact Form Submissions</h3>
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="margin:0;">
                                <tr>
                                    <th width="150">Date</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Topic</th>
                                    <th>Message</th>
                                    <th width="80"></th>
                                </tr>
                                <?php foreach($contact_subs as $sub): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 13px;"><?= date('M j, Y g:i A', strtotime($sub['created_at'])) ?></td>
                                    <td><strong><?= htmlspecialchars($sub['name']) ?></strong></td>
                                    <td><a href="mailto:<?= htmlspecialchars($sub['email']) ?>" style="color: var(--primary); text-decoration: none;"><?= htmlspecialchars($sub['email']) ?></a><br><span style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($sub['phone']) ?></span></td>
                                    <td><span class="badge badge-gray"><?= htmlspecialchars($sub['topic']) ?></span></td>
                                    <td style="max-width: 300px; line-height: 1.5; color: var(--text-muted);"><?= nl2br(htmlspecialchars($sub['message'])) ?></td>
                                    <td><a href="admin.php?delete_contact=<?= $sub['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete?');">Delete</a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($contact_subs)): ?><tr><td colspan="6"><div class="empty-state"><p>No contact submissions.</p></div></td></tr><?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-staff" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Staffing Requests</h3>
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="margin:0;">
                                <tr>
                                    <th width="120">Date</th>
                                    <th>Organization</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Role Details</th>
                                    <th>Message</th>
                                    <th width="80"></th>
                                </tr>
                                <?php foreach($staff_subs as $sub): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 13px;"><?= date('M j, Y H:i', strtotime($sub['created_at'])) ?></td>
                                    <td><strong><?= htmlspecialchars($sub['org']) ?></strong><br><span style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($sub['ftype']) ?></span></td>
                                    <td><?= htmlspecialchars($sub['name']) ?><br><span style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($sub['title']) ?></span><br><a href="mailto:<?= htmlspecialchars($sub['email']) ?>" style="font-size:13px; color:var(--primary); text-decoration:none;"><?= htmlspecialchars($sub['email']) ?></a><br><span style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($sub['phone']) ?></span></td>
                                    <td><?= htmlspecialchars($sub['loc']) ?></td>
                                    <td><span class="badge badge-staff"><?= htmlspecialchars($sub['role']) ?></span><br><span style="color: var(--text-muted); font-size: 13px;">Count: <?= htmlspecialchars($sub['count']) ?> (<?= htmlspecialchars($sub['ctype']) ?>)<br>Start: <?= htmlspecialchars($sub['start']) ?></span></td>
                                    <td style="min-width: 200px; line-height: 1.5; color: var(--text-muted); font-size: 13px;"><?= nl2br(htmlspecialchars($sub['message'])) ?></td>
                                    <td><a href="admin.php?delete_staff=<?= $sub['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete?');">Delete</a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($staff_subs)): ?><tr><td colspan="7"><div class="empty-state"><p>No staffing requests.</p></div></td></tr><?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-join" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Clinician Join Applications</h3>
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="margin:0;">
                                <tr>
                                    <th width="120">Date</th>
                                    <th>Applicant</th>
                                    <th>Location</th>
                                    <th>Specialty</th>
                                    <th>Experience</th>
                                    <th>Preferences</th>
                                    <th>Message</th>
                                    <th width="80"></th>
                                </tr>
                                <?php foreach($join_subs as $sub): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 13px;"><?= date('M j, Y H:i', strtotime($sub['created_at'])) ?></td>
                                    <td><strong><?= htmlspecialchars($sub['name']) ?></strong><br><a href="mailto:<?= htmlspecialchars($sub['email']) ?>" style="font-size:13px; color:var(--primary); text-decoration:none;"><?= htmlspecialchars($sub['email']) ?></a><br><span style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($sub['phone']) ?></span></td>
                                    <td><?= htmlspecialchars($sub['country']) ?></td>
                                    <td><span class="badge badge-join"><?= htmlspecialchars($sub['prof']) ?></span><br><span style="font-size:13px; font-weight:600;"><?= htmlspecialchars($sub['specialty']) ?></span></td>
                                    <td style="font-size: 13px; color: var(--text-muted);">Exp: <?= htmlspecialchars($sub['exp']) ?><br>Lic: <?= htmlspecialchars($sub['licence']) ?></td>
                                    <td style="font-size: 13px; color: var(--text-muted);">Regions: <?= htmlspecialchars($sub['regions']) ?><br>Type: <?= htmlspecialchars($sub['ctype']) ?><br>Avail: <?= htmlspecialchars($sub['avail']) ?></td>
                                    <td style="min-width: 200px; line-height: 1.5; color: var(--text-muted); font-size: 13px;"><?= nl2br(htmlspecialchars($sub['message'])) ?></td>
                                    <td><a href="admin.php?delete_join=<?= $sub['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete?');">Delete</a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($join_subs)): ?><tr><td colspan="8"><div class="empty-state"><p>No clinician applications.</p></div></td></tr><?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    function showTab(id) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + id).classList.add('active');
        document.querySelector('.nav-item[href="#' + id + '"]').classList.add('active');
        window.location.hash = id;
    }
    // Handle page load hash
    if(window.location.hash) {
        let hash = window.location.hash.substring(1);
        if(document.getElementById('tab-' + hash)) {
            showTab(hash);
        }
    }
    </script>
</body>
</html>
