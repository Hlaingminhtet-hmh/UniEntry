<?php
session_start();
// Session အားလုံးကို ဖျက်ပစ်ခြင်း
session_unset();
session_destroy();

// Login page သို့ ပြန်လွှတ်ခြင်း
header("Location: login.php");
exit();
?>