<?php
// supabase.php
require_once 'vendor/autoload.php';
require_once 'PHPMailer/PHPMailer-master/src/Exception.php';
require_once 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


define('SUPABASE_URL', 'https://hymqarfrbjinjlhdweji.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imh5bXFhcmZyYmppbmpsaGR3ZWppIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU0MzY2MTQsImV4cCI6MjA5MTAxMjYxNH0.RZbSdjq9Fbc6ZQZp_sI2wTzFya83xdEusoyQ-sk8uPQ'); 

function curl_request($url, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($method === 'POST') curl_setopt($ch, CURLOPT_POST, true);
    if ($method === 'PATCH' || $method === 'DELETE') curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

// ၁။ ကျောင်းသားသစ် စာရင်းသွင်းရန်
function sendToSupabase($name, $email, $message, $student_id, $course_id) {
    $url = SUPABASE_URL . "/rest/v1/students";
    $payload = [
        'name' => $name, 'email' => $email, 'message' => $message, 
        'student_id' => $student_id, 'course_id' => (int)$course_id, 'status' => 'pending'
    ];
    $res = curl_request($url, 'POST', $payload);
    return ($res['code'] >= 200 && $res['code'] < 300);
}

// ၂။ ကျောင်းသားအားလုံးကို Course Name နှင့်တကွ ဆွဲထုတ်ရန်
function get_students() {
    $url = SUPABASE_URL . "/rest/v1/students?select=*,courses(course_name)&order=created_at.desc";
    $res = curl_request($url);
    return $res['data'] ?: [];
}

// ၃။ Course စာရင်းများကို ဆွဲထုတ်ရန်
function get_courses() {
    $url = SUPABASE_URL . "/rest/v1/courses?select=*&order=course_name.asc";
    $res = curl_request($url);
    return $res['data'] ?: [];
}

// ၄။ ID ဖြင့် ကျောင်းသားကို ရှာရန်
function get_student_by_id($id) {
    $url = SUPABASE_URL . "/rest/v1/students?id=eq." . $id . "&select=*,courses(course_name)";
    $res = curl_request($url);
    return (isset($res['data'][0])) ? $res['data'][0] : null;
}

// ၅။ ကျောင်းသားအရေအတွက် (Home Page အတွက်)
function get_student_count() {
    $url = SUPABASE_URL . "/rest/v1/students?status=eq.approved&select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}

// ၆။ Approve လုပ်ရန်
function approve_student($id) {
    $url = SUPABASE_URL . "/rest/v1/students?id=eq." . $id;
    $res = curl_request($url, 'PATCH', ['status' => 'approved']);
    return ($res['code'] >= 200 && $res['code'] < 300);
}

// Approved ကျောင်းသားများကိုသာ ရယူရန်
function get_approved_students() {
    $url = SUPABASE_URL . "/rest/v1/students?status=eq.approved&select=*,courses(course_name)&order=created_at.desc";
    $res = curl_request($url);
    return $res['data'] ?: [];
}
// ၁။ Approved ကျောင်းသားအရေအတွက်
function get_approved_count() {
    $url = SUPABASE_URL . "/rest/v1/students?status=eq.approved&select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}

// ၂။ Pending ကျောင်းသားအရေအတွက်
function get_pending_count() {
    $url = SUPABASE_URL . "/rest/v1/students?status=eq.pending&select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}

// ၃။ စုစုပေါင်း ကျောင်းသားအရေအတွက်
function get_total_students_count() {
    $url = SUPABASE_URL . "/rest/v1/students?select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}

// Event အရေအတွက် ရယူရန်
function get_events_count() {
    $url = SUPABASE_URL . "/rest/v1/events?select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}
// Roll Number (student_id) ရှိမရှိ စစ်ဆေးရန်
function check_student_id_exists($student_id) {
    $url = SUPABASE_URL . "/rest/v1/students?student_id=eq." . urlencode($student_id) . "&select=id";
    $res = curl_request($url);
    return !empty($res['data']);
}

// Email ရှိမရှိ စစ်ဆေးရန် (အရင်ကရှိပြီးသား)
function check_email_exists($email) {
    $url = SUPABASE_URL . "/rest/v1/students?email=eq." . urlencode($email) . "&select=id";
    $res = curl_request($url);
    return !empty($res['data']);
}

// Event တစ်ခုရဲ့ လက်ရှိပါဝင်သူဦးရေ ရေတွက်ရန်
function get_event_participant_count($event_id) {
    $url = SUPABASE_URL . "/rest/v1/event_registrations?event_id=eq." . $event_id . "&select=id";
    $res = curl_request($url);
    return is_array($res['data']) ? count($res['data']) : 0;
}

// Event ရဲ့ အချက်အလက်အပြည့်အစုံ ရယူရန် (max_participants ပါမယ်)
function get_event_details($event_id) {
    $url = SUPABASE_URL . "/rest/v1/events?id=eq." . $event_id . "&select=*";
    $res = curl_request($url);
    return $res['data'][0] ?? null;
}

// Event ကို Join လုပ်ရန် (လူမပြည့်မှသာ)
function join_event($student_id, $event_id) {
    // လူပြည့်မပြည့် အရင်စစ်
    $event = get_event_details($event_id);
    if (!$event) return ['success' => false, 'message' => 'Event မရှိပါ။'];
    
    $current_count = get_event_participant_count($event_id);
    $max_participants = $event['max_participants'] ?? 0;
    
    // လူပြည့်နေရင် Join မရအောင် တားမယ်
    if ($max_participants > 0 && $current_count >= $max_participants) {
        return ['success' => false, 'message' => 'ဤ Event အတွက် လူပြည့်သွားပါပြီ။'];
    }
    
    // နှစ်ခါ Join ထားလား စစ်မယ်
    $check_url = SUPABASE_URL . "/rest/v1/event_registrations?student_id=eq." . $student_id . "&event_id=eq." . $event_id . "&select=id";
    $check_res = curl_request($check_url);
    if (!empty($check_res['data'])) {
        return ['success' => false, 'message' => 'ဤ Event ကို သင်ယခင်က Join ပြီးပါပြီ။'];
    }
    
    // Join လုပ်မယ်
    $url = SUPABASE_URL . "/rest/v1/event_registrations";
    $data = [
        'student_id' => (int)$student_id,
        'event_id'   => (int)$event_id
    ];
    
    $res = curl_request($url, 'POST', $data);
    
    if ($res['code'] == 201 || $res['code'] == 200) {
        return ['success' => true, 'message' => 'ပွဲစာရင်းသွင်းခြင်း အောင်မြင်ပါသည်။'];
    } else {
        return ['success' => false, 'message' => 'စာရင်းသွင်း၍ မရပါ။ ထပ်မံကြိုးစားကြည့်ပါ။'];
    }
}

// Approve ဖြစ်ပြီးသား ကျောင်းသားအားလုံးရဲ့ Email စာရင်းရယူရန်
function get_all_approved_students_emails() {
    $url = SUPABASE_URL . "/rest/v1/students?status=eq.approved&select=email,name";
    $res = curl_request($url);
    return $res['data'] ?? [];
}
// Event အသစ်အကြောင်း ကျောင်းသားတွေဆီ Email ပို့ရန်
function send_new_event_notification($event_details) {
    // Debug - event_details ကိုစစ်ပါ
    error_log("send_new_event_notification called with: " . print_r($event_details, true));
    
    $students = get_all_approved_students_emails();
    
    if (empty($students)) {
        error_log("No approved students found to send emails");
        return false;
    }
    
    error_log("Found " . count($students) . " approved students");
    
    $mail = new PHPMailer(true);
    $success_count = 0;
    $error_count = 0;
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zarnitun582180123@gmail.com';
        $mail->Password   = 'kqfv xeen uqje kbbp'; // ဒါ App Password ဖြစ်ရပါမယ်
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug = 2; // Debug mode ဖွင့်ပါ
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str");
        };
        
        $mail->setFrom('zarnitun582180123@gmail.com', 'UCS Monywa Events');
        $mail->isHTML(true);
        
        foreach ($students as $student) {
            try {
                $mail->clearAddresses();
                
                // Email မှန်မမှန်စစ်ပါ
                if (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
                    error_log("Invalid email for student: " . $student['name'] . " - " . $student['email']);
                    continue;
                }
                
                $mail->addAddress($student['email'], $student['name']);
                
                // Email Content
                $event_date = date('d M Y', strtotime($event_details['event_date']));
                $event_time = !empty($event_details['event_time']) ? date('h:i A', strtotime($event_details['event_time'])) : 'TBA';
                
                $subject = "🎉 ပွဲအသစ် - " . $event_details['event_name'];
                
                $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; background: #f8fafc; padding: 30px; border-radius: 16px;'>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <h1 style='color: #3ecf8e; margin: 0;'>UCS MONYWA</h1>
                        <p style='color: #64748b;'>University of Computer Studies</p>
                    </div>
                    
                    <div style='background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                        <h2 style='color: #0f172a; margin-top: 0;'>ပြိုင်ပွဲအမည်: " . htmlspecialchars($event_details['event_name']) . "</h2>
                        
                        <p style='color: #0f172a;'>မင်္ဂလာပါ " . htmlspecialchars($student['name']) . "၊</p>
                        
                        <div style='margin: 20px 0; padding: 15px; background: #f1f5f9; border-radius: 8px;'>
                            <p style='margin: 8px 0;'><strong>📅 ရက်စွဲ:</strong> " . $event_date . "</p>
                            <p style='margin: 8px 0;'><strong>⏰ အချိန်:</strong> " . $event_time . "</p>
                            <p style='margin: 8px 0;'><strong>📍 နေရာ:</strong> " . htmlspecialchars($event_details['location']) . "</p>
                        </div>
                        
                        <div style='margin: 20px 0;'>
                            <h3 style='color: #0f172a;'>📝 အကြောင်းအရာ</h3>
                            <p style='color: #334155; line-height: 1.6;'>" . nl2br(htmlspecialchars($event_details['description'])) . "</p>
                        </div>
                        
                        <div style='margin-top: 30px; text-align: center;'>
                            <a href='http://localhost/dbapp/student_dashboard.php' 
                               style='background: #3ecf8e; color: #0f172a; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>
                                ပွဲများကြည့်ရန်
                            </a>
                        </div>
                    </div>
                    
                    <div style='margin-top: 20px; text-align: center; color: #94a3b8; font-size: 0.8rem;'>
                        <p>University of Computer Studies, Monywa</p>
                    </div>
                </div>";
                
                $mail->Subject = $subject;
                $mail->Body    = $body;
                
                $mail->send();
                $success_count++;
                error_log("Email sent to: " . $student['email']);
                
            } catch (Exception $e) {
                $error_count++;
                error_log("Failed to send to " . $student['email'] . ": " . $mail->ErrorInfo);
            }
        }
        
        error_log("Email sending complete. Success: $success_count, Failed: $error_count");
        return $success_count;
        
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>