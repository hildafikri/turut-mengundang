<?php
// undangan.php — Premium Royal Level 4 (A) - Elegant Full-Page Scroll
// Single-file premium wedding invitation (comments + RSVP via file storage)
// Save as undangan.php and upload to your hosting. Ensure folder writable for comments.txt and rsvp.txt.

// ------------------ Guest from URL (quick custom) ------------------
 $guest = isset($_GET['to']) ? trim($_GET['to']) : "";

// ------------------ Config (easy to edit) ------------------
 $BRIDE_FULL    = 'Hilda Nuraeni Syifa ';               // bride
 $GROOM_FULL    = 'Muhammad Fikri Pangestu ';   // groom

// You can put more human readable strings here for preview & meta
 $AKAD_DATE     = 'Sabtu, 13 April 2024 - 09:00 WIB';
 $AKAD_LOCATION = 'Musala Ar Ridwan, Sokaraja Kulon, Banyumas';

 $RESEPSI_DATE     = 'Minggu, 14 April 2024 - 13:00 WIB';
 $RESEPSI_LOCATION = 'PP. Daarul Istiqomah / YADRI, Kedunglemah, Kedungbanteng';

// files (ensure writable)
 $dataComments = __DIR__ . '/comments.txt';
 $dataRsvp      = __DIR__ . '/rsvp.txt';

// ------------------ Utilities ------------------
function safe_json_decode($line) {
    $decoded = @json_decode($line, true);
    return is_array($decoded) ? $decoded : null;
}
function read_lines_json($file) {
    if (!file_exists($file)) return [];
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return [];
    $out = [];
    foreach ($lines as $line) {
        $d = safe_json_decode($line);
        if ($d) $out[] = $d;
    }
    return array_reverse($out); // newest first
}
function write_json_line($file, $obj) {
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $line = json_encode($obj, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    return @file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
}

// ------------------ Handler AJAX POST ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    if ($action === 'comment') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if ($name === '' || $content === '') {
            echo json_encode(['status'=>'error','message'=>'Nama dan ucapan diperlukan.']);
            exit;
        }
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $entry = ['name'=>$safeName,'content'=>nl2br($safeContent),'timestamp'=>date('Y-m-d H:i:s')];
        if (write_json_line($dataComments, $entry)) {
            echo json_encode(['status'=>'ok','comments'=>read_lines_json($dataComments)]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Gagal menyimpan komentar. Periksa permission.']);
        }
        exit;
    }

    if ($action === 'rsvp') {
        $name = isset($_POST['r_name']) ? trim($_POST['r_name']) : '';
        $attendance = isset($_POST['attendance']) ? $_POST['attendance'] : '';
        $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 0;
        if ($name === '' || ($attendance !== 'hadir' && $attendance !== 'tidak')) {
            echo json_encode(['status'=>'error','message'=>'Nama dan pilihan kehadiran diperlukan.']);
            exit;
        }
        $entry = ['name'=>htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                  'attendance'=>$attendance,'guests'=>$guests,'timestamp'=>date('Y-m-d H:i:s')];
        if (write_json_line($dataRsvp, $entry)) {
            echo json_encode(['status'=>'ok','rsvp'=>read_lines_json($dataRsvp)]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Gagal menyimpan RSVP. Periksa permission.']);
        }
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'Action tidak dikenal.']);
    exit;
}

// ------------------ Data awal untuk render ------------------
 $comments = read_lines_json($dataComments);
 $rsvps    = read_lines_json($dataRsvp);

// If guest param empty, fallback to generic
 $guest_display = $guest !== "" ? htmlspecialchars($guest) : "Bapak/Ibu/Saudara/i";

?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php echo htmlspecialchars($BRIDE_FULL . ' & ' . $GROOM_FULL); ?> — Undangan Premium</title>
<meta name="description" content="Undangan pernikahan premium - Royal Level 4">

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@1,700&display=swap" rel="stylesheet">

<style>
:root{
  --gold-1: #d9b86a;
  --gold-2: #c79b4a;
  --text-light: #f0f0f0;
  --text-muted: #cccccc;
  --card-bg: rgba(10, 10, 20, 0.65); /* Glassmorphism effect */
}

