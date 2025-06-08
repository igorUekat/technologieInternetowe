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
    $stmt = $pdo->prepare("SELECT id, id_organizacji, rola FROM uzytkownicy WHERE id = ?");
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
function checkErrors($a, $b=null, $c=null): bool{
    if($c != null){
        if (empty($a) || empty($b || empty($c))){
            $_SESSION['edit_error'] = "Obydwa pola muszą być wypełnione";
            return true;
        }
    }
    else if ($b != null){
        if (empty($a) || empty($b)){
            $_SESSION['edit_error'] = "Obydwa pola muszą być wypełnione";
            return true;
        }
        else if($a != $b){
            $_SESSION['edit_error'] = "Obydwa pola muszą być tej samej wartości";
            return true;
        }
    }
    else{
        if (empty($a)){
            $_SESSION['edit_error'] = "Pole musi być uzupełnione";
            return true;
        }
    }
    return false;
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['action'])){
        switch($_POST['action']){
            case 'nameButton':
                $nazwa = $_POST['nazwa'];
                if(!checkErrors($nazwa)){
                    $stmtNazwa = $pdo->prepare("UPDATE organizacje
                    SET nazwa = ?
                    WHERE id = ?");
                    $stmtNazwa->execute([$nazwa, $uzytkownik['id_organizacji']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono nazwę";
                }
                header('Location: edytuj_org.php');
                exit;
                break;
            case 'addressButton':
                $ulica = $_POST['ulica'];
                $miejscowosc = $_POST['miejscowosc'];
                $wojewodztwo = $_POST['wojewodztwo'];
                if(!checkErrors($ulica, $miejscowosc, $wojewodztwo)){
                    $stmtAdres = $pdo->prepare("UPDATE organizacje
                    SET adres = ?
                    WHERE id = ?");
                    $pelenAdres = $ulica . ", " . $miejscowosc . ", woj. " . $wojewodztwo;
                    $stmtAdres->execute([$pelenAdres, $uzytkownik['id_organizacji']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono adres";
                }
                header('Location: edytuj_org.php');
                exit;
                break;
            case 'passwordButton':
                $haslo = $_POST['password'];
                $hasloPowtorz = $_POST['password_repeat'];
                if(!checkErrors($haslo, $hasloPowtorz) && strlen($haslo) >= 8){
                    $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
                    $stmtHaslo = $pdo->prepare("UPDATE organizacje
                    SET haslo = ?
                    WHERE id = ?");
                    $stmtHaslo->execute([$hasloHash, $uzytkownik['id_organizacji']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono hasło";
                }
                header('Location: edytuj_org.php');
                exit;
                break;
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
        <title>Edytuj organizację - Kulturon</title>
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
            <h1>Edytuj dane organizacji</h1>
        </div>
        <div class="buttons_container">
            <button onclick="showPopupNazwa()">Zmień nazwę</button>
            <button onclick="showPopupAdres()">Zmień adres</button>
            <button onclick="showPopupHaslo()">Zmień hasło</button>
        </div>
        <div class="pop_up_container" id="pop_up_container_haslo">
            <form action="edytuj_org.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień hasło</h1>
                <div class="input_container">
                    <label for="haslo" class="input_label">Podaj hasło:</label>
                    <input type="password" name="password" id="haslo" class="input_field">
                    <label for="powtorz_haslo" class="input_label">Powtórz hasło:</label>
                    <input type="password" name="password_repeat" id="powtorz_haslo" class="input_field" required>
                </div>
                <p class="pup_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="passwordButton" class="execute_button">Zatwierdź</button>
                    <button  type="button" onclick="hidePopupHaslo()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupHaslo() {
            document.getElementById('pop_up_container_haslo').style.display = 'flex';
            }

            function hidePopupHaslo() {
            document.getElementById('pop_up_container_haslo').style.display = 'none';
            }
        </script>
        <div class="pop_up_container" id="pop_up_container_nazwa">
            <form action="edytuj_org.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień nazwę</h1>
                <div class="input_container">
                    <label for="nazwa" class="input_label">Podaj nową nazwę</label>
                    <input type="text" name="nazwa" id="nazwa" class="input_field" required>
                </div>
                <p class="pup_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="nameButton"class="execute_button">Zatwierdź</button>
                    <button  type="button" onclick="hidePopupNazwa()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupNazwa() {
            document.getElementById('pop_up_container_nazwa').style.display = 'flex';
            }

            function hidePopupNazwa() {
            document.getElementById('pop_up_container_nazwa').style.display = 'none';
            }
        </script>
        <div class="pop_up_container" id="pop_up_container_adres">
            <form action="edytuj_org.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień adres</h1>
                <div class="input_container">
                    <label for="ulica" class="input_label">Ulica: </label>
                    <input type="text" name="ulica" id="ulica" class="input_field" required>
                    <label for="miejscowosc" class="input_label">Miejscowość: </label>
                    <input type="text" name="miejscowosc" id="miejscowosc" class="input_field" required>
                    <label for="wojewodztwo" class="input_label">Województwo: </label>
                    <input type="text" name="wojewodztwo" id="wojewodztwo" class="input_field" required>
                </div>
                <p class="pup_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="addressButton" class="execute_button">Zatwierdź</button>
                    <button  type="button" onclick="hidePopupAdres()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupAdres() {
            document.getElementById('pop_up_container_adres').style.display = 'flex';
            }

            function hidePopupAdres() {
            document.getElementById('pop_up_container_adres').style.display = 'none';
            }
        </script>         
    </body>
</html>
<?php
if(isset($_SESSION['edit_error'])){
    echo "<p class=\"error\">{$_SESSION['edit_error']}</p>";
    unset($_SESSION['edit_error']);
}
?>
<?php
if(isset($_SESSION['edit_success'])){
    echo "<p class=\"success\">{$_SESSION['edit_success']}</p>";
    unset($_SESSION['edit_success']);
}
?>