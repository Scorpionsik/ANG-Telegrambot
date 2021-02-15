<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class InlineCountPagesBotKeyboard extends BotKeyboard {
	private $max_pages_in_keyboard = 9;
	
	public function __construct($current_page, $count_pages){
		parent::__construct(1);
		$floor_max_pages = floor($this->max_pages_in_keyboard / 2);
		
		if($count_pages > 1){
			$start_page = $current_page - $floor_max_pages;
			if($count_pages >= $this->max_pages_in_keyboard){
				if($current_page <= $floor_max_pages + 1) $start_page = 1;
				else if($current_page >= $count_pages - $floor_max_pages) $start_page = $count_pages - $this->max_pages_in_keyboard + 1;
			}
			else $start_page = 1;
			
			$end_page = min($count_pages, $this->max_pages_in_keyboard);
			
			for($i = $start_page; $i < $start_page + $end_page; $i++){
				$text = $i;
				if($i == $current_page) $text = $text . "👀";
				$button = new KeyboardButton($text);
				$button->addData("callback_data", $i);
				$this->addButton($button, 0);
			}
			
			if($count_pages > $this->max_pages_in_keyboard && ($current_page > $floor_max_pages + 1 || $current_page < $count_pages - $floor_max_pages)){
				$this->addRow();
				if($current_page > $floor_max_pages + 1) {
					$button = new KeyboardButton("1 ⏪");
					$button->addData("callback_data", "1");
					$this->addButton($button, 1);
				}
				if($current_page < $count_pages - $floor_max_pages){
					$button = new KeyboardButton("⏩ ${count_pages}");
					$button->addData("callback_data", $count_pages);
					$this->addButton($button, 1);
				}
			}
		}
	}
}

?>