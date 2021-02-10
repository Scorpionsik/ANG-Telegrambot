<?php

class KeyboardButton{
	private $button_array;
	
	public function __construct(){
		$button_array = array();
	}
	
	public function addData($param, $value, $position = -1, $is_overwrite = false){
		$new_data = array($param => $value);
		if($position < 0) $this->button_array[] = $new_data;
		else{
			if($is_overwrite) $this->button_array[$position] = $new_data;
			else array_splice($this->button_array, $position, 0, $new_data);
		}
	}
	
	public function getButtonArray(){
		return $this->button_array;
	}
}

?>