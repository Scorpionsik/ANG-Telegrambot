<?php
$root_dir = explode('html',__DIR__)[0] . 'html';
include "givemyprecious.php";
require_once $root_dir . "/vendor/autoload.php";

function logicMethod($bot, $message)
{
	$chat_id = $message->getChat()->getId();
	$message_text = htmlentities($message->getText());
	if($message_text == "/start")
	{
		$bot->sendMessage($chat_id, 'On, after /start');
	}
	//else $bot->sendMessage($chat_id, 'You enter: ' . $message_text);
	
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
			[
				[
					['text'=>'📥 Включить']
				]
			],
			false,
			true);
	$text = 'Text On: ';
	if(preg_match('/Включить/'))
	{
		$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
			[
				[
					['text'=>'📥 Выключить']
				]
			],
			false,
			true);
		$text = $text . 'включил';
	}
	else if(preg_match('/Выключить/'))
	{
		$text = $text . 'выключил';
	}
	
	$bot->sendMessage($chat_id, $text, null, false, null, $keyboard);
}

$bot = new \TelegramBot\Api\Client($token_test);

//command /start
$bot->command('start', function ($message) use ($bot) {
		$chat_id = $message->getChat()->getId();
        $bot->sendMessage($chat_id, 'Start!');
		logicMethod($bot, $message);
    });
	
//command /help
$bot->command('help', function ($message) use ($bot) {
		$chat_id = $message->getChat()->getId();
        $bot->sendMessage($chat_id, 'Help!');
    });
	
	//command /key
$bot->command('key', function ($message) use ($bot) {
		$chat_id = $message->getChat()->getId();
		
		$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
			[
				[
					['text'=>'📥 Включить']
				]
			],
			false,
			true);
		
        $bot->sendMessage($chat_id, 'Send keyboard', null, false, null, $keyboard);
    });

//event on
$bot->on(function ($Update) use ($bot) {
	$message = $Update->getMessage();
	$chat_id = $message->getChat()->getId();
    $bot->sendMessage($chat_id, 'On, just on');
	logicMethod($bot, $message);
	}, function ($Update)
		{ 
			$callback = $Update->getCallbackQuery();
			if (is_null($callback)) return true;
			else return false;
		});

$bot->run();

/*
//команда Start
$bot->command('start', function ($message) use ($bot) {
	include "connection_custom.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$id_user = $message->getChat()->getId();
	$query = "SELECT * FROM custom_users where Iduser=${id_user};";
	
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
			mysqli_query($dblink,"INSERT INTO custom_users (Iduser,Status) VALUES ($id_user,0);") or die("Ошибка: " . mysqli_error($dblink));
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

//Обработка введенного текста
$bot->on(function ($Update) use ($bot) {
    $message = $Update->getMessage();
	
	include "connection.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));

	$id_user = $message->getChat()->getId();
	
	$query = "SELECT * FROM custom_users where Iduser=${id_user};";
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
				mysqli_query($dblink,"UPDATE custom_users SET Status=${id_status}, Username='${msg_text}' WHERE Id=" . $row[0] . ";") or die("Ошибка: " . mysqli_error($dblink));
				$bot->sendMessage($id_user, "Приятно познакомится, ${msg_text}!");

				$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
				[
					[
						['text'=>'1-комнатные'],['text'=>'2-комнатные']
					],
					[
						['text'=>'3-комнатные'],['text'=>'4-комнатные']
					],
					[
						['text'=>'Дальше']
					]
				]);
                $bot->sendMessage($id_user, "Выбери количество комнат:", null, false, null, $keyboard);
			}
			else
			{
				if($msg_text == 'Дальше')
				{
					$id_status++;
					mysqli_query($dblink,"UPDATE custom_users SET Status=${id_status} WHERE Id=" . $row[0] . ";") or die("Ошибка: " . mysqli_error($dblink));
					if($id_status == 2)
					{
						$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
						[
							[
								['text'=>'Алексеевка'],['text'=>'Павлово Поле']
							],
							[
								['text'=>'Салтовка'],['text'=>'Холодная Гора']
							],
							[
								['text'=>'Центр'],['text'=>'Сев. Салтовка']
							],
							[
								['text'=>'Пр. Гагарина'],['text'=>'Новые Дома']
							],
							[
								['text'=>'ХТЗ'],['text'=>'Центр. Рынок']
							],
							[
								['text'=>'Одесская'],['text'=>'Жуковского']
							],
							[
								['text'=>'Выбрать все']
							],
							[
								['text'=>'Дальше']
							]
						]);
						$bot->sendMessage($id_user, "Выбери районы:", null, false, null, $keyboard);
					}
					else if($id_status == 3)
					{
						$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
						[
							[
								['text'=>' Менее 15000$'],['text'=>'15000-30000$']
							],
							[
								['text'=>'30000-60000$'],['text'=>'60000-90000$']
							],
							[
								['text'=>'Более 90000$']
							],
							[
								['text'=>'Дальше']
							]
						]);
						$bot->sendMessage($id_user, "Выбери бюджет:", null, false, null, $keyboard);
					}
					else if($id_status == 4)
					{
						$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
						[
							[
								['text'=>' ']
							]
						]);
						$bot->sendMessage($id_user, "Предложенные варианты:", null, false, null, $keyboard);
					}
				}
								
			}
			
		}
	}
	$bot->deleteMessage($id_user, $message->getMessageId());
	mysqli_free_result($result);
	mysqli_close($dblink);
    //$bot->sendMessage($id_user, "Ты написал: " . $msg_text);
}, function () { return true; });
*/
//$bot->run();
?>