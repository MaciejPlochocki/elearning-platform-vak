<?php
// panel_moderatora.php
session_start();
require_once 'includes/db.php';

// Zabezpieczenie: dostęp tylko dla moderatora
if (!isset($_SESSION['user_id']) || $_SESSION['funkcja'] !== 'moderator') {
    header("Location: index.php");
    exit();
}

$message = '';

// AKCJE MODERATORA - PRZETWARZANIE FORMULARZY
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Usuwanie użytkownika
    if (isset($_POST['usun_usera'])) {
        $id_usera = (int)$_POST['id_usera'];
        if ($id_usera !== $_SESSION['user_id']) { // Zabezpieczenie przed usunięciem samego siebie
            $pdo->prepare("DELETE FROM uzytkownicy WHERE id = ?")->execute([$id_usera]);
            $message = "<div class='alert alert-success'>Użytkownik został usunięty z systemu.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Nie możesz usunąć własnego konta moderatora!</div>";
        }
    }

    // 2. Zmiana hasła użytkownika
    if (isset($_POST['zmien_haslo_usera'])) {
        $id_usera = (int)$_POST['id_usera'];
        $nowe_haslo = $_POST['nowe_haslo'];
        if (strlen($nowe_haslo) >= 8) {
            $hash = password_hash($nowe_haslo, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE uzytkownicy SET haslo = ? WHERE id = ?")->execute([$hash, $id_usera]);
            $message = "<div class='alert alert-success'>Hasło wybranego użytkownika zostało zmienione.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Hasło musi mieć co najmniej 8 znaków.</div>";
        }
    }

    // 3. Usuwanie kursu
    if (isset($_POST['usun_kurs'])) {
        $id_kursu = (int)$_POST['id_kursu'];
        $pdo->prepare("DELETE FROM kursy WHERE id = ?")->execute([$id_kursu]);
        $message = "<div class='alert alert-success'>Kurs (oraz przypisane do niego lekcje i testy) został pomyślnie usunięty.</div>";
    }

    // 4. Dodawanie kursu przez moderatora
    if (isset($_POST['dodaj_kurs'])) {
        $tytul = trim($_POST['tytul']);
        $opis = trim($_POST['opis']);
        $id_kategorii = (int)$_POST['id_kategorii'];
        $id_nauczyciela = (int)$_POST['id_nauczyciela'];
        $styl_uczenia = $_POST['styl_uczenia'];
        
        $stmt = $pdo->prepare("INSERT INTO kursy (id_nauczyciela, id_kategorii, tytul, opis, styl_uczenia) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_nauczyciela, $id_kategorii, $tytul, $opis, $styl_uczenia]);
        $nowy_id_kursu = $pdo->lastInsertId();
        
        // Automatyczne generowanie działów (zgodnie z wymogami)
        $stmtDzial = $pdo->prepare("INSERT INTO dzialy (id_kursu, nazwa) VALUES (?, ?)");
        $stmtDzial->execute([$nowy_id_kursu, 'Dział 1 - Wprowadzenie']);
        $stmtDzial->execute([$nowy_id_kursu, 'Dział 2 - Zaawansowane']);
        
        $message = "<div class='alert alert-success'>Kurs został utworzony. Przejdź do edycji, aby dodać do niego lekcje.</div>";
    }
}

// Pobieranie danych do wyświetlenia w panelu
$wszyscy_uzytkownicy = $pdo->query("SELECT * FROM uzytkownicy ORDER BY funkcja, nazwisko")->fetchAll();
$wszystkie_kursy = $pdo->query("
    SELECT k.*, u.imie, u.nazwisko, kat.nazwa as kategoria 
    FROM kursy k 
    JOIN uzytkownicy u ON k.id_nauczyciela = u.id 
    JOIN kategorie kat ON k.id_kategorii = kat.id 
    ORDER BY k.data_utworzenia DESC
")->fetchAll();
$nauczyciele = $pdo->query("SELECT * FROM uzytkownicy WHERE funkcja = 'nauczyciel'")->fetchAll();
$kategorie = $pdo->query("SELECT * FROM kategorie")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Moderatora - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .mod-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); margin-bottom: 30px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        .data-table th, .data-table td { padding: 10px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background-color: var(--bg-color); color: var(--primary-color); }
        .inline-form { display: flex; gap: 10px; align-items: center; }
        .inline-form input { padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-small { padding: 5px 10px; font-size: 12px; }
        .badge-role { padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .role-moderator { background: #e74c3c; color: white; }
        .role-nauczyciel { background: #3498db; color: white; }
        .role-uczen { background: #2ecc71; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="section-title" style="color: #e74c3c;">SuperPanel Moderatora</h2>
        <p>Masz uprawnienia administracyjne. Możesz globalnie zarządzać użytkownikami oraz kursami w systemie.</p>
        
        <?php echo $message; ?>

        <div class="mod-section">
            <h3 style="margin-bottom: 15px;">Zarządzanie Kursami</h3>
            
            <div style="background: var(--bg-color); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <h4>Dodaj kurs jako moderator</h4>
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; align-items: end; margin-top: 10px;">
                    <div><label>Tytuł:</label><input type="text" name="tytul" required style="width:100%; padding:8px;"></div>
                    <div>
                        <label>Przypisz Nauczyciela:</label>
                        <select name="id_nauczyciela" required style="width:100%; padding:8px;">
                            <?php foreach($nauczyciele as $n): ?>
                                <option value="<?php echo $n['id']; ?>"><?php echo htmlspecialchars($n['imie'].' '.$n['nazwisko']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Kategoria i Styl:</label>
                        <div style="display:flex; gap:5px;">
                            <select name="id_kategorii" required style="width:50%; padding:8px;">
                                <?php foreach($kategorie as $kat): ?><option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['nazwa']); ?></option><?php endforeach; ?>
                            </select>
                            <select name="styl_uczenia" required style="width:50%; padding:8px;">
                                <option value="wzrokowiec">Wzrok.</option><option value="sluchowiec">Słuch.</option><option value="kinestetyk">Kines.</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="opis" value="Kurs dodany przez moderatora.">
                    <button type="submit" name="dodaj_kurs" class="btn">Dodaj kurs</button>
                </form>
            </div>

            <table class="data-table">
                <thead><tr><th>ID</th><th>Tytuł</th><th>Nauczyciel (Autor)</th><th>Kategoria</th><th>Akcje</th></tr></thead>
                <tbody>
                    <?php foreach($wszystkie_kursy as $kurs): ?>
                    <tr>
                        <td><?php echo $kurs['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($kurs['tytul']); ?></strong></td>
                        <td><?php echo htmlspecialchars($kurs['imie'] . ' ' . $kurs['nazwisko']); ?></td>
                        <td><?php echo htmlspecialchars($kurs['kategoria']); ?></td>
                        <td style="display: flex; gap: 5px;">
                            <a href="edytuj_kurs.php?id=<?php echo $kurs['id']; ?>" class="btn btn-small" style="background-color: var(--primary-color);">Edytuj</a>
                            <form method="POST" onsubmit="return confirm('Trwale usunąć ten kurs?');">
                                <input type="hidden" name="id_kursu" value="<?php echo $kurs['id']; ?>">
                                <button type="submit" name="usun_kurs" class="btn btn-small" style="background-color: var(--danger-color);">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mod-section">
            <h3>Zarządzanie Użytkownikami</h3>
            <table class="data-table">
                <thead><tr><th>ID</th><th>Dane</th><th>Rola</th><th>Zmiana Hasła</th><th>Akcja</th></tr></thead>
                <tbody>
                    <?php foreach($wszyscy_uzytkownicy as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']); ?></strong><br>
                            <small><?php echo htmlspecialchars($u['email']); ?> | Tel: <?php echo htmlspecialchars($u['telefon']); ?></small>
                        </td>
                        <td><span class="badge-role role-<?php echo $u['funkcja']; ?>"><?php echo $u['funkcja']; ?></span></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="id_usera" value="<?php echo $u['id']; ?>">
                                <input type="password" name="nowe_haslo" placeholder="Nowe hasło" required>
                                <button type="submit" name="zmien_haslo_usera" class="btn btn-small" style="background-color: #f39c12;">Zmień</button>
                            </form>
                        </td>
                        <td>
                            <?php if($u['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">
                                <input type="hidden" name="id_usera" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="usun_usera" class="btn btn-small" style="background-color: var(--danger-color);">Usuń</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>