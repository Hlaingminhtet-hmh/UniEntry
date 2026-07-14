<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
require_once 'supabase.php';

// အရေအတွက်တွေ တွက်ချက်ခြင်း
$approved_count = get_approved_count();
$pending_count = get_pending_count();
$total_registered = get_total_students_count();
$events_count = get_events_count(); // Event အရေအတွက် ယူမယ်
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home | UCS Monywa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3ecf8e;
            --primary-dark: #2eb87e;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.8);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #38bdf8;
            --danger: #ef4444;
            --warning: #f59e0b;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text-light);
            min-height: 100vh;
            margin: 0;
            position: relative;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            animation: slowZoom 20s infinite alternate;
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        /* Floating particles effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% {
                transform: translateY(-100px) translateX(100px);
                opacity: 0;
            }
        }

        /* Generate particles */
        <?php for($i = 1; $i <= 20; $i++): ?>
        .particle:nth-child(<?php echo $i; ?>) {
            left: <?php echo rand(0, 100); ?>%;
            width: <?php echo rand(2, 6); ?>px;
            height: <?php echo rand(2, 6); ?>px;
            animation-duration: <?php echo rand(10, 30); ?>s;
            animation-delay: <?php echo rand(0, 10); ?>s;
            background: <?php echo rand(0, 1) ? 'var(--primary)' : 'var(--accent)'; ?>;
        }
        <?php endfor; ?>

        /* Main Container */
        .main-container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(62, 207, 142, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-section h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-section p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            line-height: 1.6;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(62, 207, 142, 0.1);
            border: 1px solid var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            margin-bottom: 20px;
        }

        .admin-badge i {
            color: var(--primary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(62, 207, 142, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(62, 207, 142, 0.2);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(62, 207, 142, 0.1);
            border: 1px solid var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .stat-icon i {
            font-size: 28px;
            color: var(--primary);
        }

        .stat-card h3 {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: white;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Rules Section */
        .rules-section {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            color: var(--primary);
            font-size: 2rem;
        }

        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .rule-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            transition: 0.3s;
        }

        .rule-card:hover {
            border-color: var(--primary);
            transform: translateX(10px);
        }

        .rule-icon {
            width: 50px;
            height: 50px;
            background: rgba(62, 207, 142, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .rule-icon i {
            font-size: 24px;
            color: var(--primary);
        }

        .rule-card h4 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }

        .rule-card p {
            color: var(--text-muted);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .rule-card ul {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }

        .rule-card ul li {
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .rule-card ul li i {
            color: var(--primary);
            font-size: 0.8rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .action-btn {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }

        .action-btn:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            background: rgba(62, 207, 142, 0.1);
        }

        .action-btn i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .action-btn span {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .action-btn small {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 60px;
            padding: 20px;
            border-top: 1px solid var(--card-border);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .rules-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <!-- Floating Particles -->
    <div class="particles">
        <?php for($i = 1; $i <= 20; $i++): ?>
        <div class="particle"></div>
        <?php endfor; ?>
    </div>

    <div class="main-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Administrator Access</span>
                </div>
                <h1>Welcome Back, Admin</h1>
                <p>Manage and oversee the university's student registration system, events, and approvals from this centralized dashboard.</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Total Students</h3>
                <div class="stat-number"><?php echo $total_registered; ?></div>
                <div class="stat-label">Registered in system</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Approved</h3>
                <div class="stat-number"><?php echo $approved_count; ?></div>
                <div class="stat-label">Active students</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Pending</h3>
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Awaiting approval</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Events</h3>
                <div class="stat-number"><?php echo $events_count ?? 0; ?></div>
                <div class="stat-label">Upcoming events</div>
            </div>
        </div>

        <!-- Admin Guidelines & Rules -->
        <div class="rules-section">
            <h2 class="section-title">
                <i class="fas fa-gavel"></i>
                Admin Guidelines & Best Practices
            </h2>

            <div class="rules-grid">
                <!-- Rule 1 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h4>Student Approval Protocol</h4>
                    <p>Follow these steps when approving new student registrations:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Verify student ID format (CU-XXXXX)</li>
                        <li><i class="fas fa-check"></i> Check email domain (@cu.edu.mm)</li>
                        <li><i class="fas fa-check"></i> Review submitted documents</li>
                        <li><i class="fas fa-check"></i> Confirm course selection</li>
                    </ul>
                </div>

                <!-- Rule 2 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h4>Event Management</h4>
                    <p>Best practices for creating and managing events:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Post events at least 1 week in advance</li>
                        <li><i class="fas fa-check"></i> Include clear description and location</li>
                        <li><i class="fas fa-check"></i> Set correct date and time</li>
                        <li><i class="fas fa-check"></i> Monitor participant count</li>
                    </ul>
                </div>

                <!-- Rule 3 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Security Guidelines</h4>
                    <p>Maintain system security and data integrity:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Never share admin credentials</li>
                        <li><i class="fas fa-check"></i> Log out after each session</li>
                        <li><i class="fas fa-check"></i> Regular password updates</li>
                        <li><i class="fas fa-check"></i> Monitor suspicious activities</li>
                    </ul>
                </div>

                <!-- Rule 4 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Response Time Standards</h4>
                    <p>Expected response times for different tasks:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Student approvals: Within 24 hours</li>
                        <li><i class="fas fa-check"></i> Event creation: 2 days before event</li>
                        <li><i class="fas fa-check"></i> Email responses: 48 hours max</li>
                        <li><i class="fas fa-check"></i> System updates: Weekly review</li>
                    </ul>
                </div>

                <!-- Rule 5 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h4>Data Management</h4>
                    <p>Guidelines for handling student data:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Regular backups verification</li>
                        <li><i class="fas fa-check"></i> Remove duplicate entries</li>
                        <li><i class="fas fa-check"></i> Update student records</li>
                        <li><i class="fas fa-check"></i> Archive old data monthly</li>
                    </ul>
                </div>

                <!-- Rule 6 -->
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-communication"></i>
                    </div>
                    <h4>Communication Etiquette</h4>
                    <p>Professional communication standards:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Use formal language in emails</li>
                        <li><i class="fas fa-check"></i> Include university signatures</li>
                        <li><i class="fas fa-check"></i> Respond professionally to queries</li>
                        <li><i class="fas fa-check"></i> Maintain confidentiality</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="admin.php" class="action-btn">
                <i class="fas fa-users"></i>
                <span>Manage Students</span>
                <small>View & approve registrations</small>
            </a>

            <a href="admin_events.php" class="action-btn">
                <i class="fas fa-calendar-alt"></i>
                <span>Manage Events</span>
                <small>Create & edit events</small>
            </a>

            <a href="students_list.php" class="action-btn">
                <i class="fas fa-user-graduate"></i>
                <span>Active Students</span>
                <small>View approved students</small>
            </a>

            <a href="add_event.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Event</span>
                <small>Create upcoming event</small>
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> University of Computer Studies, Monywa. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">Version 2.0 | Admin Portal</p>
        </div>
    </div>
</body>
</html>