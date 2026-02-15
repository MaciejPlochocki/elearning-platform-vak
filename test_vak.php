<?php
// test_vak.php
session_start();
require_once 'includes/db.php';

// Zabezpieczenie - tylko zalogowani użytkownicy mogą rozwiązać test
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sprawdzenie czy użytkownik ma już przypisany styl
$stmt = $pdo->prepare("SELECT styl_uczenia FROM uzytkownicy WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user && $user['styl_uczenia'] != NULL) {
    header("Location: index.php"); // Jeśli ma już styl, wraca na główną
    exit();
}

// Prawdziwe pytania do testu VAK (20 pytań)
$pytania = [
    1 => ['pytanie' => 'Gdy uczę się do egzaminu, wpadam w rytm, gdy:', 'a' => 'Czytam notatki i oglądam schematy', 'b' => 'Powtarzam materiał na głos i o nim dyskutuję', 'c' => 'Przepisuję notatki i robię modele przestrzenne'],
    2 => ['pytanie' => 'Gdy słucham wykładu:', 'a' => 'Zwracam uwagę na slajdy i mimikę wykładowcy', 'b' => 'Skupiam się na tonie głosu i łapię każde słowo', 'c' => 'Kręcę się w fotelu i robię doodling (bazgroły) na kartce'],
    3 => ['pytanie' => 'Kiedy mam wolny czas, najbardziej lubię:', 'a' => 'Oglądać filmy lub czytać książki', 'b' => 'Słuchać muzyki lub podcastów', 'c' => 'Uprawiać sport lub majsterkować'],
    4 => ['pytanie' => 'Gdy próbuję przypomnieć sobie jakieś wydarzenie:', 'a' => 'Widzę obrazy i otoczenie w swojej głowie', 'b' => 'Przypominam sobie dźwięki i to, co ktoś powiedział', 'c' => 'Pamiętam emocje i to, co wtedy robiłem fizycznie'],
    5 => ['pytanie' => 'W nowym miejscu najszybciej odnajdę się:', 'a' => 'Patrząc na mapę lub plan budynku', 'b' => 'Pytając kogoś o drogę', 'c' => 'Idąc przed siebie i kierując się intuicją'],
    6 => ['pytanie' => 'Moje notatki zazwyczaj:', 'a' => 'Są kolorowe, podkreślone i uporządkowane', 'b' => 'Są dość chaotyczne, wole nagrywać wykłady', 'c' => 'Zawierają dużo strzałek, szkiców i skrótów myślowych'],
    7 => ['pytanie' => 'Gdy tłumaczę komuś drogę:', 'a' => 'Rysuję mu mapkę', 'b' => 'Tłumaczę słownie (np. "idź prosto, potem w lewo")', 'c' => 'Używam gestów i pokazuje rękami kierunki'],
    8 => ['pytanie' => 'Najlepiej zapamiętuję numer telefonu:', 'a' => 'Gdy widzę go zapisanego na kartce', 'b' => 'Gdy powtórzę go kilka razy na głos', 'c' => 'Gdy "wystukam" go na klawiaturze telefonu'],
    9 => ['pytanie' => 'Wybierając sprzęt w sklepie, zwracam uwagę na:', 'a' => 'Jego wygląd i design', 'b' => 'Rekomendację sprzedawcy lub znajomego', 'c' => 'To, jak leży w dłoni lub jak się z nim pracuje'],
    10 => ['pytanie' => 'Gdy gram w nową grę planszową:', 'a' => 'Czytam dokładnie instrukcję', 'b' => 'Proszę, by ktoś mi wytłumaczył zasady', 'c' => 'Chcę od razu zagrać rundę próbną, by załapać o co chodzi'],
    11 => ['pytanie' => 'Rozwiązując trudny problem:', 'a' => 'Zapisuję wszystko, co wiem i robię listę', 'b' => 'Rozmawiam o tym sam ze sobą lub z kimś innym', 'c' => 'Muszę iść na spacer, by pomyśleć w ruchu'],
    12 => ['pytanie' => 'Podczas rozmowy z inną osobą:', 'a' => 'Zwracam uwagę na jej ubiór i kontakt wzrokowy', 'b' => 'Analizuję jej dobór słów i ton głosu', 'c' => 'Zwracam uwagę na to, czy stoimy/siedzimy blisko siebie'],
    13 => ['pytanie' => 'Ucząc się nowego oprogramowania:', 'a' => 'Szukam samouczków wideo lub screenów', 'b' => 'Dzwonię do pomocy technicznej lub pytam znajomego', 'c' => 'Klikam we wszystko i sprawdzam, co się stanie'],
    14 => ['pytanie' => 'Najbardziej denerwuje mnie podczas nauki:', 'a' => 'Bałagan na biurku', 'b' => 'Hałas zza okna lub rozmowy innych', 'c' => 'Konieczność siedzenia w bezruchu przez długi czas'],
    15 => ['pytanie' => 'Nagrody, które cenię najbardziej, to:', 'a' => 'Książki, plakaty, bilety do kina', 'b' => 'Płyty z muzyką, wejściówki na koncert', 'c' => 'Vouchery na aktywne spędzanie czasu (np. park linowy)'],
    16 => ['pytanie' => 'W sytuacjach stresowych zazwyczaj:', 'a' => 'Zaciskam zęby i obserwuję otoczenie', 'b' => 'Dużo mówię lub słucham uspokajającej muzyki', 'c' => 'Chodzę w kółko lub bawię się czymś w dłoniach'],
    17 => ['pytanie' => 'Czytając książkę dla przyjemności, preferuję:', 'a' => 'Książki z dużą ilością opisów wizualnych', 'b' => 'Książki z dużą ilością dialogów', 'c' => 'Książki z szybką akcją, w których dużo się dzieje'],
    18 => ['pytanie' => 'Gdy jestem zły:', 'a' => 'Moja twarz wyraża więcej niż słowa', 'b' => 'Podnoszę głos i krzyczę', 'c' => 'Trzaskam drzwiami lub zaciskam pięści'],
    19 => ['pytanie' => 'Kiedy składam nowe meble:', 'a' => 'Uważnie studiuję obrazkową instrukcję obsługi', 'b' => 'Proszę kogoś o czytanie kroków na głos', 'c' => 'Patrzę na części i instynktownie zaczynam je łączyć'],
    20 => ['pytanie' => 'Na wakacjach najbardziej lubię:', 'a' => 'Zwiedzać muzea i podziwiać widoki', 'b' => 'Siedzieć w kawiarni, słuchać gwaru i muzyki', 'c' => 'Uprawiać sporty wodne lub chodzić po górach']
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $punkty = ['a' => 0, 'b' => 0, 'c' => 0];

    // Zliczanie odpowiedzi
    for ($i = 1; $i <= 20; $i++) {
        if (isset($_POST["q$i"])) {
            $punkty[$_POST["q$i"]]++;
        }
    }

    // Wyznaczanie dominującego stylu
    $max = max($punkty);
    $styl_uczenia = 'wzrokowiec'; // Domyślnie
    if ($punkty['b'] == $max) $styl_uczenia = 'sluchowiec';
    if ($punkty['c'] == $max) $styl_uczenia = 'kinestetyk';

    // Aktualizacja w bazie
    $stmt = $pdo->prepare("UPDATE uzytkownicy SET styl_uczenia = ? WHERE id = ?");
    $stmt->execute([$styl_uczenia, $_SESSION['user_id']]);

    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test VAK - Poznaj swój styl</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container" style="max-width: 800px;">
            <h2 style="text-align: center; color: var(--primary-color);">Test VAK</h2>
            <p style="text-align: center; margin-bottom: 30px;">Abyśmy mogli dopasować kursy do Ciebie, odpowiedz szczerze na 20 pytań.</p>
            
            <form method="POST" action="">
                <?php foreach ($pytania as $nr => $p): ?>
                    <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <p style="font-weight: 600; margin-bottom: 10px;"><?php echo $nr . ". " . $p['pytanie']; ?></p>
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="radio" name="q<?php echo $nr; ?>" value="a" required> <?php echo $p['a']; ?>
                        </label>
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="radio" name="q<?php echo $nr; ?>" value="b"> <?php echo $p['b']; ?>
                        </label>
                        <label style="display: block; cursor: pointer;">
                            <input type="radio" name="q<?php echo $nr; ?>" value="c"> <?php echo $p['c']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn" style="margin-top: 20px;">Zakończ test i zobacz mój styl!</button>
            </form>
        </div>
    </div>
</body>
</html>