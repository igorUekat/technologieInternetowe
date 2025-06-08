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
        <title>Kontakt = KulturOn</title>
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
    <h2>Kontakt</h2>

    <p>
      Masz pytania, sugestie lub chcesz zgłosić wydarzenie? Skontaktuj się z nami!
    </p>

    <h2>Dane kontaktowe</h2>
    <ul>
      <li>Adres e-mail: <a href="mailto:kontakt@kulturon.pl">kontakt@kulturon.pl</a></li>
      <li>Telefon: +48 123 456 789</li>
      <li>Godziny pracy: poniedziałek – piątek, 9:00–17:00</li>
    </ul>

    <h2>Adres korespondencyjny</h2>
    <p>
      Kulturon<br>
      ul. Przykładowa 1<br>
      00-001 Warszawa<br>
      Polska
    </p>

    <h2>Dla organizatorów wydarzeń</h2>
    <p>
      Jeśli chcesz dodać wydarzenie lub nawiązać współpracę, napisz do nas na adres:  
      <a href="mailto:partnerzy@kulturon.pl">partnerzy@kulturon.pl</a>
    </p>
    </div>
    </body>
</html>