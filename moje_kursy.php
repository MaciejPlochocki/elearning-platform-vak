<?php
// moje_kursy.php
session_start();
require_once 'includes/db.php';

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Pobranie kursów, na które zapisany jest użytkownik
$stmt = $pdo->prepare("
    SELECT k.id as kurs_id, k.tytul, k.opis, k.obrazek, z.procent_ukonczenia, z.status 
    FROM zapisy z
    JOIN kursy k ON z.id_kursu = k.id
    WHERE z.id_ucznia = ?
    ORDER BY z.data_zapisu DESC
");
$stmt->execute([$user_id]);
$moje_kursy = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje Kursy - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .progress-wrapper {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .progress-label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .progress-bar-container {
            width: 100%;
            height: 20px;
            background-color: #e1e5eb;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .progress-bar {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
            transition: width 0.5s ease;
        }
        /* Klasy kolorów dla paska - zgodnie z wymaganiami */
        .bg-red {
            background-color: var(--danger-color); /* Czerwony dla nieukończonych */
        }
        .bg-green {
            background-color: var(--success-color); /* Zielony dla ukończonych */
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="section-title">Moje Kursy</h2>
        <p>Tutaj znajdziesz wszystkie kursy, do których dołączyłeś. Śledź swoje postępy i kontynuuj naukę!</p>

        <div class="courses-grid" style="margin-top: 30px;">
            <?php if ($moje_kursy): ?>
                <?php foreach ($moje_kursy as $kurs): 
                    // Ustalenie koloru paska na podstawie statusu i procentów
                    // Jeśli kurs ma status ukończony lub 100% -> zielony, w przeciwnym razie -> czerwony
                    $is_completed = ($kurs['status'] === 'ukonczony' || $kurs['procent_ukonczenia'] == 100);
                    $bar_class = $is_completed ? 'bg-green' : 'bg-red';
                ?>
                    <div class="course-card">
                        <img src="img/<?php echo htmlspecialchars($kurs['obrazek']); ?>" alt="Kurs" class="course-img" onerror="this.src='https://via.placeholder.com/300x150?text=Brak+zdjęcia'">
                        
                        <div class="course-content">
                            <h4><?php echo htmlspecialchars($kurs['tytul']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($kurs['opis'], 0, 80)) . '...'; ?></p>
                            
                            <div class="progress-wrapper">
                                <div class="progress-label">
                                    <span>Postęp ukończenia:</span>
                                    <span><?php echo $kurs['procent_ukonczenia']; ?>%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo $kurs['procent_ukonczenia']; ?>%;">
                                        <?php if($kurs['procent_ukonczenia'] > 5) echo $kurs['procent_ukonczenia'] . '%'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="course-footer" style="padding: 0 15px 15px 15px;">
                            <?php if ($is_completed): ?>
                                <button class="btn" style="background-color: var(--success-color); cursor: default;" disabled>Kurs ukończony</button>
                            <?php else: ?>
                                <a href="lekcja.php?id_kursu=<?php echo $kurs['kurs_id']; ?>" class="btn">Kontynuuj naukę</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert" style="background-color: white; border: 1px solid var(--border-color); grid-column: 1 / -1;">
                    <p>Nie jesteś jeszcze zapisany na żaden kurs. <a href="kursy.php" style="color: var(--primary-color); font-weight: bold;">Przejdź do listy kursów</a>, aby coś wybrać!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>