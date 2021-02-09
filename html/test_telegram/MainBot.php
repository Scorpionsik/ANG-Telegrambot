<?php 
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";
include "WhitelistInfo.php";
include __DIR__ . "/Modules/MainBotModule.php";
include __DIR__ . "/Modules/RegisterBotModule.php";
include __DIR__ . "/Modules/TestBotModule.php";


class MainBot{
	private $bot;
	private $db;
	private $id_admin = 780925203; //id телеграма админа

	//инициализация бота
	public function __construct($bot_token){
		include "connection_agent.php";
		$this->db = new mysqli($host, $dblogin, $dbpassw, $database);

		$this->bot = new \TelegramBot\Api\Client($bot_token);
		/*
		$this->bot->command('test', function ($message) {
			$request_info = $this->getFullRequestInfo(new RequestInfo($message));
			$this->sendMessage($request_info->getIdTelegram(), $request_info->getIdTelegram() . ": " . $request_info->getIdWhitelist() . ", " . $request_info->getModeValue());
		});*/

		$this->bot->command('help', function ($message) {
			$this->commandHelp($message->getChat()->getId());
		});

		$this->bot->on(function ($Update) {
			$this->distribute($this->getFullRequestInfo(new RequestInfo($Update)));
		}, function ($Update){
			return true;
		});

		$this->bot->run();
	}

	//очистка данных
	function __destruct(){
		$this->dispose();
	}

	private function distribute($request_info, $whitelist_info = null){
		$module = null;
		if(is_null($request_info->getIdWhitelist())){
			$module = new RegisterBotModule($this);
		}
		else{
			if(is_null($whitelist_info)){
				$whitelist_info = $this->getFullWhitelistInfo($request_info);
			}

			switch($request_info->getModeValue()){
				//изменение максимальной цены для агентов
				case 1:
					$module = new TestBotModule($this);
				break;
				//стандартный режим работы бота
				default:
					$module = new MainBotModule($this);
				break;
			}
		}
		if(!is_null($module)) $module->Start($request_info, $whitelist_info);
	}

	//отправка сообщений в телеграм-чат
	public function sendMessage($id_telegram, $message_text){
		//$this->bot->sendMessage($id_telegram, mb_convert_encoding($message_text, "UTF-8", "auto"), 'HTML');
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML');
	}

	public function getMessageText($message_data){
		return htmlentities(mysqli_real_escape_string($this->db, $message_data->getText()));
	}

	//отправка сообщения админу
	public function callAdmin($message_text){
		$this->bot->sendMessage($this->id_admin, $message_text, 'HTML');
	}

	//отправка ошибки админу
	public function sendException($exception, $request_info, $whitelist_info){
		$this->callAdmin("<b><u>Ошибка</u></b>, Id_whitelist: " . $request_info->getIdWhitelist());
		$this->callAdmin($exception->getMessage());
		$this->callAdmin($exception->getFile() . ", строка " . $exception->getLine());
		$this->callAdmin($exception->getTraceAsString());
	}

	//получить результат запроса из базы данных
	public function getRequestResult($query){
		$result = mysqli_query($this->db, $query) or die("Ошибка " . mysqli_error($this->db));
		return $result;
	}

	private function getFullWhitelistInfo($request_info){
		$id_whitelist = $request_info->getIdWhitelist();
		$return = null;
		if(!is_null($id_whitelist)){
			$query = "SELECT * FROM white_list WHERE id_whitelist_user=${id_whitelist};";
			$result = $this->getRequestResult($query);
			if($result){
				$row_check = mysqli_num_rows($result);
				if($row_check > 0){
					$row = mysqli_fetch_row($result);
					$return = new WhitelistInfo($id_whitelist, $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8]);
				}
			}
		}
		return $return;
	}

	//Получает RequestInfo с информацией о id_whitelist пользователя; если пользователя не было в базе данных, добавляет его
	private function getFullRequestInfo($request_info){
		$return = $request_info;
		$query = "SELECT * FROM telegram_users WHERE Id_telegram_user=". $request_info->getIdTelegram() .";";
		$result = $this->getRequestResult($query);
		if($result)
		{
			$row_check = mysqli_num_rows($result);
			if($row_check == 0){
				$query = "INSERT INTO telegram_users (Id_telegram_user) VALUES (". $request_info->getIdTelegram() .");";
				$this->getRequestResult($query);
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

	//закрывает подключение к базе данных
	private function dispose(){
		mysqli_close($this->db);
	}
}

?>