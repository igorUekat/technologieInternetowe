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
    $stmt = $pdo->prepare("SELECT id, id_organizacji FROM uzytkownicy WHERE id = ?");
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
$stmtEvents = $pdo->prepare("SELECT * FROM wydarzenia where id_organizacji = ? and data < NOW()");
$stmtEvents->execute([$uzytkownik['id_organizacji']]);
$wydarzenia = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>Historia wydarzeń organizacji</title>
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
        <div class="main_text">
            <h1>Historia wydarzeń</h1>
        </div>
        <div class="lista_wydarzen_history">
                <?php
                foreach($wydarzenia as $wydarzenie){
                    $zdjecia = explode(',', $wydarzenie['zdjecia']);
                    echo "<div class=\"list_event_container\">
                                <div class=\"list_event_info_wrapper\">
                                    <div class=\"list_event_img_container\">
                                        <img src=\"{$zdjecia[0]}\" alt=\"Koncert\">
                                    </div>
                                    <div class=\"list_event_info_container\">
                                        <div class=\"list_event_title\">
                                            <p>{$wydarzenie['nazwa']}</p>
                                        </div>
                                        <div class=\"list_event_description\">
                                            <p>{$wydarzenie['opis']}</p>
                                        </div>
                                        <div class=\"list_event_overall_info\">
                                            <img src=\"./pictures/kalendarz.svg\" alt=\"Kalendarz\">
                                            <p>{$wydarzenie['data']}</p>
                                            <img src=\"./pictures/location.svg\" alt=\"Lokalizacja\">
                                            <p>{$wydarzenie['adres']},{$wydarzenie['miasto']}</p>
                                            <img src=\"./pictures/cena.svg\" alt=\"Cena\">
                                            <p>{$wydarzenie['cenaNormalna']}zł/{$wydarzenie['cenaUlgowa']}zł</p>
                                            <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                                            <p>{$wydarzenie['liczba_obserwujacych']}</p>
                                            <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                                            <p>{$wydarzenie['liczba_uczestnikow']}</p>
                                        </div>
                                    </div>
                                </div>
                        </div>";
                }
                ?>
            </div>
    </body>
</html>