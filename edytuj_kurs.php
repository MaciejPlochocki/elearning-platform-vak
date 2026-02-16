<?php
// edytuj_kurs.php
session_start();
require_once 'includes/db.php';

// Zabezpieczenie: dostęp tylko dla nauczyciela i moderatora
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['funkcja'], ['nauczyciel', 'moderator'])) {
    header("Location: index.php");
    exit();
}

$id_nauczyciela = $_SESSION['user_id'];
$id_kursu = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Pobranie kursu
$stmtKurs = $pdo->prepare("SELECT * FROM kursy WHERE id = ?");
$stmtKurs->execute([$id_kursu]);
$kurs = $stmtKurs->fetch();

if (!$kurs || ($_SESSION['funkcja'] == 'nauczyciel' && $kurs['id_nauczyciela'] != $id_nauczyciela)) {
    die("<div style='padding: 20px; text-align: center;'>Błąd dostępu do kursu. <a href='panel_nauczyciela.php'>Wróć</a></div>");
}

// ---------------------------------------------------------
// OBSŁUGA FORMULARZY (ZAPIS DO BAZY)
// ---------------------------------------------------------

// 1. Dodawanie nowego działu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_dzial'])) {
    $nazwa_dzialu = trim($_POST['nazwa_dzialu']);
    if (!empty($nazwa_dzialu)) {
        $stmt = $pdo->prepare("INSERT INTO dzialy (id_kursu, nazwa) VALUES (?, ?)");
        $stmt->execute([$id_kursu, $nazwa_dzialu]);
        header("Location: edytuj_kurs.php?id=$id_kursu&success_dzial=1");
        exit();
    }
}

