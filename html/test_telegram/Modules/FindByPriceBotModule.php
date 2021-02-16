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
		
		$this->default_keyboard = new BotKeyboard(2);
		$this->default_keyboard->addButton(new KeyboardButton("Сбросить цену"), 0);
		$this->default_keyboard->addButton(new KeyboardButton("Отмена"), 1);
	}
	
	protected function forMessages($request_info, $whitelist_info){
		if($request_info->getModeParam() == 0){
			$this->changeModeParam($request_info, $whitelist_info, 1);
		}
		else{
			$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
			if(preg_match('/^\d+$/', $message_text)){
				$this->changeFindByPrice($message_text, $whitelist_info);
				$this->exitModule($request_info, $whitelist_info);
			}
			else if($message_text == "Сбросить цену"){
				$this->changeFindByPrice(0, $whitelist_info);
				$this->exitModule($request_info, $whitelist_info);
			}
			else if($message_text == "Отмена") $this->exitModule($request_info, $whitelist_info);
			else $this->main_bot->sendMessage($request_info->getIdTelegram(), "Введено неверное значение!");
		}
		if(!$this->lock) $this->main_bot->sendMessage($request_info->getIdTelegram(), "Введите цену <u>без пробелов</u>. Бот найдёт и отобразит объекты с такой же ценой или ниже. Чтобы убрать фильтр по цене, <b>введите 0</b> или нажмите кнопку <b>Сбросить цену</b>", $this->default_keyboard);
		
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		
	}
	
	private function exitModule($request_info, $whitelist_info){
		$this->lock = true;
		$this->resetToDefaultMode($request_info, $whitelist_info, true);
	}
	
	private function changeFindByPrice($value, $whitelist_info){
		$query = "update bind_whitelist_distr_flats set Price_lower_than=". $value ." where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
}

?>