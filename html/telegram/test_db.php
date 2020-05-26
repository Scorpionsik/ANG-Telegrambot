<?php
require_once "givemyprecious.php";

// подключаемся к серверу

$link = new mysqli($host, $dblogin, $dbpassw, $database); 

if ($link->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $link->connect_errno . ") " . $link->connect_error;
}
else echo "Подключились!<br/><br/>";
/*
$query = "INSERT INTO test_user (Iduser,Status) VALUES (1,1);";

$result = mysqli_query($link,$query) or die("Error: " . mysqli_error($link));
if($result)
{
    echo "Done!";
}
*/

// закрываем подключение
mysqli_close($link);
echo "<br/><br/>Подключение закрыто!";
?>