/* Base */
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  font-family: 'Inter', sans-serif;
  color: var(--text-light);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* --- PERUBAHAN PENTING: Background Gambar --- */
body {
  background-image: url('bg.jpeg');   /* gunakan nama file Anda */
  background-size: contain;          /* menjaga proporsi gambar */
  background-repeat: no-repeat;      
  background-position: top center;   /* foto selalu rata tengah */
  background-color: #f7f7f7;         /* warna dasar di bawahnya */
}

/* Membuat konten tetap rapi di atas background */
.wrapper {
  max-width: 550px;
  margin: 0 auto;
  padding-top: 20px;
}

/* Cover */
#cover {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 20px;
}
.royal-card {
  background-color: rgba(0, 0, 0, 0.6); /* Cover card lebih gelap agar menonjol */
  backdrop-filter: blur(0px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 40px;
  text-align: center;
  max-width: 600px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}
.pair { font-family: 'Playfair Display', serif; font-size: 3.5rem; color: var(--gold-1); margin: 10px 0; }
.title { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--text-light); margin: 0; }
.date { color: var(--text-muted); font-size: 1.1rem; margin-top: 10px; }
.btn-cta {
  display: inline-block;
  margin-top: 25px;
  padding: 14px 32px;
  border-radius: 50px;
  background: linear-gradient(90deg, var(--gold-1), var(--gold-2));
  color: #000;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 10px 25px rgba(201, 155, 74, 0.3);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.btn-cta:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(201, 155, 74, 0.4); }

/* Main Content Sections */
#main { display: none; }
section {
  padding: 80px 20px;
  max-width: 1000px;
  margin: 0 auto;
}
.section-card {
  background: var(--card-bg);
  backdrop-filter: blur(0px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.5rem;
  color: var(--gold-1);
  margin-top: 0;
  margin-bottom: 20px;
  text-align: center;
}
.section-subtitle {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: var(--text-light);
  margin-top: 0;
  margin-bottom: 15px;
}
p, li { color: var(--text-muted); line-height: 1.7; }
strong { color: var(--text-light); }

.quran-section {
  padding: 40px;
  margin-top: 30px;
  margin-bottom: 30px;
}

.quran-title {
  text-align: center;
  color: var(--gold-1);
  font-size: 1.6rem;
  margin-bottom: 15px;
  font-family: "Playfair Display", serif;
}

.quran-box {
  text-align: center;
  max-width: 780px;
  margin: 0 auto;
}

.quran-arab {
  font-family: 'Amiri', serif;
  font-size: 30px;
  color: var(--gold-1);
  direction: rtl;
  line-height: 1.9;
  margin-bottom: 15px;
}

.quran-terjemahan {
  font-size: 17px;
  color: var(--text-light);
  line-height: 1.7;
  max-width: 720px;
  margin: 0 auto;
}

/* Event Details */
.event-list { list-style: none; padding: 0; }
.event-list li { margin-bottom: 30px; display: flex; align-items: center; gap: 20px; }
.event-date {
  background: var(--gold-1);
  color: #000;
  border-radius: 12px;
  padding: 15px;
  font-weight: 700;
  min-width: 100px;
  text-align: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

/* Countdown */
.countdown { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
.count-item { text-align: center; }
.count-num { font-family: 'Playfair Display', serif; font-size: 3rem; color: var(--gold-1); display: block; }
.count-label { font-size: 1rem; color: var(--text-muted); text-transform: uppercase; }

/* Gallery */
.gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 30px; }
.gallery img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; cursor: pointer; transition: transform 0.3s ease; }
.gallery img:hover { transform: scale(1.05); }

/* Gift & RSVP Grid */
.gift-rsvp-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  align-items: start;
}
@media (max-width: 768px) {
  .gift-rsvp-grid { grid-template-columns: 1fr; }
}

.arab-calligraphy {
    font-family: 'Amiri', serif;
    font-size: 48px;
    color: #d8c07a; /* warna emas elegan */
    text-align: center;
    margin-bottom: 25px;
    direction: rtl;         /* penting untuk teks Arab */
    unicode-bidi: isolate;  /* membuat rendering lebih rapi */
}

/* Forms */
.input, textarea, select {
  width: 100%;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.05);
  color: var(--text-light);
  font-size: 1rem;
  margin-bottom: 15px;
}
.input::placeholder { color: var(--text-muted); }
.btn-submit {
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 8px;
  background: linear-gradient(90deg, var(--gold-1), var(--gold-2));
  color: #000;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(201, 155, 74, 0.3); }

/* Comments */
.comment-list { margin-top: 30px; }
.comment-item {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 15px;
}
.comment-meta { display: flex; justify-content: space-between; margin-bottom: 10px; }
.comment-meta strong { color: var(--gold-1); }
.comment-meta small { color: var(--text-muted); }

/* Footer */
.footer {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
}

/* Modal */
#modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; }
#modal img { max-width: 90%; max-height: 90%; border-radius: 10px; }
</style>
</head>
<body>

<!-- COVER -->
<div id="cover">
  <div class="royal-card" data-aos="fade-up">
    <div class="title">The Wedding Of</div>
    <div class="pair"><?php echo htmlspecialchars($BRIDE_FULL); ?> &amp; <?php echo htmlspecialchars($GROOM_FULL); ?></div>
    <div class="date"><?php echo htmlspecialchars($RESEPSI_DATE); ?></div>
    <div style="margin-top: 15px; color: var(--text-muted);">Kepada Yth. Bapak/Ibu/Saudara/i</div>
    <div style="margin-top: 5px; font-weight: bold; color: var(--text-light);"><?php echo $guest_display; ?></div>
    <a class="btn-cta" id="btn-open" href="javascript:void(0)">Buka Undangan</a>
  </div>
</div>

<!-- MAIN CONTENT -->
<main id="main">
  <!-- Couple Intro -->
  <section data-aos="fade-up">
    <div class="section-card">
      <h2 class="arab-calligraphy">السلام عليكم ورحمة الله وبركاته</h1>
      <p style="text-align: center; font-size: 1.1rem; color: var(--text-light);">
        Maha Suci Allah yang telah menciptakan makhluk-Nya berpasang-pasangan. Ya Allah, perkenankanlah kami merangkai kasih sayang yang Kau ciptakan untuk membina mahligai pernikahan suci.
      </p>
      <div style="display: flex; justify-content: center; align-items: center; gap: 40px; margin-top: 40px; flex-wrap: wrap;">
        <div style="text-align: center;">
           <div style="width:150px; height:150px; border-radius: 50%; background: var(--gold-1); display: flex; align-items: center; justify-content: center; font-size: 60px; color: #000; font-weight: bold; margin: 0 auto;"><?php echo strtoupper(substr($BRIDE_FULL,0,1)); ?></div>
           <h3 class="section-subtitle" style="margin-top: 15px;"><?php echo htmlspecialchars($BRIDE_FULL); ?></h3>
           <p>Putri dari Bapak ... & Ibu ...</p>
        </div>
        <div style="font-family: 'Playfair Display', serif; font-size: 3rem; color: var(--gold-1);">&</div>
        <div style="text-align: center;">
           <div style="width:150px; height:150px; border-radius: 50%; background: var(--gold-1); display: flex; align-items: center; justify-content: center; font-size: 60px; color: #000; font-weight: bold; margin: 0 auto;"><?php echo strtoupper(substr($GROOM_FULL,0,1)); ?></div>
           <h3 class="section-subtitle" style="margin-top: 15px;"><?php echo htmlspecialchars($GROOM_FULL); ?></h3>
           <p>Putra dari Bapak ... & Ibu ...</p>
        </div>
      </div>
    </div>
  </section>

<!-- Quran Section -->
<section id="quran-ayat" data-aos="fade-up">
  <div class="section-card quran-section">

    <!-- Ayat Ar-Rum 21 -->
    <div class="quran-box">
      <h2 class="quran-title">QS. Ar-Rûm : 21</h2>

      <div class="quran-arab">
        وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُمْ مِنْ أَنْفُسِكُمْ أَزْوَاجًا 
        لِتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُمْ مَوَدَّةً وَرَحْمَةً 
        إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِقَوْمٍ يَتَفَكَّرُونَ
      </div>

      <div class="quran-terjemahan">
        “Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu
        pasangan dari jenismu sendiri agar kamu cenderung dan merasa tenteram
        kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.”
      </div>
    </div>

  </div>
</section>

  <!-- Event Details -->
  <section data-aos="fade-up">
    <div class="section-card">
      <h2 class="section-title">Waktu & Tempat</h2>
      <ul class="event-list">
        <li>
          <div class="event-date">13<br>Apr</div>
          <div>
            <strong>Akad Nikah</strong>
            <p><?php echo htmlspecialchars($AKAD_DATE); ?></p>
            <p><?php echo htmlspecialchars($AKAD_LOCATION); ?></p>
          </div>
        </li>
        <li>
          <div class="event-date">14<br>Apr</div>
          <div>
            <strong>Resepsi</strong>
            <p><?php echo htmlspecialchars($RESEPSI_DATE); ?></p>
            <p><?php echo htmlspecialchars($RESEPSI_LOCATION); ?></p>
          </div>
        </li>
      </ul>
    </div>
  </section>
<!-- ==========================
     PREMIUM MAP SECTION
=========================== -->
<section id="maps" style="padding:90px 20px; background:#f7f3ea; position:relative;">

    <div class="section-title" data-aos="fade-up">
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size:40px; font-weight:700; color:#b48a40;">
            Lokasi Acara
        </h2>
        <p style="font-size:18px; color:#7a6a4a; margin-top:5px;">
            Dengan penuh sukacita kami mengundang Anda hadir di tempat terbaik kami
        </p>
    </div>

    <div data-aos="fade-up" style="max-width:900px; margin:40px auto 0 auto;">
        
        <!-- Premium Frame -->
        <div style="
            background:#fff8ed;
            padding:18px;
            border-radius:25px;
            border:4px solid #d9c6a3;
            box-shadow:0 15px 40px rgba(0,0,0,0.15);
            transition:0.3s;
        "
        onmouseover="this.style.boxShadow='0 18px 45px rgba(0,0,0,0.25)'"
        onmouseout="this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'">

            <!-- Pin Gold -->
            <div style="text-align:center; margin-bottom:15px;">
                <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png"
                     style="width:55px; opacity:0.9; filter:sepia(100%) hue-rotate(10deg) saturate(300%);">
            </div>

            <!-- Map -->
            <div style="
                width:100%;
                height:380px;
                border-radius:20px;
                overflow:hidden;
            ">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1983.0734!2d106.827153!3d-6.175392!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e778adf997%3A0x7e221c1ac67b1f93!2sMonas!5e0!3m2!1sid!2sid!4v123456789"
                    width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy">
                </iframe>
            </div>

        </div>

        <!-- Tombol Premium -->
        <div style="text-align:center; margin-top:35px;">
            <a href="https://maps.google.com/?q=Monas+Jakarta"
               target="_blank"
               style="
                    background:linear-gradient(135deg, #b48a40, #e4c284);
                    padding:14px 35px;
                    color:white;
                    border-radius:40px;
                    text-decoration:none;
                    font-size:18px;
                    font-weight:700;
                    letter-spacing:0.5px;
                    box-shadow:0 8px 25px rgba(0,0,0,0.25);
                    transition:0.3s;
               "
               onmouseover="this.style.boxShadow='0 12px 35px rgba(0,0,0,0.35)'"
               onmouseout="this.style.boxShadow='0 8px 25px rgba(0,0,0,0.25)'">
               📍 Buka Lokasi di Google Maps
            </a>
        </div>

    </div>

</section>

  <!-- Countdown -->
  <section data-aos="fade-up">
    <div class="section-card" style="text-align: center;">
      <h2 class="section-title">Hitung Mundur</h2>
      <div class="countdown" data-date="<?php echo date('c', strtotime('2025-12-17 07:00:00')); ?>">
        <div class="count-item"><span class="count-num" data-days>00</span><span class="count-label">Hari</span></div>
        <div class="count-item"><span class="count-num" data-hours>00</span><span class="count-label">Jam</span></div>
        <div class="count-item"><span class="count-num" data-minutes>00</span><span class="count-label">Menit</span></div>
        <div class="count-item"><span class="count-num" data-seconds>00</span><span class="count-label">Detik</span></div>
      </div>
    </div>
  </section>

  <!-- Gallery -->
  <section data-aos="fade-up">
    <div class="section-card">
      <h2 class="section-title">Gallery</h2>
      <div class="gallery">
        <img src="https://placehold.co/600x600/efe1dd/ffffff?text=1" alt="Gallery 1">
        <img src="https://placehold.co/600x600/ece7e0/ffffff?text=2" alt="Gallery 2">
        <img src="https://placehold.co/600x600/f6efe8/ffffff?text=3" alt="Gallery 3">
      </div>
    </div>
  </section>

  <!-- Wedding Gift & RSVP -->
<!-- ================= WEDDING GIFT + RSVP (2 KOLOM) ================= -->
<section data-aos="fade-up">
  <div class="gift-rsvp-grid">

    <!-- ========== WEDDING GIFT ========== -->
    <div class="section-card">
      <h3 class="section-subtitle">Wedding Gift</h3>
      <p>Bagi keluarga dan sahabat yang ingin memberikan tanda kasih, dapat melalui:</p>

      <!-- CARD STYLE BCA -->
      <div style="
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        padding: 20px;
        border-radius: 15px;
        margin-top: 15px;
        color: white;
        box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        position: relative;
        overflow: hidden;
      ">

        <!-- Pattern Lingkaran -->
        <div style="
          position:absolute;
          top:-20px;
          right:-20px;
          width:120px;
          height:120px;
          background:rgba(255,255,255,0.15);
          border-radius:50%;
        "></div>

        <!-- Logo BCA -->
        <div style="
          font-weight:bold;
          font-size:1.2rem;
          letter-spacing:1px;
          display:flex;
          align-items:center;
          gap:8px;
        ">
          <span style="
            background:white; 
            color:#1d4ed8; 
            padding:3px 8px; 
            border-radius:6px; 
            font-weight:700;
          ">BCA</span>
          <span style="opacity:0.9">Kartu Kasih</span>
        </div>

        <!-- Nomor Rekening -->
        <p style="
          font-family: monospace;
          font-size: 1.4rem;
          letter-spacing: 3px;
          margin: 18px 0 5px 0;
        ">1294 0100 3981 508</p>

        <!-- Nama -->
        <p style="
          text-transform: uppercase;
          font-size: 0.9rem;
          letter-spacing: 1px;
          margin-bottom: 0;
        ">a.n. <?php echo htmlspecialchars($BRIDE_FULL); ?></p>

        <!-- Chip -->
        <div style="
          width:45px;
          height:35px;
          background:rgba(255,255,255,0.4);
          border-radius:8px;
          position:absolute;
          top:60px;
          left:20px;
        "></div>
      </div>
      <!-- END CARD -->
    </div>

    <!-- ========== RSVP FORM ========== -->
    <div class="section-card">
      <h3 class="section-subtitle">Konfirmasi Kehadiran (RSVP)</h3>

      <form id="rsvp-form">
        <input class="input" id="r_name" name="r_name" placeholder="Nama Lengkap" required>

        <select class="input" id="attendance" name="attendance" required>
          <option value="">-- Konfirmasi Kehadiran --</option>
          <option value="hadir">Hadir</option>
          <option value="tidak">Tidak Hadir</option>
        </select>

        <button class="btn-submit" type="submit">Kirim Konfirmasi</button>
      </form>

      <p style="margin-top: 15px; text-align: center;">
        Total RSVP: <strong id="rsvp-count"><?php echo count($rsvps); ?></strong>
      </p>
    </div>

  </div>
</section>

  <!-- Comments -->
  <section data-aos="fade-up">
    <div class="section-card">
      <h2 class="section-title">Ucapan & Doa</h2>
      <form id="comment-form">
        <input class="input" id="c_name" name="name" placeholder="Nama Anda" required>
        <textarea class="input" id="c_content" name="content" rows="4" placeholder="Tulis ucapan dan doa" required></textarea>
        <button class="btn-submit" type="submit">Kirim Ucapan</button>
      </form>
      <div class="comment-list" id="comment-list">
        <?php foreach($comments as $c): ?>
          <div class="comment-item">
            <div class="comment-meta">
              <strong><?php echo htmlspecialchars($c['name']); ?></strong>
              <small><?php echo htmlspecialchars($c['timestamp']); ?></small>
            </div>
            <p><?php echo $c['content']; ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <div class="footer">© 2024 <?php echo htmlspecialchars($BRIDE_FULL . ' & ' . $GROOM_FULL); ?>. All Rights Reserved.</div>
</main>

<!-- Modal Gallery -->
<div id="modal"><img id="modal-img" src=""></div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ duration: 1000, once: true });

