<?php
require_once 'supabase.php';
require 'vendor/autoload.php';

$redis = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
    'read_write_timeout' => -1 
]);

echo "Worker စတင်နေပါပြီ... ဒေတာများကို စောင့်ဆိုင်းနေသည်...\n";

while (true) {
    // Queue မှ ဒေတာကို ဆွဲထုတ်ခြင်း
    $data = $redis->blpop('registration_queue', 0);
    $student = json_decode($data[1], true);

    // Retry count ကို စစ်ဆေးခြင်း (မပါလာရင် 0 လို့ သတ်မှတ်မယ်)
    $retry_count = isset($student['retry_count']) ? $student['retry_count'] : 0;
    $max_retries = 3;

    echo "ကျောင်းသား {$student['name']} ၏ ဒေတာကို သိမ်းဆည်းနေပါသည် (ကြိုးစားမှု - " . ($retry_count + 1) . ")...\n";

    $success = sendToSupabase(
        $student['name'], 
        $student['email'], 
        $student['message'], 
        $student['student_id'], 
        $student['course_id']
    );

    if ($success) {
        echo "✅ အောင်မြင်စွာ သိမ်းဆည်းပြီးပါပြီ။\n";
    } else {
        if ($retry_count < $max_retries) {
            // Retry လုပ်မည့်အကြိမ်ရေ မပြည့်သေးပါက Queue ထဲ ပြန်ထည့်မည်
            $student['retry_count'] = $retry_count + 1;
            
            echo "❌ အမှားရှိနေပါသည်... ၅ စက္ကန့်အကြာတွင် ပြန်ကြိုးစားပါမည် (ကျန်ရှိသည့်အကြိမ်ရေ: " . ($max_retries - $student['retry_count']) . ")...\n";
            
            sleep(5);
            $redis->rpush('registration_queue', json_encode($student));
        } else {
            // ၃ ကြိမ်ပြည့်သွားပါက လက်လျှော့လိုက်ပြီး Error Log ထုတ်မည်
            echo "⚠️ အကြိမ်ကြိမ် ကြိုးစားသော်လည်း မအောင်မြင်ပါ။ ဒေတာကို စာရင်းမှ ဖယ်ထုတ်လိုက်ပါပြီ (Dead Letter Queue သို့ ပို့သင့်သည်)။\n";
            // ဒီနေရာမှာ မအောင်မြင်တဲ့ ဒေတာတွေကို text file တစ်ခုထဲ သိမ်းထားတာမျိုး (သို့) 
            // 'failed_jobs' ဆိုတဲ့ queue ထဲ သီးသန့် ပို့ထားတာမျိုး လုပ်နိုင်ပါတယ်။
            file_put_contents('failed_registrations.log', json_encode($student) . PHP_EOL, FILE_APPEND);
        }
    }
}