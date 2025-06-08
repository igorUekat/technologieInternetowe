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
        <title>Regulamin korzystania ze strony - KulturOn</title>
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
            <h1>Regulamin korzystania ze strony Kulturon</h1>

            <h2>§1. Postanowienia ogólne</h2>
            <ul>
            <li>Regulamin określa zasady korzystania ze strony Kulturon.</li>
            <li>Właścicielem serwisu jest [Twoja nazwa/organizacja].</li>
            <li>Korzystając z serwisu, akceptujesz ten regulamin.</li>
            </ul>

            <h2>§2. Zakres usług</h2>
            <ul>
            <li>Serwis umożliwia przeglądanie wydarzeń kulturalnych.</li>
            <li>Nie prowadzimy sprzedaży biletów – odsyłamy do zewnętrznych źródeł.</li>
            </ul>

            <h2>§3. Rejestracja i konto użytkownika</h2>
            <ul>
            <li>Rejestracja jest dobrowolna i darmowa.</li>
            <li>Użytkownik odpowiada za prawdziwość danych i bezpieczeństwo konta.</li>
            </ul>

            <h2>§4. Zasady korzystania z serwisu</h2>
            <ul>
            <li>Zakaz publikacji treści niezgodnych z prawem i naruszających dobra innych.</li>
            <li>Nie wolno wykorzystywać serwisu do celów komercyjnych bez zgody.</li>
            </ul>

            <h2>§5. Odpowiedzialność</h2>
            <ul>
            <li>Dokładamy starań, by dane były aktualne, ale nie ponosimy odpowiedzialności za ich zmiany.</li>
            <li>Nie odpowiadamy za działania organizatorów wydarzeń.</li>
            </ul>

            <h2>§6. Dane osobowe i prywatność</h2>
            <ul>
            <li>Dane użytkowników są przetwarzane zgodnie z RODO.</li>
            <li>Więcej informacji znajdziesz w naszej Polityce Prywatności.</li>
            </ul>

            <h2>§7. Postanowienia końcowe</h2>
            <ul>
            <li>Administrator zastrzega prawo do zmian w regulaminie.</li>
            <li>W sprawach spornych obowiązuje prawo polskie.</li>
            </ul>

            <p>
            Kontakt: <a href="mailto:kontakt@kulturon.pl">kontakt@kulturon.pl</a>
            </p>
        </div>
    </body>
</html>