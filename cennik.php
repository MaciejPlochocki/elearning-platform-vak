<?php
// cennik.php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cennik - E-Learning</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pricing-header { 
            text-align: center; 
            margin-bottom: 40px; 
            margin-top: 20px;
        }
        .pricing-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 0 auto; 
            max-width: 950px; 
            background: white; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            border-radius: 8px; 
            overflow: hidden; 
        }
        .pricing-table th, .pricing-table td { 
            padding: 18px 20px; 
            text-align: center; 
            border-bottom: 1px solid var(--border-color); 
        }
        .pricing-table th { 
            background-color: var(--primary-color); 
            color: white; 
            font-size: 16px; 
            font-weight: 500; 
        }
        .pricing-table th:first-child { 
            text-align: left; 
            background-color: var(--primary-hover); 
        }
        .pricing-table td:first-child { 
            text-align: left; 
            font-weight: 600; 
            color: var(--text-color); 
        }
        .pricing-table tr:last-child td { 
            border-bottom: none; 
            padding-bottom: 30px;
        }
        .price-tag { 
            font-size: 26px; 
            font-weight: bold; 
            color: var(--primary-color); 
        }
        .pricing-table tr:hover {
            background-color: #fcfcfc;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="pricing-header">
            <h2 class="section-title" style="border-bottom: none; display: inline-block;">Cennik Subskrypcji</h2>
            <p style="color: var(--text-muted); font-size: 16px;">
                Wybierz plan idealnie dopasowany do Twoich potrzeb i zyskaj dostęp do materiałów z matematyki, informatyki, elektryki i angielskiego skrojonych pod Twój styl uczenia się!
            </p>
        </div>

        <table class="pricing-table">
            <thead>
                <tr>
                    <th>Funkcje i możliwości platformy</th>
                    <th>Plan Miesięczny</th>
                    <th>Plan Półroczny</th>
                    <th>Plan Roczny</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Dostęp do wszystkich kursów i lekcji</td>
                    <td>✔️</td>
                    <td>✔️</td>
                    <td>✔️</td>
                </tr>
                <tr>
                    <td>Test psychometryczny VAK po rejestracji</td>
                    <td>✔️</td>
                    <td>✔️</td>
                    <td>✔️</td>
                </tr>
                <tr>
                    <td>Algorytm dopasowujący styl ucznia</td>
                    <td>✔️</td>
                    <td>✔️</td>
                    <td>✔️</td>
                </tr>
                <tr>
                    <td>Wsparcie i kontakt z nauczycielami</td>
                    <td>❌</td>
                    <td>✔️</td>
                    <td>✔️</td>
                </tr>
                <tr>
                    <td>Priorytetowa pomoc techniczna</td>
                    <td>❌</td>
                    <td>❌</td>
                    <td>✔️</td>
                </tr>
                <tr style="background-color: var(--bg-color);">
                    <td><strong>Cena całkowita</strong></td>
                    <td><span class="price-tag">49 zł</span> <br><small>/ miesiąc</small></td>
                    <td><span class="price-tag">249 zł</span> <br><small>/ 6 miesięcy</small></td>
                    <td><span class="price-tag">399 zł</span> <br><small>/ rok</small></td>
                </tr>
                <tr>
                    <td></td>
                    <td><a href="register.php" class="btn">Wybierz plan</a></td>
                    <td><a href="register.php" class="btn">Wybierz plan</a></td>
                    <td><a href="register.php" class="btn">Wybierz plan</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>