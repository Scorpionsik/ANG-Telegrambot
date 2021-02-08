<?php
namespace ANGBot;

class RequestInfo{
	private $id_telegram;
	private $message_text;
	private $callback_data;

	public function __construct($update){
		$this->message_text = $update->getMessage();
		if(is_null($this->message_text)){
			$callback = $update->getCallbackQuery();
			if(!is_null($callback)){
				$this->callback_data = $callback->getData();
				$this->message = $callback->getMessage();
			}
		}
		$this->id_telegram = $message->getChat()->getId();
	}

	public function __construct($id_telegram, $message_text){
		$this->id_telegram = $id_telegram;
		$this->message_text = $message_text;
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


