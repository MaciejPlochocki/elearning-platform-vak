<?php
// panel_nauczyciela.php
session_start();
require_once 'includes/db.php';

// Zabezpieczenie: dostęp tylko dla nauczyciela
if (!isset($_SESSION['user_id']) || $_SESSION['funkcja'] !== 'nauczyciel') {
    header("Location: index.php");
    exit();
}

$id_nauczyciela = $_SESSION['user_id'];
$message = '';

// Obsługa formularza DODAJ KURS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_kurs'])) {
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $id_kategorii = (int)$_POST['id_kategorii'];
    $styl_uczenia = $_POST['styl_uczenia'];
    $obrazek = 'default_course.jpg'; // Domyślny obrazek dla uproszczenia

    // KROK 1: Najpierw dodajemy sam kurs do bazy
    $stmt = $pdo->prepare("INSERT INTO kursy (id_nauczyciela, id_kategorii, tytul, opis, obrazek, styl_uczenia) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$id_nauczyciela, $id_kategorii, $tytul, $opis, $obrazek, $styl_uczenia])) {
        $nowy_id_kursu = $pdo->lastInsertId();
        
        // Zgodnie z wymaganiami: "kursy maja po dwa dzialy"
        // Automatycznie generujemy 2 działy dla nowego kursu
        $stmtDzial = $pdo->prepare("INSERT INTO dzialy (id_kursu, nazwa) VALUES (?, ?)");
        $stmtDzial->execute([$nowy_id_kursu, 'Dział 1 - Wprowadzenie']);
        $stmtDzial->execute([$nowy_id_kursu, 'Dział 2 - Zaawansowane']);

        // KROK 2: Komunikat zachęcający do dodania lekcji
        $message = "<div class='alert alert-success'>Kurs został utworzony pomyślnie! Zgodnie z procedurą, przejdź teraz do edycji, aby dodać do niego lekcje. <br><br> <a href='edytuj_kurs.php?id=$nowy_id_kursu' class='btn' style='background-color: var(--success-color); display: inline-block; padding: 5px 15px; margin-top: 10px;'>Dodaj lekcje do tego kursu</a></div>";
    } else {
        $message = "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania kursu.</div>";
    }
}

// Pobranie kategorii do formularza
$kategorie = $pdo->query("SELECT * FROM kategorie")->fetchAll();

// Pobranie kursów przypisanych TYLKO do tego nauczyciela
$stmtKursy = $pdo->prepare("
    SELECT k.*, kat.nazwa as kategoria 
    FROM kursy k 
    JOIN kategorie kat ON k.id_kategorii = kat.id 
    WHERE k.id_nauczyciela = ? 
    ORDER BY k.data_utworzenia DESC
");
$stmtKursy->execute([$id_nauczyciela]);
$moje_kursy = $stmtKursy->fetchAll();

// Pobranie listy uczniów zapisanych na kursy tego nauczyciela
$stmtUczniowie = $pdo->prepare("
    SELECT u.imie, u.nazwisko, u.email, k.tytul as nazwa_kursu, z.procent_ukonczenia, z.status
    FROM zapisy z
    JOIN uzytkownicy u ON z.id_ucznia = u.id
    JOIN kursy k ON z.id_kursu = k.id
    WHERE k.id_nauczyciela = ?
    ORDER BY k.tytul, u.nazwisko
");
$stmtUczniowie->execute([$id_nauczyciela]);
$uczniowie = $stmtUczniowie->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Nauczyciela - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .panel-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px; }
        .panel-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background-color: var(--bg-color); color: var(--primary-color); }
        .action-links a { margin-right: 10px; color: var(--primary-color); text-decoration: none; font-weight: bold; }
        .action-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="section-title">Panel Nauczyciela</h2>
        <p>Witaj w swoim centrum dowodzenia. Zarządzaj kursami, dodawaj lekcje i śledź postępy swoich uczniów.</p>
        
        <?php echo $message; ?>

        <div class="panel-grid">
            <div class="panel-section">
                <h3>Dodaj nowy kurs</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Krok 1: Utwórz szkielet kursu. Krok 2: Dodaj lekcje i testy w trybie edycji.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Tytuł kursu:</label>
                        <input type="text" name="tytul" required>
                    </div>
                    <div class="form-group">
                        <label>Kategoria:</label>
                        <select name="id_kategorii" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                            <?php foreach($kategorie as $kat): ?>
                                <option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['nazwa']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Styl uczenia się:</label>
                        <select name="styl_uczenia" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                            <option value="wzrokowiec">Wzrokowiec</option>
                            <option value="sluchowiec">Słuchowiec</option>
                            <option value="kinestetyk">Kinestetyk</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Opis kursu:</label>
                        <textarea name="opis" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; resize: vertical;" required></textarea>
                    </div>
                    <button type="submit" name="dodaj_kurs" class="btn">Utwórz kurs</button>
                </form>
            </div>

            <div>
                <div class="panel-section" style="margin-bottom: 30px;">
                    <h3>Twoje Kursy</h3>
                    <?php if ($moje_kursy): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tytuł</th>
                                    <th>Kategoria</th>
                                    <th>Styl</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($moje_kursy as $kurs): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kurs['tytul']); ?></td>
                                    <td><?php echo htmlspecialchars($kurs['kategoria']); ?></td>
                                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($kurs['styl_uczenia']); ?></td>
                                    <td class="action-links">
                                        <a href="edytuj_kurs.php?id=<?php echo $kurs['id']; ?>">Edytuj / Dodaj lekcje</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nie utworzyłeś jeszcze żadnego kursu.</p>
                    <?php endif; ?>
                </div>

                <div class="panel-section">
                    <h3>Uczniowie na Twoich kursach</h3>
                    <?php if ($uczniowie): ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Uczeń</th>
                                        <th>Kurs</th>
                                        <th>Postęp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($uczniowie as $uczen): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($uczen['imie'] . ' ' . $uczen['nazwisko']); ?><br><small><?php echo htmlspecialchars($uczen['email']); ?></small></td>
                                        <td><?php echo htmlspecialchars($uczen['nazwa_kursu']); ?></td>
                                        <td>
                                            <span style="color: <?php echo $uczen['procent_ukonczenia'] == 100 ? 'var(--success-color)' : 'var(--text-color)'; ?>; font-weight: bold;">
                                                <?php echo $uczen['procent_ukonczenia']; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Brak zapisanych uczniów na Twoje kursy.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>