document.getElementById('btn-open').addEventListener('click', () => {
  document.getElementById('cover').style.display = 'none';
  document.getElementById('main').style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });
  // playMusic(); // Uncomment jika ada fungsi musik
});

// Countdown
(function(){
  const el = document.querySelector('[data-date]');
  if(!el) return;
  const date = new Date(el.getAttribute('data-date')).getTime();
  const tick = () => {
    const now = Date.now(); const diff = date - now;
    if(diff <= 0){ document.querySelector('[data-days]').innerText='00'; document.querySelector('[data-hours]').innerText='00'; document.querySelector('[data-minutes]').innerText='00'; document.querySelector('[data-seconds]').innerText='00'; return; }
    const d = Math.floor(diff / (1000*60*60*24)); const h = Math.floor((diff % (1000*60*60*24)) / (1000*60*60));
    const m = Math.floor((diff % (1000*60*60)) / (1000*60)); const s = Math.floor((diff % (1000*60)) / 1000);
    document.querySelector('[data-days]').innerText = String(d).padStart(2,'0'); document.querySelector('[data-hours]').innerText = String(h).padStart(2,'0');
    document.querySelector('[data-minutes]').innerText = String(m).padStart(2,'0'); document.querySelector('[data-seconds]').innerText = String(s).padStart(2,'0');
  };
  tick(); setInterval(tick, 1000);
})();

