<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
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
<?php
$pdo = new PDO($dsn, $user, $pass, $options);
$stmtZalogowany = $pdo->prepare("SELECT id,email,nazwa,typ,id_organizacji,rola FROM uzytkownicy WHERE id = ?");
$stmtZalogowany->execute([$uzytkownik['id']]);
$uzytkownikZalogowany = $stmtZalogowany->fetch();
$org = [];
if ($uzytkownikZalogowany['typ'] == 'org' && $uzytkownikZalogowany['id_organizacji'] != null){
    $stmtOrg = $pdo->prepare("SELECT id, nazwa, adres FROM organizacje where id = ?");
    $stmtOrg->execute([$uzytkownikZalogowany['id_organizacji']]);
    $org = $stmtOrg->fetch();
}
function panelUzytkownika($uzytkownikZalogowany, $org){
    if($uzytkownikZalogowany['typ'] == 'org'){
        if($uzytkownikZalogowany['id_organizacji'] == null){
            echo "<div class=\"user_panel_container\">
                    <img src=\"./pictures/company.svg\" alt=\"Organizacja\">
                    <div class=\"user_panel_info\">
                        <h1>Brak organizacji</h1>
                    </div>
                    <div class=\"user_panel_buttons\">
                        <a href=\"./znajdz_stworz_org.php\"><button>Znajdź lub stwórz organizację</button></a>
                    </div>
                </div>";
        }
        else{
            echo "<div class=\"user_panel_container\">
                    <img src=\"./pictures/company.svg\" alt=\"Organizacja\">
                    <div class=\"user_panel_info\">
                        <h1>{$org['nazwa']}</h1>
                        <p>{$org['adres']}</p>
                    </div>";
            if($uzytkownikZalogowany['rola'] == 'administrator'){
                echo"<div class=\"user_panel_buttons\">
                        <a href=\"./zarzadzaj_organizacja.php\"><button>Zarządzaj organizacją</button></a>
                        <a href=\"./edytuj_org.php\"><button>Zmień dane organizacji</button></a>
                        <a href=\"./dodaj_wydarzenie.php\"><button>Dodaj wydarzenie</button></a>
                        <a href=\"./zarzadzaj_wydarzeniami.php\"><button>Zarządzaj wydarzeniami</button></a>
                        <a href=\"./historia_wydarzen_org.php\"><button>Wasze wydarzenia</button></a>
                    </div>";
            }
            else if($uzytkownikZalogowany['rola'] == 'czlonek'){
                    echo"<div class=\"user_panel_buttons\">
                        <a href=\"./dodaj_wydarzenie.php\"><button>Dodaj wydarzenie</button></a>
                        <a href=\"./zarzadzaj_wydarzeniami.php\"><button>Zarządzaj wydarzeniami</button></a>
                        <a href=\"./historia_wydarzen_org.php\"><button>Wasze wydarzenia</button></a>
                    </div>";
            }
            echo"</div>";
        }
    }
    echo "<div class=\"user_panel_container\">
            <img src=\"./pictures/user.svg\" alt=\"Użytkownik\">
            <div class=\"user_panel_info\">
                <h1>{$uzytkownikZalogowany['nazwa']}</h1>
                <p>{$uzytkownikZalogowany['email']}</p>
            </div>
            <div class=\"user_panel_buttons\">
                <a href=\"./lista_wydarzen_user.php\"><button>Lista Wydarzeń</button></a>
                <a href=\"./historia_wydarzen.php\"><button>Historia wydarzeń</button></a>
                <a href=\"./edytuj_profil.php\"><button>Edytuj profil</button></a>
            </div>
        </div>";
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>Panel użytkownika - KulturOn</title>
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
        <?php
        panelUzytkownika($uzytkownikZalogowany, $org);
        ?>
    </body>   
</html>