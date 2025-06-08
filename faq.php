<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$uzytkownik = [];
$host = 'localhost';
$user = 'root';
$dbname = 'kulturon_db';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$pass = '';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
if ($isLoggedIn) {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $uzytkownik = $stmt->fetch();
}
function makeHeader($isLoggedIn, $uzytkownik){
    if($isLoggedIn){
        echo "<div class=\"logged_header_container\">
                <a href=\"./index.php\"><img src=\"./pictures/Logo.svg\" alt=\"Fioletowy napis KulturOn\"></a>
                <h1>Odkrywaj, Doświadczaj, Bądź Częścią</h1>
                <div class=\"logged_user_container\">
                    <a href=\"./powiadomienia.php\"><img src=\"./pictures/bell.svg\" alt=\"Dzwonek\" class=\"logged_user_icon\"></a>
                    <a href=\"./panel_uzytkownika.php\"><img src=\"./pictures/user.svg\" alt=\"Użytkownik\" class=\"logged_user_icon\"></a>
                    <a href=\"./logout.php\"><p>Wyloguj się</p></a>
                </div>
            </div>";
    }
    else{
        echo "<div class=\"header_container\">
                <div><a href=\"./index.php\"><img src=\"./pictures/Logo.svg\" alt=\"Fioletowy napis KulturOn\" class=\"logo_img\"></a></div>
                <div><h1 class=\"motto\">Odkrywaj, Doświadczaj, Bądź Częścią</h1></div>
                <div><a href=\"./login.php\" class=\"login-button\">Zaloguj się</a></div>
            </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>FAQ - KulturOn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php
        makeHeader($isLoggedIn, $uzytkownik);
        ?>
        <div class ="navigation_container">
            <div><a href="./lista_wydarzen.php"><button>Lista Wydarzeń</button></a></div>
            <div class="dropdown">
                <div><button>Regulamin</button></div>
                <div class="dropdown_content">
                    <a href="./dla_standard.php">Dla standardowych użytkowników</a>
                    <a href="./dla_firm.php">Dla firm</a>
                </div>
            </div>
            <div class="dropdown">
                <div><button>O nas</button></div>
                <div class="dropdown_content">
                    <a href="./cel_strony.php">Cel strony</a>
                    <a href="./faq.php">FAQ</a>
                    <a href="./kontakt.php">Kontakt</a>
                </div>
            </div>
        </div>
        <div class="regulamin_container">
    <h1>FAQ – Najczęściej zadawane pytania</h1>

    <h2>1. Czym jest Kulturon?</h2>
    <p>
      Kulturon to platforma internetowa umożliwiająca przeglądanie i promowanie wydarzeń kulturalnych w całej Polsce – takich jak koncerty, spektakle, wystawy, warsztaty i wiele innych.
    </p>

    <h2>2. Czy korzystanie z Kulturon jest darmowe?</h2>
    <p>
      Tak, przeglądanie wydarzeń na Kulturon jest całkowicie bezpłatne. Dodatkowe funkcje dla organizatorów (np. promowanie wydarzenia) mogą być płatne.
    </p>

    <h2>3. Czy muszę się rejestrować, aby korzystać z serwisu?</h2>
    <p>
      Nie. Możesz swobodnie przeglądać wydarzenia bez zakładania konta. Rejestracja jest wymagana tylko, jeśli chcesz dodawać lub zarządzać wydarzeniami.
    </p>

    <h2>4. Jak mogę dodać własne wydarzenie?</h2>
    <p>
      Wystarczy założyć konto organizatora, a następnie przejść do sekcji "Dodaj wydarzenie" i uzupełnić formularz z informacjami o wydarzeniu.
    </p>

    <h2>5. Jak długo trwa moderacja wydarzenia?</h2>
    <p>
      Zgłoszone wydarzenia są zazwyczaj weryfikowane w ciągu 24 godzin roboczych. Po akceptacji pojawią się w serwisie.
    </p>

    <h2>6. Czy mogę edytować wydarzenie po jego dodaniu?</h2>
    <p>
      Tak, po zalogowaniu się na swoje konto organizatora możesz edytować treść wydarzenia lub je usunąć.
    </p>

    <h2>7. Jak mogę skontaktować się z zespołem Kulturon?</h2>
    <p>
      Możesz napisać do nas na adres <a href="mailto:kontakt@kulturon.pl">kontakt@kulturon.pl</a>. Odpowiadamy w dni robocze w godzinach 9:00–17:00.
    </p>
  </div>
    </body>
</html>