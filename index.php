<?php

require_once 'config/db.php';
require_once 'includes/func.php';
require_once 'includes/errors.php';

$table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(11) NOT NULL UNIQUE,
    email VARCHAR(75) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL    
)";

if ($conn->query($table_sql) !== TRUE) {
    echo "Ошибка создания таблицы: " . $conn->error . "<br>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = strip_tags($_POST['login']);
    $phone = strip_tags($_POST['phone']);
    $email = strip_tags($_POST['email']);
    $password_1 = strip_tags($_POST['password_1']);
    $password_2 = strip_tags($_POST['password_2']);

    try {
        $email_error = checkPattern("/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email, "Email не существующий!");
        $phone_error = checkPattern("/^8[0-9]{10}$/", $phone, "Номер не существующий!");
        if ($email_error || $phone_error) throw new Exception();


        if ($password_1 === $password_2) {
            $password = password_hash(strip_tags($_POST['password_1']), PASSWORD_DEFAULT);
        } else {
            $password_error = "Пароли не совпадают!";
            throw new Exception();
        }

        if (!checkCol($conn, 'login', $login)) {
            $login_error = "Такой логин уже занят!";
            throw new Exception();
        } elseif (!checkCol($conn, 'phone', $phone)) {
            $phone_error = "Такой номер уже занят!";
            throw new Exception();
        } elseif (!checkCol($conn, 'email', $email)) {
            $email_error = "Такой email уже занят!";
            throw new Exception();
        }

        $sql = "INSERT INTO users (login, phone, email, password) VALUES ('$login', '$phone', '$email', '$password')";

        if ($conn->query($sql) !== TRUE) {
            throw new Exception($conn->error);
        }

        echo 'Регистрация прошла успешно! Вы можете войти!';

    } catch (Exception $e) {
        echo $e->getMessage();
    } finally {
        if (isset($conn)) $conn->close();
    }

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
</head>
<body>
<h1>Регистрация</h1>
<form action="index.php" method="post">
    <?php if ($login_error) {
        echo "<span style='color: red'>$login_error</span><br><br>";
    } ?>
    <label for="login">Имя пользователя (логин):</label>
    <input type="text" id="login" name="login" placeholder="Имя (логин)" required
           value="<?= isset($login) ? $login : '' ?>"><br><br>

    <?php
    if ($phone_error) {
        echo "<span style='color: red'>$phone_error</span><br><br>";
    }
    ?>
    <label for="phone">Телефон:</label>
    <input type="text" id="phone" name="phone" placeholder="87776665544" required
           value="<?= isset($phone) ? $phone : '' ?>"><br><br>

    <?php
    if ($email_error) {
        echo "<span style='color: red'>$email_error</span><br><br>";
    }
    ?>
    <label for="email">E-mail:</label>
    <input type="text" id="email" name="email" placeholder="E-mail" required value="<?= isset($email) ? $email : '' ?>"><br><br>

    <?php
    if ($password_error) {
        echo "<span style='color: red'>$password_error</span><br><br>";
    }
    ?>
    <label for="password_1">Пароль:</label>
    <input type="password" id="password_1" name="password_1" placeholder="Пароль" required><br><br>

    <label for="password_2">Повтор пароля:</label>
    <input type="password" id="password_2" name="password_2" placeholder="Повтор пароля" required><br><br>

    <input type="submit" value="Зарегистрироваться">

    <a href="auth.php">Авторизация</a>
</form>
</body>
</html>
