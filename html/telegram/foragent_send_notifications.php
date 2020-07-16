<?php
$root_dir = explode('html',__DIR__)[0] . html;

include "givemyprecious.php";
include "connection_agent.php";
require_once "${root_dir}/vendor/autoload.php";

$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
$bot = new \TelegramBot\Api\Client(${token});

/*
0	telegram_users.Id_whitelist_user 	int
1	telegram_users.Id_telegram_user 	int
2	white_list.Is_accept_base_button	boolean
3	white_list.Is_get_new_offers		boolean
4	white_list.Is_get_edit_offers		boolean
5	telegram_users.IsExist				boolean
*/
$query = "select telegram_users.Id_whitelist_user as 'Id', telegram_users.Id_telegram_user as 'Telegram', white_list.Is_accept_base_button, white_list.Is_get_new_offers, white_list.Is_get_edit_offers, telegram_users.IsExist from telegram_users join white_list using (Id_whitelist_user) WHERE telegram_users.Id_whitelist_user != 11 && white_list.Is_Banned != 1;";
$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
if($result)
{
	//код выдачи данных
	include "foragent_functions.php";
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
	[
		[
			['text'=>'📥 Получить всё за последние 3 дня']
		]
	],
	false,
	true);
	$count = mysqli_num_rows($result);
	for($i = 0; $i < $count; $i++)
	{
		$row = mysqli_fetch_row($result);
		
		
		if($row)
		{
			$id_whitelist = $row[0];
			$is_accept_base_button = $row[2];
			$is_new = $row[3];
			$is_edit = $row[4];
			$is_exist = $row[5];
			if($is_new == 1 || $is_edit == 1)
			{
				$id_user = $row[1];
				
				//show results code
				
				$query = "";
				if($is_new == 1) 
				{
					$query = $query . "offers.IsNew=1";
					if($is_edit == 1)
					{
						$query = $query . " or ";
					}
				}
				if($is_edit == 1) 
				{
					$query = $query . "offers.IsEdit=1";
				}
				
				$offer_array = makeOfferMessages($dblink, $id_whitelist, $query);
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
						if($is_accept_base_button == 0)
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
						
						//Выбивало ошибку, что не может отправить агенту. Возможно, удалил бота у себя. Проверить и фиксировать
						try{
							$bot->sendMessage($id_user, $offer->getMessage(), null, false, null, $keyboard_inline);
						}	
						catch (Exception $e)
						{
							break;
						}
					}
					
					//Выбивало ошибку, что не может отправить агенту. Возможно, удалил бота у себя. Проверить и фиксировать
					try{
						$bot->sendMessage($id_user, declOfNum($count_offer_array,array('объект пришел','объекта пришло','объектов пришло')) . " за последние пару минут.", null, false, null, $keyboard);
						if($is_exist == 0){
							$query = "update telegram_users set IsExist=1 where telegram_users.Id_whitelist_user=" . $id_whitelist . ";";
							mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
						}
					}	
					catch (Exception $e) 
					{
						$query = "update telegram_users set IsExist=0 where telegram_users.Id_whitelist_user=" . $id_whitelist . ";";
						mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
					}
					echo $is_exist . "<br>";
				}
			}
		}
	}
	mysqli_free_result($result);
}
mysqli_close($dblink);

echo date('l jS \of F Y h:i:s A');
?>