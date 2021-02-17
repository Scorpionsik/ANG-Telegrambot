<?php

class KeyboardButton{
	private $button_array;
	
	public function __construct($text){
		$this->button_array = array();
		$this->button_array["text"] = $text;
	}
	
	public function addData($param, $value){
		$valid_param = "";
		switch($param){
			case "text":
			case "url":
			case "callback_data":
			case "request_contact":
			$valid_param = $param;
			break;
		}
		if($valid_param != "") $this->button_array[$valid_param] = $value;
	}
	
	public function getButtonArray(){
		return $this->button_array;
	}
}

?>