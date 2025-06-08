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
<?php
$pdo = new PDO($dsn, $user, $pass, $options);
$stmtEvents = $pdo->query("SELECT * from wydarzenia where data > NOW() and czyAktywny = 1");
$stmtEvents->execute();
$wydarzenia = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
function zaTrzyDni(DateTime $eventTime): bool{
    $today = new DateTime('today');
    $threeDaysLater = (clone $today)->add(new DateInterval('P3D'));
    return $eventTime->format('Y-m-d') === $threeDaysLater->format('Y-m-d');
}
foreach($wydarzenia as $wydarzenie){
    $eventDate = new DateTime($wydarzenie['data']);
    if(zaTrzyDni($eventDate)){
        $stmtPowiadomienie = $pdo -> prepare("INSERT INTO powiadomienia (data, tresc, id_wydarzenia) VALUES(?,?,?)");
        $stmtPowiadomienie->execute([date('Y-m-d H:i:s'), "Wydarzenie " . $wydarzenie['nazwa'] . " odbędzie się za mniej niż 3 dni!",$wydarzenie['id']]);
    }
}
usort($wydarzenia, function($a,$b){
    return $b['liczba_obserwujacych'] - $a['liczba_obserwujacych'];
});
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>KulturOn</title>
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
        <div class="main">
            <div class="main_text">
                <h1>Odkrywaj wydarzenia w twojej okolicy!</h1>
            </div>
            <div class="container">
                <a id="wydarzenie_link" href="./wydarzenie.php?id="><div class="event_info_wrapper">
                    <div class="event_img_container">
                        <div class="box">
                            <span id="wydarzenie_nazwa">Lorem Ipsum - Koncert</span>
                        </div>
                        <img id="wydarzenie_zdjecie" src="./pictures/koncert.jpg" alt="Koncert">
                    </div>
                    <div class="event_info">
                        <img src="./pictures/kalendarz.svg" alt="Kalendarz">
                        <span id="wydarzenie_data">1.1.2000 00:00:00</span>
                        <img src="./pictures/location.svg" alt="Lokalizacja">
                        <span id="wydarzenie_miasto">Piekary śląskie</span>
                        <img src="./pictures/cena.svg" alt="Cena">
                        <span id="wydarzenie_cena">500zł/500zł</span>
                        <img class="optional" src="./pictures/oko.svg" alt="Obserwujący">
                        <span class="optional" id="wydarzenie_obserwujacy">100000000</span>
                        <img class="optional" src="./pictures/czlowiek.svg" alt="Biorący udział">
                        <span class="optional" id="wydarzenie_uczestnicy">100000000</span>
                    </div>                                                             
                </div></a>
            </div>
            <div class="sliders_radios">
                <p>
                    <?php
                    $i = 1;
                    foreach($wydarzenia as $wydarzenie){
                        $zdjecia = explode(",",$wydarzenie['zdjecia']);
                        $zdjecie = $zdjecia[0];
                        if($i == 1){
                            echo "<label><input type=\"radio\" name=\"color\" onchange=\"showEvent(this, '{$wydarzenie['id']}', '{$wydarzenie['nazwa']}', '{$zdjecie}', '{$wydarzenie['data']}', '{$wydarzenie['miasto']}', '{$wydarzenie['cenaNormalna']}', '{$wydarzenie['cenaUlgowa']}', '{$wydarzenie['liczba_obserwujacych']}', '{$wydarzenie['liczba_uczestnikow']}')\" checked/></label>";
                            $firstEventJS = "showEvent({checked: true}, '{$wydarzenie['id']}', '{$wydarzenie['nazwa']}', '{$zdjecie}', '{$wydarzenie['data']}', '{$wydarzenie['miasto']}', '{$wydarzenie['cenaNormalna']}', '{$wydarzenie['cenaUlgowa']}', '{$wydarzenie['liczba_obserwujacych']}', '{$wydarzenie['liczba_uczestnikow']}');";
                        }
                        else if($i == 6){
                            break;
                        }
                        else{
                            echo "<label><input type=\"radio\" name=\"color\" onchange=\"showEvent(this, '{$wydarzenie['id']}', '{$wydarzenie['nazwa']}', '{$zdjecie}', '{$wydarzenie['data']}', '{$wydarzenie['miasto']}', '{$wydarzenie['cenaNormalna']}', '{$wydarzenie['cenaUlgowa']}', '{$wydarzenie['liczba_obserwujacych']}', '{$wydarzenie['liczba_uczestnikow']}')\"/></label>";
                        }
                        $i++;
                    }
                    ?>
                </p>
            </div>
        </div>
        <script>
        <?php echo $firstEventJS ?>
        function showEvent(checkbox, id, nazwa, zdjecie, data, miasto, normalne, ulgowe, obserwujacy, uczestnicy){
            if(checkbox.checked == true){
                document.getElementById('wydarzenie_link').href = './wydarzenie.php?id=' + id;
                document.getElementById('wydarzenie_nazwa').textContent = nazwa;
                document.getElementById('wydarzenie_zdjecie').src = zdjecie
                document.getElementById('wydarzenie_data').textContent = data;
                document.getElementById('wydarzenie_miasto').textContent = miasto;
                document.getElementById('wydarzenie_cena').textContent = normalne + 'zł/' + ulgowe + 'zł';
                document.getElementById('wydarzenie_obserwujacy').textContent = obserwujacy;
                document.getElementById('wydarzenie_uczestnicy').textContent = uczestnicy;
            }
        }
        </script>
    </body>
</html>