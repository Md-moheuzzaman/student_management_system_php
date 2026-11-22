<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f9fc">
    <meta name="description" content="Learn about Ustudent, a clean and modern student management platform built for organised academic administration.">
    <title>Ustudent — Student Management System</title>

    <!-- Bootstrap CSS only: no JavaScript is required for this page. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Shared styles (navbar, buttons, footer, fonts) -->
    <link rel="stylesheet" href="assets/css/homepage_style.css">
    <!-- About page only styles -->
    <link rel="stylesheet" href="assets/css/about_style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar site-navbar">
        <div class="container nav-container">
            <a class="navbar-brand brand-mark" href="index.php" aria-label="Ustudent home">
                <span class="brand-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                <span>u<span class="brand-accent">student</span></span>
            </a>

            <div class="desktop-menu d-none d-lg-flex align-items-center">
                <a class="nav-link active" href="index.php">Home</a> &nbsp;
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link " href="about.php">About</a>
                <a class="btn btn-brand nav-login" href="login.php">Login</a>
                <a class="btn btn-brand nav-login" href="registration.php">Register</a>
            </div>

            <!-- Native details menu keeps mobile navigation interactive without JavaScript. -->
            <details class="mobile-menu d-lg-none">
                <summary aria-label="Open navigation menu"><i class="bi bi-list" aria-hidden="true"></i><span>Menu</span></summary>
                <div class="mobile-menu-panel">
                    <a href="index.php">Home</a> 
                    <a href="#features">Features</a>
                    <a href="about.php">About</a>
                    <a href="login.php">Login</a>
                    <a class="mobile-login" href="registration.php">Register</a>
                </div>
            </details>
        </div>
    </nav>
</header>

<main>
    <!-- Page hero -->
    <section class="page-hero" id="top">
        <div class="hero-orb hero-orb-one" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-two" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="page-hero-copy text-center mx-auto">
                <!-- <span class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span> ABOUT USTUDENT</span> -->
                <h1>Making campus life <span>simpler to manage.</span></h1>
                <p class="hero-description mx-auto">Ustudent is a student management platform that brings records, courses and academic administration into one clear, organised space.</p>
            </div>
        </div>
    </section>

    <!-- Our story -->
    <section class="about-section section-space" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="about-image-wrap">
                        <img src="assets/images/photo-1523240795612-9a054b0db644.avif" alt="Students collaborating on campus" class="about-image">
                        <div class="about-image-note">
                            <i class="bi bi-lightbulb-fill" aria-hidden="true"></i>
                            <div><strong>Our idea</strong><br>Clear records for everyone</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-copy">
                        <span class="section-kicker">OUR STORY</span>
                        <h2>Built around the everyday needs of campus</h2>
                        <p>Managing students often means scattered files, repeated data entry and slow communication. Ustudent was imagined to change that by keeping student information organised and easy to reach.</p>
                        <p>Whether you are an administrator, a teacher or a student, the goal is the same: less time on paperwork, more time on learning.</p>
                        <div class="about-points">
                            <div><span><i class="bi bi-check" aria-hidden="true"></i></span> One place for student information</div>
                            <div><span><i class="bi bi-check" aria-hidden="true"></i></span> Simple tools for academic administration</div>
                            <div><span><i class="bi bi-check" aria-hidden="true"></i></span> A clean, easy-to-learn interface</div>
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
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-blue"><i class="bi bi-people-fill" aria-hidden="true"></i></div>
                        <h3>Student records</h3>
                        <p>Keep every student profile and academic detail organised and easy to find in one place.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-purple"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></div>
                        <h3>Courses &amp; subjects</h3>
                        <p>Manage courses and subjects with a clear structure that teachers and students can follow.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-orange"><i class="bi bi-calendar-check-fill" aria-hidden="true"></i></div>
                        <h3>Attendance</h3>
                        <p>Record and review attendance without paper registers or repeated data entry.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-green"><i class="bi bi-bar-chart-line-fill" aria-hidden="true"></i></div>
                        <h3>Results &amp; reports</h3>
                        <p>See academic progress at a glance with simple, readable results and summaries.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-blue"><i class="bi bi-person-badge-fill" aria-hidden="true"></i></div>
                        <h3>Role-based access</h3>
                        <p>Administrators, teachers and students each see what is relevant to them.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-purple"><i class="bi bi-phone-fill" aria-hidden="true"></i></div>
                        <h3>Works on any screen</h3>
                        <p>A responsive layout that looks clean on desktop, tablet and mobile.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & vision -->
    <section class="mission-section section-space">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">WHAT DRIVES US</span>
                <h2>Our mission and vision</h2>
                <p>The principles that guide how Ustudent is designed and improved.</p>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-blue"><i class="bi bi-bullseye" aria-hidden="true"></i></div>
                        <h3>Our mission</h3>
                        <p>To give academic teams a simple, reliable way to manage student information so that administration supports learning instead of slowing it down.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon feature-purple"><i class="bi bi-eye-fill" aria-hidden="true"></i></div>
                        <h3>Our vision</h3>
                        <p>A connected campus where every student, teacher and administrator has a clear view of the academic journey in one organised space.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section section-space">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">OUR VALUES</span>
                <h2>What we care about</h2>
                <p>Simple ideas that shape every page and feature.</p>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card value-card h-100">
                        <div class="feature-icon feature-blue"><i class="bi bi-layout-text-window-reverse" aria-hidden="true"></i></div>
                        <h3>Clarity</h3>
                        <p>Information that is easy to find, read and understand.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card value-card h-100">
                        <div class="feature-icon feature-green"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                        <h3>Trust</h3>
                        <p>Student data handled with care, accuracy and respect.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card value-card h-100">
                        <div class="feature-icon feature-orange"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></div>
                        <h3>Simplicity</h3>
                        <p>Tools that save time without a steep learning curve.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card value-card h-100">
                        <div class="feature-icon feature-purple"><i class="bi bi-people-fill" aria-hidden="true"></i></div>
                        <h3>Support</h3>
                        <p>Designed to help every learner and every team member.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Who it is for -->
    <section class="audience-section section-space">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">WHO IT IS FOR</span>
                <h2>One platform, every role</h2>
                <p>Ustudent is designed for everyone involved in campus life.</p>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="audience-card h-100">
                        <span class="audience-icon"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
                        <h3>Administrators</h3>
                        <p>Manage student records and academic information from one organised panel.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="audience-card h-100">
                        <span class="audience-icon"><i class="bi bi-easel2-fill" aria-hidden="true"></i></span>
                        <h3>Teachers</h3>
                        <p>Stay connected with students and keep academic details accurate and current.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="audience-card h-100">
                        <span class="audience-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                        <h3>Students</h3>
                        <p>Get a clear view of your own academic journey, whenever you need it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <i class="bi bi-mortarboard-fill cta-decoration" aria-hidden="true"></i>
                <div>
                    <span class="section-kicker">GET STARTED</span>
                    <h2>Ready to organise your campus?</h2>
                    <p>Create an account or log in to start using Ustudent.</p>
                </div>
                <a href="registration.php" class="btn btn-light">Register now <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <a class="navbar-brand brand-mark footer-brand" href="index.php" aria-label="Ustudent home">
                <span class="brand-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                <span>u<span class="brand-accent">student</span></span>
            </a>
            <p>A smarter way to manage students and academic records.</p>
            <a class="back-top" href="#top">Back to top <i class="bi bi-arrow-up" aria-hidden="true"></i></a>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> Ustudent. All rights reserved.</span>
            <span>Student Management System</span>
        </div>
    </div>
</footer>

</body>
</html>