// Simple POST helper
async function postData(payload){
  const form = new FormData();
  for(const k in payload) form.append(k,payload[k]);
  const res = await fetch(window.location.href, { method:'POST', body: form });
  return res.json();
}

// COMMENTS
document.getElementById('comment-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const name = document.getElementById('c_name').value.trim();
  const content = document.getElementById('c_content').value.trim();
  if(!name || !content){ alert('Nama dan ucapan wajib diisi.'); return; }
  const res = await postData({ action:'comment', name, content });
  if(res.status === 'ok'){
    renderComments(res.comments);
    document.getElementById('comment-form').reset();
    alert('Ucapan berhasil terkirim!');
  } else { alert(res.message || 'Terjadi kesalahan'); }
});

function renderComments(list){
  const wrap = document.getElementById('comment-list');
  wrap.innerHTML = '';
  (list || []).forEach(it => {
    const div = document.createElement('div'); div.className = 'comment-item';
    div.innerHTML = `<div class="comment-meta"><strong>${it.name}</strong><small>${it.timestamp}</small></div><p>${it.content}</p>`;
    wrap.prepend(div); // Tambahkan komentar baru di atas
  });
}

// RSVP
document.getElementById('rsvp-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const r_name = document.getElementById('r_name').value.trim();
  const attendance = document.getElementById('attendance').value;
  if(!r_name || !attendance){ alert('Nama dan konfirmasi kehadiran wajib diisi.'); return; }
  const res = await postData({ action:'rsvp', r_name, attendance, guests: 1 });
  if(res.status === 'ok'){
    document.getElementById('rsvp-count').innerText = (res.rsvp || []).length;
    document.getElementById('rsvp-form').reset();
    alert('Konfirmasi kehadiran Anda telah tercatat. Terima kasih!');
  } else { alert(res.message || 'Gagal mengirim RSVP'); }
});

// Gallery Modal
document.querySelectorAll('.gallery img').forEach(img => {
  img.addEventListener('click', () => {
    document.getElementById('modal-img').src = img.src;
    document.getElementById('modal').style.display = 'flex';
  });
});
document.getElementById('modal').addEventListener('click', () => { document.getElementById('modal').style.display = 'none'; });

</script>
</body>
</html>
