<?php
// profil.php
session_start();
require_once 'includes/db.php';

// Sprawdzenie czy zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Obsługa akcji formularzy z profilu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['zmien_styl'])) {
        // Resetuje styl i wyrzuca do testu
        $stmt = $pdo->prepare("UPDATE uzytkownicy SET styl_uczenia = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        header("Location: test_vak.php");
        exit();
    }
    
    if (isset($_POST['anuluj_subskrypcje'])) {
        $stmt = $pdo->prepare("UPDATE uzytkownicy SET subskrypcja_aktywna = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "<div class='alert alert-success'>Subskrypcja została anulowana.</div>";
    }

    if (isset($_POST['zmien_haslo'])) {
        $nowe_haslo = $_POST['nowe_haslo'];
        if (strlen($nowe_haslo) < 8 || !preg_match("#[0-9]+#", $nowe_haslo) || !preg_match("#[A-Z]+#", $nowe_haslo)) {
            $message = "<div class='alert alert-danger'>Nowe hasło jest za słabe!</div>";
        } else {
            $hashed = password_hash($nowe_haslo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET haslo = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $message = "<div class='alert alert-success'>Hasło zostało zmienione.</div>";
        }
    }
}

// Pobranie danych użytkownika
$stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Pobranie ilości ukończonych kursów
$stmtUkonczone = $pdo->prepare("SELECT COUNT(*) as ilosc FROM zapisy WHERE id_ucznia = ? AND status = 'ukonczony'");
$stmtUkonczone->execute([$user_id]);
$ukonczone = $stmtUkonczone->fetch()['ilosc'];

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mój Profil - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .profile-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .profile-info p { font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .profile-info strong { color: var(--primary-color); }
        .action-form { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="section-title">Mój Profil</h2>
        <?php echo $message; ?>

        <div class="profile-grid">
            <div class="profile-card profile-info">
                <h3>Informacje o koncie</h3>
                <p><strong>Imię i Nazwisko:</strong> <?php echo htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($user['telefon']); ?></p>
                <p><strong>Od kiedy z nami:</strong> <?php echo date("d.m.Y", strtotime($user['data_dolaczenia'])); ?></p>
                <p><strong>Ukończonych kursów:</strong> <?php echo $ukonczone; ?></p>
                <p><strong>Twój styl uczenia się:</strong> <span style="text-transform: capitalize; font-weight: bold; color: var(--success-color);"><?php echo htmlspecialchars($user['styl_uczenia']); ?></span></p>
                <p><strong>Subskrypcja:</strong> <?php echo $user['subskrypcja_aktywna'] ? 'Aktywna' : 'Nieaktywna'; ?></p>
                
                <form method="POST" class="action-form">
                    <button type="submit" name="zmien_styl" class="btn" style="background-color: #f39c12;">Zmień styl uczenia się (rozwiąż test ponownie)</button>
                </form>
                
                <?php if($user['subskrypcja_aktywna']): ?>
                <form method="POST" style="margin-top: 10px;">
                    <button type="submit" name="anuluj_subskrypcje" class="btn" style="background-color: var(--danger-color);" onclick="return confirm('Na pewno chcesz anulować subskrypcję?');">Anuluj subskrypcję</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="profile-card">
                <h3>Zmień hasło</h3>
                <form method="POST" style="margin-top: 15px;">
                    <div class="form-group">
                        <input type="password" name="nowe_haslo" placeholder="Wpisz nowe hasło" required>
                        <small>Min. 8 znaków, wielka litera i cyfra.</small>
                    </div>
                    <button type="submit" name="zmien_haslo" class="btn">Zaktualizuj hasło</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>