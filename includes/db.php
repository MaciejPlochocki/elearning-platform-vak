<?php
// includes/db.php
$host = 'localhost';
$dbname = 'elearning_db';
$username = 'root'; // domyślny użytkownik XAMPP
$password = ''; // w XAMPP domyślnie hasło jest puste

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Ustawienie trybu zgłaszania błędów na wyjątki
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Zabezpieczenie przed atakami typu SQL Injection przy limitach
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>