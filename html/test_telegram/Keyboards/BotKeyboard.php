<?php

abstract class BotKeyboard{
	private $keyboard_array;
	
	public function __construct($row_count){
		$this->keyboard_array = array();
		for($i = 0; $i < $row_count; $i++){
			$this->addRow();
		}
	}
	
	public function addRow(){
		$this->keyboard_array[] = array();
	}
	
	public function addButton($keyboard_button, $index_row, $position = -1, $is_overwrite = false){
		if($position < 0) $this->keyboard_array[$index_row][] = $keyboard_button->getButtonArray();
		else{
			if($is_overwrite) $this->keyboard_array[$index_row][$position] = $keyboard_button->getButtonArray();
			else array_splice($this->keyboard_array, $position, 0, $keyboard_button->getButtonArray());
		}
	}
	
	public function getKeyboardArray(){
		return $this->keyboard_array;
	}
}

?>