<?php
$telegram_dir = explode('Modules',__DIR__)[0];
require_once "BotModule.php";
require_once $telegram_dir . "Keyboards/BotKeyboard.php";
require_once $telegram_dir . "Keyboards/KeyboardButton.php";

class SomeBotModule extends BotModule{
	private $love_array;
	private $inline_keyboard;
	private $default_keyboard;
	
	public function __construct($main_bot){
		parent::__construct($main_bot);
		$this->love_array = array(
			'Люблю тебя, счастье моё!❤️',
			'Радость моя, мне так хорошо с тобой 😘',
			'Любимая моя) Хочу тебя обнять!',
			'Счастье моё! Радость моя! Любимая😍 Хорошая моя🥰',
			'Лови воздушный поцелуйчик!😊😘',
			'Я тебя кусь кусь кусь😼😉',
			'Обнимашки целовашки☺️',
			'Моя умничка, люблю тебя 😘',
			'Как здорово, что ты есть у меня!❤️',
			'Всё будет хорошо, любимая!😊',
			'Ты ж моя сдобная булочка 🥯😘',
			'Ты ж моя мать крысек!🐭🥰'
			); 
		$this->default_keyboard = new BotKeyboard(1);
		$this->default_keyboard->addButton(new KeyboardButton("Цём 💋"));
		
		$this->inline_keyboard = new BotKeyboard(1);
		$button = new KeyboardButton("Получить ещё комплимент!");
		$button->addData("callback_data", "get");
		$this->inline_keyboard->addButton($button);
	}
	
	protected function forMessages($request_info, $whitelist_info){
		if(preg_match('/\/key(board)?/',$this->main_bot->getMessageText($request_info->getMessageData()))){
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Возвращаю клавиатуру", $this->default_keyboard);
		}
		
		$this->main_bot->sendMessage($request_info->getIdTelegram(), $this->randLoveArray(), $this->inline_keyboard, true);
	}
	
	protected function forCallbacks($request_info, $whitelist_info){
		$this->main_bot->editMessage($request_info->getIdTelegram(), $request_info->getMessageData(), $this->randLoveArray(), $this->inline_keyboard, true);
	}
	
	private function randLoveArray(){
		return $this->love_array[mt_rand(0, count($this->love_array)-1)];
	}
}

?>