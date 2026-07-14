<?php
session_start();
require_once 'supabase.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Event အချက်အလက်ယူမယ်
    $event = get_event_details($event_id);
    
    if (!$event) {
        echo "<script>alert('Event မရှိပါ။'); window.location='admin_events.php';</script>";
        exit();
    }
    
    // Email ပို့မယ်
    $email_sent = send_new_event_notification($event);
    
    if ($email_sent) {
        echo "<script>alert('အောင်မြင်ပါသည်။ ကျောင်းသား " . $email_sent . " ဦးဆီကို Email ပို့ပြီးပါပြီ။'); window.location='admin_events.php';</script>";
    } else {
        echo "<script>alert('Email ပို့ရာတွင် ပြဿနာရှိခဲ့ပါသည်။'); window.location='admin_events.php';</script>";
    }
} else {
    header("Location: admin_events.php");
    exit();
}
?>