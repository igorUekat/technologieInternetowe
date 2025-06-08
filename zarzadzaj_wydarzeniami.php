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
?>
<?php
$stmtEvents = $pdo->prepare("SELECT * FROM wydarzenia where id_organizacji = ? AND data > NOW()");
$stmtEvents->execute([$uzytkownik['id_organizacji']]);
$wydarzenia = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $eventId = $_POST['eventId'];
    $stmtOdwolaj = $pdo->prepare("UPDATE wydarzenia
    SET czyAktywny = 0
    where id = ?");
    $stmtOdwolaj->execute([$eventId]);
    $nazwa = [];
    foreach($wydarzenia as $wydarzenie){
        if($wydarzenie['id'] == $eventId){
            $nazwa = $wydarzenie['nazwa'];
            break;
        }
    }
    $data = date("Y-m-d H:i:s");
    $tresc = "Wydarzenie " . $wydarzenie['nazwa'] . " zostało odwołane";
    $stmtPowiadom = $pdo -> prepare("INSERT INTO powiadomienia (data, tresc, id_wydarzenia) VALUES(?,?,?)");
    $stmtPowiadom -> execute([$data, $tresc, $eventId]);
    header('Location: zarzadzaj_wydarzeniami.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>Zarządzaj wydarzeniami - KulturOn</title>
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
        <h1 class="notification_header">Bieżące wydarzenia organizacji</h1>
        <div class="lista_wydarzen_user">
                <?php
                    foreach($wydarzenia as $wydarzenie){
                        $zdjecia = explode(",",$wydarzenie['zdjecia']);
                        echo "<div class=\"list_event_container_user\">
                                <div class=\"list_event_info_wrapper_user\">
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
                                    <div class=\"user_event_info\">
                                        <a href=\"./edytuj_wydarzenie.php?id={$wydarzenie['id']}\"><p>Edytuj Wydarzenie</p></a>";
                        if($wydarzenie['czyAktywny'] == 1){
                            echo "<a href=\"#\" onclick=\"showPopup({$wydarzenie['id']})\"><p>Odwołaj Wydarzenie</p></a>";
                        }
                        else{
                            echo "<p>Wydarzenie odwołane</p>";
                        }
                        echo"<a href=\"./wydarzenie.php?id={$wydarzenie['id']}\"><p>Zobacz wydarzenie</p></a>
                                    </div>
                                </div>
                            </div>";
                        }
                ?>
        </div>
        <div class="pop_up_container" id="pop_up_container">
            <form action="zarzadzaj_wydarzeniami.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Czy na pewno chcesz odwołać wydarzenie?</h1>
                <input type="hidden" name="eventId" id="eventId" value="">
                <div class="pop_up_buttons">
                    <button  type="submit" class="execute_button">Tak</button>
                    <button  type="button" onclick="hidePopup()" class="cancel_button" id="cancelButton">Nie</button>
                </div>
            </form>
        </div>
        <script>
            function showPopup(eventId) {
            document.getElementById('pop_up_container').style.display = 'flex';
            document.getElementById('eventId').value = eventId
            }

            function hidePopup() {
            document.getElementById('pop_up_container').style.display = 'none';
            }
        </script>
    </body>
</html>