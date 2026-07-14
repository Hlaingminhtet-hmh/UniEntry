<?php
// Guest Header အတွက် Session စစ်စရာမလိုသလို Redirect လုပ်စရာလည်း မလိုပါ
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
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-links a {
            color: #f8fafc;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        /* Login Button Style */
        .login-link {
            border-right: 1px solid var(--border);
            padding-right: 20px;
        }

        /* Enroll Now Button Style */
        .enroll-btn {
            background: var(--primary);
            color: #0f172a !important;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700 !important;
            transition: all 0.3s ease;
        }

        .enroll-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(62, 207, 142, 0.3);
            background: #2eb87e !important;
        }
    </style>
</head>
<body>

<nav>
    <a href="home.php" class="logo">UCS MONYWA</a>
    
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="home.php">About</a>

        <a href="login.php" class="login-link">Login</a>

        <a href="register.php" class="enroll-btn">
            Enroll Now <i class="fas fa-user-plus" style="margin-left: 5px;"></i>
        </a>
    </div>
</nav>

</body>
</html>