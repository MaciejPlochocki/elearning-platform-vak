<?php
// index.php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <?php if(isset($_SESSION['user_id'])): ?>
            
            <div class="dashboard-header">
                <h2>Witaj z powrotem!</h2>
                <p>Oto co dla Ciebie przygotowaliśmy.</p>
            </div>

            <h3 class="section-title">Ostatnio dodane kursy</h3>
            <div class="courses-grid">
                <?php
                // Pobieranie 3 ostatnich kursów
                $stmt = $pdo->query("SELECT * FROM kursy ORDER BY data_utworzenia DESC LIMIT 3");
                $ostatnie_kursy = $stmt->fetchAll();
                
                if($ostatnie_kursy) {
                    foreach($ostatnie_kursy as $kurs) {
                        echo "<div class='course-card'>";
                        echo "<img src='img/" . htmlspecialchars($kurs['obrazek']) . "' alt='Obrazek kursu' class='course-img' onerror=\"this.src='https://via.placeholder.com/300x150?text=Kurs'\">";
                        echo "<div class='course-content'>";
                        echo "<h4>" . htmlspecialchars($kurs['tytul']) . "</h4>";
                        echo "<p>" . htmlspecialchars(substr($kurs['opis'], 0, 80)) . "...</p>";
                        echo "<a href='kursy.php' class='btn btn-small'>Zobacz szczegóły</a>";
                        echo "</div></div>";
                    }
                } else {
                    echo "<p>Brak nowych kursów w systemie.</p>";
                }
                ?>
            </div>

            <h3 class="section-title">Dla Ciebie (Dopasowane do Twojego stylu)</h3>
            <div class="courses-grid">
                <?php
                // Pobieranie kursów pasujących do stylu uczenia się użytkownika
                $user_id = $_SESSION['user_id'];
                $stmtUser = $pdo->prepare("SELECT styl_uczenia FROM uzytkownicy WHERE id = ?");
                $stmtUser->execute([$user_id]);
                $user = $stmtUser->fetch();

                if($user && $user['styl_uczenia']) {
                    $stmtDopasowane = $pdo->prepare("SELECT * FROM kursy WHERE styl_uczenia = ? LIMIT 3");
                    $stmtDopasowane->execute([$user['styl_uczenia']]);
                    $dopasowane_kursy = $stmtDopasowane->fetchAll();

                    if($dopasowane_kursy) {
                        foreach($dopasowane_kursy as $kurs) {
                            echo "<div class='course-card'>";
                            echo "<img src='img/" . htmlspecialchars($kurs['obrazek']) . "' alt='Obrazek kursu' class='course-img' onerror=\"this.src='https://via.placeholder.com/300x150?text=Kurs'\">";
                            echo "<div class='course-content'>";
                            echo "<h4>" . htmlspecialchars($kurs['tytul']) . "</h4>";
                            echo "<p>" . htmlspecialchars(substr($kurs['opis'], 0, 80)) . "...</p>";
                            echo "<a href='kursy.php' class='btn btn-small'>Zobacz szczegóły</a>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>Aktualnie brak kursów idealnie dopasowanych do Twojego profilu, sprawdź zakładkę Kursy.</p>";
                    }
                } else {
                    echo "<p>Rozwiąż test VAK w swoim profilu, abyśmy mogli dopasować kursy do Ciebie!</p>";
                }
                ?>
            </div>

        <?php else: ?>
            
            <div class="hero-section">
                <h1>Ucz się we własnym stylu</h1>
                <p>Nasza platforma to innowacyjne podejście do edukacji. Dopasowujemy materiały z matematyki, informatyki, elektryki i języka angielskiego do Twojego indywidualnego stylu uczenia się (VAK). Osiągaj lepsze wyniki szybciej i przyjemniej!</p>
                <a href="register.php" class="btn hero-btn">Dołącz teraz</a>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>