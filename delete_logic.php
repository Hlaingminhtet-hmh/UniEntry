<?php
session_start();

// ၁။ Admin Login ဝင်ထားခြင်း ရှိမရှိ စစ်ဆေးခြင်း
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'supabase.php';

// ၂။ URL မှ ID ပါမပါ နှင့် အမှားအယွင်းရှိမရှိ စစ်ဆေးခြင်း
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // ၃။ Supabase DELETE API URL တည်ဆောက်ခြင်း
    // ယခင် 'contacts' အစား Table အမည်သစ် 'students' ကို အသုံးပြုထားပါသည်
    $url = SUPABASE_URL . "/rest/v1/students?id=eq." . $id;

    $ch = curl_init($url);
    
    // ၄။ CURL Options သတ်မှတ်ခြင်း (DELETE Method ကို သုံးမည်)
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Localhost အတွက် SSL error မတက်စေရန်
    
    // ၅။ Headers သတ်မှတ်ခြင်း (API Key များ သေချာစေရန်)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    // ၆။ Execute လုပ်ခြင်းနှင့် Response ရယူခြင်း
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ၇။ ရလဒ်ကို စစ်ဆေးပြီး Redirect လုပ်ခြင်း
    if ($httpCode >= 200 && $httpCode < 300) {
        // အောင်မြင်လျှင် status=deleted message နှင့်အတူ admin.php သို့ ပြန်သွားမည်
        header("Location: admin.php?status=deleted");
        exit();
    } else {
        // မအောင်မြင်လျှင် Error ထုတ်ပြမည်
        echo "<h3>ဒေတာဖျက်၍ မရပါ - အမှားတစ်ခု ရှိနေပါသည်</h3>";
        echo "HTTP Status Code: " . $httpCode . "<br>";
        echo "Response: " . htmlspecialchars($response);
        echo "<br><br><a href='admin.php'>Dashboard သို့ ပြန်သွားရန်</a>";
    }
} else {
    // ID မပါလာလျှင် Dashboard သို့ ပြန်ပို့မည်
    header("Location: admin.php");
    exit();
}
?>