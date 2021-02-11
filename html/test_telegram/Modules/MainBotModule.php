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
		$current_turn_page = $whitelist_info->getTurnPage();
		$is_show_offers = true;
		switch($message_text){
			case "✅ Получать все объекты в уведомлениях":
			$is_show_offers = false;
			$this->switchIsGetEditOffers($whitelist_info, true);
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>и новые, и обновленные объекты</b>. Если вы снова хотите получать только новые объекты, нажмите на \"Присылать только новые объекты в уведомлениях\".", new DefaultBotKeyboard(true));
			break;
			case "❕ Присылать только новые объекты в уведомлениях":
			case " Присылать только новые объекты в уведомлениях":
			$is_show_offers = false;
			$this->switchIsGetEditOffers($whitelist_info, false);
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>только новые объекты</b>. Если вы снова хотите получать обновленные объекты, нажмите на \"Получать все объекты в уведомлениях\".", new DefaultBotKeyboard(false));
			break;
			
		}
		if($is_show_offers){
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Привет, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
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