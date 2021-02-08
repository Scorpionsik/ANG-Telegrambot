<?php 

namespace ANGBot;

$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";

class MainBot{
	private $bot;
	private $db;

	//инициализация бота
	public function __construct($bot_token){
		$this->bot = new \TelegramBot\Api\Client($bot_token);

		$this->bot->command('help', function ($message) {
			$this->commandHelp(new \ANGBot\RequestInfo($message->getChat()->getId(), $message));
		});

		$this->bot->run();
	}

	//отправка сообщений
	public function sendMessage($id_telegram, $message_text){
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML');
	}

	//логика команды help
	private function commandHelp($request_info){
		$this->sendMessage($request_info->getIdTelegram(), 'help');
	}
}


?>