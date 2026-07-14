<?php 
require_once 'supabase.php'; 

// Database မှ ကျောင်းသားစာရင်းကို ဆွဲထုတ်ခြင်း (Approved သူတွေပဲ)
$students = get_approved_students(); 

// အရေအတွက်တွေ တွက်ချက်ခြင်း
$approved_count = get_approved_count();     // Approved ဖြစ်တဲ့သူ အရေအတွက်
$pending_count = get_pending_count();       // Pending ဖြစ်တဲ့သူ အရေအတွက်
$total_registered = get_total_students_count(); // စုစုပေါင်း
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Students List | UCS Monywa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3ecf8e;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-light: #f8fafc;
            --accent: #38bdf8;
            --pending: #f59e0b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            margin: 0;
            padding: 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* Header နဲ့ မကပ်အောင် */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 { 
            color: var(--primary); 
            margin: 0; 
            font-size: 2rem; 
            font-weight: 800; 
        }

        .page-header p {
            color: #64748b;
            margin: 5px 0 0 0;
            font-size: 0.95rem;
        }

        /* Stats Cards */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: var(--bg-dark);
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

        .stat-total .stat-number {
            color: var(--accent);
        }

        .stat-pending {
            border-left: 4px solid var(--pending);
        }

        .stat-pending .stat-number {
            color: var(--pending);
        }

        /* Search Box - ဒါအသစ်ထည့်တာ */
        .search-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
            background: var(--bg-dark);
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid #334155;
        }

        .results-count {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .results-count strong {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .search-box {
            position: relative;
            width: 100%;
            max-width: 350px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: var(--card-bg);
            border: 1px solid #334155;
            border-radius: 50px;
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
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            opacity: 0.5;
            z-index: 1;
            color: white;
        }

        .search-box input::placeholder {
            color: #64748b;
            font-style: italic;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #334155;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        
        th {
            text-align: left;
            padding: 18px 20px;
            background: rgba(15, 23, 42, 0.8);
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
            font-weight: 700;
            border-bottom: 2px solid #334155;
        }
        
        td { 
            padding: 16px 20px; 
            border-bottom: 1px solid #334155; 
            color: #cbd5e1; 
            font-size: 0.9rem; 
        }
        
        tr:hover { 
            background: rgba(255,255,255,0.02); 
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            background: rgba(62, 207, 142, 0.15);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            border: 1px solid rgba(62, 207, 142, 0.3);
        }

        .id-text { 
            color: var(--accent); 
            font-family: 'Courier New', monospace; 
            font-weight: 600; 
            font-size: 0.9rem;
        }

        .back-btn {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            transition: 0.3s;
            background: rgba(255,255,255,0.05);
            padding: 8px 16px;
            border-radius: 30px;
            width: fit-content;
        }
        .back-btn:hover { 
            color: white; 
            background: rgba(255,255,255,0.1);
        }

        .no-data { 
            text-align: center; 
            padding: 60px; 
            color: #64748b; 
            font-size: 1.1rem;
        }

        .summary-footer {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 25px;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid #334155;
            padding-top: 20px;
        }

        .summary-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .search-section {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: 100%;
            }
            .summary-footer {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
            .stats-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?> 
    <div class="main-container">
        <div class="container">
            
            <!-- Back Button -->
            <a href="admin.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <!-- Page Header -->
            <div class="page-header">
                <h2>Active Students</h2>
                <p>အတည်ပြုပြီးသား ကျောင်းသားများစာရင်း</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card stat-approved">
                    <div class="stat-label"><i class="fas fa-check-circle"></i> APPROVED STUDENTS</div>
                    <div class="stat-number">
                        <?php echo $approved_count; ?> <small>active</small>
                    </div>
                    <div style="color: #3ecf8e; font-size: 0.8rem; margin-top: 8px;">
                        <i class="fas fa-user-check"></i> အတည်ပြုပြီးသား
                    </div>
                </div>

                <div class="stat-card stat-total">
                    <div class="stat-label"><i class="fas fa-users"></i> TOTAL REGISTERED</div>
                    <div class="stat-number">
                        <?php echo $total_registered; ?> <small>students</small>
                    </div>
                    <div style="color: #38bdf8; font-size: 0.8rem; margin-top: 8px;">
                        <i class="fas fa-clipboard-list"></i> စုစုပေါင်း စာရင်းသွင်းပြီးသူ
                    </div>
                </div>

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

            <!-- Search Section - ဒါအသစ်ထည့်တာ -->
            <div class="search-section">
                <div class="results-count">
                    <i class="fas fa-users" style="color: var(--primary);"></i> 
                    ပြသနေသော ကျောင်းသား: <strong><?php echo count($students); ?></strong> ဦး
                </div>
                
                <div class="search-box">
                    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by name, ID, email, major...">
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-container">
                <table id="studentTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Major</th>
                            <th>Email</th>
                            <th>Registered Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $row): ?>
                                <tr>
                                    <td class="id-text">
                                        <?php echo htmlspecialchars($row['student_id'] ?? 'TBA'); ?>
                                    </td>
                                    <td style="font-weight: 600; color: white;">
                                        <?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td>
                                        <span style="color: var(--accent); background: rgba(56, 189, 248, 0.1); padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($row['courses']['course_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td style="color: #94a3b8;">
                                        <?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (!empty($row['created_at'])) {
                                                $date = new DateTime($row['created_at']);
                                                echo $date->format('d M Y');
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status-badge">
                                            <i class="fas fa-check-circle" style="font-size: 0.7rem;"></i> Approved
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">
                                    <i class="fas fa-user-slash fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i>
                                    <br>🎓 အတည်ပြုပြီးသား ကျောင်းသားစာရင်း မရှိသေးပါ။
                                </td>
                            </tr>
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
        </div>
    </div>

    <!-- Search Filter Script -->
    <script>
        function filterTable() {
            // ရှာဖွေမည့် စာသားကို ယူမယ်
            let input = document.getElementById("searchInput");
            let filter = input.value.toUpperCase();
            
            // Table နဲ့ row တွေကို ယူမယ်
            let table = document.getElementById("studentTable");
            let tr = table.getElementsByTagName("tr");
            
            // ရှာတွေ့သော ရလဒ်အရေအတွက်
            let visibleCount = 0;

            // ပထမ row (thead) ကို ကျော်ပြီး tbody ထဲက row တွေကို စစ်မယ်
            for (let i = 1; i < tr.length; i++) {
                let rowText = tr[i].innerText.toUpperCase();
                
                if (rowText.indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    visibleCount++;
                } else {
                    tr[i].style.display = "none";
                }
            }

            // ရှာတွေ့သော အရေအတွက်ကို update လုပ်မယ်
            let resultsSpan = document.querySelector('.results-count strong');
            if (resultsSpan) {
                resultsSpan.textContent = visibleCount;
            }

            // ဘာမှမရှိရင် "No results" message ပြမယ်
            if (visibleCount === 0 && tr.length > 1) {
                // အကယ်၍ no-data row မရှိသေးရင် ထည့်ပေးမယ်
                let tbody = table.getElementsByTagName('tbody')[0];
                let noResultRow = document.getElementById('no-result-row');
                
                if (!noResultRow) {
                    let newRow = tbody.insertRow(0);
                    newRow.id = 'no-result-row';
                    newRow.style.display = '';
                    let cell = newRow.insertCell(0);
                    cell.colSpan = 6;
                    cell.className = 'no-data';
                    cell.innerHTML = '<i class="fas fa-search fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i><br>🔍 ရှာဖွေတွေ့ရှိချက် မရှိပါ။';
                }
            } else {
                let noResultRow = document.getElementById('no-result-row');
                if (noResultRow) {
                    noResultRow.remove();
                }
            }
        }
    </script>
</body>
</html>