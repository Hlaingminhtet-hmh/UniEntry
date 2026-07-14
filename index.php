<?php 
session_start();
require_once 'supabase.php';
$student_count = get_student_count();

// Login မဝင်ထားရင် index.php ကို ပေးမကြည့်ချင်ဘူးဆိုရင် အောက်က code ကို သုံးနိုင်ပါတယ်
// if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['student_id'])) {
//     header("Location: login.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Computer Studies, Monywa | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3ecf8e;
            --primary-dark: #2eb87e;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #38bdf8;
            --danger: #ef4444;
            --warning: #f59e0b;
            --border: #334155;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Hero Section with Background */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
           background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.8) 100%), 
                url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2070&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
            padding: 0 20px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            animation: slowZoom 20s infinite alternate;
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .hero-content {
            max-width: 900px;
            position: relative;
            z-index: 1;
        }

        .university-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(62, 207, 142, 0.1);
            border: 1px solid var(--primary);
            padding: 8px 25px;
            border-radius: 50px;
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
        }

        .university-badge i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .university-badge span {
            color: var(--primary);
            font-weight: 600;
            letter-spacing: 2px;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero h1 span {
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .hero h1 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(62, 207, 142, 0.3);
            z-index: -1;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }

        .stats-container {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Section Styles */
        .section {
            padding: 100px 10%;
            position: relative;
        }

        .section-light {
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 50px;
            text-align: center;
            color: var(--primary);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .section-title i {
            margin-right: 15px;
            color: var(--accent);
        }

        /* About Section */
        .about-content {
            max-width: 1000px;
            margin: 0 auto;
        }

        .about-text {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
            text-align: justify;
        }

        .mission-box {
            background: rgba(62, 207, 142, 0.05);
            border-left: 4px solid var(--primary);
            padding: 30px;
            border-radius: 0 20px 20px 0;
            margin: 40px 0;
        }

        .mission-box h3 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .mission-box p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-style: italic;
            line-height: 1.8;
        }

        /* History Timeline */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: var(--primary);
            opacity: 0.3;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 50px;
        }

        .timeline-content {
            position: relative;
            width: calc(50% - 40px);
            padding: 25px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: 0.3s;
        }

        .timeline-item:nth-child(odd) .timeline-content {
            left: 0;
        }

        .timeline-item:nth-child(even) .timeline-content {
            left: calc(50% + 40px);
        }

        .timeline-content:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .timeline-date {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(62, 207, 142, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .timeline-content h4 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: white;
        }

        .timeline-content p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background: var(--primary);
            border: 4px solid var(--bg-dark);
            border-radius: 50%;
            z-index: 1;
        }

        /* Rules Section */
        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .rule-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            transition: 0.3s;
        }

        .rule-card:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .rule-icon {
            width: 60px;
            height: 60px;
            background: rgba(62, 207, 142, 0.1);
            border: 1px solid var(--primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .rule-icon i {
            font-size: 28px;
            color: var(--primary);
        }

        .rule-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }

        .rule-card ul {
            list-style: none;
            padding: 0;
        }

        .rule-card ul li {
            color: var(--text-muted);
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .rule-card ul li i {
            color: var(--primary);
            font-size: 0.9rem;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .rule-card ul li i.fa-circle {
            font-size: 0.5rem;
            margin-top: 8px;
        }

        .myanmar-text {
            font-style: italic;
            color: var(--accent);
            margin-top: 20px;
            padding: 15px;
            background: rgba(56, 189, 248, 0.05);
            border-radius: 12px;
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, rgba(62, 207, 142, 0.1) 0%, transparent 100%);
            border-radius: 40px;
            margin: 40px 0;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: white;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--bg-dark);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(62, 207, 142, 0.3);
        }

        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--bg-dark);
            transform: translateY(-3px);
        }

        /* Footer */
        footer {
            background: #0b1120;
            padding: 60px 10% 30px;
            border-top: 1px solid var(--border);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h4 {
            color: var(--primary);
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .footer-column p, .footer-column a {
            color: var(--text-muted);
            text-decoration: none;
            line-height: 1.8;
            display: block;
            margin-bottom: 10px;
        }

        .footer-column a:hover {
            color: var(--primary);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .social-links a:hover i {
            color: var(--bg-dark);
        }

        .social-links i {
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        .copyright {
            text-align: center;
            color: var(--text-muted);
            padding-top: 30px;
            border-top: 1px solid var(--border);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .section { padding: 60px 5%; }
            .section-title { font-size: 2rem; }
            
            .timeline::before { left: 30px; }
            .timeline-content { 
                width: calc(100% - 60px);
                left: 60px !important;
            }
            .timeline-dot { left: 30px; }
            
            .rules-grid { grid-template-columns: 1fr; }
            .cta-buttons { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }

        /* Pulse Animation for Active Students */
        .pulse-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            70% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="university-badge">
                <i class="fas fa-university"></i>
                <span>EST. 2000</span>
            </div>

            <h1>Welcome To <span>Our University</span></h1>
            
            <p>The Government of the Republic of the Union of Myanmar is actively working to transform the nation from an agrarian economy to an industrialized one, driven by advanced technologies. This transformation requires a rapid development of skilled human resources.</p>

            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($student_count); ?>+</div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">20+</div>
                    <div class="stat-label">Academic Programs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2000</div>
                    <div class="stat-label">Founded</div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section section-light">
        <div class="about-content">
            <h2 class="section-title"><i class="fas fa-info-circle"></i> About Our University</h2>
            
            <p class="about-text">The University of Computer Studies, Monywa, plays a vital role in producing competent young IT professionals in relevant fields. Our aim is to equip students with the knowledge and skills necessary to secure employment in the rapidly evolving IT sector and contribute to the growth of Myanmar's IT industry. UCS (Monywa) produces IT professionals and technicians each year, addressing the critical need for skilled human resources not only in the Sagaing Region but also nationwide.</p>
            
            <p class="about-text">Furthermore, we are committed to enhancing the competencies and qualifications of our teaching staff to ensure the highest quality of education. To provide access to internationally standardized interdisciplinary education in the Sagaing Region, particularly for those in rural areas, the University of Computer Studies, Monywa, offers undergraduate B.C.Sc./B.C.Tech degrees, postgraduate Diplomas (D.C.Sc.), and Master's degrees (M.C.Sc./M.C.Tech and M.I.Sc.). By nurturing these IT professionals, we contribute to raising living standards and expanding career opportunities for the local population.</p>

            <div class="mission-box">
                <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                <p>"Our university's mission is to produce skilled software and hardware professionals, innovative researchers, applications and system developers in Information Technology. These graduates will apply their expertise to real-world situations, ensuring Myanmar keeps pace with international standards, and thereby contributing to the development of a peaceful and modern nation."</p>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section class="section">
        <h2 class="section-title"><i class="fas fa-history"></i> Background History</h2>
        
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date">September 4, 2000</span>
                    <h4>Foundation</h4>
                    <p>Founded at No. 260, Bogyoke Aung San Road, Monywa, Sagaing Region as Government Computer College, Monywa under the former Ministry of Science and Technology.</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date">September 3, 2001</span>
                    <h4>First Relocation</h4>
                    <p>Relocated to a site near Moehnyinthanbokedae Temple to accommodate growing student population.</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date">February 5, 2006</span>
                    <h4>Second Relocation</h4>
                    <p>Moved to the premises of the former Government Technical College, Monywa, located in Myothit Ward.</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date">January 20, 2007</span>
                    <h4>University Status</h4>
                    <p>Upgraded to University of Computer Studies, Monywa, marking a new chapter in higher education.</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date">Present</span>
                    <h4>Current Campus</h4>
                    <p>Located on Monywa-Yargyi-Kalaywa Road, Aung Zeya Myothit, Yinmarbin Township, Sagaing Region, Myanmar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Student Rules & Ethics Section -->
    <section class="section section-light">
        <h2 class="section-title"><i class="fas fa-gavel"></i> ကျောင်းသားလိုက်နာရမည့် စည်းကမ်းများ</h2>
        
        <div class="rules-grid">
            <!-- Academic Rules -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>ပညာရေးဆိုင်ရာ စည်းကမ်းများ</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> ပုံမှန်အတန်းတက်ရောက်ရမည်</li>
                    <li><i class="fas fa-circle"></i> စာမေးပွဲများကို ရိုးသားစွာဖြေဆိုရမည်</li>
                    <li><i class="fas fa-circle"></i> သင်ခန်းစာများကို ကြိုတင်ပြင်ဆင်လေ့လာရမည်</li>
                    <li><i class="fas fa-circle"></i> စာကြည့်တိုက်စည်းကမ်းများကို လိုက်နာရမည်</li>
                </ul>
            </div>

            <!-- မြန်မာ့ရိုးရာကျင့်ဝတ် -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-hand-sparkles"></i>
                </div>
                <h3>မြန်မာ့ရိုးရာ လူမှုရေးကျင့်ဝတ်များ</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> ဆရာသမားကို ရိုသေလေးစားရမည်</li>
                    <li><i class="fas fa-circle"></i> အနည်းငယ်သော စကားကိုသာ ပြောဆိုရမည်</li>
                    <li><i class="fas fa-circle"></i> ရှိခိုးယဉ်ကျေးမှုကို လိုက်နာရမည်</li>
                    <li><i class="fas fa-circle"></i> ဝတ်စားဆင်ယင်မှု ယဉ်ကျေးသန့်ပြန့်ရမည်</li>
                    <li><i class="fas fa-circle"></i> အကြီးအကဲများကို ရိုသေစွာဆက်ဆံရမည်</li>
                </ul>
            </div>

            <!-- Discipline Rules -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>စည်းကမ်းပိုင်းဆိုင်ရာ</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> ကျောင်းဝတ်စုံကို စနစ်တကျဝတ်ဆင်ရမည်</li>
                    <li><i class="fas fa-circle"></i> ကျောင်းပစ္စည်းများကို ထိန်းသိမ်းရမည်</li>
                    <li><i class="fas fa-circle"></i> သတ်မှတ်ထားသော နေရာများတွင်သာ ယာဉ်ရပ်နားရမည်</li>
                    <li><i class="fas fa-circle"></i> ဆေးလိပ်၊ အရက်၊ မူးယစ်ဆေးလုံးဝ ရှောင်ကြဉ်ရမည်</li>
                </ul>
            </div>

            <!-- Digital Ethics -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <h3>နည်းပညာအသုံးပြုမှုဆိုင်ရာ ကျင့်ဝတ်</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> ကွန်ပျူတာဓလေ့ထုံးတမ်းစည်းကမ်းများ လိုက်နာရမည်</li>
                    <li><i class="fas fa-circle"></i> ဆိုရှယ်မီဒီယာ အသုံးပြုရာတွင် ယဉ်ကျေးရမည်</li>
                    <li><i class="fas fa-circle"></i> ကျောင်း၏ နည်းပညာဆိုင်ရာ စည်းကမ်းများ လိုက်နာရမည်</li>
                    <li><i class="fas fa-circle"></i> ဆိုက်ဘာ အနိုင်ကျင့်မှု လုံးဝမပြုလုပ်ရ</li>
                </ul>
            </div>

            <!-- Social Ethics -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3>လူမှုဆက်ဆံရေး ကျင့်ဝတ်</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> လုပ်ဖော်ကိုင်ဖက်များနှင့် ညီညွတ်စွာနေထိုင်ရမည်</li>
                    <li><i class="fas fa-circle"></i> ငယ်သားများကို ချစ်ခင်ကြင်နာရမည်</li>
                    <li><i class="fas fa-circle"></i> မိမိထက် ကြီးသူများကို ရိုသေရမည်</li>
                    <li><i class="fas fa-circle"></i> ပရဟိတစိတ်ထားရှိရမည်</li>
                </ul>
            </div>

            <!-- Campus Rules -->
            <div class="rule-card">
                <div class="rule-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <h3>ကျောင်းဝင်းအတွင်း လိုက်နာရန်</h3>
                <ul>
                    <li><i class="fas fa-circle"></i> ကျောင်းဝင်းအတွင်း သန့်ရှင်းစွာထားရှိရမည်</li>
                    <li><i class="fas fa-circle"></i> အမှိုက်များကို စနစ်တကျစွန့်ပစ်ရမည်</li>
                    <li><i class="fas fa-circle"></i> ကျောင်းပိုင်ပစ္စည်းများ မပျက်စီးအောင် ထိန်းသိမ်းရမည်</li>
                    <li><i class="fas fa-circle"></i> သတ်မှတ်ထားသော နေရာများတွင်သာ စားသောက်ရမည်</li>
                </ul>
            </div>
        </div>

        <div class="myanmar-text">
            <i class="fas fa-quote-left" style="color: var(--primary);"></i>
            သုကာရီ ဘယျ ရူပ ဒက္ခိဏာ ဥဘောဝ သမ္ပန္န ဥဘောဝ ဝိရဟိတ ပညာ ဝ နရော န သောဘတိ။
            <i class="fas fa-quote-right" style="color: var(--primary); float: right;"></i>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section">
        <div class="cta-section">
            <h2>Start Your Journey With Us</h2>
            <p style="color: var(--text-muted); font-size: 1.2rem; max-width: 600px; margin: 0 auto;">Join the University of Computer Studies, Monywa and become part of Myanmar's digital future.</p>
            
            <div class="cta-buttons">
                <?php if (isset($_SESSION['student_id'])): ?>
                    <a href="student_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                <?php elseif (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Admin Panel
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Enroll Now
                    </a>
                    <a href="login.php" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h4>UCS Monywa</h4>
                <p>University of Computer Studies, Monywa</p>
                <p>Sagaing Region, Myanmar</p>
                <p>Monywa-Yargyi-Kalaywa Road, Aung Zeya Myothit</p>
            </div>

            <div class="footer-column">
                <h4>Quick Links</h4>
                <a href="#about"><i class="fas fa-chevron-right"></i> About Us</a>
                <a href="#"><i class="fas fa-chevron-right"></i> Academic Programs</a>
                <a href="#"><i class="fas fa-chevron-right"></i> Admissions</a>
                <a href="#"><i class="fas fa-chevron-right"></i> Contact Us</a>
            </div>

            <div class="footer-column">
                <h4>Contact Info</h4>
                <p><i class="fas fa-phone"></i> +95 (0)71 123 456</p>
                <p><i class="fas fa-envelope"></i> info@ucsmonywa.edu.mm</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="copyright">
            <p>© <?php echo date('Y'); ?> University of Computer Studies, Monywa. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">Developing skilled IT professionals to drive Myanmar's digital transformation.</p>
        </div>
    </footer>
</body>
</html>