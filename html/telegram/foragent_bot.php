<?php
$root_dir = explode('html',__DIR__)[0] . 'html';

include "givemyprecious.php";
require_once $root_dir . "/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client($token);

//command /help
$bot->command('help', function ($message) use ($bot) {
		$id_user = $message->getChat()->getId();
        $bot->sendMessage($id_user, 'Если у вас возникли вопросы или ошибки при работе с ботом, напишите мне и подробно изложите суть вопроса или проблемы.');
		$bot->sendMessage($id_user, 'Хорошего дня и отличного настроения, будьте здоровы!');
		$bot->sendContact($id_user,'+380951473711','Саша');
    });
	
	//command /send_news
$bot->command('send_news', function ($message) use ($bot) {
		$id_user = $message->getChat()->getId();
		if($id_user == 425486413)
		{
			$message_text = $message->getText();
			
			$news_text = preg_replace('/^\/[^ ]+[ ]+/',"",$message_text);
			
			
			if(!preg_match('/send_news/', $news_text))
			{
				include "connection_agent.php";
				$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
				
				$query = 'SELECT telegram_users.Id_telegram_user, white_list.Is_get_edit_offers from telegram_users join white_list on telegram_users.Id_whitelist_user=white_list.Id_whitelist_user where telegram_users.Id_whitelist_user != 11 AND white_list.Is_banned=0;';
				$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
				
				if($result)
				{
					$count = mysqli_num_rows($result);
					if($count > 0)
					{
						for($i=0; $i < $count; $i++)
						{
							$row = mysqli_fetch_row($result);
							$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
								[
									[
										['text'=>'📥 Получить всё за последние 3 дня']
									],[
										['text'=>'❕ Присылать только новые объекты в уведомлениях']
									]
								],
								false,
								true);
							if($row[1] == 0)
							{
								$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
									[
										[
											['text'=>'📥 Получить всё за последние 3 дня']
										],[
											['text'=>'✅ Получать все объекты в уведомлениях']
										]
									],
									false,
									true);
							}
							
							$array = preg_split('/=/',$news_text);
							$count_array = count($array) - 1;
							$index = 0;
							
							for(;$index < $count_array; $index++)
							{
								try
								{
									$bot->sendMessage($row[0], $array[$index], "HTML");
								}
								catch(Exception $e)
								{
									break;
								}
							}
							try
							{
								$bot->sendMessage($row[0], $array[$index], "HTML", false, null, $keyboard);
							}
							catch(Exception $e)
							{
								$query='update telegram_users set IsExist=0 where telegram_users.Id_telegram_user=' . $row[0] . ";";
								mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
							}
							
							//$bot->sendMessage($id_user, $news_text, "HTML", false, null, $keyboard);
						}
					}
					mysqli_free_result($result);
				}
				mysqli_close($dblink);
			}			
		}
    });


