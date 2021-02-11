<?php
$keyboard_dir = explode('test_telegram',__DIR__)[0] . 'test_telegram/Keyboards';
require_once $keyboard_dir . "/DefaultBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function forMessages($request_info, $whitelist_info){
		/*
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		$current_turn_page = $whitelist_info->getTurnPage();
		$is_show_offers = true;
		switch($message_text){
			case "✅ Получать все объекты в уведомлениях":
			$is_show_offers = false;
			break;
			
			case "":
			
			break;
		}*/
		$this->main_bot->sendMessage($request_info->getIdTelegram(), "Привет, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
}

?>