<?php
$telegram_dir = explode('Modules',__DIR__)[0];
require_once $telegram_dir . "Functions.php";
require_once $telegram_dir . "Offer.php";
require_once $telegram_dir . "Keyboards/DefaultBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineOfferBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineCountPagesBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	//максимальное количество объявлений на 1 странице
	private $quantity_per_page = 10;
	private $functions;
	public function __construct($main_bot){
		parent::__construct($main_bot);
		$this->functions = new Functions();
	}

	/* Обработка вводимых сообщений*/
	protected function forMessages($request_info, $whitelist_info){
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		$is_show_offers = true;
		$current_turn_page = $whitelist_info->getTurnPage();
		if(preg_match('/уведомл/',$message_text)){
			if(preg_match('/Присылать только/', $message_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, 0);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>только новые объекты</b>. Если вы снова хотите получать обновленные объекты, нажмите на \"Получать все объекты в уведомлениях\".", new DefaultBotKeyboard(false));
			}
			else if(preg_match('/Получать/', $message_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, 1);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>и новые, и обновленные объекты</b>. Если вы снова хотите получать только новые объекты, нажмите на \"Присылать только новые объекты в уведомлениях\".", new DefaultBotKeyboard(true));
			}
		}
		else{
			//переключить на модуль выбора максимальной/минимальной цены
			if(preg_match('/Поиск по цене/', $message_text)){
				$is_show_offers = false;
				//code here
			}
			//перелистнуть страницу
			else if(preg_match('/^\d+$/', $message_text)){
				$current_turn_page=$message_text;
				$this->turnThePage($whitelist_info, $message_text);
			}
			//найти в базе данных по коду
			else if(preg_match('/^\d+\/\d+$/', $message_text)){
				$is_show_offers = false;
				//code here
			}
		}
		//показ объектов
		if($is_show_offers){
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Добро пожаловать, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
				$this->showOffersOnPage($current_turn_page, $request_info, $whitelist_info);
				$this->setOffersPress($request_info, $whitelist_info);
			}
			//информации нет
			else{
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Информации по вашему району на данный момент нет, попробуйте позже!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
			}
	}
	/* конец Обработка вводимых сообщений*/
		
	/* Обработка инлайн запросов*/
	protected function forCallbacks($request_info, $whitelist_info){
		//перелистнуть страницу
		if(preg_match('/^\d+$/', $request_info->getCallbackData())){
			$this->turnThePage($whitelist_info, $request_info->getCallbackData());
			$this->showOffersOnPage($request_info->getCallbackData(), $request_info, $whitelist_info);
			$this->setOffersPress($request_info, $whitelist_info);
		}
		//отобразить телефоны
		else if(preg_match('/^\d+\/\d+$/', $request_info->getCallbackData())){
			$text_title = "Контакты объекта";
			//проверка, изменялся ли уже текст в сообщении
			if(!preg_match("/Контакты объекта/", $request_info->getMessageData()->getText())){
				$text_title = "➖➖➖<b>${text_title}</b>➖➖➖";
				$query = "SELECT flat_owners.User_entity_id, flat_owners.Username, flat_owners.Agency , owner_phones.Phonenumber, offers.Entity_id, offers.Image_url, localities.Locality_name, offers.Address, offers.House_number, flat_types.Typename, types.Type_name, flat_owners.IsExclusive FROM flat_owners LEFT JOIN offers USING (User_entity_id) LEFT JOIN owner_phones USING (User_entity_id) LEFT JOIN localities USING (Id_locality) LEFT JOIN flat_types USING (Id_flat_type) LEFT JOIN types USING (Id_type) WHERE offers.Internal_id='" . $request_info->getCallbackData() . "';";
				$result = $this->main_bot->getRequestResult($query);
				if($result){
					$row_check = mysqli_num_rows($result);
					if($row_check > 0){ 
						$inline_offer_keyboard = null;
						$offer = null;
						$text_body = "\n";
						//собираем текст сообщения и необходимую информацию
						for($i = 0; $i < $row_check; $i++){
							$row = mysqli_fetch_row($result);
							
							if($i == 0){
								$is_exclusive = $row[11];
								$id_user = $row[0];
								$username = $row[1];
								$agency = $row[2];
								$id_database = $row[4];
								$image_url = $row[5];
								$city = $row[6];
								$street = $row[7];
								$house_num = $row[8];
								$flat_type = $row[9];
								$offer_type = $row[10];
								
								
								//собираем клавиатуру
								$offer = new Offer("", $request_info->getCallbackData(), $id_database, $image_url, $this->functions->getSiteUrl($offer_type, $flat_type), $city, $street, $house_num, $id_user);
								$inline_offer_keyboard = new InlineOfferBotKeyboard($offer, $whitelist_info, false);
								
								//проверка на эксклюзивы
								if($is_exclusive == 1){
									$text_body = "\nКонтакты скрыты";
									break;
								}
								
								//пишем имя агента
								if(!is_null($username) && $username != ""){
									foreach(preg_split("/;/", $username) as $newname)
										{
											$text_body = $text_body . "💁‍♂️ ${newname}\r\n";
										}
								}
								else $text_body = $text_body . "🤷 Имя не указано\r\n";
								//пишем агенство
								if(!is_null($agency) && $agency != "") $text_body = $text_body . "📎 Агенство ${agency}\r\n";
							}
							//пишем телефоны
							$phonenumber = $row[3];
							$text_body = $text_body . preg_replace("/(0\d{2})(\d{3})(\d{2})(\d{2})/", "$1 $2 $3 $4", $phonenumber) . "\r\n";
						}
						//редактируем
						$this->main_bot->editMessage($request_info->getIdTelegram(), $request_info->getMessageData(), $text_title . $text_body, $inline_offer_keyboard);
						if(!is_null($offer)) $this->setPhonesPress($offer, $whitelist_info);
					}
					//если информации о пользователе нет в базе
					else{
						$this->main_bot->editMessage($request_info->getIdTelegram(), $request_info->getMessageData(), "Данные о владельце не найдены.");
					}
					mysqli_free_result($result);
				}
			}
		}
		else{
			$this->main_bot->callAdmin("MainBotModule - Callback - " . $request_info->getCallbackData() . " - " . $whitelist_info->getIdWhitelist() . " " .$whitelist_info->getUsername());
		}
	}
	/* конец Обработка инлайн запросов*/
	
	private function showOffersOnPage($current_turn_page, $request_info, $whitelist_info){
		$offers_array = $this->getOffers("WHERE bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . " ORDER BY offers.Update_timestamp desc;");
		$count_offers_array = count($offers_array);
		
		//есть информация для показа
		if($count_offers_array > 0){
			//вычисляем общее количество страниц
			$total_pages = 1;
			if($count_offers_array > $this->quantity_per_page){
				$total_pages = ceil($count_offers_array / $this->quantity_per_page);
				if($total_pages < 1) $total_pages=1;
			}
			//проверяем на валидность выбранную страницу
			if($current_turn_page > $total_pages) $current_turn_page = $total_pages;
			else if ($current_turn_page < 1) $current_turn_page = 1;
			//вычисляем диапазон, который покажем агенту
			$start_index = ($this->quantity_per_page * ($current_turn_page - 1));
			$end_index = min($start_index + $this->quantity_per_page, $count_offers_array);
			
			//начало страницы
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Начало страницы ${current_turn_page} из ${total_pages}, " . $this->functions->declOfNum($end_index - $start_index, array('объект','объекта','объектов')));
			
			//показываем объявления
			for($i = $start_index; $i < $end_index; $i++){
				$inline_offer_keyboard = new InlineOfferBotKeyboard($offers_array[$i], $whitelist_info);
				//основное сообщение
				$this->main_bot->sendMessage($request_info->getIdTelegram(), $offers_array[$i]->getOfferDescription());
				//фотографии
				if(!is_null($offers_array[$i]->getImageUrl()) && $offers_array[$i]->getImageUrl() != ""){
					try{
						$this->main_bot->sendPhoto($request_info->getIdTelegram(), "https://an-gorod-image.com.ua/storage/uploads/preview/" . $offers_array[$i]->getImageUrl(), "<a href='https://angbots.ddns.net/image_ang/some_pic_get.php?entity=" . $offers_array[$i]->getIdOffer() . "'><b>Посмотреть все фотографии</b></a>");
					}
					catch(Exception $e){
					}
				}
				//место под телефоны и инлайн клаву
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Чтобы посмотреть контакты владельца объекта <b>". $offers_array[$i]->getIdOffer() ."</b>, нажмите на кнопку 'Телефоны' ниже.", $inline_offer_keyboard, true);
			}
			//конец страницы
			$inline_count_pages_keyboard = new InlineCountPagesBotKeyboard($current_turn_page, $total_pages);
			
			$end_page_text = "Всего " . $this->functions->declOfNum($count_offers_array, array('объект','объекта','объектов')) . " за последние 3 дня.";
			if($total_pages > 1) $end_page_text = "Конец страницы ${current_turn_page} из ${total_pages}, " . $this->functions->declOfNum($end_index - $start_index, array('объект','объекта','объектов')) . "\n\n" . $end_page_text;
			
			$this->main_bot->sendMessage($request_info->getIdTelegram(), $end_page_text, $inline_count_pages_keyboard, true);
		}
	}
	
	private function getOffers($where_query_part){
		$result = $this->main_bot->getRequestResult($this->functions->getSelectAndFromQueryPart() . $where_query_part);
		$offers_array = $this->functions->getOffersFromDBResult($result);
		mysqli_free_result($result);
		return $offers_array;
	}
	
	private function switchIsGetEditOffers($whitelist_info, $value){
		$query = "update white_list set Is_get_edit_offers=${value} where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
	
	private function turnThePage($whitelist_info, $page){
		$query = "update white_list set Turn_page=${page} where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
	
	private function setOffersPress($request_info, $whitelist_info){
		if(!$this->main_bot->checkIsIdAdmin($request_info)){
			$query = "insert into get_offers_press values(" . $whitelist_info->getIdWhitelist() . ", " . time() . ");";
			$this->main_bot->getRequestResult($query);
		}
	}
	
	private function setPhonesPress($offer, $request_info, $whitelist_info){
		if(!$this->main_bot->checkIsIdAdmin($request_info)){
			$query = "insert into agent_phone_press values (" . $whitelist_info->getIdWhitelist() . ", '" . $offer->getIdOffer() . "', " . $offer->getIdDatabase() .  "," . time() . ");";
			$this->main_bot->getRequestResult($query);
		}
	}
}

?>