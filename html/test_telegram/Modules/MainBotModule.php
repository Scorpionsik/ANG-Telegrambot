<?php
include "BotModule.php";

class MainBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	public function Start($request_info){
		$this->main_bot->sendMessage($request_info->getIdTelegram(), 'MainModule');
	}
}

?>