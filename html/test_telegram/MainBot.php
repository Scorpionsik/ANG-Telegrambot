<?php 
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";
include "WhitelistInfo.php";
include __DIR__ . "/Modules/MainBotModule.php";
include __DIR__ . "/Modules/RegisterBotModule.php";
include __DIR__ . "/Modules/TestBotModule.php";
require_once __DIR__ . "/Keyboards/BotKeyboard.php";

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
			$this->deleteMessage($message);
			$this->commandHelp($message->getChat()->getId());
		});

		$this->bot->on(function ($Update) {
			$request_info = $this->getFullRequestInfo(new RequestInfo($Update));
			$this->distribute($request_info);
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
			//проверяем, забанен ли пользователь
			if(!is_null($whitelist_info) && !$whitelist_info->getIsBanned()){
				switch($request_info->getModeValue()){
					//изменение максимальной цены для агентов
					case 1:
						$module = new TestBotModule($this);
					break;
					//стандартный режим работы бота
					case 0:
					default:
						$module = new MainBotModule($this);
					break;
				}
			}
			else $this->sendMessageForBanned($request_info->getIdTelegram());
		}
		if(!is_null($module)) $module->start($request_info, $whitelist_info);
	}

	//отправка сообщений в телеграм-чат
	public function sendMessage($id_telegram, $message_text, $bot_keyboard = null, $is_inline = false){
		$keyboard = null;
		if(!is_null($bot_keyboard)){
			if($is_inline){
				$keyboard = $this->getInlineKeyboard($bot_keyboard);
			}
			else{
				$keyboard = $this->getReplyKeyboard($bot_keyboard);
			}
		}
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML', true, null, $keyboard);
	}
	
	public function sendPhoto($id_telegram, $photo_link, $description){
		$this->bot->sendPhoto($id_telegram, $photo_link, $description, null, null, false, "HTML");
	}
	
	public function editMessage($id_telegram, $message_data, $new_message_text, $bot_keyboard = null, $is_inline = true){
		$keyboard = null;
		if(!is_null($bot_keyboard)){
			if($is_inline){
				$keyboard = $this->getInlineKeyboard($bot_keyboard);
			}
			else{
				$keyboard = $this->getReplyKeyboard($bot_keyboard);
			}
		}
		$this->callAdmin($id_telegram . " " . $message_data->getMessageId() . " " $new_message_text);
		$bot->editMessageText($id_telegram, $message_data->getMessageId(), $new_message_text, "HTML", false, $keyboard);
	}
	
	public function deleteMessage($message_data){
		$this->bot->deleteMessage($message_data->getChat()->getId(), $message_data->getMessageId());
	}
	
	private function sendMessageForBanned($id_telegram){
		$this->bot->sendMessage($id_telegram, 'У нас технические неполадки-шоколадки!😱🍫 Но не переживайте, скоро всё заработает. Хорошего вам настроения и удачного дня!😊');
	}

	public function getMessageText($message_data){
		return htmlentities(mysqli_real_escape_string($this->db, $message_data->getText()));
	}

	//отправка сообщения админу
	public function callAdmin($message_text){
		$this->bot->sendMessage($this->id_admin, $message_text, "HTML");
	}
	
	public function sendAdminContact($id_telegram){
		$query = "SELECT white_list.Phonenumber, white_list.Username FROM white_list JOIN telegram_users USING (Id_whitelist_user) WHERE telegram_users.Id_telegram_user = ". $this->id_admin .";";
		$result = $this->getRequestResult($query);
		if($result){
			$row_check = mysqli_num_rows($result);
			if($row_check > 0){
				$row = mysqli_fetch_row($result);
				$this->bot->sendContact($id_telegram, $row[0], $row[1]);
			}
			mysqli_free_result($result);
		}
	}

	//отправка ошибки админу
	public function sendException($exception, $request_info, $whitelist_info){
		$this->callAdmin("<b><u>Ошибка</u></b>\n<b>Id_whitelist:</b> " . $request_info->getIdWhitelist() . "\n<b>Username:</b> " . $whitelist_info->getUsername());
		$this->callAdmin($exception->getMessage());
		$this->callAdmin($exception->getFile() . ", строка " . $exception->getLine());
		$this->callAdmin($exception->getTraceAsString());
	}

	//получить результат запроса из базы данных
	public function getRequestResult($query){
		$result = mysqli_query($this->db, $query) or die("Ошибка " . mysqli_error($this->db));
		return $result;
	}

	private function getReplyKeyboard($bot_keyboard){
		$return = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($bot_keyboard->getKeyboardArray(), false, true);
		return $return;
	}
	
	private function getInlineKeyboard($bot_keyboard){
		$return = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($bot_keyboard->getKeyboardArray());
		return $return;
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
				mysqli_free_result($result);
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
					$return = new RequestInfo($request_info, $row[1], $row[4], $row[5]);
				}
			}
			mysqli_free_result($result);
		}
		return $return;
	}

	//выводит сообщение о помощи
	private function commandHelp($id_telegram){
		$this->bot->sendMessage($id_telegram, "Если у вас возникли вопросы или ошибки при работе с ботом, напишите мне и подробно изложите суть вопроса или проблемы.");
		$this->bot->sendMessage($id_telegram, "Хорошего дня и отличного настроения, будьте здоровы!");
		$this->sendAdminContact($id_telegram);
	}

	//закрывает подключение к базе данных
	private function dispose(){
		mysqli_close($this->db);
	}
}

?>