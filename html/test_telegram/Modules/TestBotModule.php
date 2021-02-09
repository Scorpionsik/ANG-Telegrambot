<?php
require_once "BotModule.php";

class TestBotModule extends BotModule{
	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function StartMethod($request_info, $whitelist_info){
		$this->main_bot->callAdmin('Check');
	}
}

?>