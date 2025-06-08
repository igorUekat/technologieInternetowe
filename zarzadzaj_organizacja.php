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
?>
<?php
if($uzytkownik['rola'] == 'administrator'){
    $stmtOrg = $pdo->prepare("SELECT id, email, nazwa, rola from uzytkownicy where id_organizacji = ?");
    $stmtOrg -> execute([$uzytkownik['id_organizacji']]);
    $czlonkowie = $stmtOrg->fetchAll(PDO::FETCH_ASSOC);
}else{
    echo "Nie masz tutaj wstępu!";
    header('Location: index.php');
    exit;
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['action'])){
        switch($_POST['action']){
            case 'usunCzlonka':
                $idCzlonka = $_POST['idCzlonka'];
                $stmtUsun = $pdo->prepare("UPDATE uzytkownicy
                SET id_organizacji = null,
                rola = null
                where id = ?");
                $stmtUsun->execute([$idCzlonka]);
                header('Location: zarzadzaj_organizacja.php');
                exit;
                break;
            case 'zmienRole':
                $idCzlonka = $_POST['idCzlonka'];
                $rola = $_POST['roleSelect'];
                $stmtZmien = $pdo->prepare("UPDATE uzytkownicy
                SET rola = ?
                where id = ?");
                $stmtZmien->execute([$rola, $idCzlonka]);
                header('Location: zarzadzaj_organizacja.php');
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
        <title>Zarządzanie organizacją - KulturOn</title>
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
            <h1 class="notification_header">Zarządzaj organizacją</h1>
        </div>
        <div class="notification_list">
            <?php
            if(empty($czlonkowie)){
                echo"<p>Brak innych członków</p>";
            }
            else{
                foreach($czlonkowie as $czlonek){
                if ($czlonek['id'] == $uzytkownik['id']){
                    continue;
                }
                echo "            <div class=\"member_container\">
                                    <img class=\"user_icon\" src=\"./pictures/user.svg\" alt=\"Użytkownik\">
                                    <div class=\"member_info_list\">
                                        <h1>{$czlonek['nazwa']}</h1>
                                        <p>{$czlonek['email']}</p>
                                    </div>
                                    <div class=\"rola\">
                                        <p>{$czlonek['rola']}</p>
                                        <a href=\"#\" onclick=\"showPopupRole({$czlonek['id']})\"><p>(Zmień Rolę)</p></a>
                                    </div>
                                    <a href=\"#\" onclick=\"showPopupUsun({$czlonek['id']})\"><img class=\"delete_icon\"src=\"./pictures/smietnik.svg\" alt=\"Usuń użytkownika\" class=\"delete_user_icon\"></a>
                                </div>";
            }
            }
            ?>
        </div>
        <div class="pop_up_container" id="pop_up_container_role">
            <form action="zarzadzaj_organizacja.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Zmień rolę członka</h1>
                <div class="input_container">
                    <label for="role" class="input_label">Wybierz rolę:</label>
                    <select id="roleSelect" name="roleSelect" class="role">
                        <option value="administrator">Administrator</option>
                        <option value="czlonek">Członek</option>
                    </select>
                    <input type="hidden" name="idCzlonka" id="id_czlonka1" value="">
                </div>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="zmienRole" class="execute_button">Zatwierdź</button>
                    <button  type="button" onclick="hidePopupRole()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupRole(idCzlonka) {
            document.getElementById('pop_up_container_role').style.display = 'flex';
            document.getElementById('id_czlonka1').value = idCzlonka;
            }

            function hidePopupRole() {
            document.getElementById('pop_up_container_role').style.display = 'none';
            }
        </script>
        <div class="pop_up_container" id="pop_up_container_usun">
            <form action="zarzadzaj_organizacja.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Czy na pewno chcesz usunąć członka z organizacji?</h1>
                <input type="hidden" name="idCzlonka" id="id_czlonka2" value="">
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="usunCzlonka" class="execute_button">Tak</button>
                    <button  type="button" onclick="hidePopupUsun()" class="cancel_button" id="cancelButton">Nie</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupUsun(idCzlonka) {
            document.getElementById('pop_up_container_usun').style.display = 'flex';
            document.getElementById('id_czlonka2').value = idCzlonka;
            }

            function hidePopupUsun() {
            document.getElementById('pop_up_container_usun').style.display = 'none';
            }
        </script>
    </body>
</html>