<?php
if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $eventId = $_GET['id'];
}
else{
    echo "<p class=\"error\"> Nieoczekiwane ID wydarzenia</p>"; 
    exit;
}
$host = 'localhost';
$database = 'kulturon_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try{
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT * from wydarzenia where id = $eventId");
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    $zdjecia = explode(",",$event['zdjecia']);
    $stmtCompany = $pdo->prepare("SELECT nazwa FROM organizacje WHERE id = ?");
    $stmtCompany->execute([$event['id_organizacji']]);
    $organizator = $stmtCompany->fetch();

}catch(PDOException $e){
    die("connection failed". $e->getMessage());
}
?>
<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$uzytkownik = [];
$czyKupiono = [];
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
    $stmtCzyKupiono = $pdo->prepare("SELECT * FROM `{$uzytkownik['id']}_wydarzenia` WHERE id_wydarzenia = ?");
    $stmtCzyKupiono->execute([$eventId]);
    $czyKupiono = $stmtCzyKupiono->fetch();
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
function makeButtons($czyKupiono, $isLoggedIn, $event, $eventId){
        if($event['czyAktywny'] == 0){
            echo"	<div class=\"info2\">
                        <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                        <p>{$event['liczba_uczestnikow']}</p>
                        <button>WYDARZENIE ODWOŁANE</button>
                        <p>{$event['liczba_obserwujacych']}</p>
                        <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                    </div>";            
        }
        else if(strtotime($event['data']) > date("Y-m-d")){
                    echo"	<div class=\"info2\">
                        <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                        <p>{$event['liczba_uczestnikow']}</p>
                        <button>WYDARZENIE SIĘ ODBYŁO</button>
                        <p>{$event['liczba_obserwujacych']}</p>
                        <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                    </div>";  
        }
        else if(empty($czyKupiono) || !$isLoggedIn){
            echo"	<div class=\"info2\">
                        <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                        <p>{$event['liczba_uczestnikow']}</p>
                        <button onclick=\"showPopup()\">Zgłoś udział/kup bilet</button>
                        <form action=\"wydarzenie.php?id={$eventId}\" method=\"POST\">
                            <button type=\"submit\" name=\"action\" value=\"zapisz\">Zapisz wydarzenie</button>
                        </form>
                        <p>{$event['liczba_obserwujacych']}</p>
                        <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                    </div>";
        }  
        else if($isLoggedIn && $czyKupiono['czyKupiono'] == 1){
            echo"	<div class=\"info2\">
                        <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                        <p>{$event['liczba_uczestnikow']}</p>
                        <button>Bierzesz udział</button>
                        <button>Zapisano</button>
                        <p>{$event['liczba_obserwujacych']}</p>
                        <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                    </div>";
        }
        else if($isLoggedIn && $czyKupiono['czyKupiono'] == 0){
            echo"	<div class=\"info2\">
                        <img src=\"./pictures/czlowiek.svg\" alt=\"Biorący udział\">
                        <p>{$event['liczba_uczestnikow']}</p>
                        <button onclick=\"showPopup()\">Zgłoś udział/kup bilet</button>
                        <button>Zapisano</button>
                        <p>{$event['liczba_obserwujacych']}</p>
                        <img src=\"./pictures/oko.svg\" alt=\"Obserwujący\">
                    </div>";       
        }
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['action'])){
        if($isLoggedIn){
            switch($_POST['action']){
                case 'zapisz':
                    $stmtZapisz = $pdo->prepare("INSERT INTO {$uzytkownik['id']}_wydarzenia (id_wydarzenia, czyKupiono) VALUES(?,?)");
                    $stmtZapisz->execute([$event['id'],0]);
                    $stmtDodajObserw = $pdo->prepare("UPDATE wydarzenia
                    SET liczba_obserwujacych = liczba_obserwujacych + 1
                    WHERE id = ?");
                    $stmtDodajObserw->execute([$event['id']]);
                    header('Location: lista_wydarzen_user.php');
                    exit;
                    break;
                case 'kup':
                    if(empty($_POST['normalne']) && empty($_POST['ulgowe'])){
                        $_SESSION['error'] = "Trzeba wybrać chociaż jedną opcję!";
                        header("Location: wydarzenie.php?id={$eventId}");
                        exit;
                    }
                    else{
                        if(empty($czyKupiono)){
                            $stmtKup = $pdo->prepare("INSERT INTO {$uzytkownik['id']}_wydarzenia (id_wydarzenia, czyKupiono) VALUES(?,?)");
                            $stmtKup->execute([$event['id'],1]);
                        }
                        else{
                            $stmtDodaj = $pdo->prepare("UPDATE {$uzytkownik['id']}_wydarzenia
                            SET czyKupiono = 1
                            WHERE id_wydarzenia = ?");
                            $stmtDodaj->execute([$event['id']]);
                        }
                        $stmtDodajUczes = $pdo->prepare("UPDATE wydarzenia
                        SET liczba_uczestnikow = liczba_uczestnikow + ? + ?
                        WHERE id = ?");
                        $stmtDodajUczes->execute([$_POST['normalne'],$_POST['ulgowe'],$event['id']]);
                        header('Location: lista_wydarzen_user.php');
                        exit;
                    }
                    break;
            }
        }
        else{
            header('Location: login.php');
            exit;
        }
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
        <title><?php echo htmlspecialchars($event['nazwa']); ?></title>
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
        <div class="event_container">
            <div class="image_container">
                <?php
                echo "<img src=\"{$zdjecia[0]}\" alt=\"Koncert\">";
                ?>
            </div>
            <div class="info_container">
                <div class="info">
                    <div class="basic_info">
                        <h1>Podstawowe informacje:</h1>
                        <div class="basic_info_content">
                            <img src="./pictures/kalendarz.svg" alt="Kalendarz">
                            <?php
                            echo "<p>{$event['data']}</p>";
                            ?>
                        </div>
                        <div class="basic_info_content">
                            <img src="./pictures/location.svg" alt="Lokalizacja">
                            <?php
                            echo "<p>{$event['adres']},{$event['miasto']}</p>";
                            ?>
                        </div>
                        <div class="basic_info_content">
                            <img src="./pictures/cena.svg" alt="Cena">
                            <?php
                            echo "<p>{$event['cenaNormalna']}zł\\{$event['cenaNormalna']}zł</p>";
                            ?>
                        </div>
                    </div>
                    <div class="title">
                        <?php
                        echo "<h1>{$event['nazwa']}</h1>
                              <h2>{$event['kategoria']} by {$organizator['nazwa']}</h2>"; 
                        ?>
                    </div>
                    <div class="maps">
                        <iframe 
                        src="https://www.google.com/maps/embed/v1/place?key=______________________=<?php echo urlencode($event['miasto']);?>"
                        width="600" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
                <?php
                makeButtons($czyKupiono, $isLoggedIn, $event, $eventId);
                ?>
            </div>
            <div class="description_container">
                <h2>Opis wydarzenia</h2>
                <?php echo"<p>{$event['opis']}</p>";?>
            </div>
            <div class="gallery_container">
                <h2>Galeria</h2>
                <div class="gallery_image_container">
                    <?php
                    foreach($zdjecia as $zdjecie){
                        echo"<div class=\"gallery_image\">
                                <img src=\"{$zdjecie}\" alt=\"{$event['kategoria']}\">
                            </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <form action="wydarzenie.php?id=<?php echo $eventId?>" method="POST" class="pop_up_container" id="pop_up_container">
            <div class="pop_up_window">
                <h1 class="pop_up_title">Kup bilety</h1>
                <div class="input_container">
                    <?php echo "<label for=\"Normalne\" class=\"input_label\">Normalne: <span id=\"normalnaCena\">{$event['cenaNormalna']}</span>zł</label>"; ?>
                    <input type="number" id="Normalne" name="normalne" class="input_field" placeholder="Ilość biletów normalnych" min="0">
                    <?php echo "<label for=\"Ulgowe\" class=\"input_label\">Ulgowe: <span id=\"ulgowaCena\">{$event['cenaUlgowa']}</span>zł</label>"; ?>
                    <input type="number" id="Ulgowe" name="ulgowe" class="input_field" placeholder="Ilość biletów ulgowych" min="0">
                </div>
                <p>Razem: <span id="suma">0</span>zł</p>
                <p class="pop_up_message" id="pop_up_message"></p>
                <div class="pop_up_buttons">
                    <button type="submit" name="action" value="kup">Kup</button>
                    <button type="button" onclick="hidePopup()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </div>
        </form>
        <script>
            function showPopup() {
            document.getElementById('pop_up_container').style.display = 'flex';
            }

            function hidePopup() {
            document.getElementById('pop_up_container').style.display = 'none';
            }
            function calculateTotal() {
                const normalPrice = parseFloat(document.getElementById('normalnaCena').textContent);
                const discountPrice = parseFloat(document.getElementById('ulgowaCena').textContent);
                const normalTickets = parseInt(document.getElementById('Normalne').value) || 0;
                const discountTickets = parseInt(document.getElementById('Ulgowe').value) || 0;
                const total = (normalPrice * normalTickets) + (discountPrice * discountTickets);
                return total;
            }

            function updateTotal() {
                const total = calculateTotal();
                document.getElementById('suma').textContent = total.toFixed(2);
            }
            document.getElementById('Normalne').addEventListener('input', updateTotal);
            document.getElementById('Ulgowe').addEventListener('input', updateTotal);
            updateTotal();
        </script>
    </body>
</html>
<?php
if(isset($_SESSION['error'])){
    $errorMessage = json_encode($_SESSION['error']);
    echo "<script>
            showPopup();
            document.getElementById('pop_up_message').textContent = $errorMessage;
        </script>";
    unset($_SESSION['error']);
}
?>
