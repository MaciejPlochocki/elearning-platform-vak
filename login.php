<?php
// login.php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telefon = trim($_POST['telefon']);
    $haslo = $_POST['haslo'];

    // Pobranie użytkownika
    $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE telefon = ?");
    $stmt->execute([$telefon]);
    $user = $stmt->fetch();

    if ($user && password_verify($haslo, $user['haslo'])) {
        // Poprawne logowanie - zapisanie do sesji
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['funkcja'] = $user['funkcja'];

        // Przekierowania na podstawie pełnionej funkcji
        if ($user['funkcja'] == 'nauczyciel') {
            header("Location: panel_nauczyciela.php");
            exit();
        } elseif ($user['funkcja'] == 'moderator') {
            header("Location: panel_moderatora.php");
            exit();
        } else {
            // Uczeń - sprawdzamy czy ma zrobiony test VAK
            if (empty($user['styl_uczenia'])) {
                header("Location: test_vak.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        }
    } else {
        $error = "Błędny numer telefonu lub hasło.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 style="margin-bottom: 20px; text-align: center;">Zaloguj się</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="telefon" placeholder="Numer telefonu" required>
                </div>
                <div class="form-group">
                    <input type="password" name="haslo" placeholder="Hasło" required>
                </div>
                
                <button type="submit" class="btn">Zaloguj</button>
                <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                    Nie masz konta? <a href="register.php" style="color: var(--primary-color);">Zarejestruj się</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>