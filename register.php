<?php
session_start();

// အကယ်၍ တစ်ယောက်ယောက် login ဝင်ထားလျက်သား ဖြစ်နေရင် 
// register လုပ်ခါနီးမှာ session ကို အရင်ရှင်းပစ်လိုက်ပါ (Guest အသစ်ဖြစ်သွားအောင်)
if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['student_id'])) {
    session_unset();
    session_destroy();
    session_start(); // Session အသစ် ပြန်ဖွင့်ပါ
}

require_once 'supabase.php';
// ... ကျန်တဲ့ registration logic များ ...

require 'vendor/autoload.php'; 

$error_messages = [];
$message_sent = false;
$redis_error_msg = "";

// ၁။ Redis ချိတ်ဆက်ရန်
try {
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ]);
} catch (Exception $e) {
    $redis_error_msg = "Redis ချိတ်ဆက်မှု မအောင်မြင်ပါ - " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ၂။ Form မှ ရလာသော Data များကို စုစည်းခြင်း
    $student_data = [
        'name'       => $_POST['name'] ?? '',
        'email'      => $_POST['email'] ?? '',
        'student_id' => $_POST['student_id'] ?? '',
        'course_id'  => $_POST['course_id'] ?? '',
        'message'    => $_POST['message'] ?? '',
        'timestamp'  => time()
    ];

    // Email နဲ့ Roll Number စစ်ဆေးခြင်း
    $email_exists = check_email_exists($student_data['email']);
    $student_id_exists = check_student_id_exists($student_data['student_id']);
    
    $error_messages = [];
    
    if ($email_exists) {
        $error_messages[] = "ဒီအီးမေးလ် (" . htmlspecialchars($student_data['email']) . ") ရှိပြီးသားဖြစ်ပါသည်။";
    }
    
    if ($student_id_exists) {
        $error_messages[] = "ဒီကျောင်းသားအမှတ် (" . htmlspecialchars($student_data['student_id']) . ") ရှိပြီးသားဖြစ်ပါသည်။";
    }
    
    if (empty($error_messages)) {
        try {
            if (isset($redis)) {
                $redis->rpush('registration_queue', json_encode($student_data));
                $message_sent = true;
            } else {
                $redis_error_msg = "Redis ချိတ်ဆက်မှု မရှိပါ။";
            }
        } catch (Exception $e) {
            $redis_error_msg = "Queue ထဲထည့်ရာတွင် အမှားရှိနေပါသည်။";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CU - Student Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3ecf8e;
            --primary-dark: #2eb87b;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-light: #f8fafc;
            --accent: #38bdf8;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-dark);
            color: var(--text-light);
            margin: 0;
            background-image: radial-gradient(circle at 10% 20%, rgba(62, 207, 142, 0.1) 0%, transparent 40%);
            min-height: 100vh;
        }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 8%; background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo { font-weight: 800; font-size: 1.2rem; color: var(--primary); letter-spacing: 1px; text-decoration: none;}

        .wrapper {
            display: flex; justify-content: center; align-items: center; padding: 60px 20px;
        }

        .container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        h2 { margin-top: 0; color: var(--primary); font-weight: 600; text-align: center; }
        p.subtitle { text-align: center; color: #94a3b8; font-size: 0.9rem; margin-top: -10px; margin-bottom: 25px; }

        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 5px; font-size: 0.85rem; color: #94a3b8; }

        input, textarea, select { 
            width: 100%; padding: 12px; background: #0f172a;
            border: 1px solid #334155; border-radius: 8px;
            color: white; box-sizing: border-box; transition: all 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(62, 207, 142, 0.2);
        }

        .submit-btn { 
            width: 100%; padding: 14px; background: var(--primary); color: #0f172a; 
            border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 1rem;
            margin-top: 10px; transition: transform 0.2s, background 0.2s;
        }
        .submit-btn:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .success { background: rgba(62, 207, 142, 0.15); color: #3ecf8e; border: 1px solid var(--primary); }
        .error { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid #ef4444; }
        
        .queue-info {
            text-align: center; font-size: 0.75rem; color: var(--accent); margin-bottom: 15px;
            display: block; background: rgba(56, 189, 248, 0.1); padding: 5px; border-radius: 4px;
        }
    </style>
</head>
<body>
 <?php include 'guest_header.php'; ?> 


<div class="wrapper">
    <div class="container">
        <h2>CU Registration</h2>
        <p class="subtitle">Computer University Student Portal</p>

        <span class="queue-info">⚡ Distributed Queue System Active</span>

        <?php if ($message_sent): ?>
            <div class="alert success">✓ Registered successfully! Your data is being processed.</div>
        <?php endif; ?>

       <!-- Error Messages Display -->
<?php if (!empty($error_messages) || !empty($redis_error_msg)): ?>
    <div class="alert error">
        <i class="fas fa-exclamation-triangle"></i>
        <div style="margin-top: 5px;">
            <?php 
            if (!empty($error_messages)) {
                foreach ($error_messages as $msg) {
                    echo "<div style='margin-bottom: 5px;'>• " . $msg . "</div>";
                }
            }
            if (!empty($redis_error_msg)) {
                echo "<div>• " . $redis_error_msg . "</div>";
            }
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Success Message -->
<?php if ($message_sent): ?>
    <div class="alert success">
        <i class="fas fa-check-circle"></i> 
        ကျေးဇူးတင်ပါသည်! သင်၏ မှတ်ပုံတင်ခြင်း အောင်မြင်ပါသည်။ စီမံခန့်ခွဲသူမှ အတည်ပြုပြီးပါက အီးမေးလ်သို့ အကြောင်းကြားပါမည်။
    </div>
<?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>အမည် (Full Name)</label>
                <input type="text" name="name" placeholder="Mg Mg" required>
            </div>
            
            <div class="form-group">
                <label>ကျောင်းသားကတ် နံပါတ် (Student ID)</label>
                <input type="text" name="student_id" placeholder="CU-12345" required>
            </div>

            <div class="form-group">
                <label>အီးမေးလ် (Email Address)</label>
                <input type="email" name="email" placeholder="name@cu.edu.mm" required>
            </div>

            <div class="form-group">
                <label>အထူးပြုဘာသာ (Major)</label>
                <select name="course_id" required>
                    <option value="">-- သင်တန်းရွေးချယ်ပါ --</option>
                    <?php 
                    $courses = get_courses(); 
                    foreach($courses as $c): 
                    ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>မှတ်ချက် (Message/Note)</label>
                <textarea name="message" rows="3" placeholder="မှတ်ချက်များ..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Register Now</button>
        </form>
    </div>
</div>

</body>
</html>