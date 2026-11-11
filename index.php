<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f9fc">
    <meta name="description" content="Ustudent is a clean and modern student management platform concept for organised academic administration.">
    <title>Ustudent — Student Management System</title>

    <!-- Bootstrap CSS only: no JavaScript is required for this landing page. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/homepage_style.css"> 
</head>
<body>

<header class="site-header">
    <nav class="navbar site-navbar">
        <div class="container nav-container">
            <a class="navbar-brand brand-mark" href="#home" aria-label="Ustudent home">
                <span class="brand-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                <span>u<span class="brand-accent">student</span></span>
            </a>

            <div class="desktop-menu d-none d-lg-flex align-items-center">
                <a class="nav-link active" href="index.php">Home</a> &nbsp;
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link" href="#about">About</a>
                <a class="btn btn-brand nav-login" href="admin/index.php"> Login <i class= aria-hidden="true"></i></a>
                <a class="btn btn-brand nav-login" href="admin/index.php"> Register <i class= aria-hidden="true"></i></a>
            </div>

            <!-- Native details menu keeps mobile navigation interactive without JavaScript. -->
            <details class="mobile-menu d-lg-none">
                <summary aria-label="Open navigation menu"><i class="bi bi-list" aria-hidden="true"></i><span>Menu</span></summary>
                <div class="mobile-menu-panel">
                    <a href="index.php">Home</a> 
                    <a href="#features">Features</a> 
                    <a href="#about">About</a>
                    <!-- <a class="mobile-login" href="admin/index.php">Admin Login <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a> -->
                </div>
            </details>
        </div>
    </nav>
</header>

<main>
    <!-- Hero -->
    <section class="hero-section" id="home">
        <div class="hero-orb hero-orb-one" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-two" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-copy">
                        <span class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span> SMARTER CAMPUS MANAGEMENT</span>
                        <h1>Build a better way to <span>manage students.</span></h1>
                        <p class="hero-description">Bring student information and academic administration into one organised space. Ustudent makes campus management feel clearer, simpler and more connected.</p>
                        <div class="hero-buttons">
                            <!-- <a href="admin/index.php" class="btn btn-brand btn-lg">Go to Admin Panel <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a> -->
                            <a href="#features" class="btn btn-soft btn-lg"><i class="bi bi-grid me-2" aria-hidden="true"></i> Explore features</a>
                        </div>
                        <div class="hero-proof">
                            <!-- <div class="proof-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></div> -->
                            <!-- <div><strong>Made for academic teams</strong><small>A clearer view of the student journey.</small></div> -->
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual-wrap">
                        <div class="hero-image-frame">
                            <img src="assets/images/photo-1523240795612-9a054b0db644.avif" alt="University students studying together on campus" class="hero-image">
                            <div class="image-shade" aria-hidden="true"></div>
                        </div>
                        <div class="floating-card attendance-card">
                            <span class="floating-icon green"><i class="bi bi-journal-check" aria-hidden="true"></i></span>
                            <div><strong>Academic records</strong><small>Information in one place</small></div>
                        </div>
                        <div class="floating-card student-card">
                            <div class="student-card-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></div>
                            <div><small>Student focused</small><strong>Support every learner</strong></div>
                            <!-- <i class="bi bi-stars ms-auto" aria-hidden="true"></i> -->
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features-section section-space" id="features">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">A CONNECTED CAMPUS</span>
                <h2>Everything starts with<br class="d-none d-md-block"> better Records</h2>
                <p>A welcoming digital space designed around the everyday needs of students, teachers and academic teams.</p>