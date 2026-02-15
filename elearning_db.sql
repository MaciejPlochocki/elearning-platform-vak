-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2026 at 10:39 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning_db`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dzialy`
--

CREATE TABLE `dzialy` (
  `id` int(11) NOT NULL,
  `id_kursu` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dzialy`
--

INSERT INTO `dzialy` (`id`, `id_kursu`, `nazwa`) VALUES
(1, 1, 'Dział 1 - Wprowadzenie'),
(2, 1, 'Dział 2 - Zaawansowane'),
(3, 2, 'Dział 1 - Wprowadzenie'),
(4, 2, 'Dział 2 - Zaawansowane');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kategorie`
--

CREATE TABLE `kategorie` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategorie`
--

INSERT INTO `kategorie` (`id`, `nazwa`) VALUES
(1, 'Matematyka'),
(2, 'Informatyka'),
(3, 'Elektryka'),
(4, 'Język angielski');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kursy`
--

CREATE TABLE `kursy` (
  `id` int(11) NOT NULL,
  `id_nauczyciela` int(11) NOT NULL,
  `id_kategorii` int(11) NOT NULL,
  `tytul` varchar(100) NOT NULL,
  `opis` text NOT NULL,
  `obrazek` varchar(255) DEFAULT 'default_course.jpg',
  `styl_uczenia` enum('wzrokowiec','sluchowiec','kinestetyk') NOT NULL,
  `data_utworzenia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kursy`
--

INSERT INTO `kursy` (`id`, `id_nauczyciela`, `id_kategorii`, `tytul`, `opis`, `obrazek`, `styl_uczenia`, `data_utworzenia`) VALUES
(1, 4, 1, 'Matematyka Dyskretna', 'Podstawy matematyki dyskretnej', 'default_course.jpg', 'wzrokowiec', '2026-02-15 21:09:30'),
(2, 4, 1, 'Matematyka Dyskretna 2', 'Kontynuacja', 'default_course.jpg', 'kinestetyk', '2026-02-15 21:33:20');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `lekcje`
--

CREATE TABLE `lekcje` (
  `id` int(11) NOT NULL,
  `id_dzialu` int(11) NOT NULL,
  `tytul` varchar(100) NOT NULL,
  `tresc` text NOT NULL,
  `kolejnosc` int(11) NOT NULL,
  `dodatek_medialny` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lekcje`
--

INSERT INTO `lekcje` (`id`, `id_dzialu`, `tytul`, `tresc`, `kolejnosc`, `dodatek_medialny`) VALUES
(1, 1, 'Podstawy', 'Matematyka dyskretna to dział matematyki zajmujący się badaniem struktur nieciągłych, skończonych lub przeliczalnych, takich jak liczby całkowite, grafy czy wyrażenia logiczne. Stanowi fundament teoretyczny informatyki, analizy algorytmów, kryptografii oraz teorii gier. Główne zagadnienia obejmują kombinatorykę, teorię grafów, logikę i teorię liczb.', 1, NULL),
(2, 1, 'Logika', 'Logika matematyczna i Algebra Boole\'a (algebra dwuelementowa \r\n) stanowią matematyczne podstawy informatyki i elektroniki cyfrowej, operując na wartościach prawda (1) i fałsz (0) za pomocą trzech głównych działań: koniunkcji (AND/\r\n), alternatywy (OR/+\r\n) i negacji (NOT/\r\n). Pozwalają one na sformalizowany zapis, upraszczanie wyrażeń logicznych i projektowanie układów bramek', 2, NULL),
(3, 1, 'Teoria Liczb', 'Teoria liczb to dział matematyki wywodzący się ze starożytności, zajmujący się badaniem własności liczb, głównie naturalnych i całkowitych, w tym liczb pierwszych. Bada podzielność, kongruencje, a także równania diofantyczne. Wyróżnia się m.in. teorię analityczną (metody analizy) oraz algorytmiczną (zastosowania w informatyce).', 3, NULL),
(4, 3, 'Podstawy', 'podstawy', 1, 'https://www.youtube.com/watch?v=AR-EyJjTKsM');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `postepy`
--

CREATE TABLE `postepy` (
  `id` int(11) NOT NULL,
  `id_zapisu` int(11) NOT NULL,
  `id_lekcji` int(11) NOT NULL,
  `zdany` tinyint(1) DEFAULT 0,
  `liczba_niezaliczen` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postepy`
--

INSERT INTO `postepy` (`id`, `id_zapisu`, `id_lekcji`, `zdany`, `liczba_niezaliczen`) VALUES
(1, 1, 1, 1, 0),
(2, 1, 2, 1, 0),
(3, 1, 3, 1, 0),
(4, 2, 4, 1, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `testy`
--

CREATE TABLE `testy` (
  `id` int(11) NOT NULL,
  `id_lekcji` int(11) NOT NULL,
  `pytanie` text NOT NULL,
  `odpowiedz_a` varchar(255) NOT NULL,
  `odpowiedz_b` varchar(255) NOT NULL,
  `odpowiedz_c` varchar(255) NOT NULL,
  `poprawna_odpowiedz` enum('a','b','c') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testy`
--

INSERT INTO `testy` (`id`, `id_lekcji`, `pytanie`, `odpowiedz_a`, `odpowiedz_b`, `odpowiedz_c`, `poprawna_odpowiedz`) VALUES
(1, 1, 'Jakie jest glowne zagadnienie matematyki dyskretnej?', 'logika', 'dodawanie', 'odejmowanie', 'a'),
(2, 2, 'Ktore z tych nie jest dzialaniem logiki?', 'koniunkcja', 'alternatywa', 'dodawanie', 'c'),
(3, 3, 'Czym zajmuje sie teoria liczb?', 'Badaniem własnosci liczb', 'Teoretycznym wyjasnianiem liczb', 'Dedukcją', 'a'),
(4, 4, 'Jakie jest glowne zagadnienie matematyki dyskretnej?', 'logika', 'alternatywa', 'Dedukcją', 'a');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `telefon` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `funkcja` enum('uczen','nauczyciel','moderator') DEFAULT 'uczen',
  `styl_uczenia` enum('wzrokowiec','sluchowiec','kinestetyk') DEFAULT NULL,
  `data_dolaczenia` timestamp NOT NULL DEFAULT current_timestamp(),
  `subskrypcja_aktywna` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `imie`, `nazwisko`, `telefon`, `email`, `haslo`, `funkcja`, `styl_uczenia`, `data_dolaczenia`, `subskrypcja_aktywna`) VALUES
(2, 'Jan', 'Kowalski', '111111111', 'nauczyciel@elearning.pl', '$2y$10$tZ2R.H7hT/K7xR4w5Y5V.O5.O3G2z7J1/2.P3/4.', 'nauczyciel', NULL, '2026-02-15 20:45:27', 0),
(3, 'Jan', 'Testowy', '123456789', 'jantesttowy@gmail.com', '$2y$10$r78hDXHFvEOAzdUzgvgt0O1rFwWI/dcjZ0yNNJTjtvjfVlcee0DAm', 'uczen', 'wzrokowiec', '2026-02-15 20:54:48', 0),
(4, 'Pan', 'Nauczyciel', '111222333', 'nauczyciel1@wp.pl', '$2y$10$MvI8UVPxYC7KzGd9hWzM5uTiaxTsLKG.QWDCRv2pnIkAzctpoSz2S', 'nauczyciel', NULL, '2026-02-15 21:06:57', 0),
(5, 'Pan', 'Moderator', '001221331', 'panmoderator@wp.pl', '$2y$10$kE03lJ8Gdc5k2MghGHpe3OMcfJ4EzGTJNPNuhXx714JOwTs9ealzu', 'moderator', NULL, '2026-02-15 21:17:40', 0),
(6, 'Maciej', 'Płochocki', '122322222', 'plochocki.maciej001@gmail.com', '$2y$10$8zRpPQiv.1xDIpQZFlBc1efzOAx.h5NT93bZkQKAFASO2T0imMH.i', 'uczen', 'kinestetyk', '2026-02-15 21:24:35', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zapisy`
--

CREATE TABLE `zapisy` (
  `id` int(11) NOT NULL,
  `id_ucznia` int(11) NOT NULL,
  `id_kursu` int(11) NOT NULL,
  `procent_ukonczenia` int(11) DEFAULT 0,
  `status` enum('nieukonczony','ukonczony') DEFAULT 'nieukonczony',
  `data_zapisu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zapisy`
--

INSERT INTO `zapisy` (`id`, `id_ucznia`, `id_kursu`, `procent_ukonczenia`, `status`, `data_zapisu`) VALUES
(1, 3, 1, 100, 'ukonczony', '2026-02-15 21:11:26'),
(2, 6, 2, 33, 'nieukonczony', '2026-02-15 21:34:36');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `dzialy`
--
ALTER TABLE `dzialy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kursu` (`id_kursu`);

--
-- Indeksy dla tabeli `kategorie`
--
ALTER TABLE `kategorie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `kursy`
--
ALTER TABLE `kursy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nauczyciela` (`id_nauczyciela`),
  ADD KEY `id_kategorii` (`id_kategorii`);

--
-- Indeksy dla tabeli `lekcje`
--
ALTER TABLE `lekcje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dzialu` (`id_dzialu`);

--
-- Indeksy dla tabeli `postepy`
--
ALTER TABLE `postepy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_zapisu` (`id_zapisu`),
  ADD KEY `id_lekcji` (`id_lekcji`);

--
-- Indeksy dla tabeli `testy`
--
ALTER TABLE `testy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_lekcji` (`id_lekcji`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telefon` (`telefon`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `zapisy`
--
ALTER TABLE `zapisy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ucznia` (`id_ucznia`),
  ADD KEY `id_kursu` (`id_kursu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dzialy`
--
ALTER TABLE `dzialy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategorie`
--
ALTER TABLE `kategorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kursy`
--
ALTER TABLE `kursy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lekcje`
--
ALTER TABLE `lekcje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `postepy`
--
ALTER TABLE `postepy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `testy`
--
ALTER TABLE `testy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `zapisy`
--
ALTER TABLE `zapisy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dzialy`
--
ALTER TABLE `dzialy`
  ADD CONSTRAINT `dzialy_ibfk_1` FOREIGN KEY (`id_kursu`) REFERENCES `kursy` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kursy`
--
ALTER TABLE `kursy`
  ADD CONSTRAINT `kursy_ibfk_1` FOREIGN KEY (`id_nauczyciela`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kursy_ibfk_2` FOREIGN KEY (`id_kategorii`) REFERENCES `kategorie` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lekcje`
--
ALTER TABLE `lekcje`
  ADD CONSTRAINT `lekcje_ibfk_1` FOREIGN KEY (`id_dzialu`) REFERENCES `dzialy` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `postepy`
--
ALTER TABLE `postepy`
  ADD CONSTRAINT `postepy_ibfk_1` FOREIGN KEY (`id_zapisu`) REFERENCES `zapisy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `postepy_ibfk_2` FOREIGN KEY (`id_lekcji`) REFERENCES `lekcje` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testy`
--
ALTER TABLE `testy`
  ADD CONSTRAINT `testy_ibfk_1` FOREIGN KEY (`id_lekcji`) REFERENCES `lekcje` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `zapisy`
--
ALTER TABLE `zapisy`
  ADD CONSTRAINT `zapisy_ibfk_1` FOREIGN KEY (`id_ucznia`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zapisy_ibfk_2` FOREIGN KEY (`id_kursu`) REFERENCES `kursy` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
