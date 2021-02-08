<?php 

namespace ANGBot;

$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";

class MainBot{
	private $bot;
	//private $db;

	public function __construct($bot_token){
		$this->bot = new \TelegramBot\Api\Client($bot_token);

		$this->bot->command('help', function ($message) {
			$id_user = $message->getChat()->getId();
			$error_id_user = $id_user;
			$this->sendMessage($id_user, 'help');
		});
	}

	public function sendMessage($id_telegram, $message_text){
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML');
	}

	public function run(){
		$this->bot->run();
	}
}


?>