// 2. Dodawanie nowej lekcji i testu do konkretnego działu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_lekcje'])) {
    $id_dzialu = (int)$_POST['id_dzialu'];
    
    // Sprawdzamy, ile lekcji ma ten KONKRETNY dział
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM lekcje WHERE id_dzialu = ?");
    $stmtCount->execute([$id_dzialu]);
    $liczba_lekcji_w_dziale = $stmtCount->fetchColumn();

    if ($liczba_lekcji_w_dziale < 3) {
        $tytul = trim($_POST['tytul']);
        $tresc = trim($_POST['tresc']);
        $dodatek = trim($_POST['dodatek_medialny']); 
        $kolejnosc = $liczba_lekcji_w_dziale + 1; // Kolejność w ramach działu
        
        $pytanie = trim($_POST['pytanie']);
        $odp_a = trim($_POST['odp_a']);
        $odp_b = trim($_POST['odp_b']);
        $odp_c = trim($_POST['odp_c']);
        $poprawna = $_POST['poprawna_odpowiedz'];

        try {
            $pdo->beginTransaction();
            // Zapis lekcji
            $stmtInsert = $pdo->prepare("INSERT INTO lekcje (id_dzialu, tytul, tresc, dodatek_medialny, kolejnosc) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->execute([$id_dzialu, $tytul, $tresc, $dodatek, $kolejnosc]);
            $id_nowej_lekcji = $pdo->lastInsertId();

            // Zapis testu
            $stmtTest = $pdo->prepare("INSERT INTO testy (id_lekcji, pytanie, odpowiedz_a, odpowiedz_b, odpowiedz_c, poprawna_odpowiedz) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtTest->execute([$id_nowej_lekcji, $pytanie, $odp_a, $odp_b, $odp_c, $poprawna]);

            $pdo->commit();
            header("Location: edytuj_kurs.php?id=$id_kursu&success_lekcja=1");
            exit();
        } catch(PDOException $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Błąd: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Wybrany dział ma już maksymalną liczbę 3 lekcji!</div>";
    }
}

// Komunikaty po przeładowaniu
if (isset($_GET['success_dzial'])) $message = "<div class='alert alert-success'>Nowy dział został dodany!</div>";
if (isset($_GET['success_lekcja'])) $message = "<div class='alert alert-success'>Lekcja wraz z testem została dodana do działu!</div>";

// ---------------------------------------------------------
// POBIERANIE DANYCH DO WYŚWIETLENIA (Grupowanie lekcji w działach)
// ---------------------------------------------------------
$stmtDzialy = $pdo->prepare("SELECT * FROM dzialy WHERE id_kursu = ? ORDER BY id ASC");
$stmtDzialy->execute([$id_kursu]);
$dzialy_baza = $stmtDzialy->fetchAll();

$struktura_kursu = [];
$czy_mozna_dodac_lekcje = false; // Flaga sprawdzająca, czy jest jakikolwiek dział z < 3 lekcjami

foreach ($dzialy_baza as $dzial) {
    // Pobieramy lekcje tylko dla tego działu
    $stmtLekcje = $pdo->prepare("
        SELECT l.*, t.pytanie 
        FROM lekcje l 
        LEFT JOIN testy t ON t.id_lekcji = l.id 
        WHERE l.id_dzialu = ? 
        ORDER BY l.kolejnosc ASC
    ");
    $stmtLekcje->execute([$dzial['id']]);
    $lekcje_dzialu = $stmtLekcje->fetchAll();
    
    $ilosc = count($lekcje_dzialu);
    if ($ilosc < 3) {
        $czy_mozna_dodac_lekcje = true;
    }

    $struktura_kursu[] = [
        'id' => $dzial['id'],
        'nazwa' => $dzial['nazwa'],
        'lekcje' => $lekcje_dzialu,
        'ilosc_lekcji' => $ilosc
    ];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Kurs - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dzial-box { background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .dzial-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 15px; }
        .lekcja-item { background: white; padding: 12px; margin-bottom: 10px; border-left: 4px solid var(--primary-color); border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .badge-limit { background-color: var(--success-color); color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-warning { background-color: #f39c12; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <a href="panel_nauczyciela.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">&larr; Wróć do Panelu</a>
        <h2 class="section-title" style="margin-top: 15px;">Zarządzanie strukturą: <?php echo htmlspecialchars($kurs['tytul']); ?></h2>
        
        <?php echo $message; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
            
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 20px;">Struktura Kursu</h3>
                
                <?php if (empty($struktura_kursu)): ?>
                    <p>Brak działów. Dodaj pierwszy dział!</p>
                <?php else: ?>
                    <?php foreach ($struktura_kursu as $dzial): ?>
                        <div class="dzial-box">
                            <div class="dzial-header">
                                <h4 style="margin: 0; color: var(--text-color);"><?php echo htmlspecialchars($dzial['nazwa']); ?></h4>
                                <?php if ($dzial['ilosc_lekcji'] >= 3): ?>
                                    <span class="badge-limit">Komplet 3/3 lekcji</span>
                                <?php else: ?>
                                    <span class="badge-warning"><?php echo $dzial['ilosc_lekcji']; ?>/3 lekcji</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($dzial['ilosc_lekcji'] > 0): ?>
                                <?php foreach ($dzial['lekcje'] as $l): ?>
                                    <div class="lekcja-item">
                                        <div style="font-weight: bold;"><?php echo htmlspecialchars($l['tytul']); ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">Test: <?php echo htmlspecialchars($l['pytanie']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Ten dział nie ma jeszcze żadnych lekcji.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
                    <h3>➕ Dodaj nowy dział</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Możesz utworzyć dowolną liczbę działów w tym kursie.</p>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="text" name="nazwa_dzialu" placeholder="np. Dział 3 - Podsumowanie" style="flex-grow: 1; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                        <button type="submit" name="dodaj_dzial" class="btn" style="width: auto;">Utwórz</button>
                    </form>
                </div>

                <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <h3>📝 Dodaj lekcję do działu</h3>
                    
                    <?php if (empty($struktura_kursu)): ?>
                        <div class="alert alert-warning">Musisz najpierw utworzyć przynajmniej jeden dział!</div>
                    <?php elseif (!$czy_mozna_dodac_lekcje): ?>
                        <div class="alert alert-success">Wszystkie Twoje działy mają już limit 3 lekcji. Dodaj nowy dział, aby móc wstawić kolejne lekcje.</div>
                    <?php else: ?>
                        <div style="background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                            <strong>Wskazówka:</strong> Twój kurs jest dla stylu: <b style="text-transform: capitalize;"><?php echo $kurs['styl_uczenia']; ?></b>.<br>
                            <?php if($kurs['styl_uczenia'] == 'wzrokowiec'): ?>
                                Skup się na formacie tekstu i podaj link do schematu/grafiki w polu "Dodatek Medialny".
                            <?php elseif($kurs['styl_uczenia'] == 'sluchowiec'): ?>
                                Wklej link do pliku MP3 z nagranym wykładem w polu "Dodatek Medialny"!
                            <?php else: ?>
                                W polu "Dodatek Medialny" podaj link do interaktywnego zadania / symulatora.
                            <?php endif; ?>
                        </div>

                        <form method="POST">
                            <label style="font-weight: bold; font-size: 14px;">Wybierz dział (tylko te, które mają miejsce):</label>
                            <select name="id_dzialu" class="form-group" style="width:100%; padding:10px; margin-top:5px; margin-bottom:15px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                                <?php foreach($struktura_kursu as $dzial): ?>
                                    <?php if($dzial['ilosc_lekcji'] < 3): ?>
                                        <option value="<?php echo $dzial['id']; ?>"><?php echo htmlspecialchars($dzial['nazwa']); ?> (<?php echo $dzial['ilosc_lekcji']; ?>/3)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="tytul" placeholder="Tytuł lekcji" class="form-group" style="width:100%; padding:10px; margin-bottom:10px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                            <textarea name="tresc" rows="4" placeholder="Treść lekcji..." style="width:100%; padding:10px; margin-bottom:10px; border: 1px solid var(--border-color); border-radius: 5px;" required></textarea>
                            
                            <input type="text" name="dodatek_medialny" placeholder="Dodatek Medialny (URL opcjonalnie)" style="width:100%; padding:10px; margin-bottom:20px; border: 1px solid var(--primary-color); border-radius: 5px;">

                            <h4 style="color: var(--danger-color); border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px;">Test podsumowujący (Wymagany)</h4>
                            <input type="text" name="pytanie" placeholder="Pytanie" style="width:100%; padding:8px; margin-bottom:5px; border: 1px solid var(--border-color);" required>
                            <input type="text" name="odp_a" placeholder="Odp A" style="width:100%; padding:8px; margin-bottom:5px; border: 1px solid var(--border-color);" required>
                            <input type="text" name="odp_b" placeholder="Odp B" style="width:100%; padding:8px; margin-bottom:5px; border: 1px solid var(--border-color);" required>
                            <input type="text" name="odp_c" placeholder="Odp C" style="width:100%; padding:8px; margin-bottom:5px; border: 1px solid var(--border-color);" required>
                            
                            <label style="font-size: 13px; display: block; margin-top: 5px;">Prawidłowa odpowiedź:</label>
                            <select name="poprawna_odpowiedz" style="width:100%; padding:8px; margin-bottom:15px; border: 1px solid var(--border-color); border-radius: 5px;">
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>

                            <button type="submit" name="dodaj_lekcje" class="btn">Zapisz Lekcję i Test</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>