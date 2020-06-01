<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

//команда Start
$bot->command('start', function ($message) use ($bot) {
	include "connection.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$id_user = $message->getChat()->getId();
	$query = "SELECT * FROM test_user where Iduser=${id_user};";
	
	$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
	if($result)
	{
		$row = mysqli_fetch_row($result);
		
		if($row)
		{
			$bot->sendMessage($id_user, 'С возвращением, ' . $row[3] . "!");
		}
		else
		{
			mysqli_query($dblink,"INSERT INTO test_user (Iduser,Status) VALUES ($id_user,0);") or die("Ошибка: " . mysqli_error($dblink));
			$bot->sendMessage($id_user, 'Добро пожаловать!');	
			$bot->sendMessage($id_user, 'Пожалуйста, напиши своё имя:');
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
    $id_user = $message->getChat()->getId();
    $bot->sendMessage($id_user,$id_user);
});*/

//Обработка введенного текста
$bot->on(function ($Update) use ($bot) {
    $message = $Update->getMessage();
	
	include "connection.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));

	$id_user = $message->getChat()->getId();
	
	$query = "SELECT * FROM test_user where Iduser=${id_user};";
	$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));

	if($result)
	{
		$row = mysqli_fetch_row($result);
		if($row)
		{
			$id_status = $row[2];
			//Получили 
			if($id_status == 0)
			{
				$id_status++;
				mysqli_query($dblink,"UPDATE test_user SET Status=${id_status}, Username='${msg_text}' WHERE Id=" . $row[0] . ";") or die("Ошибка: " . mysqli_error($dblink));
				$bot->sendMessage($id_user, "Приятно познакомится, ${msg_text}!");
				/*
				$keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
					[
						[
							['text' => 'link', 'url' => 'https://core.telegram.org']
						]
					]
				);
						
				$bot->sendMessage($id_user, "Try me", null, false, null, $keyboard);
				*/
				$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([[['text'=>'⬅️ Назад'],['text'=>'Дальше ➡️']]]);
                $bot->sendMessage($id_user, null, null, false, null, $keyboard);
			}

			//Логика по умолчанию
			else
			{
				$id_user_message = $message->getMessageId();
				$bot->deleteMessage($id_user, $id_user_message);
				$bot->sendMessage($id_user, "Ты написал: ${msg_text}");
			}
		}
	}

	mysqli_free_result($result);
	mysqli_close($dblink);
    //$bot->sendMessage($id_user, "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>