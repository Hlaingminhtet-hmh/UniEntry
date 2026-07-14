<?php
include 'supabase.php'; // SUPABASE_URL နဲ့ SUPABASE_KEY ပါတဲ့ဖိုင်ဖြစ်ရပါမယ်
include 'admin_header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ၁။ Form data များကို ယူခြင်း
   // ၁။ Form data များကို ယူခြင်း
$data = [
    'event_name'       => $_POST['event_name'],
    'event_date'       => $_POST['event_date'],
    'event_time'       => $_POST['event_time'],
    'location'         => $_POST['location'],
    'description'      => $_POST['description'],
    'max_participants' => (int)($_POST['max_participants'] ?? 0)  // ဒါအသစ်ထည့်
];

    // ၂။ Supabase API သို့ ပို့ရန် URL (Table အမည်က events ဖြစ်ရပါမယ်)
    $url = SUPABASE_URL . "/rest/v1/events";

   $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // ဒီစာကြောင်း (၂) ကြောင်းကို ထပ်ထည့်ပေးပါ
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ၃။ အောင်မြင်မှု ရှိမရှိ စစ်ဆေးခြင်း (201 Created)
    if ($httpCode == 201) {
    // Event အသစ်အကြောင်း ကျောင်းသားတွေဆီ Email ပို့မယ်
    $email_sent = send_new_event_notification($data);
    
    if ($email_sent) {
        echo "<script>alert('Event ထည့်ပြီးပါပြီ။ ကျောင်းသား " . $email_sent . " ဦးဆီကို Email ပို့ပြီးပါပြီ။'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Event ထည့်ပြီးပါပြီ။ သို့သော် Email ပို့ရာတွင် ပြဿနာရှိခဲ့ပါသည်။'); window.location='admin.php';</script>";
    }
}
}
?>
<style>
    /* Header နဲ့ တစ်သားတည်းဖြစ်အောင် မင်းရဲ့ variable တွေကို ပြန်သုံးထားပါတယ် */
    body {
        background-color: #0f172a; /* --bg-dark */
        margin: 0;
        font-family: 'Inter', sans-serif;
    }

    .main-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 80px); /* Header ရဲ့ အမြင့်ကို ဖယ်ပြီး ကျန်တာကို အလည်ပို့တာပါ */
    }

    .event-card {
        background: #1e293b; /* --card-bg */
        width: 100%;
        max-width: 450px;
        padding: 40px;
        border-radius: 16px;
        border: 1px solid #334155;
        box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    }

    .event-card h2 {
        color: #3ecf8e; /* --primary */
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #94a3b8; /* --text-muted */
        margin-bottom: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        color: white;
        font-size: 0.95rem;
        transition: 0.3s;
        box-sizing: border-box;
    }

    .form-group input:focus {
        border-color: #3ecf8e;
        box-shadow: 0 0 0 2px rgba(62, 207, 142, 0.1);
        outline: none;
    }

    .submit-btn {
        width: 100%;
        padding: 14px;
        background: #3ecf8e;
        border: none;
        border-radius: 8px;
        color: #0f172a;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        font-size: 1rem;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(62, 207, 142, 0.2);
    }

    .submit-btn:hover {
        background: #2eb87e;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(62, 207, 142, 0.3);
    }

    .back-btn {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #64748b;
        text-decoration: none;
        font-size: 0.9rem;
        transition: 0.3s;
    }

    .back-btn:hover {
        color: #3ecf8e;
    }

    /* Date နဲ့ Time ကို ယှဉ်လျက်ပြဖို့ */
    .row-flex {
        display: flex;
        gap: 15px;
    }
    .row-flex .form-group {
        flex: 1;
    }
</style>

<div class="main-container">
    <div class="event-card">
        <h2>Add Event</h2>
        <form method="POST">
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name" placeholder="E.g. Freshers' Welcome" required>
            </div>

            <div class="row-flex">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="event_date" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="event_time">
                </div>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="E.g. Main Hall">
            </div>

            <div class="form-group">
    <label>ပါဝင်နိုင်သော လူဦးရေ အများဆုံး (Max Participants)</label>
    <input type="number" name="max_participants" min="0" value="0" placeholder="0 ဆိုရင် အကန့်အသတ်မရှိ">
    <small style="color: #64748b; display: block; margin-top: 5px;">သတ်မှတ်မထားချင်ရင် 0 ထားပါ</small>
</div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Briefly explain about event..."></textarea>
            </div>

            <button type="submit" class="submit-btn">CREATE EVENT</button>
            <a href="admin.php" class="back-btn">Cancel and return</a>
        </form>
    </div>
</div>