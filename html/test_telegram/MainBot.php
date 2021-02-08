<?php 
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";


class MainBot{
	private $bot;
	private $db;

	//инициализация бота
	public function __construct($bot_token){
		include "connection_agent.php";
		$this->bot = new \TelegramBot\Api\Client($bot_token);
		$this->db = new mysqli($host, $dblogin, $dbpassw, $database);

		$this->bot->command('start', function ($message) {
			$request_info = $this->getFullInfo(new RequestInfo($message));
			$this->sendMessage($request_info->getIdTelegram(), $request_info->getIdTelegram() . ": " . $request_info->getIdWhitelist() . ", " . $request_info->getModeValue());
		});

		$this->bot->command('help', function ($message) {
			$this->commandHelp($message->getChat()->getId());
		});

		$this->bot->run();

		
	}

	function __destruct(){
		$this->dispose();
	}

	//отправка сообщений
	public function sendMessage($id_telegram, $message_text){
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML');
	}

	//Получает RequestInfo с информацией о id_whitelist пользователя; если пользователя не было в базе данных, добавляет его
	private function getFullInfo($request_info){
		$return = $request_info;
		$query = "SELECT * FROM telegram_users  where Id_telegram_user=". $request_info->getIdTelegram() .";";
		$result = mysqli_query($this->db, $query) or die("Ошибка " . mysqli_error($this->db));
		if($result){
			$row_check = mysqli_num_rows($result);
			if($row_check == 0){
				$query = "INSERT INTO telegram_users (Id_telegram_user) values (". $request_info->getIdTelegram() .");";
				mysqli_query($this->db, $query) or die("Ошибка " . mysqli_error($this->db));
			}
			else{
				$row = mysqli_fetch_row($result);
				if($row){
					$return = new RequestInfo($request_info, $row[1], $row[4]);
				}
				
			}
			mysqli_free_result($result);
		}
		return $return;
	}

	//выводит сообщение о помощи
	private function commandHelp($id_telegram){
		$this->sendMessage($id_telegram, 'help <b>me</b>');
	}

	private function dispose(){
		mysqli_close($this->db);
	}
}

?>