<?php
// register.php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $telefon = trim($_POST['telefon']);
    $email = trim($_POST['email']);
    $haslo = $_POST['haslo'];

    // Walidacja siły hasła po stronie serwera
    if (strlen($haslo) < 8 || !preg_match("#[0-9]+#", $haslo) || !preg_match("#[A-Z]+#", $haslo)) {
        $error = "Hasło musi mieć minimum 8 znaków, co najmniej jedną cyfrę i jedną wielką literę.";
    } else {
        $hashed_password = password_hash($haslo, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO uzytkownicy (imie, nazwisko, telefon, email, haslo, funkcja) VALUES (?, ?, ?, ?, ?, 'uczen')");
            $stmt->execute([$imie, $nazwisko, $telefon, $email, $hashed_password]);
            
            // Automatyczne logowanie po udanej rejestracji
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['funkcja'] = 'uczen';
            
            // Przekierowanie do testu VAK po rejestracji
            header("Location: test_vak.php");
            exit();
        } catch(PDOException $e) {
            if($e->getCode() == 23000) { // Kod błędu dla naruszenia unikalności
                $error = "Użytkownik o podanym adresie email lub numerze telefonu już istnieje.";
            } else {
                $error = "Wystąpił błąd podczas rejestracji: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Lokalne style dla paska siły hasła */
        .password-strength-container {
            width: 100%;
            height: 6px;
            background-color: var(--border-color);
            border-radius: 3px;
            margin-top: 5px;
            overflow: hidden;
        }
        #password-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        #password-feedback {
            font-size: 12px;
            color: var(--text-color);
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 style="margin-bottom: 20px; text-align: center;">Zarejestruj się</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="imie" placeholder="Imię" required>
                </div>
                <div class="form-group">
                    <input type="text" name="nazwisko" placeholder="Nazwisko" required>
                </div>
                <div class="form-group">
                    <input type="text" name="telefon" placeholder="Numer telefonu" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Adres E-mail" required>
                </div>
                <div class="form-group">
                    <input type="password" name="haslo" id="haslo" placeholder="Hasło" required>
                    <div class="password-strength-container">
                        <div id="password-bar"></div>
                    </div>
                    <small id="password-feedback">Wpisz minimum 8 znaków, cyfrę i wielką literę.</small>
                </div>
                
                <button type="submit" class="btn">Utwórz konto</button>
                <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                    Masz już konto? <a href="login.php" style="color: var(--primary-color);">Zaloguj się</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('haslo').addEventListener('input', function() {
            const haslo = this.value;
            const bar = document.getElementById('password-bar');
            const feedback = document.getElementById('password-feedback');
            
            let strength = 0;
            
            if (haslo.length >= 8) strength += 25;
            if (haslo.match(/[A-Z]/)) strength += 25;
            if (haslo.match(/[0-9]/)) strength += 25;
            if (haslo.match(/[^a-zA-Z0-9]/)) strength += 25; // Znak specjalny
            
            bar.style.width = strength + '%';
            
            if (haslo.length === 0) {
                bar.style.backgroundColor = 'transparent';
                feedback.textContent = 'Wpisz minimum 8 znaków, cyfrę i wielką literę.';
            } else if (strength < 50) {
                bar.style.backgroundColor = 'var(--danger-color)';
                feedback.textContent = 'Słabe hasło';
            } else if (strength < 75) {
                bar.style.backgroundColor = 'orange';
                feedback.textContent = 'Średnie hasło';
            } else {
                bar.style.backgroundColor = 'var(--success-color)';
                feedback.textContent = 'Silne hasło!';
            }
        });
    </script>
</body>
</html>