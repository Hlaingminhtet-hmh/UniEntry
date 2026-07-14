<?php
session_start();
require_once 'supabase.php';

// ၁။ Admin မဟုတ်လျှင် Login Page သို့ ပြန်ပို့မည်
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// ၂။ URL မှ event_id ကို ရယူခြင်း
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

if (!$event_id) {
    header("Location: admin_events.php");
    exit();
}

/**
 * Supabase API မှ Data ဆွဲထုတ်ရန် Function
 */
function fetchData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ]);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) return [];
    
    return json_decode($res, true) ?: [];
}

// ၃။ ပွဲအသေးစိတ် (Event Details) ကို ရယူခြင်း
$event_url = SUPABASE_URL . "/rest/v1/events?id=eq." . $event_id . "&select=*";
$event_data = fetchData($event_url);
$event_details = !empty($event_data) ? $event_data[0] : null;

// ၄။ Join ထားသော ကျောင်းသားစာရင်းကို ရယူခြင်း (Relationship ပြဿနာကို ဖြေရှင်းပြီး)
// Relationship နှစ်ခုရှိနေလို့ fkey ကို အတိအကျ သတ်မှတ်ပေးရမယ်
$participants_url = SUPABASE_URL . "/rest/v1/event_registrations?event_id=eq." . $event_id . "&select=*,students!event_registrations_student_id_fkey(*)";

$participants = fetchData($participants_url);

// Error မတက်စေရန် Participants ကို Array ပြောင်းထားခြင်း
if (!is_array($participants)) {
    $participants = [];
}

// Debug mode (URL မှာ ?debug=1 ထည့်ရင် ပြမယ်)
$debug = isset($_GET['debug']) && $_GET['debug'] == 1;

