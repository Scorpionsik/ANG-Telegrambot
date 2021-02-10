<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class DefaultBotKeyboard extends BotKeyboard{
	public function __construct($is_get_only_new_offers = false){
		parent::__construct(2);
		
		$button = new KeyboardButton();
		$button->addData("text", "📥 Получить всё за последние 3 дня");
		$this->addButton($button, 1);
		
		$button = new KeyboardButton();
		if($is_get_only_new_offers) $button->addData("text", "✅ Получать все объекты в уведомлениях");
		else $button->addData("text", "❕ Присылать только новые объекты в уведомлениях");
		$this->addButton($button, 0);
		
		$button = new KeyboardButton();
		$button->addData("text", "🔎 Поиск по цене");
		$this->addButton($button, 0);
	}
}

?>