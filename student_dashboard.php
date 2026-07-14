<?php
session_start();
require_once 'supabase.php';

// ၁။ ကျောင်းသား Login စစ်ဆေးခြင်း
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// ၂။ Supabase မှ Events များ ဆွဲထုတ်ခြင်း
function get_student_events() {
    $url = SUPABASE_URL . "/rest/v1/events?select=*&order=event_date.asc";
    
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

    if ($httpCode == 200) {
        return json_decode($response, true);
    } else {
        return []; // Error ဖြစ်ရင် array အလွတ်ပြန်ပေးမယ်
    }
}

$events = get_student_events();

// Header ကို include လုပ်ပါ (မင်းရဲ့ project အလိုက် header.php ဖြစ်နိုင်ပါတယ်)
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3ecf8e;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-muted: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .welcome-section { margin-bottom: 40px; }
        .welcome-section h1 { color: var(--primary); font-size: 2rem; }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .event-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid #334155;
            transition: 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .event-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }

        .event-body { padding: 25px; flex-grow: 1; }
        
        .date-badge {
            background: rgba(62, 207, 142, 0.1);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 800;
            display: inline-block;
        }

        .event-card h3 { margin: 15px 0 10px; font-size: 1.3rem; }
        
        .event-meta { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 5px; }
        .event-meta i { margin-right: 8px; width: 15px; }

        .event-footer {
            padding: 20px 25px;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid #334155;
        }

        .btn-join {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: var(--bg-dark);
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 800;
            transition: 0.3s;
        }

        .btn-join:hover {
            background: #2eb87e;
            transform: scale(1.02);
        }

        .no-data {
            text-align: center;
            grid-column: 1 / -1;
            padding: 100px 0;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="welcome-section">
        <h1>Welcome Back! 👋</h1>
        <p style="color: var(--text-muted);">Explore and join upcoming school events.</p>
    </div>

    <div class="event-grid">
        <?php if (!empty($events)): ?>
           <?php foreach ($events as $event): 
    // လက်ရှိပါဝင်သူဦးရေ ရေတွက်မယ်
    $current_count = get_event_participant_count($event['id']);
    $max_participants = $event['max_participants'] ?? 0;
    $is_full = ($max_participants > 0 && $current_count >= $max_participants);
?>
    <div class="event-card">
        <div class="event-body">
            <div class="date-badge">
                <i class="far fa-calendar-alt"></i> 
                <?php echo date("d M Y", strtotime($event['event_date'])); ?>
            </div>
            <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
            <div class="event-meta">
                <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> 
                <?php echo htmlspecialchars($event['location']); ?>
            </div>
            <div class="event-meta">
                <i class="far fa-clock"></i> 
                <?php echo $event['event_time'] ?: 'TBA'; ?>
            </div>
            
            <!-- လူဦးရေအခြေအနေ ပြမယ် -->
            <div class="event-meta" style="margin-top: 10px;">
                <i class="fas fa-users"></i> 
                <?php echo $current_count; ?> / <?php echo $max_participants > 0 ? $max_participants : 'အကန့်အသတ်မရှိ'; ?>
                <?php if ($max_participants > 0): ?>
                    <span style="color: <?php echo $is_full ? '#ef4444' : '#3ecf8e'; ?>; margin-left: 5px;">
                        (<?php echo $is_full ? 'လူပြည့်' : ($max_participants - $current_count) . ' နေရာလွတ်'; ?>)
                    </span>
                <?php endif; ?>
            </div>
            
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 15px; line-height: 1.5;">
                <?php echo htmlspecialchars($event['description']); ?>
            </p>
        </div>
        <div class="event-footer">
            <?php if ($is_full): ?>
                <button class="btn-join" style="background: #64748b; cursor: not-allowed;" disabled>
                    <i class="fas fa-ban"></i> လူပြည့်ပြီ
                </button>
            <?php else: ?>
                <a href="join_event.php?event_id=<?php echo $event['id']; ?>" 
                   class="btn-join" 
                   onclick="return confirm('Join လုပ်မှာ သေချာလား?')">
                   <i class="fas fa-calendar-check"></i> JOIN EVENT
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-calendar-times" style="font-size: 4rem; opacity: 0.2; margin-bottom: 20px;"></i>
                <p>No upcoming events at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>