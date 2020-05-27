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
	$query = "SELECT * FROM test_user where Iduser=${id};";
	
	$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
	if($result)
	{
		$row = mysqli_fetch_row($result);
		
		if($row)
		{
			$bot->sendMessage($id, 'С возвращением, ' . $row[3] . "!");
		}
		else
		{
			mysqli_query($dblink,"INSERT INTO test_user (Iduser,Status) VALUES ($id,0);") or die("Ошибка: " . mysqli_error($dblink));
			$bot->sendMessage($id, 'Добро пожаловать!');	
			$bot->sendMessage($id, 'Пожалуйста, напишите своё имя:');
		}
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
	
	include "connection.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));
	$id = $message->getChat()->getId();
	$query = "SELECT * FROM test_user where Iduser=${id};";
	$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));

	if($result)
	{
		$row = mysqli_fetch_row($result);
		if($row)
		{
			//Получили имя
			if($row[2] == 0)
			{
				mysqli_query($dblink,"UPDATE test_user SET Status=1, Username='${msg_text}' WHERE Id=" . $row[0] . ";") or die("Ошибка: " . mysqli_error($dblink));
				$bot->sendMessage($id, "Приятно познакомится, ${msg_text}!");
				$keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
					[
						[
							['text' => 'link', 'url' => 'url']
						]
					]
				);
				$bot->sendMessage($id, "Выбери количество комнат:", null, false, null, $keyboard);
			}
			//Логика по умолчанию
			else
			{
				$bot->sendMessage($id, "Ты написал: ${msg_text}");
			}
		}
	}

	mysqli_free_result($result);
	mysqli_close($dblink);
    //$bot->sendMessage($id, "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>