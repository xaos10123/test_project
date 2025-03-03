<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
require_once 'config/db.php';
require_once 'includes/func.php';
require_once 'includes/errors.php';


$id = $_SESSION['user_id'];
$check_user = $conn->query("SELECT * FROM users WHERE id = '$id'");
$user = $check_user->fetch_assoc();

$login = $user['login'];
$phone = $user['phone'];
$email = $user['email'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_login = strip_tags($_POST['new_login']);
    $new_phone = strip_tags($_POST['new_phone']);
    $new_email = strip_tags($_POST['new_email']);
    $new_password = $_POST['new_password'] ? password_hash(strip_tags($_POST['new_password']), PASSWORD_DEFAULT) : '';

    $email_error = checkPattern("/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $new_email, "Email не существующий!");
    $phone_error = checkPattern("/^8[0-9]{10}$/", $new_phone, "Номер не существующий!");

    if (isNew($login, $new_login)) {
        echo updateCol($conn, 'login', $login, $new_login, $id) == true ? 'Логин изменен' : 'Логин занят';
        $login = $new_login;
    }
    if (isNew($phone, $new_phone) && !$phone_error) {
        echo updateCol($conn, 'phone', $phone, $new_phone, $id) == true ? 'Номер изменен' : 'Номер занят';
        $phone = $new_phone;
    }
    if (isNew($email, $new_email) && !$email_error) {
        echo updateCol($conn, 'email', $email, $new_email, $id) == true ? 'E-mail изменен' : 'E-mail занят';
        $email = $new_email;
    }
    if ($new_password) {
        updatePass($conn, $new_password, $id);
        echo 'Пароль изменен';
    }

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
</head>
<body>
<h1>Профиль</h1>
<form action="profile.php" method="post">

    <?php if ($login_error) {
        echo "<span style='color: red'>$login_error</span><br><br>";
    } ?>
    <label for="login">Имя пользователя (логин):</label>
    <input type="text" id="login" name="new_login" placeholder="Имя (логин)" value="<?= isset($login) ? $login : '' ?>"><br><br>

    <?php if ($phone_error) {
        echo "<span style='color: red'>$phone_error</span><br><br>";
    } ?>
    <label for="phone">Телефон:</label>
    <input type="text" id="phone" name="new_phone" placeholder="87776665544" value="<?= isset($phone) ? $phone : '' ?>"><br><br>

    <?php if ($email_error) {
        echo "<span style='color: red'>$email_error</span><br><br>";
    } ?>
    <label for="email">E-mail:</label>
    <input type="text" id="email" name="new_email" placeholder="E-mail"
           value="<?= isset($email) ? $email : '' ?>"><br><br>

    <label for="password">Пароль:</label>
    <input type="password" id="password" name="new_password" placeholder="Пароль"><br><br>


    <input type="submit" value="Изменить">
</form>
</body>
</html>


