<?php
session_start();
require_once 'supabase.php';

// ၁။ Login ဝင်ပြီးသားသူဆိုလျှင် သက်ဆိုင်ရာ Page သို့ ပို့ရန်
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_home.php");
    exit();
} elseif (isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = trim($_POST['username_email']);

    // --- CASE A: ADMIN LOGIN ---
    if (strtolower($username_email) === 'admin') {
        $password = $_POST['password'];
        if ($password === "admin123") {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $error = "Admin password မှားယွင်းနေပါသည်။";
        }
    } 
    // --- CASE B: STUDENT LOGIN (Approval Logic ပါဝင်သည်) ---
    else {
        // email တူရမည့်အပြင် status သည်လည်း approved ဖြစ်နေမှ data ယူမည်
        $url = SUPABASE_URL . "/rest/v1/students?email=eq." . urlencode($username_email) . "&status=eq.approved&select=*";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY
        ]);
        
        $response = curl_exec($ch);
        $user_data = json_decode($response, true);
        curl_close($ch);

        if (!empty($user_data)) {
            // Admin က approve လုပ်ထားသော ကျောင်းသားဖြစ်လျှင်
            $user = $user_data[0];
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            $_SESSION['student_email'] = $user['email'];
            header("Location: index.php");
            exit();
        } else {
            // အကယ်၍ email မရှိခြင်း သို့မဟုတ် status က pending ဖြစ်နေခြင်း
            $error = "ဝင်ရောက်ခွင့်မရှိပါ။ Email မှားယွင်းနေခြင်း သို့မဟုတ် Admin Approval စောင့်ဆိုင်းနေခြင်း ဖြစ်နိုင်ပါသည်။";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UCS Monywa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #3ecf8e; --bg: #0f172a; --card: #1e293b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: var(--card); padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); width: 380px; border: 1px solid #334155; }
        h2 { color: var(--primary); text-align: center; font-size: 1.8rem; margin-bottom: 5px; }
        p.desc { text-align: center; color: #94a3b8; font-size: 0.85rem; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; }
        input { width: 100%; padding: 14px; border-radius: 10px; border: 1px solid #334155; background: #0f172a; color: white; box-sizing: border-box; font-size: 1rem; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 2px rgba(62, 207, 142, 0.1); }
        
        button { width: 100%; padding: 14px; background: var(--primary); border: none; border-radius: 10px; color: #0f172a; font-weight: 800; cursor: pointer; transition: 0.3s; font-size: 1rem; margin-top: 10px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(62, 207, 142, 0.3); }
        
        #admin-pass-box { display: none; transition: 0.5s; }
        .error-msg { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 10px; text-align: center; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Portal Login</h2>
    <p class="desc">Enter 'admin' for management or your email</p>

    <?php if ($error): ?> <div class="error-msg"><?php echo $error; ?></div> <?php endif; ?>

    <form method="POST" id="loginForm">
        <div class="form-group">
            <label>Admin Username / Student Email</label>
            <input type="text" name="username_email" id="userInput" placeholder="admin or email@gmail.com" required oninput="toggleAdminField()">
        </div>

        <div class="form-group" id="admin-pass-box">
            <label>Admin Password</label>
            <input type="password" name="password" id="adminPass" placeholder="••••••••">
        </div>

        <button type="submit">Sign In <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
    </form>
</div>

<script>
    function toggleAdminField() {
        const val = document.getElementById('userInput').value.toLowerCase();
        const passBox = document.getElementById('admin-pass-box');
        const passInput = document.getElementById('adminPass');

        if (val === 'admin') {
            passBox.style.display = 'block';
            passInput.setAttribute('required', '');
        } else {
            passBox.style.display = 'none';
            passInput.removeAttribute('required');
        }
    }
</script>

</body>
</html>