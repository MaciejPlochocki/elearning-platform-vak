<?php
// kursy.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Pobranie stylu użytkownika
$stmtUser = $pdo->prepare("SELECT styl_uczenia FROM uzytkownicy WHERE id = ?");
$stmtUser->execute([$user_id]);
$user_styl = $stmtUser->fetch()['styl_uczenia'];

// Obsługa zapisu na kurs
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zapisz_na_kurs'])) {
    $id_kursu = (int)$_POST['id_kursu'];
    
    // Sprawdzenie czy już nie jest zapisany
    $check = $pdo->prepare("SELECT id FROM zapisy WHERE id_ucznia = ? AND id_kursu = ?");
    $check->execute([$user_id, $id_kursu]);
    
    if ($check->rowCount() == 0) {
        $stmtZapisz = $pdo->prepare("INSERT INTO zapisy (id_ucznia, id_kursu) VALUES (?, ?)");
        $stmtZapisz->execute([$user_id, $id_kursu]);
        $message = "<div class='alert alert-success'>Pomyślnie zapisano na kurs! Możesz go znaleźć w zakładce 'Moje kursy'.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Jesteś już zapisany na ten kurs.</div>";
    }
}

// Pobranie wszystkich zapisów użytkownika, by ukryć przycisk "Zapisz się" tam gdzie to konieczne
$stmtMojeZapisy = $pdo->prepare("SELECT id_kursu FROM zapisy WHERE id_ucznia = ?");
$stmtMojeZapisy->execute([$user_id]);
$zapisane_kursy = $stmtMojeZapisy->fetchAll(PDO::FETCH_COLUMN);

// Zdefiniowane kategorie z pliku wymagań
$kategorie = ['Matematyka', 'Informatyka', 'Elektryka', 'Język angielski'];

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kursy - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .badge-matched {
            background-color: var(--success-color);
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .category-section { margin-bottom: 50px; }
        .course-card { 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            border: 2px solid var(--success-color); /* Od razu wyróżniona ramka, bo wszystko tu pasuje */
        }
        .course-footer { margin-top: auto; padding-top: 15px; }
        .course-img-wrapper {
            width: 100%;
            height: 150px;
            background-color: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
        }
        .course-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2>Dostępne Kursy</h2>
        <p>Przeglądaj materiały dopasowane do Ciebie. Twój styl to: <strong style="text-transform: capitalize; color: var(--primary-color);"><?php echo htmlspecialchars($user_styl); ?></strong></p>
        
        <?php echo $message; ?>

        <?php foreach ($kategorie as $nazwa_kategorii): ?>
            
            <div class="category-section">
                <h3 class="section-title"><?php echo $nazwa_kategorii; ?></h3>
                <div class="courses-grid">
                    <?php
                    // POPRAWKA: Twarde filtrowanie w bazie - szukamy kursów w kategorii AND dopasowanych do stylu
                    $stmtKursy = $pdo->prepare("
                        SELECT k.* FROM kursy k
                        JOIN kategorie kat ON k.id_kategorii = kat.id
                        WHERE kat.nazwa = ? AND k.styl_uczenia = ?
                    ");
                    $stmtKursy->execute([$nazwa_kategorii, $user_styl]);
                    $kursy = $stmtKursy->fetchAll();

                    if ($kursy):
                        foreach ($kursy as $kurs):
                            $is_enrolled = in_array($kurs['id'], $zapisane_kursy);
                    ?>
                        <div class="course-card">
                            <div class="course-img-wrapper">
                                <img src="img/<?php echo htmlspecialchars($kurs['obrazek']); ?>" alt="Obrazek kursu" onerror="this.onerror=null; this.src='default_course.jpg'">
                            </div>
                            
                            <div class="course-content">
                                <div class="badge-matched">Dopasowano do Ciebie</div>
                                <h4><?php echo htmlspecialchars($kurs['tytul']); ?></h4>
                                <p><?php echo htmlspecialchars($kurs['opis']); ?></p>
                            </div>

                            <div class="course-footer" style="padding: 0 15px 15px 15px;">
                                <?php if($is_enrolled): ?>
                                    <button class="btn" style="background-color: #ccc; cursor: not-allowed;" disabled>Już zapisany</button>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="id_kursu" value="<?php echo $kurs['id']; ?>">
                                        <button type="submit" name="zapisz_na_kurs" class="btn">Zapisz się</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <p style="color: var(--text-muted); font-size: 14px;">Brak kursów dla Twojego stylu (<?php echo htmlspecialchars($user_styl); ?>) w tej kategorii. Dodamy je wkrótce!</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</body>
</html>