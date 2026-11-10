<header class="top">
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="search" placeholder="Search students, teachers, classes" aria-label="Search">
      </div>
      <div class="me">
        <div><b><?php echo $_SESSION['name']; ?></b><small><?php echo $_SESSION['email']; ?></small></div>
        <span class="av"><?php echo strtoupper(substr($_SESSION['name'], 0,2)) ?></span>
        <button class="logout" type="button"> <a href="logout.php">Logout</a></button>
      </div>
    </header>