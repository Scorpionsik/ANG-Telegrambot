<?php 
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";
include "WhitelistInfo.php";
include __DIR__ . "/Modules/RegisterBotModule.php";
include __DIR__ . "/Modules/MainBotModule.php";
include __DIR__ . "/Modules/FindByPriceBotModule.php";
include __DIR__ . "/Modules/SomeBotModule.php";
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

		$this->bot->command('help', function ($message) {
			$this->deleteMessage($message);
			$this->commandHelp($message->getChat()->getId());
		});

		$this->bot->on(function ($Update) {
			if(!is_null($Update)){
				try{
					$request_info = new RequestInfo($Update);
					$current_message_time = $request_info->getLastMessageDate();
					$request_info = $this->getFullRequestInfo($request_info);
					$time_currency = $current_message_time - $request_info->getLastMessageDate();
					//$this->callAdmin($time_currency . " : " . $request_info->getLastMessageDate() . " - ${current_message_time}");
					/*
					 * Именно эти проверки нужны для того, чтобы можно было редактировать уже присланные сообщения
					 * Иначе почти все инлайн запросы типа показа телефонов переставали работать
					 */
					if($time_currency > 1 || is_a($Update, '\TelegramBot\Api\Types\Update')){ 
					    $this->getRequestResult("update telegram_users set Last_message_date=${current_message_time} where Id_telegram_user=" . $request_info->getIdTelegram() . ";");
    					$this->distribute($request_info);
					}
				}
				catch(Exception $ex){
					$this->sendException($ex, null, null);
				}
			}
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
			if(!is_null($whitelist_info) && !$whitelist_info->getIsBanned() && !$whitelist_info->getIsLocked()){
				if($whitelist_info->getIdWhitelist() == 11){
					$module = new SomeBotModule($this);
				}
				else{
					switch($request_info->getModeValue()){
						//изменение максимальной цены для агентов
						case 1:
							$module = new FindByPriceBotModule($this);
						break;
						//стандартный режим работы бота
						case 0:
						default:
							$module = new MainBotModule($this);
						break;
					}
				}
			}
			else $this->sendMessageForBanned($request_info->getIdTelegram());
		}
		if(!is_null($module)) $module->start($request_info, $whitelist_info);
	}
	
	public function changeMode($request_info, $whitelist_info, $mode = 0, $mode_param = 0, $is_distribute = true){
		$new_request_info = $request_info;
		if($mode != $request_info->getModeValue() || $mode_param != $request_info->getModeParam()){
			$query = "update telegram_users set Mode=${mode}, Mode_param=${mode_param} where Id_telegram_user=" . $request_info->getIdTelegram() . ";";
			$this->getRequestResult($query);
			
			if($is_distribute) $new_request_info = new RequestInfo($request_info, $whitelist_info->getIdWhitelist(), $mode, $mode_param);
		}
		
		if($is_distribute) $this->distribute($new_request_info, $whitelist_info);
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
	    $this->bot->sendPhoto($id_telegram, $photo_link, $description, null, null, false, 'HTML');
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
		$this->bot->editMessageText($id_telegram, $message_data->getMessageId(), $new_message_text, "HTML", false, $keyboard);
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
	public function callAdmin($message_text, $is_html = true){
		$html = null;
		if($is_html) $html = "HTML";
		$this->bot->sendMessage($this->id_admin, $message_text, $html);
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
	
	public function checkIsIdAdmin($request_info){
		if($request_info->getIdTelegram() == $this->id_admin) return true;
		else return false;
	}

	//отправка ошибки админу
	public function sendException($exception, $request_info, $whitelist_info){
		$text = "<b><u>Ошибка</u></b>";
		if(!is_null($request_info) && !is_null($whitelist_info)){
			if(is_null($request_info->getIdWhitelist())){
				$text = $text . "\n<b>Id_telegram: </b> " . $request_info->getIdTelegram();
			}
			else{
				$text = $text . "\n<b>Id_whitelist:</b> " . $request_info->getIdWhitelist() . "\n<b>Username:</b> " . $whitelist_info->getUsername();
			}
		}
		
		$this->callAdmin($text);
		$this->callAdmin($exception->getMessage(), false);
		$this->callAdmin($exception->getFile() . ", строка " . $exception->getLine(), false);
		$this->callAdmin($exception->getTraceAsString(), false);
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
					$return = new WhitelistInfo($id_whitelist, $row[1], $row[2], $row[3], $row[8], $row[4], $row[5], $row[6], $row[7]);
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
				$return = new RequestInfo($request_info, null, 0, 0, 0);
			}
			else{
				$row = mysqli_fetch_row($result);
				if($row){
				    $return = new RequestInfo($request_info, $row[1], $row[4], $row[5], $row[6]);
				}
			}
			mysqli_free_result($result);
		}
		return $return;
	}

	//выводит сообщение о помощи
	private function commandHelp($id_telegram){
	    $this->bot->sendMessage($id_telegram, "https://telegra.ph/Gajd-po-poisku-v-telegram-bote---Bot-dlya-agentov-09-13");
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