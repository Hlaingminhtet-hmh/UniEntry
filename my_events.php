<?php
session_start();
require_once 'supabase.php';

if (!isset($_SESSION['student_id'])) {
    die("Error: Login Session မရှိပါ။ Login ပြန်ဝင်ပေးပါ။");
}

$student_id = $_SESSION['student_id'];
$student_email = $_SESSION['student_email'] ?? "No Email";

// Method 1: Relationship ကို အတိအကျ သတ်မှတ်မယ်
$url = SUPABASE_URL . "/rest/v1/event_registrations?" . http_build_query([
    'student_id' => 'eq.' . $student_id,
    'select' => '*,events!event_registrations_event_id_fkey(*)'
]);

// Method 2: ဒါမှမဟုတ် ဒီလိုသုံးမယ်
// $url = SUPABASE_URL . "/rest/v1/event_registrations?" . http_build_query([
//     'student_id' => 'eq.' . $student_id,
//     'select' => '*,events!event_registrations_event_id_fkey1(*)'
// ]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . SUPABASE_KEY,
    'Authorization: Bearer ' . SUPABASE_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$registrations = [];
if ($httpCode === 200) {
    $registrations = json_decode($response, true) ?: [];
}

// Events ကို စုစည်းမယ်
$my_events = [];
foreach ($registrations as $reg) {
    if (isset($reg['events']) && !empty($reg['events'])) {
        $my_events[] = $reg['events'];
    }
}

// Method 3: အဆင်မပြေရင် ဒီနည်းသုံးမယ် (သီးခြားစီ query လုပ်မယ်)
if (empty($my_events)) {
    // အရင်ဆုံး registrations ယူမယ်
    $reg_url = SUPABASE_URL . "/rest/v1/event_registrations?student_id=eq." . $student_id . "&select=*";
    
    $ch2 = curl_init($reg_url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ]);
    
    $reg_response = curl_exec($ch2);
    curl_close($ch2);
    
    $registrations_only = json_decode($reg_response, true) ?: [];
    
    // ပြီးမှ event details တွေကို သီးခြားယူမယ်
    foreach ($registrations_only as $reg) {
        $event_id = $reg['event_id'];
        $event_url = SUPABASE_URL . "/rest/v1/events?id=eq." . $event_id . "&select=*";
        
        $ch3 = curl_init($event_url);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch3, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY
        ]);
        
        $event_response = curl_exec($ch3);
        curl_close($ch3);
        
        $event_data = json_decode($event_response, true) ?: [];
        if (!empty($event_data)) {
            $my_events[] = $event_data[0];
        }
    }
}
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Joined Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #3ecf8e; --bg: #0f172a; --card: #1e293b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: white; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .event-card { 
            background: var(--card); border-radius: 12px; padding: 20px; margin-bottom: 15px;
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid rgba(255,255,255,0.1); 
        }
        .badge { color: var(--primary); font-weight: bold; }
        .debug-box {
            background: #2d3748;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 12px;
            color: #a0eec0;
            white-space: pre-wrap;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-primary {
            background: var(--primary);
            color: #0f172a;
        }
        .btn-secondary {
            background: #334155;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        
        
        <h1 style="color:var(--primary); margin-top:20px;">My Joined Events</h1>
        <p style="color:#64748b;">Account: <?php echo htmlspecialchars($student_email); ?></p>
        
        <?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
        <div class="debug-box">
            <strong>Debug Information:</strong><br>
            Student ID: <?php echo $student_id; ?><br>
            Registrations Found: <?php echo count($registrations); ?><br>
            Events Found: <?php echo count($my_events); ?><br>
            <hr>
            <strong>API Response:</strong><br>
            <textarea style="width:100%; height:150px; background:#1e293b; color:#a0ec9a; border:none; padding:10px;" readonly><?php echo htmlspecialchars($response); ?></textarea>
        </div>
        <?php endif; ?>

        <?php if (!empty($my_events)): ?>
            <?php foreach ($my_events as $event): ?>
                <div class="event-card">
                    <div>
                        <h3 style="margin:0;"><?php echo htmlspecialchars($event['event_name'] ?? 'No Name'); ?></h3>
                        <p style="color:#94a3b8; font-size:0.9rem; margin-top:5px;">
                            <i class="far fa-calendar"></i> <?php echo htmlspecialchars($event['event_date'] ?? 'No Date'); ?> | 
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location'] ?? 'No Location'); ?>
                        </p>
                    </div>
                    <div class="badge">JOINED ✅</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:60px 20px; background:var(--card); border-radius:15px;">
                <i class="fas fa-calendar-times fa-4x" style="color:#64748b; margin-bottom:20px;"></i>
                <h3 style="color:#94a3b8;">No Joined Events Yet</h3>
                <p style="color:#64748b; margin-bottom:30px;">
                    You haven't joined any events yet. Browse available events and join now!
                </p>
                <a href="events.php" class="btn btn-primary" style="padding:12px 30px;">Browse Events</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>