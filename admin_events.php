<?php
session_start();

// ၁။ Admin Login စစ်ဆေးခြင်း
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'supabase.php';
include 'admin_header.php';

// ၂။ Supabase မှ Events များ ဆွဲထုတ်သည့် Function
function get_all_events() {
    $url = SUPABASE_URL . "/rest/v1/events?select=*&order=event_date.asc";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$events = get_all_events();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3ecf8e;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-muted: #94a3b8;
            --info: #3b82f6;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .title {
            color: var(--primary);
            font-size: 1.6rem;
            font-weight: 800;
        }

        .btn-add-modern {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(62, 207, 142, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .btn-add-modern:hover {
            background: var(--primary);
            color: var(--bg-dark);
            transform: translateY(-3px);
        }

        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid #334155;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: rgba(15, 23, 42, 0.5);
            padding: 18px 20px;
            text-align: left;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid #334155;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #334155;
            font-size: 0.95rem;
        }

        .event-info h4 { margin: 0; color: var(--primary); font-size: 1.1rem; }
        .event-meta { font-size: 0.85rem; color: var(--text-muted); margin-top: 5px; display: flex; gap: 15px; }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-view-students {
            color: var(--info);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 15px;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            transition: 0.3s;
        }

        .btn-view-students:hover {
            background: var(--info);
            color: white;
        }

        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 15px;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <div class="title">Manage School Events</div>
        <a href="add_event.php" class="btn-add-modern">
            <i class="fas fa-plus-circle"></i> ADD NEW EVENT
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event Details</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($events) && is_array($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <div class="event-info">
                                    <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                    <div class="event-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?php echo date("d M Y", strtotime($event['event_date'])); ?></span>
                                        <span><i class="far fa-clock"></i> <?php echo $event['event_time'] ?: 'TBA'; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="color: #f8fafc;"><i class="fas fa-map-marker-alt" style="color: #ef4444; margin-right: 5px;"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            </td>
                            <td>
                                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; max-width: 250px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                            </td>
                            <td>
    <div class="action-btns">
        <a href="admin_event_list.php?event_id=<?php echo $event['id']; ?>" class="btn-view-students">
            <i class="fas fa-users"></i> List
        </a>

        

        <?php
        // ဒီ Event ကို Join ထားတဲ့ ကျောင်းသားအရေအတွက် စစ်မယ်
        $participant_count = get_event_participant_count($event['id']);
        ?>
        
        <?php if ($participant_count == 0): ?>
            <!-- ကျောင်းသားမရှိရင်မှ Delete Button ပြမယ် -->
            <a href="delete_event_logic.php?id=<?php echo $event['id']; ?>" 
               class="btn-delete" 
               onclick="return confirm('ဤပွဲကို ဖျက်ပစ်ရန် သေချာပါသလား?')">
                <i class="far fa-trash-alt"></i> Delete
            </a>
        <?php else: ?>
            <!-- ကျောင်းသားရှိနေရင် Disabled Button ပြမယ် -->
            <span class="btn-delete-disabled" 
                  style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 700; padding: 8px 15px; border-radius: 8px; background: rgba(100, 116, 139, 0.1); border: 1px solid rgba(100, 116, 139, 0.2); cursor: not-allowed;"
                  title="ဤ Event တွင် ကျောင်းသား <?php echo $participant_count; ?> ဦး Join ထားသောကြောင့် မဖျက်နိုင်ပါ">
                <i class="fas fa-ban"></i> Delete
            </span>
        <?php endif; ?>
    </div>
</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 60px; color: var(--text-muted);">
                            ပွဲအသစ်များ မရှိသေးပါ။
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>