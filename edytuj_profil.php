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
function checkErrors($a, $b=null): bool{
    if ($b != null){
        if (empty($a) || empty($b)){
            $_SESSION['edit_error'] = "Obydwa pola muszą być wypełnione";
            return true;
        }
        else if($a != $b){
            $_SESSION['edit_error'] = "Obydwa pola musza być tej samej wartości";
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
            case 'emailButton':
                $email = $_POST['email'];
                if(!checkErrors($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $stmtEmail = $pdo->prepare("UPDATE uzytkownicy
                    SET email = ?
                    WHERE id = ?");
                    $stmtEmail->execute([$email, $uzytkownik['id']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono email";
                }
                header('Location: edytuj_profil.php');
                exit;
                break;
            case 'passwordButton':
                $haslo = $_POST['haslo'];
                $hasloPowtorz = $_POST['hasloPowtorz'];
                if(!checkErrors($haslo, $hasloPowtorz) && strlen($haslo) >= 8){
                    $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
                    $stmtHaslo = $pdo->prepare("UPDATE uzytkownicy
                    SET haslo = ?
                    WHERE id = ?");
                    $stmtHaslo->execute([$hasloHash, $uzytkownik['id']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono hasło";
                }
                header('Location: edytuj_profil.php');
                exit;
                break;
            case 'nameButton':
                $nazwa = $_POST['nazwa'];
                if(!checkErrors($nazwa)){
                    $stmtNazwa = $pdo->prepare("UPDATE uzytkownicy
                    SET nazwa = ?
                    WHERE id = ?");
                    $stmtNazwa->execute([$nazwa, $uzytkownik['id']]);
                    $_SESSION['edit_success'] = "Pomyślnie zmieniono nazwę";
                }
                header('Location: edytuj_profil.php');
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
        <title>Edytuj profil - KulturOn</title>
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
            <h1>Edytuj profil</h1>
        </div>
        <div class="buttons_container">
            <button onclick="showPopupEmail()">Zmień E-mail</button>
            <button onclick="showPopupHaslo()">Zmień hasło</button>
            <button onclick="showPopupNazwa()">Zmień nazwę</button>
            <div class="checkbox_container">
                <label for="czy_na_email">Czy wysyłać informacje na e-mail?</label>
                <input type="checkbox" id="czy_na_email" name="czy_na_email">
            </div>
        </div>
        <div class="pop_up_container" id="pop_up_container_email">
            <form action="edytuj_profil.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień Email</h1>
                <div class="input_container">
                    <label for="Email" class="input_label">Podaj nowy e-mail</label>
                    <input type="email"  name="email" id="Email" class="input_field">
                </div>
                <p class="pup_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type ="submit" name="action" value="emailButton" class="execute_button">Zatwierdź</button>
                    <button  type="button" onclick="hidePopupEmail()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupEmail() {
            document.getElementById('pop_up_container_email').style.display = 'flex';
            }

            function hidePopupEmail() {
            document.getElementById('pop_up_container_email').style.display = 'none';
            }
        </script>
        <div class="pop_up_container" id="pop_up_container_haslo">
            <form action="edytuj_profil.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień hasło</h1>
                <div class="input_container">
                    <label for="haslo" class="input_label">Podaj hasło:</label>
                    <input type="password" name="haslo" id="haslo" class="input_field" required>
                    <label for="powtorz_haslo" class="input_label">Powtórz hasło:</label>
                    <input type="password" name="hasloPowtorz"id="powtorz_haslo" class="input_field" required>
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
            <form action="edytuj_profil.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień nazwę</h1>
                <div class="input_container">
                    <label for="nazwa" class="input_label">Podaj nową nazwę</label>
                    <input type="text" name="nazwa" id="nazwa" class="input_field" required>
                </div>
                <p class="pup_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="nameButton" class="execute_button">Zatwierdź</button>
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