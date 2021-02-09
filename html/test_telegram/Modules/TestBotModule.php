<?php
require_once "BotModule.php";

class TestBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	public function Start($request_info){
		$this->main_bot->callAdmin('Check');
	}
}

?>