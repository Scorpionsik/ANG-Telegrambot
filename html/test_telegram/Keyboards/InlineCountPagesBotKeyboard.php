<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class InlineCountPagesBotKeyboard extends BotKeyboard {
	private $max_pages_in_keyboard = 5;
	
	public function __construct($current_page, $count_pages){
		parent::__construct(1);
		
		if($count_pages > 1){
			$start_page = $current_page - 1;
			if($count_pages >= 5){
				if($current_page <= 3) $start_page = 1;
				else if($current_page >= $count_pages - 2) $start_page = $count_pages - 4;
			}
			else $start_page = 1;
			
			$end_page = min($count_pages, 5);
			
			for($i = $start_page; $i < $start_page + $end_page; $i++){
				$text = $i;
				if($i == $current_page) $text = $text . "👀";
				$button = new KeyboardButton($text);
				$button->addData("callback_data", $i);
				$this->addButton($button, 0);
			}
			
			if($count_pages > 5 && ($current_page > 3 || $current_page < $count_pages - 2)){
				$this->addRow();
				if($current_page > 3) {
					$button = new KeyboardButton("1 ⏪");
					$button->addData("callback_data", "1");
					$this->addButton($button, 1);
				}
				if($current_page < $pages - 2){
					$button = new KeyboardButton("⏩ ${count_pages}");
					$button->addData("callback_data", $count_pages);
					$this->addButton($button, 1);
				}
			}
		}
	}
}

?>