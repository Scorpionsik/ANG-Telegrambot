<?php
$keyboard_dir = explode('test_telegram',__DIR__)[0] . 'test_telegram/Keyboards';
require_once $keyboard_dir . "/BotKeyboard.php";
require_once $keyboard_dir . "/KeyboardButton.php";
require_once "BotModule.php";

class TestBotModule extends BotModule{
	private $tmp_keyboard;
	
	public function __construct($main_bot){
		parent::__construct($main_bot);
		
		$this->tmp_keyboard = new BotKeyboard(1);
		$button = new KeyboardButton("Button");
		$this->tmp_keyboard->addButton($button, 0);
	}

	protected function forMessages($request_info, $whitelist_info){
		$this->main_bot->sendMessage($request_info->getIdtelegram(), 'Check', $this->tmp_keyboard);
	}
	protected function forCallbacks($request_info, $whitelist_info){
		$this->forMessages($request_info, $whitelist_info);
	}
}

?>