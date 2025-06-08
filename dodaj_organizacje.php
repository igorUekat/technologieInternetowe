<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
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
function checkErrors($name, $address, $regon, $nip, $haslo):bool{
    if(empty($name) || empty($address) || empty($regon) || empty($nip) || empty($haslo)){
        $_SESSION['org_error'] = "Wszystkie pola muszą być uzupełnione";
        return true;
    }
    else if(strlen($regon) != 9){
        $_SESSION['org_error'] = "Regon składa się z dziewięciu cyfr";
        return true;
    }
    else if(!ctype_digit((string)$regon)){
        $_SESSION['org_error'] = "Regon składa się wyłącznie z cyfr";
        return true;
    }
    else if(strlen($nip) != 10){
        $_SESSION['org_error'] = "NIP składa się z dziesięciu cyfr";
        return true;
    }
    else if(!ctype_digit((string)$nip)){
        $_SESSION['org_error'] = "NIP składa się wyłącznie cyfr";
        return true;
    }
    else if(strlen($haslo) < 8){
        $_SESSION['org_error'] = "hasło musi mieć co najmniej 8 znaków";
        return true;
    }
    else{
        return false;
    }
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nazwa = $_POST['nazwa'];
    $adres = $_POST['adres'];
    $regon = $_POST['regon'];
    $nip = $_POST['nip'];
    $haslo = $_POST['haslo'];
    $host = 'localhost';
    $db = 'kulturon_db';
    $pass = '';
    $user = 'root';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if(!checkErrors($nazwa, $adres, $regon, $nip, $haslo)){
        try{
            $pdo = new PDO($dsn, $user, $pass, $options);
            $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
            INSERT INTO organizacje(nazwa, adres, regon, nip, haslo)
            VALUES(?,?,?,?,?)");
            $stmt->execute([$nazwa, $adres, $regon, $nip, $hasloHash]);
            $stmtId = $pdo ->lastInsertId();
            $stmtDodaj = $pdo ->prepare("
            UPDATE uzytkownicy
            SET id_organizacji = ?,
                rola = 'administrator'
            WHERE id = ?");
            $stmtDodaj->execute([$stmtId, $uzytkownik['id']]);
            header('Location: panel_uzytkownika.php');
            exit;
        }catch(PDOException $e){
            die("Connection failed: ".$e->getMessage());
        }
    }
    else{
        header('Location: dodaj_organizacje.php');
        exit;
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
        <title>Rejestracja organizacji</title>
        <link rel="stylesheet" href="style.css">       
    </head>
    <body>
        <div class="login_main">
            <div class="login_logo_container">
                <a href="./index.php"><img src="./pictures/Logo.svg" alt="Fioletowy napis KulturOn" class="login_logo_img"></a>
            </div>
            <form action="dodaj_organizacje.php" method="POST" class="login_container">
                <h1 class="login_title">Zarejestruj firmę</h1>
                <div class="login_input_container">
                    <input type="text" id="nazwa" name="nazwa"  placeholder = "nazwa" required>
                </div>
                <div class="login_input_container">
                    <input type="text" id="adres" name="adres" placeholder = "adres" required>
                </div>
                <div class="login_input_container">
                    <input type="text" id="regon" name="regon" placeholder = "regon" required>
                </div>
                <div class="login_input_container">
                    <input type="text" id="nip" name="nip" placeholder = "nip" required>
                </div>
                <div class="login_input_container">
                    <input type="password" id="haslo" name="haslo" placeholder = "haslo" required>
                </div>
                <button type="submit" class="login_button">Zarejestruj firmę</button>
            </form>
        </div>
    </body>
</html>
<?php
if(isset($_SESSION['org_error'])){
    echo "<p class=\"error\">{$_SESSION['org_error']}</p>";
    unset($_SESSION['org_error']);
}
?>
