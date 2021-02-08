<?php
class RequestInfo{
	private $id_telegram;
	private $id_whitelist;
	private $message_data;
	private $callback_data;
	private $mode_value;

	public function __construct($update, $id_whitelist = null, $mode_value = 0){
		if(is_a($update, '\TelegramBot\Api\Types\Message')){
			$this->id_telegram = $update->getChat()->getId();
			$this->$message_data = $update;
		}
		else if(is_a($update, '\TelegramBot\Api\Types\Update')){
			$this->$message_data = $update->getMessage();
			if(is_null($this->$message_data)){
				$callback = $update->getCallbackQuery();
				if(!is_null($callback)){
					$this->callback_data = $callback->getData();
					$this->$message_data = $callback->getMessage();
				}
			}
			$this->id_telegram = $this->$message_data->getChat()->getId();
		}
		else if(is_a($update, 'RequestInfo')){
			$this->id_telegram = $update->getIdTelegram();
			$this->message_data = $update->getMessageData();
			$this->callback_data = $update->getCallbackData();
		}
		$this->id_whitelist = $id_whitelist;
		$this->mode_value = $mode_value;
	}

	public function getIdTelegram(){
		return $this->id_telegram;
	}

	public function getMessageData(){
		return $this->$message_data;
	}

	public function getCallbackData(){
		return $this->callback_data;
	}

	public function getIdWhitelist(){
		return $this->$id_whitelist;
	}

	public function getModeValue(){
		return $this->mode_value;
	}
}
?>

