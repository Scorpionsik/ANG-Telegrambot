<?php
$keyboard_dir = explode('Modules',__DIR__)[0] . 'Keyboards';
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
		if($is_show_offers){
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Добро пожаловать, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
		}
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
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