<?php
$typ = isset($_GET['type']) ? $_GET['type'] : '';
if($typ != 'user' && $typ != 'org'){
    echo "<p class='error'>Zły typ konta</p>";
    exit;
}
$host = 'localhost';
$db = 'kulturon_db';
$pass = '';
$user = 'root';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try{
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmtEmaile = $pdo->query("SELECT email from uzytkownicy");
    $emaile = array_column($stmtEmaile->fetchAll(PDO::FETCH_ASSOC), 'email');
}catch(PDOException $e){
    die("W tej chwili serwer jest niedostępny".$e->getMessage());
}
function checkErrors($email, $emailRepeat, $password, $passwordRepeat, $emaile): bool{
    if(empty($email) || empty($emailRepeat) || empty($password) || empty($passwordRepeat)){
        $_SESSION['register_error'] = "Wszystkie pola muszą być uzupełnione";
        return true;
    }
    else if($email != $emailRepeat){
        $_SESSION['register_error'] = "Emaile muszą być takie same";
        return true;
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Nieprawidłowy Email";
        return true;
    }
    else if (!empty($emaile) && in_array(strtolower($email), array_map('strtolower', $emaile))) {
        $_SESSION['register_error'] = "Ten adres E-mail został już wykrzoystany";
        return true;
    }
    else if($password != $passwordRepeat){
        $_SESSION['register_error'] = "Hasła muszą być takie same";
        return true;
    }
    else if(strlen($password) < 8){
        $_SESSION['register_error'] = "Hasło musi mieć co najmniej 8 znaków";
        return true;
    }
    else{
        return false;
    }
}
function getEmailUsername($email) {
    return substr($email, 0, strpos($email, "@"));
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>Rejestracja - KulturOn</title>
        <link rel="stylesheet" href="style.css">       
    </head>
    <body>
        <div class="login_main">
            <div class="login_logo_container">
                <a href="./index.php"><img src="./pictures/Logo.svg" alt="Fioletowy napis KulturOn" class="login_logo_img"></a>
            </div>
            <?php echo "<form action=\"register.php?type={$typ}\" method='POST' class=\"login_container\">"; ?>
                <h1 class="login_title">Zarejestruj się</h1>
                <div class="login_input_container">
                    <input type="email" id="email" name="email" placeholder = "Email" required>
                </div>
                <div class="login_input_container">
                    <input type="email" id="emailPowtorz" name="emailPowtorz" placeholder = "Powtórz email" required>
                </div>
                <div class="login_input_container">
                    <input type="password" id="password" name="password" placeholder = "hasło" required>
                </div>
                <div class="login_input_container">
                    <input type="password" id="passwordPowtorz" name="passwordPowtorz" placeholder = "Powtórz hasło" required>
                </div>
                <div class="login_input_container">
                    <label for="show_password">Pokaż hasła</label>
                    <input type="checkbox" id="show_password" name="show_password">
                </div>
                <button type="submit" class="login_button">Załóż konto</button>
            </form>
        </div>
        <script>
            document.getElementById('show_password').addEventListener('change', function(e) {
                const passwordFields = [
                    document.getElementById('password'),
                    document.getElementById('passwordPowtorz')
                ];
                
                passwordFields.forEach(field => {
                    field.type = e.target.checked ? 'text' : 'password';
                });
            });
        </script>
    </body>
</html>
<?php
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'];
    $emailPowtorz = $_POST['emailPowtorz'];
    $haslo = $_POST['password'];
    $hasloPowtorz = $_POST['passwordPowtorz'];
    if(!checkErrors($email, $emailPowtorz, $haslo, $hasloPowtorz, $emaile)){
        $nazwa = getEmailUsername($email);
        try{
            $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
            INSERT INTO uzytkownicy(email, haslo, nazwa, typ)
            VALUES(?,?,?,?)");
            $stmt->execute([$email, $hasloHash, $nazwa, $typ]);
            $stmtId = $pdo ->lastInsertId();
            $stmtTabela = $pdo ->prepare("
            CREATE TABLE {$stmtId}_wydarzenia(
                id_wydarzenia INT not null,
                czyKupiono BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (id_wydarzenia) REFERENCES wydarzenia(id)
            )");
            $stmtTabela->execute();
            header('Location: login.php');
            exit;
        }catch(PDOException $e){
            die("W tej chwili serwer jest niedostępny".$e->getMessage());
        }
    }
    else{
        header("Location: register.php?type={$typ}");
        exit;
    }
}
?>
<?php
if (isset($_SESSION['register_error'])) {
    echo '<p class="error">'.htmlspecialchars($_SESSION['register_error']).'</p>';
    unset($_SESSION['register_error']);
}
?>