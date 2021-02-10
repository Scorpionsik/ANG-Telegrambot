<?php
$keyboard_dir = explode('test_telegram',__DIR__)[0] . 'test_telegram/Keyboards';
require_once $keyboard_dir . "/DefaultBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function forMessages($request_info, $whitelist_info){
		$this->main_bot->sendMessage($request_info->getIdTelegram(), "Привет, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard());
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
}

?>