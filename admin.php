<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
require_once 'supabase.php';

// ကျောင်းသားအားလုံးကို ယူမယ် (pending ရော approved ရော)
$students = get_students();

// အရေအတွက်တွေ တွက်ချက်ခြင်း
$approved_count = get_approved_count();     // Approved ဖြစ်တဲ့သူ အရေအတွက်
$pending_count = get_pending_count();       // Pending ဖြစ်တဲ့သူ အရေအတွက်
$total_registered = get_total_students_count(); // စုစုပေါင်း

// Status အရ 'pending' ကို ထိပ်ဆုံးပို့ရန် Sort လုပ်ခြင်း
if (!empty($students)) {
    usort($students, function($a, $b) {
        // pending ကို အရင်ပြချင်တာဖြစ်လို့ status ကို နှိုင်းယှဉ်မယ်
        if ($a['status'] === 'pending' && $b['status'] !== 'pending') return -1;
        if ($a['status'] !== 'pending' && $b['status'] === 'pending') return 1;
        
        // status တူနေရင် ရက်စွဲအလိုက် အသစ်ဆုံးကို ထိပ်မှာထားမယ်
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | UCS Monywa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3ecf8e;
            --danger: #ef4444;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --accent: #38bdf8;
            --pending: #f59e0b;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg); 
            color: var(--text); 
            margin: 0; 
            padding: 0; /* ဒီမှာ padding ဖြုတ်လိုက်တယ် */
        }

        /* Main Container - ဒါအသစ်ထည့်တာ */
        .main-container {
            max-width: 1400px;  /* Screen အပြည့်မဖြစ်အောင် limit လုပ်တာ */
            margin: 0 auto;      /* အလယ်ညှိပေးတာ */
            padding: 30px 20px;  /* အပေါ်အောက် ဘယ်ညာ padding */
        }

        /* Stats Cards */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-top: 30px;     /* Header နဲ့ မကပ်အောင် margin-top ထည့်တာ */
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 25px 30px;
            border: 1px solid #334155;
            flex: 1;
            min-width: 200px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .stat-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(62, 207, 142, 0.1);
        }

        .stat-label {
            color: #94a3b8;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .stat-number small {
            font-size: 1rem;
            color: #64748b;
            font-weight: 400;
        }

        .stat-approved {
            border-left: 4px solid var(--primary);
        }

        .stat-total {
            border-left: 4px solid var(--accent);
        }

        .stat-pending {
            border-left: 4px solid var(--pending);
        }

        .stat-pending .stat-number {
            color: var(--pending);
        }

        .stat-total .stat-number {
            color: var(--accent);
        }

        /* Search Box */
        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: 20px 0 30px 0;  /* အောက်ဘက် margin ထည့်တာ */
        }

        .search-box {
            position: relative;
            width: 100%;
            max-width: 450px;
        }

        .search-box input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 50px;    /* ဝိုင်းဝိုင်းလေး ဖြစ်အောင် */
            color: white;
            outline: none;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
        }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            opacity: 0.5;
            z-index: 1;
        }

        /* Table Container */
        .table-container { 
            background: var(--card); 
            border-radius: 20px; 
            overflow: hidden; 
            border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
            margin-top: 20px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left; 
        }
        
        th { 
            background: rgba(15, 23, 42, 0.9); 
            color: var(--primary); 
            padding: 18px 20px; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1px; 
        }
        
        td { 
            padding: 16px 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            font-size: 0.9rem; 
            color: #cbd5e1; 
        }
        
        tr:hover { 
            background: rgba(255,255,255,0.02); 
        }

        .id-text { 
            color: var(--accent); 
            font-family: 'Courier New', monospace; 
            font-weight: bold; 
        }
        
        .badge { 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-size: 0.75rem; 
            font-weight: 600; 
        }
        
        .btn-action { 
            text-decoration: none; 
            font-size: 0.85rem; 
            font-weight: 600; 
            transition: 0.2s; 
            padding: 6px 12px; 
            border-radius: 6px; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-approve { 
            color: var(--primary); 
            background: rgba(62, 207, 142, 0.1); 
            border: 1px solid rgba(62, 207, 142, 0.2);
        }
        
        .btn-delete { 
            color: var(--danger); 
            background: rgba(239, 68, 68, 0.1); 
            border: 1px solid rgba(239, 68, 68, 0.2);
            margin-left: 8px; 
        }
        
        .btn-action:hover { 
            opacity: 0.9; 
            transform: translateY(-2px); 
        }

        .status-pill { 
            padding: 5px 14px; 
            border-radius: 30px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: var(--pending);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-approved {
            background: rgba(62, 207, 142, 0.15);
            color: var(--primary);
            border: 1px solid rgba(62, 207, 142, 0.3);
        }

        .summary-footer {
            margin-top: 25px;
            text-align: right;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid #334155;
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 20px;
        }

        .summary-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) { 
            .table-container { overflow-x: auto; } 
            .stats-container { flex-direction: column; }
            .main-container { padding: 20px 15px; }
            .summary-footer { flex-direction: column; align-items: flex-end; gap: 10px; }
        }

        /* Welcome Text လေး ထည့်ထားတယ် */
        .welcome-text {
            margin-bottom: 10px;
        }
        .welcome-text h1 {
            color: var(--primary);
            font-size: 1.8rem;
            margin: 0;
        }
        .welcome-text p {
            color: #64748b;
            margin: 5px 0 0 0;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?> 

    <!-- Main Container - ဒါအသစ်ထည့်တာ -->
    <div class="main-container">

        <!-- Welcome Text -->
        <div class="welcome-text">
            <h1>Admin Dashboard</h1>
            <p>Welcome back! Here's the overview of student registrations.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <!-- Approved Students Card -->
            <div class="stat-card stat-approved">
                <div class="stat-label"><i class="fas fa-check-circle"></i> APPROVED STUDENTS</div>
                <div class="stat-number">
                    <?php echo $approved_count; ?> <small>active</small>
                </div>
                <div style="color: #3ecf8e; font-size: 0.8rem; margin-top: 8px;">
                    <i class="fas fa-user-check"></i> အတည်ပြုပြီးသား
                </div>
            </div>

            <!-- Total Registered Card -->
            <div class="stat-card stat-total">
                <div class="stat-label"><i class="fas fa-users"></i> TOTAL REGISTERED</div>
                <div class="stat-number">
                    <?php echo $total_registered; ?> <small>students</small>
                </div>
                <div style="color: #38bdf8; font-size: 0.8rem; margin-top: 8px;">
                    <i class="fas fa-clipboard-list"></i> စုစုပေါင်း စာရင်းသွင်းပြီးသူ
                </div>
            </div>

            <!-- Pending Approval Card -->
            <div class="stat-card stat-pending">
                <div class="stat-label"><i class="fas fa-clock"></i> PENDING APPROVAL</div>
                <div class="stat-number">
                    <?php echo $pending_count; ?> <small>waiting</small>
                </div>
                <div style="color: #f59e0b; font-size: 0.8rem; margin-top: 8px;">
                    <i class="fas fa-hourglass-half"></i> စောင့်ဆိုင်းဆဲ
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search students by name, ID, email...">
            </div>
        </div>

        <!-- Students Table -->
        <div class="table-container">
            <table id="adminTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name & Email</th>
                        <th>Major</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $row): ?>
                        <tr>
                            <td class="id-text"><?php echo htmlspecialchars($row['student_id'] ?? 'TBA'); ?></td>
                            <td>
                                <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;">
                                    <?php echo htmlspecialchars($row['courses']['course_name'] ?? 'General'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <span class="status-pill status-pending">⏳ Pending</span>
                                <?php else: ?>
                                    <span class="status-pill status-approved">✅ Approved</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="approve_logic.php?id=<?php echo $row['id']; ?>" 
                                           class="btn-action btn-approve"
                                           onclick="return confirm('ဤကျောင်းသားကို Approve လုပ်မှာ သေချာပါသလား?')">
                                           <i class="fas fa-check-circle"></i> Approve
                                        </a>
                                    <?php endif; ?>

                                    <a href="delete_logic.php?id=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete"
                                       onclick="return confirm('ဤကျောင်းသားကို စာရင်းမှ ဖျက်ပစ်မှာ သေချာပါသလား?')">
                                       <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 50px; color: #64748b;">📭 ဒေတာ မရှိသေးပါ။</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary Footer -->
        <div class="summary-footer">
            <span class="summary-item">
                <i class="fas fa-users" style="color: #64748b;"></i> စုစုပေါင်း: <strong style="color: white;"><?php echo $total_registered; ?></strong>
            </span>
            <span class="summary-item">
                <i class="fas fa-check-circle" style="color: #3ecf8e;"></i> Approved: <strong style="color: white;"><?php echo $approved_count; ?></strong>
            </span>
            <span class="summary-item">
                <i class="fas fa-clock" style="color: #f59e0b;"></i> Pending: <strong style="color: white;"><?php echo $pending_count; ?></strong>
            </span>
        </div>

    </div> <!-- Main Container End -->

    <script>
        function filterTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("adminTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let rowText = tr[i].innerText.toUpperCase();
                if (rowText.indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>