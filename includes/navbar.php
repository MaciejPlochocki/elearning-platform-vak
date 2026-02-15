<?php
// includes/navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <div class="nav-left">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
        <?php else: ?>
            <a href="index.php">E-Learning Platform</a>
        <?php endif; ?>
    </div>
    
    <div class="nav-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="kursy.php">Kursy</a>
            <a href="moje_kursy.php">Moje kursy</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Wyloguj</a>
        <?php else: ?>
            <a href="cennik.php">Cennik</a>
            <a href="register.php">Zarejestruj</a>
            <a href="login.php">Zaloguj</a>
        <?php endif; ?>
    </div>
</nav>