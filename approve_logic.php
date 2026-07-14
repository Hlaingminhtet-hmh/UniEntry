<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ၁။ Admin Login ဝင်ထားခြင်း ရှိမရှိ စစ်ဆေးခြင်း
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

require_once 'supabase.php';

// ၂။ PHPMailer Library ကို ချိတ်ဆက်ခြင်း
require 'PHPMailer/PHPMailer-master/src/Exception.php';
require 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // ၃။ ကျောင်းသားဒေတာကို Database မှ အရင်ဆွဲထုတ်ခြင်း
    $student = get_student_by_id($id); 

    // ၄။ ကျောင်းသားရှိလျှင် Database status ကို 'approved' သို့ Update လုပ်ခြင်း
    if ($student && approve_student($id)) {
        
        // Email ပို့ဖို့ ကြိုးစားမယ်
        // Email ပို့ဖို့ ကြိုးစားမယ်
$email_sent = false;
$email_error = "";

try {
    $mail = new PHPMailer(true);

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'zarnitun582180123@gmail.com'; 
    $mail->Password   = 'kqfv xeen uqje kbbp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // ပို့သူနှင့် လက်ခံသူ
    $mail->setFrom('zarnitun582180123@gmail.com', 'UCS Monywa Admin');
    $mail->addAddress($student['email'], $student['name']);

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'Registration Approved - UCS Monywa';
    
    $course_name = $student['courses']['course_name'] ?? 'လျှောက်ထားသောသင်တန်း';

    $mail->Body = "
        <div style='font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
            <h2 style='color: #3ecf8e;'>မင်္ဂလာပါ {$student['name']}၊</h2>
            <p>သင်လျှောက်ထားသော <b>{$course_name}</b> ဘာသာရပ်အတွက် ကျောင်းဝင်ခွင့်မှတ်ပုံတင်ခြင်းကို Admin မှ <b>အတည်ပြု (Approved)</b> ပေးလိုက်ပြီဖြစ်ကြောင်း အကြောင်းကြားအပ်ပါသည်။</p>
            
            <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 0;'>သင်၏ Student ID မှာ: <span style='color: #3ecf8e; font-weight: bold; font-size: 1.2rem;'>{$student['student_id']}</span> ဖြစ်ပါသည်။</p>
            </div>

            <p>လိုအပ်သည်များရှိပါက ကျောင်းသို့ လူကိုယ်တိုင်လာရောက်စုံစမ်းနိုင်ပါသည်။</p>
            <hr style='border: 0; border-top: 1px solid #eee;'>
            <p style='font-size: 0.9rem; color: #777;'>လေးစားစွာဖြင့်၊<br>University Database Team<br>UCS Monywa</p>
        </div>
    ";

    // ************ ဒီနေရာမှာ ပြင်ရမယ် ************
    // Email မှန်မမှန် စစ်မယ်
    if (filter_var($student['email'])) {
        try {
            // Email ပို့ဖို့ ကြိုးစားမယ်
            $mail->send();
            $email_sent = true;
        } catch (Exception $e) {
            $email_error = $mail->ErrorInfo;
        }
    } else {
        // Email မမှန်ဘူးဆိုရင် ဒီမှာ log ထားခဲ့မယ်
        error_log("Invalid email for student ID: " . $student['id'] . " - Email: " . $student['email']);
        $email_error = "အီးမေးလ်လိပ်စာ မမှန်ကန်ပါ။";
    }
    // ************ ပြင်ပြီး ************
    
} catch (Exception $e) {
    $email_error = $mail->ErrorInfo;
}
        // အောင်မြင်ရင် Message ပြမယ်
        if ($email_sent) {
            $_SESSION['message'] = "ကျောင်းသားကို Approve လုပ်ပြီးပါပြီ။ အတည်ပြုအီးမေးလ် ပို့ပြီးပါပြီ။";
        } else {
            // Email မရှိလို့ မပို့နိုင်ရင် ဒီ Message ပြမယ်
            $_SESSION['message'] = "ကျောင်းသားကို Approve လုပ်ပြီးပါပြီ။ သို့သော် အီးမေးလ်ပို့၍ မရပါ (အီးမေးလ်လိပ်စာ မှားယွင်းနေခြင်း သို့မဟုတ် မရှိခြင်း)။";
        }
        
        header("Location: admin.php");
        exit();

    } else {
        $_SESSION['message'] = "ကျောင်းသားကို Approve လုပ်၍ မရပါ။";
        header("Location: admin.php");
        exit();
    }
} else {
    header("Location: admin.php");
    exit();
}
?>