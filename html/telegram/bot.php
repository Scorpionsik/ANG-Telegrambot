<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

//команда Start
$bot->command('start', function ($message) use ($bot) {
	include "connection.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$id = $message->getChat()->getId();
	$query = "SELECT * FROM test_user where Id=${id};";
	
	$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
	if($result)
	{
		$rows = mysqli_num_rows($result);
		if($rows == 0)
		{
			mysqli_query($dblink,"INSERT INTO test_user ('Iduser','Status') VALUES (${id},'0')") or die("Ошибка: " . mysqli_error($dblink));
			$bot->sendMessage($id, 'Добро пожаловать!');	
			$bot->sendMessage($id, 'Пожалуйста, напишите своё имя:');	
		}
		else $bot->sendMessage($id, 'С возвращением!');
	}
	mysqli_free_result($result);
	mysqli_close($dblink);
});

//команда Help
$bot->command('help', function ($message) use ($bot) {
    $bot->sendMessage($message->getChat()->getId(), 'help');
});
/*
//команда Id
$bot->command('id', function ($message) use ($bot) {
    $id = $message->getChat()->getId();
    $bot->sendMessage($id,$id);
});*/

//Обработка введенного текста
$bot->on(function ($Update) use ($bot) {
    $message = $Update->getMessage();
    $msg_text = $message->getText();

    $bot->sendMessage($message->getChat()->getId(), "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>