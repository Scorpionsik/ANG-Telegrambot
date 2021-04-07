<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class DefaultBotKeyboard extends BotKeyboard{
	public function __construct($is_get_edit_offers = true){
		parent::__construct(3);
		
		$button = new KeyboardButton("📥 Получить всё за последние 3 дня");
		$this->addButton($button, 0);
		
		if(!$is_get_edit_offers) $button = new KeyboardButton("✅ Получать все объекты в уведомлениях");
		else $button = new KeyboardButton("❕ Присылать только новые объекты в уведомлениях");
		$this->addButton($button, 1);
		
		$button = new KeyboardButton("🔎 Поиск по цене");
		$this->addButton($button, 1);
		
		$button = new KeyboardButton("Поиск в базе...");
		$this->addButton($button, 2);
	}
}

?>