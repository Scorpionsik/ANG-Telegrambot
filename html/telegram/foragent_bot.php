<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

$bot->on(function ($Update) use ($bot) {
	include "connection.php";
	
    $message = $Update->getMessage();
	
	$id_user = $message->getChat()->getId();
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));
	//$bot->deleteMessage($id_user, $message->getMessageId());
	
	if($msg_text == "/help")
	{
		$bot->sendMessage($id_user, 'Если у вас возникли вопросы или ошибки при работе с ботом, напишите мне и подробно изложите суть вопроса или проблемы.');
		$bot->sendMessage($id_user, 'Хорошего дня и отличного настроения, будьте здоровы!');
		$bot->sendContact($id_user,'+380951473711','Саша');
	}
	else
	{
		
	
		
		if($msg_text == "/start")
		{
			$query = "INSERT INTO telegram_users (Id_telegram_user) values (${id_user});";
			mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
		}
		
		$query = "SELECT * FROM telegram_users where Id_telegram_user=${id_user};";
		$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
			
		if($result)
		{
			$row = mysqli_fetch_row($result);
			if($row)
			{
				if($row[1] == null)
				{
					if(preg_match("/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/i",$msg_text))
					{
						//код проверки по белому листу
						
						$clear_phone = preg_replace("/\D/i","",$msg_text);
						$bot->sendMessage($id_user, $clear_phone);
						$clear_phone = preg_replace("/^[380]{0,3}/i",""$clear_phone);
						$bot->sendMessage($id_user, $clear_phone);
					}
					else
					{
						if($msg_text == "/start")$bot->sendMessage($id_user, "Здравствуйте!");
						else 
						{
							$bot->sendMessage($id_user, "Введён некорректный номер!");
							$bot->sendMessage($id_user, $msg_text);
						}
					}
					/*
					$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
					[
						[
							['text'=>'Отправить номер с телеграма','request_contact'=>true]
						]
					]);*/
					//$bot->sendMessage($id_user, "Для подтверждения входа, введите свой рабочий номер телефона, пожалуйста!", null, false, null, $keyboard);
					$bot->sendMessage($id_user, "Для подтверждения входа, введите свой рабочий номер телефона, пожалуйста!");
				}
				else
				{
					//код получения информации из белого списка
					//код выдачи данных
				}
			}
		}
		
		mysqli_free_result($result);
		mysqli_close($dblink);
	}
	
    //$bot->sendMessage($id_user, "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>