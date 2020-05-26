<?php
require_once "givemyprecious.php";
 
// подключаемся к серверу
$link = mysqli_connect($host, $dblogin, $dbpassw, $database) 
    or die("Ошибка " . mysqli_error($link));
 
if ($link->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $link->connect_errno . ") " . $link->connect_error;
}
 
// закрываем подключение
mysqli_close($link);
?>