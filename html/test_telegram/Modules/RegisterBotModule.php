<?php
require_once "BotModule.php";

class RegisterBotModule extends BotModule{
	//
	private $default_error_text = "Введён некорректный номер!";
	//ищет признаки номера телефона в сообщении
	private $regex_check_phones = "/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/i";
	//
	private $regex_check_command_in_text = "/^\s*\/start/i";
	//
	private $regex_clear_all_not_digit = "/\D/i";
	//
	private $regex_clear_global_id_in_phone = "/^[38]{0,2}/i";

	public function __construct($main_bot){
		parent::__construct($main_bot);
	}

	protected function StartMethod($request_info, $whitelist_info = null){
		//получаем ввод
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		//если ввод валидный и мы получили телефон
		if(preg_match($regex_check_phones, $message_text)){
			//чистим введенный телефон
			$input_phone = preg_replace($regex_clear_all_not_digit, "", $message_text);
			$input_phone = preg_replace($regex_clear_global_id_in_phone, "", $input_phone);
			
			//ищем телефон в white_list
			$query = "SELECT * FROM white_list where Phonenumber like ('%${input_phone}%');";
			$result = $this->main_bot->getRequestResult($query);
			if($result){
				$row_check = mysqli_num_rows($result);
				//телефон найден, конфликтов нет
				if($row_check == 1){
					$row = mysqli_fetch_row($result);
					if($row){
						$id_whitelist = $row[0];
						$username = $row[2];
						//проверяем, подключен ли уже такой пользователь в telegram_users
						$query = "SELECT * FROM telegram_users where Id_whitelist_user=${id_whitelist};";
						$result = $this->main_bot->getRequestResult($query);
						if($result){
							$row_check = mysqli_num_rows($result);
							//такой пользователь уже подключен
							if($row_check > 0){
								$this->sendErrorMessage($request_info->getIdTelegram());
							}
							//пользователь ещё не подключен
							else{
								//здесь обновляем таблицу telegram_users, указывая для id_telegram его id_whitelist и дату, когда случилось это знаменательное событие
								$query = "UPDATE telegram_users SET Id_whitelist_user=${id_whitelist}, Register_date=" . time() . " where Id_telegram_user=" . $request_info->getIdTelegram() . ";";
								$this->main_bot->getRequestResult($query);
								$this->sendAccessMessage($request_info->getIdTelegram(), $username);
							}
						}
					}
				}
				//телефон найден, один и тот же номер есть у разных агентов в white_list
				else if($row_check > 1){
					$this->sendErrorMessage($request_info->getIdTelegram(), "Похоже, что номер (${input_phone}) уже привязан к другому человеку. Если это точно ваш номер - напишите мне сюда (Телеграм):");
					$this->main_bot->sendAdminContact();
					
					$this->main_bot->callAdmin("Внимание, есть повторный номер (${input_phone}) у:");
					for($i = 0; $i < $row_check; $i++){
						$row = mysqli_fetch_row($result);
						$this->main_bot->callAdmin($row[0] . " - " . $row[2]);
					}
				}
				//телефон не найден
				else{
					$this->sendErrorMessage($request_info->getIdTelegram());
				}
				
			}
		}
		//если ввод невалидный, вывод ошибки
		else {
			$error_text = $default_error_text;
			if(preg_match($regex_check_command_in_text, $message_text) $error_text = "Здравствуйте!";
			$this->sendErrorMessage($request_info->getIdTelegram(), $error_text);
		}
	}

	private function sendErrorMessage($id_telegram, $error_text = $default_error_text){
		$this->main_bot->sendMessage($id_telegram, $error_text);
	}
	
	private function sendAccessMessage($id_telegram, $username){
		$this->main_bot->sendMessage($id_telegram, "Здравствуйте, ${username}!");
		$this->main_bot->sendMessage($id_telegram, "Ваша личность подтверждена! Вы подписаны на обновления по вашему району, они будут приходить вам в течении дня автоматически!");
		$this->main_bot->sendMessage($id_telegram, "Если в уведомлениях вам нужны <b>только новые объявления</b>, нажмите на кнопку ниже - <b>❕ Присылать только новые объекты в уведомлениях</b>.");
		$this->main_bot->sendMessage($id_telegram, "Чтобы получить всю информацию по вашему району за последние 3 дня, нажмите кнопку ниже.");
	}
}

?>