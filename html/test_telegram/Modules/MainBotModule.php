<?php
$keyboard_dir = explode('test_telegram',__DIR__)[0] . 'test_telegram/Keyboards';
require_once $keyboard_dir . "/DefaultBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function forMessages($request_info, $whitelist_info){
		
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		$is_show_offers = true;
		$current_turn_page = $whitelist_info->getTurnPage();
		$current_keyboard = new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers());
		$this->main_bot->callAdmin($message_text);
		if(preg_match('/уведомл/',$message_text)){
			if(preg_match('/Присылать только/', $msg_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, false);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>только новые объекты</b>. Если вы снова хотите получать обновленные объекты, нажмите на \"Получать все объекты в уведомлениях\".", new DefaultBotKeyboard(false));
			}
			else if(preg_match('/Получать/', $msg_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, true);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>и новые, и обновленные объекты</b>. Если вы снова хотите получать только новые объекты, нажмите на \"Присылать только новые объекты в уведомлениях\".", new DefaultBotKeyboard(true));
			}
		}
		else{
			//переключить на модуль выбора максимальной/минимальной цены
			if(preg_match('/Поиск по цене/', $message_text)){
				
			}
			//перелистнуть страницу
			else if(preg_match('/^\d+$/', $message_text)){
				
			}
			//найти в базе данных по коду
			else if(preg_match('/^\d+\/\d+$/', $message_text)){
				
			}
		}
		if($is_show_offers){
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Привет, " . $whitelist_info->getUsername() . "!", $current_keyboard);
		}
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
	
	private function switchIsGetEditOffers($whitelist_info, $value){
		$query = "update white_list set Is_get_edit_offers=${value} where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
}

?>