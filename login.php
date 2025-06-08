
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="Igor Skrzyński">
        <meta name="description" content="Aplikacja do przeglądania i zarządzania wydarzeniami kulturalnymi w Polsce.">
        <meta name="keywords" content="kultura, wydarzenia, koncerty, festiwale, sztuka, wydarzenia sportowe">
        <title>Logowanie się - KulturOn</title>
        <link rel="stylesheet" href="style.css">       
    </head>
    <body>
        <div class="login_main">
            <div class="login_logo_container">
                <a href="./index.php"><img src="./pictures/Logo.svg" alt="Fioletowy napis KulturOn" class="login_logo_img"></a>
            </div>
            <form action="login.php" method="POST" class="login_container">
                <h1 class="login_title">Zaloguj się</h1>
                <div class="login_input_container">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="login_input_container">
                    <label for="password">Hasło:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="login_input_container">
                    <label for="show_password">Pokaż hasło</label>
                    <input type="checkbox" id="show_password" name="show_password">
                </div>
                <button type="submit" class="login_button">Zaloguj się</button>
                <p class="message"></p>
            </form>
            <a href="./register_choice.html">Nie masz konta? Zarejestruj się!</a>
        </div>
        <script>
            document.getElementById('show_password').addEventListener('change', function(e) {
                const passwordField = document.getElementById('password');           
                passwordField.type = e.target.checked ? 'text' : 'password';
            });
        </script>
    </body>
</html>
<?php
session_start();
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
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    try{
        $pdo = new PDO($dsn, $user, $pass, $options);
        $email = $_POST['email'];
        $haslo = $_POST['password'];
        $stmt = $pdo->prepare('SELECT * FROM uzytkownicy WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt -> fetch();
        if ($user && password_verify($haslo, $user['haslo'])){
            $_SESSION['user_id'] = $user['id'];
            $token = bin2hex(random_bytes(32));
            $expiry = time() + 60 * 60 * 24 * 30;       
            setcookie('remember_token', $token, $expiry, '/');       
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET remember_token = ?, token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);
            header("Location: index.php");
            exit;
        }
        else{
            $_SESSION['login_error'] = "Nieprawidłowy login lub hasło.";
            header("Location: login.php");
            exit;
        }
    }catch(PDOExcpetion $e){
        die("Connection failed: ".$e->getMessage());
    }
}
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);       
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE remember_token = ? AND token_expiry > NOW()");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();       
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Connection failed: ".$e->getMessage());
    }
}      
?>
<?php
if (isset($_SESSION['login_error'])) {
    echo '<p class="error">'.htmlspecialchars($_SESSION['login_error']).'</p>';
    unset($_SESSION['login_error']);
}
?>