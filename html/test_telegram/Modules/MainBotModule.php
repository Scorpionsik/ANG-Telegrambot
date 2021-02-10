<?php
require_once "BotModule.php";

class MainBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function forMessages($request_info, $whitelist_info){
		$this->main_bot->sendMessage($request_info->getIdTelegram(), "Привет, " . $whitelist_info->getUsername() . "!");
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
}

?>