$bot->on(function ($Update) use ($bot) {
	include "connection_agent.php";

	$lock=true;
    $message = $Update->getMessage();
	if($message)
	{
		$id_user = $message->getChat()->getId();
		$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
		$msg_text = htmlentities(mysqli_real_escape_string($dblink,$message->getText()));

		//---команда start---//
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
				mysqli_free_result($result);
			}
			
		}
		//---конец команда start---//
		$query = "SELECT * FROM telegram_users where Id_telegram_user=${id_user};";
		$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
			
		if($result)
		{
			$row = mysqli_fetch_row($result);
			if($row)
			{
				//если id чата ещё не указан
				if($row[1] == null)
				{
					//проверка по шаблону, введён телефонный номер или нет
					if(preg_match("/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/i",$msg_text))
					{
						//код проверки по белому листу
						$clear_phone = preg_replace("/\D/i","",$msg_text);
						$clear_phone = preg_replace("/^[38]{0,2}/i","",$clear_phone);
						$query = "SELECT * FROM white_list where Phonenumber like ('%${clear_phone}%');";
						$result_from_whitelist = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
						
						if($result_from_whitelist)
						{
							$row_from_whitelist = mysqli_num_rows($result_from_whitelist);
							//если в white_list есть такой номер
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
											$query = "UPDATE telegram_users SET Id_whitelist_user=" . $row_from_whitelist[0] . ", Register_date=" . time() . " where Id_telegram_user=" . $row[0] . ";";
											mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
											$bot->sendMessage($id_user, "Добро пожаловать, " . $row_from_whitelist[2] . "!");
											$lock=false; //разблокировали функции бота
										}
									}
								}
							}
							else if($row_from_whitelist>1)
							{
								$bot->sendMessage(425486413, "Внимание, есть повторный номер (${clear_phone}) у:");
								for($i=0; $i<$row_from_whitelist; $i++)
								{
									$row_from_whitelist = mysqli_fetch_row($result_from_whitelist);
									$bot->sendMessage(425486413, $row[0] . " - " . $row[1]);
								}
								$bot->sendMessage($id_user, "Похоже, что номер (${clear_phone}) уже привязан к другому человеку. Если это точно ваш номер - напишите мне сюда (Вайбер/Телеграм):");
								$bot->sendContact($id_user,'+380951473711','Саша');
							}
							else //если номера в white_list нету
							{
								$bot->sendMessage($id_user, "Введён некорректный номер!");
							}
						}
					}
					else
					{
						//Первое приветствие
						if($msg_text == "/start")$bot->sendMessage($id_user, "Здравствуйте!");
						else //если шаблон не распознал введенное сообщение как номер телефона
						{
							$bot->sendMessage($id_user, "Введён некорректный номер!");
						}
					}
					//если регистрация не удалась
					if($lock) $bot->sendMessage($id_user, "Для подтверждения входа, введите свой рабочий номер телефона, пожалуйста!");
					else //если регистрация прошла успешно
					{
						$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
						[
							[
								['text'=>'📥 Получить всё за последние 3 дня']
							],[
								['text'=>'❕ Присылать только новые объекты в уведомлениях']
							]
						],
						false,
						true);
						if($row_from_whitelist[0] != 11)
						{
							//успешно зарегался
							$bot->sendMessage($id_user, "Ваша личность подтверждена! Вы подписаны на обновления по вашему району, они будут приходить вам в течении дня автоматически!");
							$bot->sendMessage($id_user, "Если в уведомлениях вам нужны <b>только новые объявления</b>, нажмите на кнопку ниже - <b>❕ Присылать только новые объекты в уведомлениях</b>.","HTML");
							$bot->sendMessage($id_user, "Чтобы получить всю информацию по вашему району за последние 3 дня, нажмите кнопку ниже.", null, false, null, $keyboard);
						}
						else 
						{
							$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
								[
									[
										['text'=>'Цём 💋']
									]
								],
								false,
								true);
							$bot->sendMessage($id_user, "Нажми на кнопочку, посмотри что из этого получится! 😉", null, false, null, $keyboard);
						}
					}
				}
				else //если id чата был в таблице
				{
					//код получения информации из белого списка
					$query = "SELECT * FROM white_list where Id_whitelist_user=" . $row[1] . ";";
					$result_from_whitelist = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
					if($result_from_whitelist)
					{
						$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
									[
										[
											['text'=>'📥 Получить всё за последние 3 дня']
										],[
											['text'=>'❕ Присылать только новые объекты в уведомлениях']
										]
									],
									false,
									true);
															
						$row_from_whitelist = mysqli_fetch_row($result_from_whitelist);
						if($row_from_whitelist) //если агент есть в таблице, приветствуем и разблокируем основные функции бота
						{
							if($row_from_whitelist[6] == 0)
							{
								$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
									[
										[
											['text'=>'📥 Получить всё за последние 3 дня']
										],[
											['text'=>'✅ Получать все объекты в уведомлениях']
										]
									],
									false,
									true);
							}
							
							if(preg_match('/уведомл/',$msg_text))
							{
								$lock=true;
								if(preg_match('/Присылать только/', $msg_text))
								{
									$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
									[
										[
											['text'=>'📥 Получить всё за последние 3 дня']
										],[
											['text'=>'✅ Получать все объекты в уведомлениях']
										]
									],
									false,
									true);
									$bot->sendMessage($id_user, "Теперь в уведомлениях будут приходить <b>только новые объекты</b>. Если вы снова хотите получать обновленные объекты, нажмите на \"Получать все объекты в уведомлениях\".", 'HTML', false, null, $keyboard);
									$query = "update white_list set Is_get_edit_offers=0 where Id_whitelist_user=" . $row[1] . ";";
									mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
								}
								else if(preg_match('/Получать/', $msg_text))
								{
									$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
									[
										[
											['text'=>'📥 Получить всё за последние 3 дня']
										],[
											['text'=>'❕ Присылать только новые объекты в уведомлениях']
										]
									],
									false,
									true);
									$bot->sendMessage($id_user, "Теперь в уведомлениях будут приходить <b>и новые, и обновленные объекты</b>. Если вы снова хотите получать только новые объекты, нажмите на \"Присылать только новые объекты в уведомлениях\".", 'HTML', false, null, $keyboard);
									$query = "update white_list set Is_get_edit_offers=1 where Id_whitelist_user=" . $row[1] . ";";
									mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
								}
								else
								{
									$bot->sendMessage($id_user, "Добро пожаловать, " . $row_from_whitelist[2] . "!", null, true, null, null, true);
									$lock=false;
								}
							}
							else
							{
								$bot->sendMessage($id_user, "Добро пожаловать, " . $row_from_whitelist[2] . "!", null, true, null, null, true);
								$lock=false;
							}
						}
					
						//если функции бота разблокированы
						if($lock == false)
						{
							//---код выдачи данных---//						
							if($row_from_whitelist[0] != 11)
							{
								//если пользователь не забанен (IsBlocked в таблице white_list)
								if($row_from_whitelist[3] == false)
								{					
									include "foragent_functions.php";
									
									$offer_array = makeOfferMessages($dblink, $row_from_whitelist[0]);
									$count_offer_array = count($offer_array);
									
									//если для агента есть информация
									if($count_offer_array > 0)
									{
										foreach($offer_array as $offer)
										{
											$tmp_internal_id = $offer->getInternalId();
											//полная инлайн клавиатура
											$keyboard_inline = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
												[
													[
														['text' => '🛄 Объект на сайте', 'url' => 'http://an-gorod.com.ua/real/flat/sale?q=' . $tmp_internal_id],['text' => '💼 Объект в базе', 'url' => 'http://newcab.bee.th1.vps-private.net/node/' . $offer->getEntityId()]
													],[
														['text' => '☎️ Телефоны', 'callback_data' => $tmp_internal_id]
													]
												]
											);
											
										//---проверка доступа к кнопке "Объект в базе"---//
										if($row_from_whitelist[4] == 0)
										{
											//инлайн клавиатура без кнопки "Объект в базе"
											$keyboard_inline = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
												[
													[
														['text' => '🛄 Объект на сайте', 'url' => 'http://an-gorod.com.ua/real/flat/sale?q=' . $tmp_internal_id]
													],[
														['text' => '☎️ Телефоны', 'callback_data' => $tmp_internal_id]
													]
												]
											);
										}
										//---конец проверка доступа к кнопке "Объект в базе"---//
										
										$bot->sendMessage($id_user, $offer->getMessage(), null, true, null, $keyboard_inline, true);
										}
										
										$bot->sendMessage($id_user, "Всего " . declOfNum($count_offer_array,array('объект','объекта','объектов')) . " за последние 3 дня.", null, false, null, $keyboard);
									}
									else $bot->sendMessage($id_user, "Информации по вашему району на данный момент нет, попробуйте позже!", null, false, null, $keyboard);
								}
								else //если пользователь забанен
								{
									$bot->sendMessage($id_user, "У нас технические неполадки-шоколадки!😱🍫 Но не переживайте, скоро всё заработает. Хорошего вам настроения и удачного дня!😊", null, false, null, $keyboard);
									//$bot->sendMessage($id_user, 'На данный момент ведутся работы по добавлению возможности просматривать хозяйские телефоны по объектам, поэтому пока ничего не приходит. Сохраняйте спокойствие, скоро всё снова будет приходить! Хорошего вам дня и отличного настроения!😊 Будьте здоровы!');
									//$bot->sendMessage($id_user, "На данный момент проблема с получением информации наблюдается по всем районам, причина выявлена и пока что я её решаю. После того, как смогу убедиться, что всё должно работать как следует, я оповещу вас в вайбер или сообщением в этом диалоге. Спасибо, что уведомляете меня о проблемах по вашим районам!", null, false, null, $keyboard);
								}
							} //---конец кода выдачи данных---//
							else 
							{
								$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
								[
									[
										['text'=>'Цём 💋']
									]
								],
								false,
								true);
								
								$love_array = array(
								'Люблю тебя, счастье моё!❤️',
								'Радость моя, мне так хорошо с тобой 😘',
								'Любимая моя) Хочу тебя обнять!',
								'Счастье моё! Радость моя! Любимая😍 Хорошая моя🥰',
								'Лови воздушный поцелуйчик!😊😘',
								'Я тебя кусь кусь кусь😼😉',
								'Обнимашки целовашки☺️',
								'Моя умничка, люблю тебя 😘',
								'Как здорово, что ты есть у меня!❤️',
								'Всё будет хорошо, любимая!😊',
								'Ты ж моя сдобная булочка 🥯😘',
								'Ты ж моя мать крысек!🐭🥰'
								);

								$bot->sendMessage($id_user, $love_array[mt_rand(0, count($love_array)-1)], null, false, null, $keyboard);
							}
						}
					}
				}
			}
		}
		
		mysqli_free_result($result);
		mysqli_close($dblink);
	}

}, function ($Update)
{ 
	$callback = $Update->getCallbackQuery();
	if (is_null($callback)) return true;
	else return false;
});

