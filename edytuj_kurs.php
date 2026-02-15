<?php
// edytuj_kurs.php
session_start();
require_once 'includes/db.php';

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

$stmtDzialy = $pdo->prepare("SELECT * FROM dzialy WHERE id_kursu = ? ORDER BY id ASC");
$stmtDzialy->execute([$id_kursu]);
$dzialy = $stmtDzialy->fetchAll();

$stmtLekcje = $pdo->prepare("
    SELECT l.*, d.nazwa as nazwa_dzialu, t.pytanie 
    FROM lekcje l
    JOIN dzialy d ON l.id_dzialu = d.id
    LEFT JOIN testy t ON t.id_lekcji = l.id
    WHERE d.id_kursu = ?
    ORDER BY l.kolejnosc ASC
");
$stmtLekcje->execute([$id_kursu]);
$lekcje = $stmtLekcje->fetchAll();
$liczba_lekcji = count($lekcje);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_lekcje'])) {
    if ($liczba_lekcji < 3) {
        $id_dzialu = (int)$_POST['id_dzialu'];
        $tytul = trim($_POST['tytul']);
        $tresc = trim($_POST['tresc']);
        $dodatek = trim($_POST['dodatek_medialny']); // NOWE POLE
        $kolejnosc = $liczba_lekcji + 1;
        
        $pytanie = trim($_POST['pytanie']);
        $odp_a = trim($_POST['odp_a']);
        $odp_b = trim($_POST['odp_b']);
        $odp_c = trim($_POST['odp_c']);
        $poprawna = $_POST['poprawna_odpowiedz'];

        try {
            $pdo->beginTransaction();
            $stmtInsert = $pdo->prepare("INSERT INTO lekcje (id_dzialu, tytul, tresc, dodatek_medialny, kolejnosc) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->execute([$id_dzialu, $tytul, $tresc, $dodatek, $kolejnosc]);
            $id_nowej_lekcji = $pdo->lastInsertId();

            $stmtTest = $pdo->prepare("INSERT INTO testy (id_lekcji, pytanie, odpowiedz_a, odpowiedz_b, odpowiedz_c, poprawna_odpowiedz) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtTest->execute([$id_nowej_lekcji, $pytanie, $odp_a, $odp_b, $odp_c, $poprawna]);

            $pdo->commit();
            header("Location: edytuj_kurs.php?id=$id_kursu&success=1");
            exit();
        } catch(PDOException $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Błąd: " . $e->getMessage() . "</div>";
        }
    }
}
if (isset($_GET['success'])) $message = "<div class='alert alert-success'>Dodano lekcję do kursu!</div>";
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Kurs</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <h2 class="section-title">Kurs (<?php echo htmlspecialchars($kurs['styl_uczenia']); ?>): <?php echo htmlspecialchars($kurs['tytul']); ?></h2>
        <?php echo $message; ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div style="background: white; padding: 25px; border-radius: 8px;">
                <h3>Lekcje (<?php echo $liczba_lekcji; ?>/3)</h3>
                <?php foreach ($lekcje as $l): ?>
                    <div style="background: var(--bg-color); padding: 10px; margin-bottom: 10px; border-left: 4px solid var(--primary-color);">
                        <strong><?php echo htmlspecialchars($l['tytul']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background: white; padding: 25px; border-radius: 8px;">
                <?php if ($liczba_lekcji < 3): ?>
                    <h3>Dodaj lekcję (Lekcja <?php echo $liczba_lekcji + 1; ?>)</h3>
                    
                    <div style="background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <strong>Wskazówka dla Nauczyciela:</strong> Twój kurs jest dla stylu: <b style="text-transform: capitalize;"><?php echo $kurs['styl_uczenia']; ?></b>.<br>
                        <?php if($kurs['styl_uczenia'] == 'wzrokowiec'): ?>
                            Skup się na bogatym formacie tekstu i podaj link do schematu/grafiki w polu "Dodatek Medialny".
                        <?php elseif($kurs['styl_uczenia'] == 'sluchowiec'): ?>
                            Opisz krótko temat, a w polu "Dodatek Medialny" wklej link do pliku MP3 z nagranym wykładem!
                        <?php else: ?>
                            W tekście dokładnie opisz co uczeń musi wykonać, a w polu "Dodatek Medialny" możesz podać link do interaktywnego zadania / symulatora.
                        <?php endif; ?>
                    </div>

                    <form method="POST">
                        <select name="id_dzialu" class="form-group" style="width:100%; padding:10px; margin-bottom:10px;" required>
                            <?php foreach($dzialy as $dzial): ?><option value="<?php echo $dzial['id']; ?>"><?php echo htmlspecialchars($dzial['nazwa']); ?></option><?php endforeach; ?>
                        </select>
                        <input type="text" name="tytul" placeholder="Tytuł lekcji" class="form-group" style="width:100%; padding:10px; margin-bottom:10px;" required>
                        <textarea name="tresc" rows="4" placeholder="Treść lekcji..." style="width:100%; padding:10px; margin-bottom:10px;" required></textarea>
                        
                        <input type="text" name="dodatek_medialny" placeholder="Dodatek Medialny (Link do: obrazu / pliku .mp3 / symulacji)" style="width:100%; padding:10px; margin-bottom:20px; border: 1px solid var(--primary-color);">

                        <h4 style="color: var(--danger-color);">Test podsumowujący</h4>
                        <input type="text" name="pytanie" placeholder="Pytanie" style="width:100%; padding:8px; margin-bottom:5px;" required>
                        <input type="text" name="odp_a" placeholder="Odp A" style="width:100%; padding:8px; margin-bottom:5px;" required>
                        <input type="text" name="odp_b" placeholder="Odp B" style="width:100%; padding:8px; margin-bottom:5px;" required>
                        <input type="text" name="odp_c" placeholder="Odp C" style="width:100%; padding:8px; margin-bottom:5px;" required>
                        <select name="poprawna_odpowiedz" style="width:100%; padding:8px; margin-bottom:15px;"><option value="a">A</option><option value="b">B</option><option value="c">C</option></select>

                        <button type="submit" name="dodaj_lekcje" class="btn">Zapisz Lekcję</button>
                    </form>
                <?php else: ?>
                    <p style="color: green; font-weight: bold;">Kurs posiada wymagane 3 lekcje!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>