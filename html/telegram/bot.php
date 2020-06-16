<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
include "connection_agent.php";
require_once "${root_dir}/vendor/autoload.php";

$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
$bot = new \TelegramBot\Api\Client(${token});
//$bot->sendMessage(425486413, 'Test');

$query = "select telegram_users.Id_whitelist_user as 'Id', telegram_users.Id_telegram_user as 'Telegram' from telegram_users join white_list using (Id_whitelist_user) WHERE telegram_users.Id_whitelist_user != 11";
$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
if($result)
{
	$count = mysqli_num_rows($result);
	for($i = 0; $i < $count; $i++)
	{
		$row = mysqli_fetch_row($result);
		if($row)
		{
			$id_user = $row[1];
			//show results code
										$query = "select offers.Internal_id, types.Type_name, flat_types.Typename, offers.Locality, districts.District_name, offers.Address, offers.Description, offers.Room_counts, offers.Floor, offers.Floors_total, offers.Area, offers.Lot_area, offers.Living_space, offers.Kitchen_space, offers.Price, offers.Image_url from offers inner join bind_whitelist_distr_flats on offers.Id_type=bind_whitelist_distr_flats.Id_type and offers.Id_flat_type=bind_whitelist_distr_flats.Id_flat_type and offers.Id_district=bind_whitelist_distr_flats.Id_district and offers.Room_counts=bind_whitelist_distr_flats.Room_counts inner join types on offers.Id_type=types.Id_type inner join flat_types on offers.Id_flat_type=flat_types.Id_flat_type inner join districts on offers.Id_district=districts.Id_district where bind_whitelist_distr_flats.Id_whitelist_user=" . $row[0] . ";";
										$result_bind = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
										if($result_bind)
										{
											//--get info code--//
											$row_bind_count = mysqli_num_rows($result_bind);
											if($row_bind_count > 0)
											{
												for($i = 0; $i < $row_bind_count; $i++)
												{
													$row_bind = mysqli_fetch_row($result_bind);
													
													$keyboard_inline = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
														[
															[
																['text' => 'Ссылка на сайт', 'url' => 'http://an-gorod.com.ua/real/flat/sale?q=' . $row_bind[0]]
															]
														]
													);
													
													$offer_message = $row_bind[0];
													$offer_message = $offer_message . "\r\n" . $row_bind[2] . " " . $row_bind[7] . "-комнатная, " . $row_bind[1] . " \r\n" . $row_bind[3] . ", " . $row_bind[4];
													if($row_bind[5] != null)
													{
														$offer_message = $offer_message . ", " . $row_bind[5];
													}
													$offer_message = $offer_message . " \r\n" . $row_bind[8] . "/" . $row_bind[9] . " \n" . $row_bind[10] . "/" . $row_bind[12] . "/" . $row_bind[13] . " \r\n \nЦена: " . $row_bind[14] . "\n\n" . $row_bind[6];
													$bot->sendMessage($id_user, $offer_message, null, false, null, $keyboard_inline);
													
													
												}
												$bot->sendMessage($id_user, "Всего ${row_bind_count} объектов за последние 3 дня.");
											}
											else $bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
											//--end get info code--//
										}
										else
										{
											$bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
										}	
										mysqli_free_result($result_bind);
			
			//$bot->sendMessage($id_user, 'Добрый день! Прошу вас проверить, приходит ли информация по вашему району из бота. Если нет, сообщите в Вайбер по номеру 095 147 37 11. Заранее вам спасибо!');
		}
	}
	mysqli_free_result($result);
}
mysqli_close($dblink);
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