// Method 2: အဆင်မပြေရင် ဒီနည်းသုံးမယ် (သီးခြားစီ query)
if (empty($participants) && $debug) {
    // အရင်ဆုံး registrations ယူမယ်
    $reg_url = SUPABASE_URL . "/rest/v1/event_registrations?event_id=eq." . $event_id . "&select=*";
    $registrations = fetchData($reg_url);
    
    // ပြီးမှ student details တွေကို သီးခြားယူမယ်
    $participants = [];
    foreach ($registrations as $reg) {
        $student_id = $reg['student_id'];
        $student_url = SUPABASE_URL . "/rest/v1/students?id=eq." . $student_id . "&select=*";
        $student_data = fetchData($student_url);
        
        if (!empty($student_data)) {
            $participants[] = [
                'students' => $student_data[0],
                'registration_date' => $reg['registration_date'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
}

// ဒါမှမဟုတ် Method 3: join နဲ့ဆွဲကြည့်မယ် (ဒုတိယ fkey)
if (empty($participants)) {
    $participants_url2 = SUPABASE_URL . "/rest/v1/event_registrations?event_id=eq." . $event_id . "&select=*,students!event_registrations_student_id_fkey1(*)";
    $participants = fetchData($participants_url2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Participants | Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #3ecf8e; 
            --bg: #0f172a; 
            --card: #1e293b; 
            --text-muted: #94a3b8;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg); 
            color: white; 
            padding: 40px 20px; 
            margin: 0; 
        }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 25px;
            transition: 0.3s;
        }
        .back-link:hover { color: var(--primary); }

        .header-card { 
            background: var(--card); 
            padding: 30px; 
            border-radius: 16px; 
            border-left: 6px solid var(--primary); 
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header-card h1 { 
            margin: 0; 
            font-size: 1.8rem; 
            color: var(--primary);
        }
        .header-card .meta {
            margin-top: 15px;
            display: flex;
            gap: 25px;
            color: var(--text-muted);
            font-size: 0.9rem;
            flex-wrap: wrap;
        }
        .header-card .meta i {
            margin-right: 6px;
            color: var(--primary);
        }

        .table-container {
            background: var(--card);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #334155;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left;
        }
        th { 
            background: rgba(15, 23, 42, 0.8); 
            color: var(--primary); 
            padding: 18px 20px; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 600;
        }
        td { 
            padding: 18px 20px; 
            border-bottom: 1px solid #334155;
            font-size: 0.95rem;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(62, 207, 142, 0.05); }

        .student-avatar {
            width: 36px;
            height: 36px;
            background: rgba(62, 207, 142, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: bold;
            margin-right: 12px;
        }

        .student-info {
            display: flex;
            align-items: center;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        .no-data i {
            display: block;
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.2;
        }
        .no-data h3 {
            color: white;
            margin-bottom: 8px;
        }

        .debug-box {
            background: #2d3748;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 12px;
            color: #a0ec9a;
            white-space: pre-wrap;
            border-left: 4px solid var(--primary);
        }

        .stats-box {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .stat-item {
            background: var(--card);
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid #334155;
            flex: 1;
            min-width: 150px;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-value {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?> 
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        
        <?php if ($debug): ?>
            <a href="?event_id=<?php echo $event_id; ?>" class="back-link" style="color: var(--primary);">
                <i class="fas fa-times"></i> Turn Off Debug
            </a>
        <?php else: ?>
           
        <?php endif; ?>
    </div>

    <?php if ($debug): ?>
    <div class="debug-box">
        <strong>🔍 Debug Information:</strong><br>
        Event ID: <?php echo $event_id; ?><br>
        Participants Found: <?php echo count($participants); ?><br>
        API URL: <?php echo htmlspecialchars($participants_url); ?><br>
        Raw Data: <pre style="max-height: 200px; overflow: auto;"><?php echo htmlspecialchars(print_r($participants, true)); ?></pre>
    </div>
    <?php endif; ?>

    <?php if ($event_details): ?>
    <div class="header-card">
        <h1><?php echo htmlspecialchars($event_details['event_name']); ?></h1>
        <div class="meta">
            <span><i class="far fa-calendar-alt"></i> <?php echo date("d M Y", strtotime($event_details['event_date'])); ?></span>
            <?php if (!empty($event_details['event_time'])): ?>
            <span><i class="far fa-clock"></i> <?php echo date("h:i A", strtotime($event_details['event_time'])); ?></span>
            <?php endif; ?>
            <?php if (!empty($event_details['location'])): ?>
            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event_details['location']); ?></span>
            <?php endif; ?>
            <span><i class="fas fa-users"></i> Total Participants: <?php echo count($participants); ?></span>
        </div>
        <?php if (!empty($event_details['description'])): ?>
        <p style="color: var(--text-muted); margin-top: 15px; padding-top: 15px; border-top: 1px solid #334155;">
            <?php echo nl2br(htmlspecialchars($event_details['description'])); ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- Stats Cards -->
    <div class="stats-box">
        <div class="stat-item">
            <div class="stat-label">Total Participants</div>
            <div class="stat-value"><?php echo count($participants); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Registration Date</div>
            <div class="stat-value" style="font-size: 1rem; padding-top: 10px;"><?php echo date("d M Y", strtotime($event_details['event_date'])); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Student Information</th>
                    <th>Email Address</th>
                    <th>Registration Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($participants)): ?>
                    <?php foreach ($participants as $index => $row): ?>
                        <?php 
                        // Check if students data exists in different possible formats
                        $student = null;
                        if (isset($row['students']) && is_array($row['students'])) {
                            $student = $row['students'];
                        } elseif (isset($row['student']) && is_array($row['student'])) {
                            $student = $row['student'];
                        }
                        
                        if ($student): 
                        ?>
                        <tr>
                            <td><strong style="color: var(--primary);"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <?php echo strtoupper(substr($student['name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong style="color: #f8fafc; font-size: 1rem;">
                                            <?php echo htmlspecialchars($student['name'] ?? 'Unknown'); ?>
                                        </strong>
                                        <?php if (!empty($student['student_id'])): ?>
                                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 3px;">
                                            ID: <?php echo htmlspecialchars($student['student_id']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php echo htmlspecialchars($student['email'] ?? 'No Email'); ?>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php 
                                    if (isset($row['registration_date']) && !empty($row['registration_date'])) {
                                        echo date('d M Y, h:i A', strtotime($row['registration_date']));
                                    } else {
                                        echo '<span style="color: #64748b;">N/A</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <span style="background: rgba(62, 207, 142, 0.1); color: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    JOINED
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">
                            <i class="fas fa-user-slash"></i>
                            <h3>No Participants Yet</h3>
                            <p>ဒီ Event မှာ Join ထားတဲ့ ကျောင်းသားစာရင်း မရှိသေးပါ။</p>
                            <?php if ($debug): ?>
                            <p style="font-size: 0.8rem; margin-top: 10px; color: var(--primary);">
                                Debug: API returned <?php echo count($participants); ?> records
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Export Section (Optional) -->
    <div style="margin-top: 20px; text-align: right;">
        <a href="#" onclick="window.print()" style="color: var(--text-muted); text-decoration: none; margin-left: 15px;">
            <i class="fas fa-print"></i> Print List
        </a>
    </div>
</div>

</body>
</html>