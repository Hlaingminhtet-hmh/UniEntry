<?php
session_start();
require_once 'supabase.php';

// ၁။ ကျောင်းသား Login ဝင်မဝင် စစ်ဆေးမယ်
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('ကျေးဇူးပြု၍ အရင် Login ဝင်ပေးပါ'); window.location='login.php';</script>";
    exit();
}

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $student_id = $_SESSION['student_id'];
    
    // Join လုပ်ရန် function ကိုခေါ်မယ်
    $result = join_event($student_id, $event_id);
    
    if ($result['success']) {
        echo "<script>alert('" . $result['message'] . "'); window.location='student_dashboard.php';</script>";
    } else {
        echo "<script>alert('" . $result['message'] . "'); window.location='student_dashboard.php';</script>";
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}
?>