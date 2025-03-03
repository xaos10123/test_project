<?php
session_start();

require_once 'config/db.php';
require_once 'config/capcha.php';



function check_captcha($token){
    $ch = curl_init("https://smartcaptcha.yandexcloud.net/validate");
    $args = [
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
        "ip" => $_SERVER['REMOTE_ADDR'],
    ];
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200) {
        echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
        return true;
    }

    $resp = json_decode($server_output);
    return $resp->status === "ok";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = strip_tags($_POST['login']);
    $password = strip_tags($_POST['password']);

    $check_user = $conn->query("SELECT * FROM users WHERE email = '$login' OR phone = '$login'");

    $token = $_POST['smart-token'];
    if (check_captcha($token)) {

        if ($check_user->num_rows > 0) {
            $user = $check_user->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['loggedin'] = true;
                $name = $user['login'];
                echo "Вы пошли как $name";
            } else {
                echo 'Неверныый логин или пароль!';
            }
        }

    } else {
        echo 'Пройдите капчу!';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
    <title>Авторизация</title>
</head>
<body>
<h2>Авторизация</h2>
<form action="auth.php" method="POST">
    <label for="login">Телефон или почта:</label><br>
    <input type="text" name="login" required><br>

    <label for="password">Пароль:</label><br>
    <input type="password" name="password" required><br>

    <div
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="<?= DATA_SITEKEY ?>"
    ></div>

    <input type="submit" value="Войти">

    <?php
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        ?>
        <a href="profile.php">Профиль</a>
    <?php } ?>
</form>
</body>
</html>
