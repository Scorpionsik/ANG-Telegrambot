<?php
$telegram_dir = explode('Modules',__DIR__)[0];
require_once $telegram_dir . "Keyboards/DefaultBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineOfferBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineCountPagesBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	//максимальное количество объявлений на 1 странице
	private $quantity_per_page = 10;
	private $telegram_dir = explode('Modules',__DIR__)[0];
	
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

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
			
			$offers_array = $this->getOffers("WHERE bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . " ORDER BY offers.Update_timestamp desc;");
			$count_offers_array = count($offers_array);
			
			//есть информация для показа
			if($count_offers_array > 0){
				include $this->telegram_dir . "Functions.php";
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
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Начало страницы ${current_turn_page} из ${total_pages}, " . declOfNum($end_index - $start_index, array('объект','объекта','объектов')));
				
				//показываем объявления
				for($i = $start_index; $i < $end_index; $i++){
					$inline_offer_keyboard = new InlineOfferBotKeyboard($offers_array[$i], $whitelist_info);
					//основное сообщение
					$this->main_bot->sendMessage($request_info->getIdTelegram(), $offers_array[$i]->getOfferDescription());
					//фотографии
					if(is_null($offers_array[$i]->getImageUrl()) && $offers_array[$i]->getImageUrl() != ""){
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
				
				$end_page_text = "Всего " . declOfNum($count_offers_array, array('объект','объекта','объектов')) . " за последние 3 дня.";
				if($total_pages > 1) $end_page_text = "Конец страницы ${current_turn_page} из ${total_pages}, " . declOfNum($end_index - $start_index, array('объект','объекта','объектов')) . $end_page_text;
				
				$this->main_bot->sendMessage($request_info->getIdTelegram(), $end_page_text, $inline_count_pages_keyboard, true);
			}
			//информации нет
			else{
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Информации по вашему району на данный момент нет, попробуйте позже!");
			}
		}
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		$this->main_bot->callAdmin("Test");
	}
	
	private function getOffers($where_query_part){
		include $this->telegram_dir . "Functions.php";
		$result = $this->main_bot->getRequestResult($select_and_from_query_part . $where_query_part);
		$offers_array = getOffersFromDBResult($result);
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
}

?>