<?php

class KeyboardButton{
	private $button_array;
	
	public function __construct(){
		$button_array = array();
	}
	
	public function addData($param, $value){
		$this->button_array[$param] = $value;
	}
	
	public function getButtonArray(){
		return $this->button_array;
	}
}

?>