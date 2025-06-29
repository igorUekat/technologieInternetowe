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
        <title>Regulamin dla firm - KulturOn</title>
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
    <h1>Regulamin współpracy z przedsiębiorstwami – Kulturon</h1>

    <h2>§1. Postanowienia ogólne</h2>
    <ul>
      <li>Niniejszy regulamin określa zasady współpracy między platformą Kulturon a przedsiębiorstwami publikującymi wydarzenia kulturalne.</li>
      <li>Właścicielem serwisu Kulturon jest [nazwa firmy/organizacji].</li>
      <li>Korzystanie z usług publikacyjnych oznacza akceptację niniejszego regulaminu.</li>
    </ul>

    <h2>§2. Zakres usług</h2>
    <ul>
      <li>Kulturon umożliwia przedsiębiorstwom dodawanie, edytowanie i promowanie wydarzeń kulturalnych.</li>
      <li>Publikacja wydarzeń może podlegać opłacie zgodnie z obowiązującym cennikiem.</li>
      <li>Platforma zastrzega sobie prawo do moderowania treści.</li>
    </ul>

    <h2>§3. Rejestracja i konto firmowe</h2>
    <ul>
      <li>Do korzystania z usług wymagane jest założenie konta firmowego.</li>
      <li>Firma zobowiązana jest do podania prawdziwych danych oraz do ich aktualizacji.</li>
      <li>Administrator może weryfikować dane firmowe przed aktywacją konta.</li>
    </ul>

    <h2>§4. Obowiązki przedsiębiorstwa</h2>
    <ul>
      <li>Publikowane wydarzenia muszą być zgodne z prawem i tematyką kulturalną.</li>
      <li>Zakazane jest publikowanie treści reklamowych niezwiązanych z wydarzeniami kulturalnymi.</li>
      <li>Firma odpowiada za rzetelność i aktualność podawanych informacji.</li>
    </ul>

    <h2>§5. Odpowiedzialność</h2>
    <ul>
      <li>Kulturon nie ponosi odpowiedzialności za ewentualne szkody wynikające z błędnych informacji przekazanych przez firmę.</li>
      <li>Administrator zastrzega sobie prawo do usunięcia wydarzenia lub zablokowania konta w przypadku naruszenia regulaminu.</li>
    </ul>

    <h2>§6. Płatności i faktury</h2>
    <ul>
      <li>Niektóre usługi mogą być płatne – szczegóły określa aktualny cennik.</li>
      <li>Faktury VAT będą wystawiane na podstawie danych podanych w koncie firmowym.</li>
    </ul>

    <h2>§7. Dane osobowe i polityka prywatności</h2>
    <ul>
      <li>Dane osobowe przedstawicieli firm są przetwarzane zgodnie z obowiązującymi przepisami prawa (RODO).</li>
      <li>Szczegóły dostępne są w Polityce Prywatności serwisu.</li>
    </ul>

    <h2>§8. Postanowienia końcowe</h2>
    <ul>
      <li>Kulturon zastrzega sobie prawo do zmian regulaminu.</li>
      <li>Wszelkie spory będą rozstrzygane przez sąd właściwy dla siedziby administratora serwisu.</li>
    </ul>

    <p>
      Kontakt dla firm: <a href="mailto:partnerzy@kulturon.pl">partnerzy@kulturon.pl</a>
    </p>
  </div>
    </body>
</html>