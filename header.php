<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Login မဝင်ထားရင် Login page ကို ပြန်လွှတ်မယ် (Guest မပေးဝင်ချင်ရင် ဒါထည့်ပါ)
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['student_id'])) {
    if (basename($_SERVER['PHP_SELF']) != 'login.php') {
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3ecf8e;
            --bg: #0f172a;
            --border: #1e293b;
        }

        nav {
            background: var(--bg);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            color: var(--primary);
            font-size: 1.4rem;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-links a {
            color: #f8fafc;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .user-tag {
            background: rgba(62, 207, 142, 0.1);
            color: var(--primary);
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn {
            background: #ef4444;
            color: white !important;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700 !important;
        }

        .logout-btn:hover {
            background: #dc2626 !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">UCS MONYWA</a>
    
    <div class="nav-links">
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <a href="admin.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="admin_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <div class="user-tag">
                <i class="fas fa-user-shield"></i> ADMIN
            </div>

        <?php elseif (isset($_SESSION['student_id'])): ?>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="my_events.php">My Events</a>
            <a href="student_dashboard.php"><i class="fas fa-star"></i> All Events</a>
            <div class="user-tag">
                <i class="fas fa-user-graduate"></i> 
                <?php echo htmlspecialchars($_SESSION['student_name']); ?>
            </div>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

</body>
</html>