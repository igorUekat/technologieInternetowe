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
$stmtOrg = $pdo->query("SELECT id,nazwa from organizacje");
$stmtOrg->execute();
$orgs = $stmtOrg->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
if($_SERVER['REQUEST_METHOD'] ==='POST'){
    if(isset($_POST['action']) && $_POST['action'] == 'szukaj'){
        $szukaneOrg = [];
        $szukaj = $_POST['szukajSearch'];
        foreach($orgs as $org){
            if(strpos(strtolower($org['nazwa']), strtolower($szukaj)) !== false && !empty($szukaj)){
                $szukaneOrg[] = $org;
            }
        }
        $_SESSION['org_search'] = $szukaneOrg;
        header('Location: znajdz_stworz_org.php');
        exit;
    }
    if(isset($_POST['action']) && $_POST['action'] == "password"){
        $orgId = $_POST['company_id'];
        $haslo = $_POST['company_password'];
        $stmtJoinOrg = $pdo->prepare("SELECT haslo From organizacje WHERE id = ?");
        $stmtJoinOrg->execute([$orgId]);
        $hasloOrg = $stmtJoinOrg->fetch();
        if(password_verify($haslo, $hasloOrg['haslo'])){
            $stmtJoining = $pdo->prepare("UPDATE uzytkownicy
                                        SET id_organizacji = ?,
                                        rola = 'czlonek'
                                        where id = ?");
            $stmtJoining->execute([$orgId, $uzytkownik['id']]);
            header('Location: panel_uzytkownika.php');
            exit;
        }
        else{
            $_SESSION['find_error'] = "Błędne hasło";
            header('Location: znajdz_stworz_org.php');
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
        <title>Znajdź lub dodaj organizację - KulturOn</title>
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
        <h1>Znajdź lub stwórz organizację</h1>
    </div>
    <div class="buttons_container">
        <button onclick="showPopupOrgs()">Znajdź organizację</button>
        <a href="./dodaj_organizacje.php"><button>Stwórz organizację</button></a>
    </div>
    <div class="pop_up_container" id="pop_up_container_orgs">
            <div class="pop_up_window">
                <h1 class="pop_up_title">Znajdź organizację</h1>
                <form action="znajdz_stworz_org.php" method="POST" id="searchForm" class="input_container">
                    <label for="szukaj" class="input_label">Szukaj:</label>
                    <input type="text" id="szukaj" name="szukajSearch" class="input_field">
                    <button type="submit" onclick="showList()" name="action" value="szukaj">Szukaj</button>
                </form>
                <div class="orgs_list" id="orgs_list">
                    <?php
                    if(isset($_SESSION['org_search'])){
                        $szukaneOrg = $_SESSION['org_search'];
                        if(empty($szukaneOrg)){
                        echo"<p class=\"error\">Nie znaleziono organizacji</p>";
                        }
                        else{
                            foreach($szukaneOrg as $org){
                                echo "<div class=\"org_container\">
                                        <img src=\"./pictures/company.svg\" alt=\"Ikona organizacji\" class=\"org_icon\">
                                        <h1>{$org['nazwa']}</h1>
                                        <button class=\"select_org_button\" onclick=\"showPopupPassword({$org['id']})\">Wybierz</button>
                                    </div>";
                            }
                        }
                    }
                    ?>
                </div>
                <p class="pop_up_message" name = "pop_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="button" onclick="hidePopupOrgs()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </div>
        </div>
        <script>
            function showList() {
                document.getElementById('orgs_list').style.display = 'flex';
            }
            function showPopupOrgs() {
            document.getElementById('pop_up_container_orgs').style.display = 'flex';
            }

            function hidePopupOrgs() {
            document.getElementById('pop_up_container_orgs').style.display = 'none';
            }
        </script>
        <div class="pop_up_container" id="pop_up_container_password">
            <form action="znajdz_stworz_org.php" method="POST" class="pop_up_window">
                <h1 class="pop_up_title">Wprowadź hasło</h1>
                <div class="input_container">
                    <label for="password" class="input_label">Hasło:</label>
                    <input type="hidden" name="company_id" id="company_id" value="">
                    <input type="password" id="password" class="input_field" name="company_password">
                </div>
                <p class="pop_up_message"></p>
                <div class="pop_up_buttons">
                    <button  type="submit" name="action" value="password" class="execute_button">Zatwierdź</button>
                    <button type="button" onclick="hidePopupPassword()" class="cancel_button" id="cancelButton">Anuluj</button>
                </div>
            </form>
        </div>
        <script>
            function showPopupPassword(companyId) {
                document.getElementById('pop_up_container_password').style.display = 'flex';
                document.getElementById('company_id').value = companyId;
            }

            function hidePopupPassword() {
                document.getElementById('pop_up_container_password').style.display = 'none';
            }
        </script>
    </body>
</html>
<?php
if(isset($_SESSION['org_search'])){
    echo "<script>
            showPopupOrgs();
            showList();
        </script>";
    unset($_SESSION['org_search']);
}
if(isset($_SESSION['find_error'])){
    echo "<p class=\"error\">{$_SESSION['find_error']}</p>";
    unset($_SESSION['find_error']);
}
?>