<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

$bot->on(function ($Update) use ($bot) {
    $message = $Update->getMessage();
	
	$id_user = $message->getChat()->getId();
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));
	$bot->deleteMessage($id_user, $message->getMessageId());
	
	if($msg_text == "/help")
	{
		$bot->sendMessage($id_user, 'Если у вас возникли вопросы или ошибки при работе с ботом, напишите мне и подробно изложите суть вопроса или проблемы.\n\nХорошего дня и отличного настроения, будьте здоровы!');
		$bot->sendContact($id_user,'+380951473711','Саша');
	}
	else
	{
		include "connection.php";
	
		
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
				$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
					[
						[
							['text'=>'Ввести номер']
						],
						[
							['text'=>'Отправить номер с телеграма','request_contact'=>true]
						]
					]);
					$bot->sendMessage($id_user, "Здравствуйте!\n\nДля подтверждения входа, введите свой рабочий номер телефона, пожалуйста!", null, false, null, $keyboard);
			}
		}
		
		mysqli_free_result($result);
		mysqli_close($dblink);
	}
	
    //$bot->sendMessage($id_user, "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>