<?php
include __DIR__ . '/../includes/dbconfig.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="A clearer way to manage student records, attendance, academic progress and fees.">
<title>Ustudent | Student Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="home-body">
<header class="home-header">
	<a class="home-brand" href="index.php"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 11.5 16 5l12 6.5L16 18 4 11.5Z"/><path d="M9 14.5v7c4.5 3.2 9.5 3.2 14 0v-7M28 12v8"/></svg></span><span>Ustudent<span class="brand-subtitle">STUDENT SERVICES</span></span></a>
	<nav class="home-nav" aria-label="Main navigation"><a href="#approach">Our approach</a><a href="#modules">System modules</a></nav>
	<a class="button button-dark" href="<?= e($portalHref) ?>"><?= e($portalText) ?><span aria-hidden="true">&rarr;</span></a>
</header>

<main>
	<section class="home-hero" id="approach">
		<div class="hero-copy">
			<p class="eyebrow"><span class="eyebrow-dot"></span> EDUCATION ADMINISTRATION, SIMPLIFIED</p>
			<h1>Make room for<br><em>better learning.</em></h1>
			<p class="hero-description">A considered workspace for the records that keep a school moving: students, attendance, academic progress and fees.</p>
			<div class="hero-actions"><a class="button button-green" href="<?= e($portalHref) ?>"><?= e($portalText) ?><span aria-hidden="true">&rarr;</span></a><a class="text-link" href="#modules">Explore the system <span aria-hidden="true">&darr;</span></a></div>
			<div class="hero-caption"><span class="caption-line"></span>ONE CONNECTED VIEW OF YOUR SCHOOL</div>
		</div>
		<div class="hero-art" aria-label="Illustration of the school management dashboard">
			<div class="art-sun"></div><div class="art-grid"></div>
			<div class="preview-window">
				<div class="preview-top"><span class="preview-dots"><i></i><i></i><i></i></span><span>STUDENT OVERVIEW</span><span class="preview-date"><?= e(date('M Y')) ?></span></div>
				<div class="preview-content">
					<div class="preview-greeting"><div><span class="preview-label">YOUR SCHOOL, AT A GLANCE</span><strong>Good morning.</strong></div><span class="preview-spark" aria-hidden="true">✳</span></div>
					<div class="preview-metrics"><div class="preview-metric metric-green"><span>STUDENT RECORDS</span><strong>Organized</strong><i aria-hidden="true">↗</i></div><div class="preview-metric metric-gold"><span>DAILY ATTENDANCE</span><strong>In focus</strong><i aria-hidden="true">✓</i></div></div>
					<div class="preview-list-head"><strong>Ustudent essentials</strong><span>01 — 04</span></div>
					<div class="preview-list"><span><i class="list-icon list-a">S</i>Students &amp; classes</span><b>01</b></div>
					<div class="preview-list"><span><i class="list-icon list-b">A</i>Attendance records</span><b>02</b></div>
					<div class="preview-list"><span><i class="list-icon list-c">R</i>Results &amp; fees</span><b>03</b></div>
				</div>
			</div>
			<div class="art-stamp"><span>PEOPLE</span><span>PROGRESS</span><span>POSSIBILITY</span></div>
		</div>
	</section>

	<section class="module-section" id="modules">
		<div class="section-heading"><div><p class="eyebrow">A CALMER WAY TO STAY ON TOP OF IT</p><h2>Every detail has<br>a place.</h2></div><p>Bring day-to-day school administration into one dependable system, with the information you need close at hand.</p></div>
		<div class="module-grid">
			<?php foreach ($modules as $module): ?>
				<article class="module-item"><span class="module-number"><?= e($module['number']) ?></span><h3><?= e($module['title']) ?></h3><p><?= e($module['text']) ?></p><a href="<?= e($portalHref) ?>" aria-label="Open <?= e(strtolower($module['title'])) ?> in the system">View module <span aria-hidden="true">&rarr;</span></a></article>
			<?php endforeach; ?>
		</div>
	</section>
</main>

<footer class="home-footer"><a class="home-brand" href="index.php"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 11.5 16 5l12 6.5L16 18 4 11.5Z"/><path d="M9 14.5v7c4.5 3.2 9.5 3.2 14 0v-7M28 12v8"/></svg></span><span>Ustudent<span class="brand-subtitle">STUDENT SERVICES</span></span></a><span>Thoughtful tools for the everyday work of education.</span><a href="<?= e($portalHref) ?>"><?= e($portalText) ?> &rarr;</a></footer>
</body>
</html>
