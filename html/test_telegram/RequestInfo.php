<?php
namespace ANGBot;

class RequestInfo{
	private $id_telegram;
	private $message_text;
	private $callback_data;

	public function __construct($update){

		if(is_a($update, '\TelegramBot\Api\Types\Message', true)){
			$this->id_telegram = $update->getChat()->getId();
			$this->message_text = $update;
		}
		else if(is_a($update, '\TelegramBot\Api\Types\Update', true)){
			$this->message_text = $update->getMessage();
			if(is_null($this->message_text)){
				$callback = $update->getCallbackQuery();
				if(!is_null($callback)){
					$this->callback_data = $callback->getData();
					$this->message_text = $callback->getMessage();
				}
			}
			$this->id_telegram = $this->message_text->getChat()->getId();
		}
	}

	public function getIdTelegram(){
		return $this->id_telegram;
	}

	public function getMessageText(){
		return $this->message_text;
	}

	public function getCallbackData(){
		return $this->callback_data;
	}
}
?>


