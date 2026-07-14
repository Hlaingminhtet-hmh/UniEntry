<?php
session_start();

// ၁။ Admin Login ဝင်ထားခြင်း ရှိမရှိ စစ်ဆေးခြင်း
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'supabase.php';

// ၂။ URL မှ ID ပါမပါ စစ်ဆေးခြင်း
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Debug: ID ကိုစစ်ကြည့်မယ်
    error_log("Deleting event ID: " . $id);

    // ၃။ Supabase DELETE API URL
    $url = SUPABASE_URL . "/rest/v1/events?id=eq." . $id;

    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Debug: Response ကိုစစ်ကြည့်မယ်
    error_log("Delete Response Code: " . $httpCode);
    error_log("Delete Response: " . $response);
    
    curl_close($ch);

    // ၄။ ရလဒ်ကို စစ်ဆေးပြီး Redirect လုပ်ခြင်း
    if ($httpCode >= 200 && $httpCode < 300) {
        // အောင်မြင်ရင်
        $_SESSION['success_message'] = "Event ကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။";
        header("Location: admin_events.php");
        exit();
    } else {
        // မအောင်မြင်ရင်
        $_SESSION['error_message'] = "Event ဖျက်၍ မရပါ။ Error Code: " . $httpCode;
        header("Location: admin_events.php");
        exit();
    }
} else {
    // ID မပါလာလျှင်
    $_SESSION['error_message'] = "Event ID မပါပါ။";
    header("Location: admin_events.php");
    exit();
}
?>