//---Обработка инлайн запросов---//
$bot->on(function ($Update) use ($bot) {
	$callback = $Update->getCallbackQuery();
	$internal_id = $callback->getData();
	$message = $callback->getMessage();
	if($message)
	{
		$id_user = $message->getChat()->getId();
		$entity_id=0;
		$text_message = $message->getText() . "\r\n\r\n";
		include "connection_agent.php";
		$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
		
		$query = "SELECT flat_owners.Username, flat_owners.Agency , owner_phones.Phonenumber, offers.Entity_id FROM offers JOIN flat_owners USING (User_entity_id) JOIN owner_phones USING (User_entity_id) WHERE offers.Internal_id='" . $internal_id . "';";
		$result_user_entity_id = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
		if($result_user_entity_id)
		{
			$num_user_entity_id = mysqli_num_rows($result_user_entity_id);
			if($num_user_entity_id > 0)
			{
				for($i=0; $i<$num_user_entity_id; $i++)
				{
					$row_user_entity_id = mysqli_fetch_row($result_user_entity_id);
					if($i==0)
					{
						$entity_id=$row_user_entity_id[3];
						if($row_user_entity_id[0] != null && $row_user_entity_id[0] != "") 
						{
							foreach(preg_split("/;/",$row_user_entity_id[0]) as $newname)
							{
								$text_message = $text_message . "💁‍♂️ " . $newname . "\r\n";
							}
						}
						else $text_message = $text_message . "🤷 Имя не указано\r\n";
						
						if($row_user_entity_id[1] != null && $row_user_entity_id[1] != "") $text_message = $text_message . "📎 Агенство " . $row_user_entity_id[1] . "\r\n";
					}
					$text_message = $text_message . $row_user_entity_id[2] . "\r\n";
				}
			}
			
			$query = "select Id_whitelist_user, Is_accept_base_button from telegram_users join white_list using (Id_whitelist_user) where Id_telegram_user=" . $id_user . ";";
			$result_whitelist_id = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
			if($result_whitelist_id)
			{
				$row_whitelist_id = mysqli_fetch_row($result_whitelist_id);
				if($row_whitelist_id)
				{
					if($row_whitelist_id[0]!=10)
					{
						$query = "insert into agent_phone_press values (" . $row_whitelist_id[0] . ", '" . $internal_id . "', " . $entity_id .  "," . time() . ");";
						mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
					}
					
					$keyboard_inline = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
						[
							[
								['text' => '🛄 Объект на сайте', 'url' => 'http://an-gorod.com.ua/real/flat/sale?q=' . $internal_id],
								['text' => '💼 Объект в базе', 'url' => 'http://newcab.bee.th1.vps-private.net/node/' . $entity_id]
							]
						]
					);
					//проверка доступа к кнопке "Объект в базе"
					if($row_whitelist_id[1] == 0)
					{
						$keyboard_inline = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
						[
							[
								['text' => '🛄 Объект на сайте', 'url' => 'http://an-gorod.com.ua/real/flat/sale?q=' . $internal_id]
							]
						]
					);
					}
					//---//
					
					
					$bot->editMessageText($id_user,$message->getMessageId(),$text_message,null,false,$keyboard_inline);
					//$bot->sendMessage($id_user, $internal_id);
				}
			}
		}
		
		

	}
	
	}, function ($Update)
		{ 
			$callback = $Update->getCallbackQuery();
			if (is_null($callback)) return false;
			else return true;
		});
		//---конец Обработка инлайн запросов---//

$bot->run();

?>