
<?php
session_start();
$host = 'localhost';
$database = 'kulturon_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT * FROM wydarzenia where data > NOW() and czyAktywny = 1");
    $pelnaLista = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
$obecnaLista = [];
$nowaLista = [];

function makeDropdown(string $typ, array $pelnaLista){
    $lista = [];
    foreach($pelnaLista as $wydarzenie){
        $lista[] = $wydarzenie[$typ];
    }
    $lista = array_unique($lista);
    foreach ($lista as $element) {
        echo "<option value=\"$element\">$element</option>";
    }
}
?>
<?php
$isLoggedIn = isset($_SESSION['user_id']);
$uzytkownik = [];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $szukaj = $_POST['szukaj'] ?? '';
    $popularnoscSortowanie = $_POST['popularnoscSortowanie'] ?? '';
    $cenaSortowanie = $_POST['cenaSortowanie'] ?? '';
    $cenaOd = $_POST['cenaOd'] ?? 0;
    $cenaDo = $_POST['cenaDo'] ?? 2000;
    $miastoSortowanie = $_POST['miastoSortowanie'] ?? '';
    $miasta = $_POST['miasta'] ?? '';
    $dataSortowanie = $_POST['dataSortowanie'] ?? '';
    $dataOd = $_POST['dataOd'] ?? '';
    $dataDo = $_POST['dataDo'] ?? '';
    $kategoriaSortowanie = $_POST['kategoriaSortowanie'] ?? '';
    $kategorie = $_POST['kategorie'] ?? '';
    $nowaLista = [];
    if($popularnoscSortowanie){
        usort($pelnaLista, function($a, $b) {
            return $b['liczba_obserwujacych'] <=> $a['liczba_obserwujacych'];
        });
    }
    if($cenaSortowanie){
        usort($pelnaLista, function($a, $b) {
            return $a['cenaNormalna'] <=> $b['cenaNormalna'];
        });
    }
    if($dataSortowanie){
        usort($pelnaLista, function($a, $b) {
            return strtotime($a['data']) <=> strtotime($b['data']);
        });
    }
    if($miastoSortowanie && !empty($miasta)){
        foreach($pelnaLista as $wydarzenie){
            if(strtolower($wydarzenie['miasto']) === strtolower($miasta)){
                $nowaLista[] = $wydarzenie;
            }
        }
    }
    if($dataSortowanie && !empty($dataOd) && !empty($dataDo)){
        foreach($pelnaLista as $wydarzenie){
            if(strtotime($wydarzenie['data']) >= strtotime($dataOd) && strtotime($wydarzenie['data']) <= strtotime($dataDo)){
                $nowaLista[] = $wydarzenie;
            }
        }
    }
    if($cenaSortowanie  && !empty($cenaOd) && !empty($cenaDo)){
        foreach($pelnaLista as $wydarzenie){
            if($wydarzenie['cenaNormalna'] >= $cenaOd && $wydarzenie['cenaNormalna'] <= $cenaDo){
                $nowaLista[] = $wydarzenie;
            }
        }
    }
    if($kategoriaSortowanie && !empty($kategorie)){
        foreach($pelnaLista as $wydarzenie){
            if(strtolower($wydarzenie['kategoria']) === strtolower($kategorie)){
                $nowaLista[] = $wydarzenie;
            }
        }
    } 
    if(!empty($szukaj)){
        foreach($pelnaLista as $wydarzenie){
            if(strpos(strtolower($wydarzenie['nazwa']), strtolower($szukaj)) !== false || strpos(strtolower($wydarzenie['opis']), strtolower($szukaj)) !== false){
                $nowaLista[] = $wydarzenie;
            }
        }
    }
    if(empty($nowaLista)){
        $nowaLista = $pelnaLista;
    }
    $_SESSION['lista'] = $nowaLista;
    header('Location: lista_wydarzen.php');
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
        <title>Lista wydarzeń - KulturOn</title>
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
            <h1>Lista wydarzeń</h1>
        </div>
        <div class="list_container">
            <form action="lista_wydarzen.php" method="POST" class="sort_container">
                <input type="text" id="szukaj" name="szukaj" placeholder="Szukaj...">
                <h2> Sortuj według:</h2>
                <label for="popularnosc">Popularności</label>
                <input type="checkbox" id="popularnosc" name="popularnoscSortowanie" value="popularnosc">
                <br>
                <label for="cena">Ceny</label>
                <input type="checkbox" id="cena" name="cenaSortowanie" value="cena">
                <br>
                <div class="pomniejszy">
                    <label for="cena">od</label>
                    <input type="text" id="od_cena" name="cenaOd" min="0" max="2000">
                    <br>
                    <label for="cena">do</label>
                    <input type="text" id="do_cena" name="cenaDo" min="0" max="2000">
                </div>
                <label for="miasto">Miasta</label>
                <input type="checkbox" id="miasto" name="miastoSortowanie" value="miasto">
                <br>
                <div class="pomniejszy">
                    <select id="miastoDropdown" name="miasta">
                        <?php
                        makeDropdown('miasto', $pelnaLista);
                        ?>
                    </select>
                </div>
                <label for="data">Daty</label>
                <input type="checkbox" id="data" name="dataSortowanie" value="data">
                <div class="pomniejszy">
                    <label for="od_data">od</label>
                    <input type="date" id="od_data" name="dataOd">
                    <br>
                    <label for="do_odleglosc">do</label>
                    <input type="date" id="do_data" name="dataDo" min="0" max="2000">
                </div>           
                <label for="kategoria">Kategorii</label>
                <input type="checkbox" id="kategoria" name="kategoriaSortowanie" value="Kategoria">
                <br>
                <div class="pomniejszy">
                    <select id="kategoriaDropdown" name="kategorie">
                        <?php
                        makeDropdown('kategoria', $pelnaLista);
                        ?>
                    </select>                
                </div>
                <button type="submit">Szukaj</button>            
            </form>
            <div class="lista_wydarzen">
                <?php
                if(isset($_SESSION['lista'])){
                    $nowaLista = $_SESSION['lista'];
                    unset($_SESSION['lista']);
                }
                else{
                    $nowaLista = $pelnaLista;
                }             
                foreach($nowaLista as $wydarzenie){
                    $zdjecia = explode(',', $wydarzenie['zdjecia']);
                    echo "<div class=\"list_event_container\">
                            <a href=\"./wydarzenie.php?id={$wydarzenie['id']}\">
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
                            </a>
                        </div>";
                }
                ?>
            </div>                         
        </div>
    </body>
</html>