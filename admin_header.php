<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3ecf8e;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-light: #f8fafc;
            --accent: #38bdf8;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
        }

        nav {
            background: var(--bg-dark);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1e293b;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a.active {
            color: var(--primary);
            font-weight: 700;
        }

        .btn-add {
            background: rgba(62, 207, 142, 0.1);
            color: var(--primary) !important;
            border: 1px solid var(--primary);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem !important;
        }

        .btn-add:hover {
            background: var(--primary) !important;
            color: var(--bg-dark) !important;
        }

        .logout-link {
            color: #ef4444 !important; /* Red color for logout */
        }

        .logout-link:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">UCS MONYWA <span style="font-weight: 300; font-size: 0.8rem; color: #64748b;">| ADMIN</span></div>
    <div class="nav-links">
    <a href="admin_home.php">Home</a>
        <a href="admin.php">Dashboard</a>
         <!-- <a href="admin_event_list.php">View Joined Student</a> -->
    <a href="students_list.php">Students List (Approved)</a>
<!-- ဒါမှမဟုတ် အောက်က ကောင်ကိုသုံးပါ -->
         <a href="admin_events.php">Events List</a>
        <a href="add_event.php" class="btn-add">+ New Event</a>
       <a href="logout.php" 
           style="background: #ef4444; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: bold;"
           onclick="return confirm('Logout ထွက်မှာ သေချာပါသလား?')">
            Logout
        </a>
    </div>
</nav>