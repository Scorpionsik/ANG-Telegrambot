<?php
require_once "BotModule.php";

class RegisterBotModule extends BotModule{

	//ищет признаки номера телефона в сообщении
	private $regex_check_phones = "/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/i";

	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function StartMethod($request_info, $whitelist_info = null){
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		if(preg_match($regex_check_phones, $message_text)){

		}
		else $this->sendErrorMessage($request_info->getIdTelegram(), $message_text);
	}

	private function sendErrorMessage($id_telegram, $message_text){
		if($message_text != '/start') $this->main_bot->sendMessage($id_telegram, '¬веден некорректный номер!');

	}
}

?>