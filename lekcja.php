<?php
// lekcja.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['funkcja'] !== 'uczen') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id_kursu = isset($_GET['id_kursu']) ? (int)$_GET['id_kursu'] : 0;
$message = '';
$pokaz_zmiane_stylu = false;

// Pobranie danych kursu, by wiedzieć jak formatować treść
$stmtKurs = $pdo->prepare("SELECT * FROM kursy WHERE id = ?");
$stmtKurs->execute([$id_kursu]);
$kurs = $stmtKurs->fetch();

$stmtZapis = $pdo->prepare("SELECT * FROM zapisy WHERE id_ucznia = ? AND id_kursu = ?");
$stmtZapis->execute([$user_id, $id_kursu]);
$zapis = $stmtZapis->fetch();

if (!$zapis || !$kurs) {
    die("<div style='padding: 20px; text-align: center;'>Brak dostępu. <a href='moje_kursy.php'>Wróć do Moich Kursów</a></div>");
}

$id_zapisu = $zapis['id'];

$stmtLekcje = $pdo->prepare("
    SELECT l.*, d.nazwa as nazwa_dzialu, t.id as id_testu, t.pytanie, t.odpowiedz_a, t.odpowiedz_b, t.odpowiedz_c, t.poprawna_odpowiedz 
    FROM lekcje l
    JOIN dzialy d ON l.id_dzialu = d.id
    LEFT JOIN testy t ON t.id_lekcji = l.id
    WHERE d.id_kursu = ?
    ORDER BY l.kolejnosc ASC
");
$stmtLekcje->execute([$id_kursu]);
$lekcje = $stmtLekcje->fetchAll();

$aktualna_lekcja = null;
$ukonczonych_lekcji = 0;

foreach ($lekcje as $lekcja) {
    $stmtPostep = $pdo->prepare("SELECT * FROM postepy WHERE id_zapisu = ? AND id_lekcji = ?");
    $stmtPostep->execute([$id_zapisu, $lekcja['id']]);
    $postep = $stmtPostep->fetch();
    
    if (!$postep || $postep['zdany'] == 0) {
        $aktualna_lekcja = $lekcja;
        $aktualny_postep = $postep; 
        break;
    } else {
        $ukonczonych_lekcji++;
    }
}

// Zmiana stylu uczenia - Logika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zmien_styl_akcja'])) {
    if ($_POST['wybor'] == 'tak') {
        $nowy_styl = $_POST['nowy_styl'];
        $pdo->prepare("UPDATE uzytkownicy SET styl_uczenia = ? WHERE id = ?")->execute([$nowy_styl, $user_id]);
        $message = "<div class='alert alert-success'>Twój styl uczenia się został zmieniony. Wyjdź do listy kursów, aby znaleźć kursy w nowym stylu!</div>";
    }
    $pdo->prepare("UPDATE postepy SET liczba_niezaliczen = 0 WHERE id_zapisu = ? AND id_lekcji = ?")->execute([$id_zapisu, $aktualna_lekcja['id']]);
    $aktualny_postep['liczba_niezaliczen'] = 0; 
}

