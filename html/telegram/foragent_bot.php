<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

$bot->on(function ($Update) use ($bot) {
	include "connection_agent.php";
	$lock=true;
    $message = $Update->getMessage();
	if($message)
	{
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
				$query = "SELECT * FROM telegram_users where Id_telegram_user=${id_user};";
				$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
				if($result)
				{
					$row_check = mysqli_num_rows($result);
					if($row_check == 0)
					{
						$query = "INSERT INTO telegram_users (Id_telegram_user) values (${id_user});";
						mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
					}
				}
				mysqli_free_result($result);
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
							//$bot->sendMessage($id_user, $clear_phone);
							$clear_phone = preg_replace("/^[380]{0,3}/i","",$clear_phone);
							//$bot->sendMessage($id_user, $clear_phone);
							$query = "SELECT * FROM white_list where Phonenumber=${clear_phone};";
							$result_from_whitelist = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
							if($result_from_whitelist)
							{
								$row_from_whitelist = mysqli_num_rows($result_from_whitelist);
								if($row_from_whitelist == 1)
								{
									$row_from_whitelist = mysqli_fetch_row($result_from_whitelist);
									
									$query = "SELECT * FROM telegram_users where Id_whitelist_user=" . $row_from_whitelist[0] . ";";
									$result_from_telegram_users =  mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
									if($result_from_telegram_users)
									{
										$row_from_telegram_users = mysqli_num_rows($result_from_telegram_users);
										if($row_from_telegram_users == 1)
										{
											$bot->sendMessage($id_user, "Введён некорректный номер!");
										}
										else
										{
											
											if($row_from_whitelist)
											{
												$query = "UPDATE telegram_users SET Id_whitelist_user=" . $row_from_whitelist[0] . " where Id_telegram_user=" . $row[0] . ";";
												mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
												$bot->sendMessage($id_user, "Добро пожаловать, " . $row_from_whitelist[2] . "!");
												$lock=false;
											}
										}
									}
								}
								else
								{
									$bot->sendMessage($id_user, "Введён некорректный номер!");
								}
							}
						}
						else
						{
							if($msg_text == "/start")$bot->sendMessage($id_user, "Здравствуйте!");
							else 
							{
								$bot->sendMessage($id_user, "Введён некорректный номер!");
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
						if($lock) $bot->sendMessage($id_user, "Для подтверждения входа, введите свой рабочий номер телефона, пожалуйста!");
						else
						{
							$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
							[
								[
									['text'=>'Обновить']
								]
							]);
							if($row_from_whitelist[0] != 11)
							{
								/*
								if($row_from_whitelist[3] == false)
								{
									//show results code
									
									$bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
									$bot->sendMessage($id_user, "Если информация по вашему району за последние 3 дня в базе есть, и Вы получили сообщение о её отсутствии, напишите об этом в Вайбер по номеру: 095 147 37 11, что бы я был в курсе, что с вашим районом всё ещё наблюдаются проблемы. Заранее вам огромное спасибо за помощь!", null, false, null, $keyboard);
								}
								else
								{
									//banned
									//$bot->sendMessage($id_user, "Ведутся технические работы, попробуйте позже!", null, false, null, $keyboard);
									$bot->sendMessage($id_user, "На данный момент проблема с получением информации наблюдается по всем районам, причина выявлена и пока что я её решаю. После того, как смогу убедиться, что всё должно работать как следует, я оповещу вас в вайбер или сообщением в этом диалоге. Спасибо, что уведомляете меня о проблемах по вашим районам!", null, false, null, $keyboard);
								}
								*/
								$bot->sendMessage($id_user, "Ваша личность подтверждена! Нажмите кнопку Обновить, чтобы начать получать объявления.", null, false, null, $keyboard);
							}
							else $bot->sendMessage($id_user, "Люблю тебя, радость моя!", null, false, null, $keyboard);
						}
					}
					else
					{
						//код получения информации из белого списка
						$query = "SELECT * FROM white_list where Id_whitelist_user=" . $row[1] . ";";
						$result_from_whitelist = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
						if($result_from_whitelist)
						{
							$row_from_whitelist = mysqli_fetch_row($result_from_whitelist);
							if($row_from_whitelist)
							{
								$bot->sendMessage($id_user, "Добро пожаловать, " . $row_from_whitelist[2] . "!");
								$lock=false;
							}
						
						
							if($lock == false)
							{
								//код выдачи данных
								
								$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
								[
									[
										['text'=>'Обновить']
									]
								]);
								if($row_from_whitelist[0] != 11)
								{
									if($row_from_whitelist[3] == false)
									{
										//show results code
										$query = "select offers.Internal_id, types.Type_name, flat_types.Typename, offers.Locality, districts.District_name, offers.Address, offers.Description, offers.Room_counts, offers.Floor, offers.Floors_total, offers.Area, offers.Lot_area, offers.Living_space, offers.Kitchen_space, offers.Price, offers.Image_url from offers inner join bind_whitelist_distr_flats on offers.Id_type=bind_whitelist_distr_flats.Id_type and offers.Id_flat_type=bind_whitelist_distr_flats.Id_flat_type and offers.Id_district=bind_whitelist_distr_flats.Id_district and offers.Room_counts=bind_whitelist_distr_flats.Room_counts inner join types on offers.Id_type=types.Id_type inner join flat_types on offers.Id_flat_type=flat_types.Id_flat_type inner join districts on offers.Id_district=districts.Id_district where bind_whitelist_distr_flats.Id_whitelist_user=18;"; //. $row_from_whitelist[0] . ";";
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
													$offer_message = $row_bind[0] . "   \n";
													$bot->sendMessage($id_user, $offer_message);
													$offer_message = $offer_message . " \r\n" . $row_bind[2] . " " . $row_bind[7] . "-комнатная, " . $row_bind[1] . " \r\n" . $row_bind[3] + ", " . $row_bind[4];
													$bot->sendMessage($id_user, $offer_message);
													if($row_bind[5] != null)
													{
														$offer_message = $offer_message . ", " . $row_bind[5];
													}
													$bot->sendMessage($id_user, $offer_message);
													$offer_message = $offer_message . " \r\n" . $row_bind[8] . "/" . $row_bind[9] . " \n" . $row_bind[10] . "/" . $row_bind[12] . "/" . $row_bind[13] . " \r\n \nЦена: " . $row_bind[14];
													$bot->sendMessage($id_user, $offer_message);
													
													
												}
												
											}
											else $bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
											//--end get info code--//
										}
										else
										{
											$bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
										}	
										mysqli_free_result($result_bind);
									}
									else
									{
										//banned
										//$bot->sendMessage($id_user, "Ведутся технические работы, попробуйте позже!", null, false, null, $keyboard);
										$bot->sendMessage($id_user, "На данный момент проблема с получением информации наблюдается по всем районам, причина выявлена и пока что я её решаю. После того, как смогу убедиться, что всё должно работать как следует, я оповещу вас в вайбер или сообщением в этом диалоге. Спасибо, что уведомляете меня о проблемах по вашим районам!", null, false, null, $keyboard);
									}
								}
								else $bot->sendMessage($id_user, "Люблю тебя, радость моя!", null, false, null, $keyboard);
							}
						}
					}
				}
			}
			
			mysqli_free_result($result);
			mysqli_close($dblink);
		}
	}
    //$bot->sendMessage($id_user, "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();


?>