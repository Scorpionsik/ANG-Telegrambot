<?php

class BotKeyboard{
	private $keyboard_array;
	
	public function __construct($row_count){
		$this->keyboard_array = array();
		for($i = 0; $i < $row_count; $i++){
			$this->addRow();
		}
	}
	
	public function addRow($position = -1, $is_overwrite = false){
		if($position < 0 && $position > count($this->keyboard_array) - 1) $this->keyboard_array[] = array();
		else{
			if($is_overwrite) $this->keyboard_array[$position] = array();
			else array_splice($this->keyboard_array, $position, 0, array());
		}
	}
	
	public function addButton($keyboard_button, $index_row = -1, $position = -1, $is_overwrite = false){
		$current_index_row = $index_row;
		if($index_row < 0) $current_index_row = count($this->keyboard_array) - 1;
		if($position < 0) $this->keyboard_array[$current_index_row][] = $keyboard_button->getButtonArray();
		else{
			if($is_overwrite) $this->keyboard_array[$current_index_row][$position] = $keyboard_button->getButtonArray();
			else array_splice($this->keyboard_array[$current_index_row], $position, 0, $keyboard_button->getButtonArray());
		}
	}
	
	public function getKeyboardArray(){
		return $this->keyboard_array;
	}
}

?>