// Logika testu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyslij_test']) && $aktualna_lekcja) {
    $wybrana_odpowiedz = $_POST['odpowiedz'];
    $id_lekcji = $aktualna_lekcja['id'];
    
    if (!$aktualny_postep) {
        $pdo->prepare("INSERT INTO postepy (id_zapisu, id_lekcji) VALUES (?, ?)")->execute([$id_zapisu, $id_lekcji]);
        $aktualny_postep = ['id_zapisu' => $id_zapisu, 'id_lekcji' => $id_lekcji, 'zdany' => 0, 'liczba_niezaliczen' => 0];
    }
    
    if ($wybrana_odpowiedz === $aktualna_lekcja['poprawna_odpowiedz']) {
        $pdo->prepare("UPDATE postepy SET zdany = 1 WHERE id_zapisu = ? AND id_lekcji = ?")->execute([$id_zapisu, $id_lekcji]);
        $ukonczonych_lekcji++;
        $procent = round(($ukonczonych_lekcji / 3) * 100);
        $status = ($procent == 100) ? 'ukonczony' : 'nieukonczony';
        $pdo->prepare("UPDATE zapisy SET procent_ukonczenia = ?, status = ? WHERE id = ?")->execute([$procent, $status, $id_zapisu]);
        
        header("Location: lekcja.php?id_kursu=$id_kursu&sukces=1");
        exit();
    } else {
        $bledy = $aktualny_postep['liczba_niezaliczen'] + 1;
        $pdo->prepare("UPDATE postepy SET liczba_niezaliczen = ? WHERE id_zapisu = ? AND id_lekcji = ?")->execute([$bledy, $id_zapisu, $id_lekcji]);
        if ($bledy >= 2) $pokaz_zmiane_stylu = true;
        else $message = "<div class='alert alert-danger'>Zła odpowiedź. Masz jeszcze 1 próbę!</div>";
        $aktualny_postep['liczba_niezaliczen'] = $bledy;
    }
}
if (isset($_GET['sukces'])) $message = "<div class='alert alert-success'>Poprawna odpowiedź! Przechodzisz dalej.</div>";
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Lekcja - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .lesson-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .lesson-text { font-size: 16px; line-height: 1.8; margin-top: 20px; padding: 20px; background-color: var(--bg-color); border-left: 4px solid var(--primary-color); }
        
        /* Specjalne style dla różnych typów uczenia się */
        .vak-visual img { max-width: 100%; border-radius: 8px; margin-top: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .vak-audio { margin-top: 20px; padding: 20px; background: #fdfdfd; border: 1px solid #eee; border-radius: 8px; text-align: center; }
        .vak-kinesthetic { margin-top: 20px; padding: 20px; background-color: #fff9e6; border-left: 5px solid #f39c12; border-radius: 0 8px 8px 0; }
        
        .test-section { border-top: 2px dashed var(--border-color); padding-top: 20px; margin-top: 30px; }
        .test-option { display: block; margin-bottom: 15px; padding: 15px; border: 1px solid var(--border-color); border-radius: 5px; cursor: pointer; }
        .test-option:hover { background-color: var(--bg-color); }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container" style="max-width: 900px;">
        <a href="moje_kursy.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">&larr; Powrót</a>
        <div style="margin: 20px 0; font-weight: bold; color: var(--text-muted);">Postęp: <?php echo round(($ukonczonych_lekcji / 3) * 100); ?>%</div>
        
        <?php echo $message; ?>

        <?php if ($pokaz_zmiane_stylu): ?>
            <div style="background-color: #fff3cd; color: #856404; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <h3>Nie udało Ci się zaliczyć testu 2 razy. Zmienić styl uczenia?</h3>
                <button onclick="document.getElementById('form-zmiana').style.display='block';" class="btn" style="width: auto;">TAK, zmień styl</button>
                <form method="POST" style="display: inline-block; margin-left: 10px;">
                    <input type="hidden" name="wybor" value="nie">
                    <button type="submit" name="zmien_styl_akcja" class="btn" style="background-color: var(--text-muted); width: auto;">NIE, ponów próbę</button>
                </form>
                <div id="form-zmiana" style="display:none; margin-top: 15px;">
                    <form method="POST">
                        <input type="hidden" name="wybor" value="tak">
                        <select name="nowy_styl" style="padding: 10px; width: 200px;"><option value="wzrokowiec">Wzrokowiec</option><option value="sluchowiec">Słuchowiec</option><option value="kinestetyk">Kinestetyk</option></select>
                        <button type="submit" name="zmien_styl_akcja" class="btn" style="width: auto; background: var(--success-color);">Zapisz</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="lesson-container">
            <?php if ($aktualna_lekcja): ?>
                <h4 style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;"><?php echo htmlspecialchars($aktualna_lekcja['nazwa_dzialu']); ?></h4>
                <h2 style="color: var(--primary-color); margin-top: 5px;"><?php echo htmlspecialchars($aktualna_lekcja['tytul']); ?></h2>
                
                <div class="lesson-text">
                    <?php echo nl2br(htmlspecialchars($aktualna_lekcja['tresc'])); ?>
                </div>

                <?php if (!empty($aktualna_lekcja['dodatek_medialny'])): ?>
                    
                    <?php if ($kurs['styl_uczenia'] == 'wzrokowiec'): ?>
                        <div class="vak-visual">
                            <h4 style="color: var(--text-muted);">Schemat pomocniczy:</h4>
                            <img src="<?php echo htmlspecialchars($aktualna_lekcja['dodatek_medialny']); ?>" onerror="this.src='https://via.placeholder.com/800x400?text=Schemat+dla+wzrokowca'">
                        </div>

                    <?php elseif ($kurs['styl_uczenia'] == 'sluchowiec'): ?>
                        <div class="vak-audio">
                            <h4 style="color: var(--text-muted); margin-bottom: 10px;">🎧 Posłuchaj materiału do tej lekcji:</h4>
                            <audio controls style="width: 100%;">
                                <source src="<?php echo htmlspecialchars($aktualna_lekcja['dodatek_medialny']); ?>" type="audio/mpeg">
                                Twoja przeglądarka nie obsługuje odtwarzacza audio. Kliknij tutaj, by pobrać plik: 
                                <a href="<?php echo htmlspecialchars($aktualna_lekcja['dodatek_medialny']); ?>">Pobierz</a>
                            </audio>
                        </div>

                    <?php elseif ($kurs['styl_uczenia'] == 'kinestetyk'): ?>
                        <div class="vak-kinesthetic">
                            <h4 style="color: #d35400; margin-bottom: 10px;">🛠️ Zadanie Praktyczne (Do wykonania fizycznie!)</h4>
                            <p>Aby w pełni zrozumieć ten temat, wejdź w poniższy link i wykonaj instrukcję / symulację:</p>
                            <a href="<?php echo htmlspecialchars($aktualna_lekcja['dodatek_medialny']); ?>" target="_blank" class="btn" style="background-color: #f39c12; margin-top: 10px; display: inline-block; width: auto;">Przejdź do zadania / symulacji</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <div class="test-section">
                    <h3>Test sprawdzający</h3>
                    <form method="POST">
                        <p style="font-weight: 600; font-size: 18px; margin-bottom: 15px;"><?php echo htmlspecialchars($aktualna_lekcja['pytanie']); ?></p>
                        <label class="test-option"><input type="radio" name="odpowiedz" value="a" required> A. <?php echo htmlspecialchars($aktualna_lekcja['odpowiedz_a']); ?></label>
                        <label class="test-option"><input type="radio" name="odpowiedz" value="b"> B. <?php echo htmlspecialchars($aktualna_lekcja['odpowiedz_b']); ?></label>
                        <label class="test-option"><input type="radio" name="odpowiedz" value="c"> C. <?php echo htmlspecialchars($aktualna_lekcja['odpowiedz_c']); ?></label>
                        <?php if(!$pokaz_zmiane_stylu): ?>
                            <button type="submit" name="wyslij_test" class="btn">Sprawdź odpowiedź</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 0;">
                    <h2 style="color: var(--success-color);">Gratulacje!</h2>
                    <p>Ukończyłeś kurs.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>