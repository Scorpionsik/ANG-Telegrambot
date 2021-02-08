<?php

abstract class BotModule{
	protected $main_bot;

	public function __construct($main_bot){
		$this->main_bot = $main_bot;
	}

	abstract public function Start($request_info);
}

?>