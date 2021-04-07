<?php
require_once "MainBotModule.php";

class SearchInOffersBotModule extends MainBotModule{
	
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}
	
	protected function forMessages($request_info, $whitelist_info){
		$this->main_bot->sendMessage($request_info->getIdTelegram(), "Hello");
		//$this->resetToDefaultMode($request_info, $whitelist_info);
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
}

?>