<?php
if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $eventId = $_GET['id'];
}
else{
    echo "<p class=\"error\"> Nieoczekiwane ID wydarzenia</p>";
    exit;
}
?>
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
        header('Location: index.php');
        exit;
    }
}
?>
<?php
$stmtWydarzenie = $pdo -> prepare("SELECT * from wydarzenia where id = ?");
$stmtWydarzenie->execute([$eventId]);
$wydarzenie = $stmtWydarzenie->fetch();
$sciezkaZdjec = $wydarzenie["zdjecia"];
?>
<?php
function checkForErrors($name, $date, $category, $address, $city, $normalPrice, $discountedPrice, $description, $paths) : bool {
        if (empty($name) || empty($date) || empty($category) || empty($address) || empty($city) || empty($description) || empty($paths)) {
            $_SESSION['create_error'] = "Wszystkie pola muszą być uzupełnione";
            return true;
        }
        else if($date < date('Y-m-d')) {
            $_SESSION['create_error'] = "Nie można dodać wydarzenia, które ma się odbywać wcześniej niż dzisiaj";
            return true;
        }
        else if(!is_numeric($normalPrice) || !is_numeric($discountedPrice)){
            $_SESSION['create_error'] = "Ceny muszą być liczbami";
            return true;
        }
        else if($normalPrice < $discountedPrice){
            $_SESSION['create_error'] = "Cena ulgowa nie może być droższa niż normalna";
            return true;
        }
        else if (strlen($description) > 255) {
            $_SESSION['create_error'] = "opis nie może być dłuższy od 255 znaków";
            return true;
        }
        else{
            return false;
        }
    }
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = 'localhost';
    $database = 'kulturon_db';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    try{
        $pdo = new PDO($dsn, $user, $pass, $options);
        $nazwa = $_POST['nazwa_wydarzenia'];
        $data = $_POST['data_wydarzenia'] . ' 00:00:00';
        $kategoria = $_POST['kategoria_wydarzenia'];
        $adres = $_POST['adres_wydarzenia'];
        $miasto = $_POST['miasto_wydarzenia'];
        $czyPlatne = isset($_POST['czy_platne'])? 1 : 0;
        $czyUlgowe = isset($_POST['czy_ulgowe'])? 1 : 0;
        $cenaNormalna = isset($_POST['cena_wydarzenia_normalna']) ? $_POST['cena_wydarzenia_normalna'] : '0';
        $cenaNormalna = str_replace(',','.',$cenaNormalna);
        $cenaUlgowa = isset($_POST['cena_wydarzenia_ulgowa']) ? $_POST['cena_wydarzenia_ulgowa'] : $cenaNormalna;
        $cenaUlgowa = str_replace(',','.',$cenaUlgowa);
        $opis = $_POST['opis_wydarzenia'];
        $zdjecia = $_POST['zdjecia'];
        $uploadDir = './uploads/';
        if (!is_dir($uploadDir)){
            mkdir($uploadDir, 0755, true);
        }
        $uploadedPaths=[];
        if (!empty($_FILES['zdjecia_wydarzenia']['name'][0])) {
            foreach ($_FILES['zdjecia_wydarzenia']['name'] as $index => $name) {
                $fileTmp = $_FILES['zdjecia_wydarzenia']['tmp_name'][$index];
                $fileName = basename($name);
                $uniqueName = uniqid() . '_' . $fileName;
                $targetPath = $uploadDir . $uniqueName;

                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $uploadedPaths[] = $targetPath;
                }
            }
        }
        $allPaths = implode(',', $uploadedPaths);
        if(!empty($zdjecia) && !empty($allPaths)){
            $zdjecia = $zdjecia . "," . $allPaths;
        }
        else if(empty($zdjecia) && !empty($allPaths)){
            $zdjecia = $allPaths;
        }
        if(checkForErrors($nazwa, $data, $kategoria, $adres, $miasto, $cenaNormalna, $cenaUlgowa, $opis, $zdjecia)) {
            header("Location:edytuj_wydarzenie.php?id={$eventId}");
            exit;
        }
        else{
            $stmt = $pdo->prepare("
            UPDATE wydarzenia
            SET nazwa = ?,
            data = ?,
            kategoria = ?,
            adres = ?,
            miasto = ?,
            id_organizacji = ?,
            czyPlatne = ?,
            czyUlgowe = ?,
            cenaNormalna = ?,
            cenaUlgowa = ?,
            opis = ?,
            zdjecia = ?
            where id = ?
            ");
            $stmt->execute([$nazwa, $data, $kategoria, $adres, $miasto, $uzytkownik['id_organizacji'], $czyPlatne, $czyUlgowe, $cenaNormalna, $cenaUlgowa, $opis, $zdjecia, $eventId]);
            $dataPow = date("Y-m-d H:i:s");
            $tresc = "Wydarzenie " . $wydarzenie['nazwa'] . " zostało edytowane. Sprawdź zmiany!";
            $stmtPowiadom = $pdo -> prepare("INSERT INTO powiadomienia (data, tresc, id_wydarzenia) VALUES(?,?,?)");
            $stmtPowiadom -> execute([$dataPow, $tresc, $eventId]);
            header('Location: zarzadzaj_wydarzeniami.php');
            exit;
                }
            } catch (PDOException $e) {
                echo "Błąd: " . $e->getMessage();
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
        <title>Edycja wydarzenia - KulturOn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php
        makeHeader($isLoggedIn, $uzytkownik)
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
        <h1>Dodaj wydarzenie</h1>
    </div>
    <?php
    echo"<form action=\"edytuj_wydarzenie.php?id={$wydarzenie['id']}\" method=\"POST\" enctype=\"multipart/form-data\" class=\"input_container\">
            <input type=\"text\" id=\"nazwa_wydarzenia\" name=\"nazwa_wydarzenia\" placeholder=\"Nazwa wydarzenia\" value=\"{$wydarzenie['nazwa']}\" required>
            <input type=\"date\" id=\"data_wydarzenia\" name=\"data_wydarzenia\" placeholder=\"Data wydarzenia\" value=\"".date('Y-m-d', strtotime($wydarzenie['data']))."\" required>
            <input type=\"text\" id=\"kategoria_wydarzenia\" name=\"kategoria_wydarzenia\" placeholder=\"Kategoria wydarzenia\" value=\"{$wydarzenie['kategoria']}\"required>
            <input type=\"text\" id=\"adres_wydarzenia\" name=\"adres_wydarzenia\" placeholder=\"Adres wydarzenia\" value=\"{$wydarzenie['adres']}\" required>
            <input type=\"text\" id=\"miasto_wydarzenia\" name=\"miasto_wydarzenia\" placeholder=\"Miasto wydarzenia\" value=\"{$wydarzenie['miasto']}\" required>
            <div class=\"checkbox_container\">
                <label for=\"czy_platne\">Czy płatne?</label>";
    if($wydarzenie['czyPlatne'] == 1){
        echo "<input type=\"checkbox\" id=\"czy_platne\" name=\"czy_platne\" checked>";
    }
    else{
        echo "<input type=\"checkbox\" id=\"czy_platne\" name=\"czy_platne\">";
    }
    echo"</div>
            <div class=\"checkbox_container\">
                <label for=\"czy_ulgowe\">Czy ulgowe?</label>";
    if($wydarzenie['czyUlgowe'] == 1){
        echo "<input type=\"checkbox\" id=\"czy_platne\" name=\"czy_ulgowe\" checked>";
    }
    else{
        echo "<input type=\"checkbox\" id=\"czy_platne\" name=\"czy_ulgowe\">";
    }
    echo    "</div>
            <input type=\"text\" id=\"cena_wydarzenia_normalna\" name=\"cena_wydarzenia_normalna\" placeholder=\"Cena wydarzenia (normalna)\" value=\"{$wydarzenie['cenaNormalna']}\" required>
            <input type=\"text\" id=\"cena_wydarzenia_ulgowa\" name=\"cena_wydarzenia_ulgowa\" placeholder=\"Cena wydarzenia (ulgowa)\" value=\"{$wydarzenie['cenaUlgowa']}\" required>
            <textarea id=\"opis_wydarzenia\" name=\"opis_wydarzenia\" placeholder=\"Opis wydarzenia (do 255 znaków)\" value=\"{$wydarzenie['opis']}\" required>{$wydarzenie['opis']}</textarea>
            <button type=\"button\" onclick=\"wyczyscZdjecia()\"id=\"wyczysc\">Wyczyść zdjęcia</button>
            <input type=\"hidden\" id=\"zdjecia\" name=\"zdjecia\" value=\"{$sciezkaZdjec}\">
            <input type=\"file\" id=\"zdjecia_wydarzenia\" name=\"zdjecia_wydarzenia[]\" accept=\"image/*\" multiple>
            <button type=\"submit\">Edytuj wydarzenie</button>
        </form>";
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const czyPlatne = document.getElementById('czy_platne');
            const czyUlgowe = document.getElementById('czy_ulgowe');
            const cenaNormalna = document.getElementById('cena_wydarzenia_normalna');
            const cenaUlgowa = document.getElementById('cena_wydarzenia_ulgowa');

            function updateCenaFields() {
                if (czyPlatne.checked) {
                    cenaNormalna.disabled = false;
                    if (czyUlgowe.checked) {
                        cenaUlgowa.disabled = false;
                    } else {
                        cenaUlgowa.disabled = true;
                        cenaUlgowa.value = cenaNormalna.value;
                    }
                } else {
                    cenaNormalna.disabled = true;
                    cenaUlgowa.disabled = true;
                    cenaNormalna.value = '0';
                    cenaUlgowa.value = '0';
                }
            }

            czyPlatne.addEventListener('change', updateCenaFields);
            czyUlgowe.addEventListener('change', updateCenaFields);

            updateCenaFields(); 
        });
        function wyczyscZdjecia(){
            console.log("wyczysc called");
            document.getElementById('zdjecia').value='';
        }
    </script>
    </body>
</html>
<?php
if(isset($_SESSION['create_error'])){
    echo "<p class=\"error\">{$_SESSION['create_error']}</p>";
    unset($_SESSION['create_error']);
}
?>