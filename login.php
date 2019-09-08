<?php


use api\ApiModel;

require_once __DIR__ . "/vendor/autoload.php";


function generateCode($length = 6)
{

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;

    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }

    return $code;
}

if (isset($_POST['submit'])) {
    $all = new ApiModel();

    $sth = $all->getPDO()->prepare("SELECT user_id, user_password FROM users WHERE user_login = ? LIMIT 1");
    $sth->execute([$_POST['login']]);
    $data = $sth->fetch(PDO::FETCH_ASSOC);

    if (password_verify($_POST['password'], $data['user_password'])) {
        $hash = md5(generateCode(10));
        $sth = $all->getPDO()->prepare("UPDATE users SET user_hash = ? WHERE user_id = ?");
        $sth->execute([$hash, $data['user_id']]);

        setcookie("id", $data['user_id'], time() + 3600);
        setcookie("hash", $hash, time() + 3600);

        header("Location: /");
        exit();

    } else {
        echo "Вы ввели неправильный логин/пароль";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Авторизация</title>
</head>
<body>
<form method="POST">

    Логин <input name="login" type="text"><br>

    Пароль <input name="password" type="password"><br>

    <input name="submit" type="submit" value="Войти">

</form>
</body>
</html>
