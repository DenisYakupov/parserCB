<?php


use api\ApiModel;

require_once __DIR__ . "/vendor/autoload.php";


if (isset($_COOKIE['id']) && isset($_COOKIE['hash'])) {

    $all = new ApiModel();

    $sth = $all->getPDO()->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $sth->execute([$_COOKIE['id']]);
    $userdata = $sth->fetch(PDO::FETCH_ASSOC);

    if (($userdata['user_hash'] !== $_COOKIE['hash']) or ($userdata['user_id'] !== $_COOKIE['id'])) {
        setcookie("id", "", time() - 3600 * 24 * 30 * 12, "/");
        setcookie("hash", "", time() - 3600 * 24 * 30 * 12, "/");
    } else {
        if (isset($_GET['route'])) {
            switch ($_GET['route']):
                case 'currencies' :
                    $limit = $_GET['limit'] ?? 10;
                    $offset = $_GET['offset'] ?? 0;
                    echo $all->dataAll($limit, $offset);
                    break;
                case 'currency' :
                    echo $all->dateOne($_GET['id']);
                    break;
            endswitch;
        }
    }
} else {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
</head>
<body>
<p>
    Примеры запросов:
</p>
<ul>
    <li>currencies?[limit = 10, offset = 0]</li>
    <li>currency?id=R01020A</li>
</ul>
</body>
</html>





