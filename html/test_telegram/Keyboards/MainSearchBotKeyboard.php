<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class MainSearchBotKeyboard extends BotKeyboard{
	public function __construct($is_get_edit_offers = true){
		parent::__construct(1);
		
		$button = new KeyboardButton("Отменить поиск");
		$this->addButton($button);
	}
}

?>