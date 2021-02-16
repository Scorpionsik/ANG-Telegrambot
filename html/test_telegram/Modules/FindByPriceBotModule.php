<?php
$telegram_dir = explode('Modules',__DIR__)[0];
require_once "BotModule.php";
require_once $telegram_dir . "Keyboards/BotKeyboard.php";
require_once $telegram_dir . "Keyboards/KeyboardButton.php";

class FindByPriceBotModule extends BotModule{
	private $lock = false;
	private $default_keyboard;
	
	public function __construct($main_bot){
		parent::__construct($main_bot);
		
		$this->default_keyboard = new BotKeyboard(1);
		$button = new KeyboardButton("Отмена");
		$this->default_keyboard->addButton($button);
	}
	
	protected function forMessages($request_info, $whitelist_info){
		if($request_info->getModeParam() == 0){
			$this->main_bot->changeMode($request_info, $whitelist_info, 1, 1, false);
		}
		else{
			$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
			if(preg_match('/^\d+$/', $message_text)){
				$query = "update bind_whitelist_distr_flats set Price_lower_than=". $message_text ." where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
				$this->main_bot->getRequestResult($query);
				$this->lock = true;
				$this->resetToDefaultMode($request_info, $whitelist_info, true);
			}
			else $this->main_bot->sendMessage($request_info->getIdTelegram(), "Введено неверное значение!");
		}
		if(!$this->lock) $this->main_bot->sendMessage($request_info->getIdTelegram(), "Введите цену <u>без пробелов</u>. Бот найдёт и отобразит объекты с такой же ценой или ниже. Чтобы убрать фильтр по цене, <b>введите 0</b>.", $this->default_keyboard);
		
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
}

?>