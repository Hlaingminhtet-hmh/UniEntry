<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // URL မှာ နောက်ဆုံးက contacts ဆိုတာ သေချာပါစေ
    $url = 'https://hymqarfrbjinjlhdweji.supabase.co/rest/v1/contacts'; // ကိုယ့် Supabase URL ပြန်ထည့်ပါ
    $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imh5bXFhcmZyYmppbmpsaGR3ZWppIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU0MzY2MTQsImV4cCI6MjA5MTAxMjYxNH0.RZbSdjq9Fbc6ZQZp_sI2wTzFya83xdEusoyQ-sk8uPQ';
    $data = json_encode([
        'name'    => $_POST['name'] ?? '',
        'email'   => $_POST['email'] ?? '',
        'major'   => $_POST['major'] ?? '',
        'student_id'   => $_POST['student_id'] ?? '',
        'message' => $_POST['message'] ?? ''
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $api_key,
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch); // ဘာလို့ ချိတ်မရလဲဆိုတာ စာတန်းပြလိမ့်မယ်
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<h3 style='color:green;'>Data Sent to Supabase Successfully!</h3>";
    } else {
        echo "<h3 style='color:red;'>Error: " . $httpCode . "</h3>";
        echo "<pre>" . $response . "</pre>";
    }
}
?>
