<?php

abstract class BotModule{
	protected $main_bot;

	public function __construct($main_bot){
		$this->main_bot = $main_bot;
	}

	public function Start($request_info, $whitelist_info){
		try{
			$this->StartMethod($request_info, $whitelist_info);
		}
		catch(Exception $e){
			$this->main_bot->sendException($e, $request_info, $whitelist_info);
		}
	}

	abstract protected function StartMethod($request_info, $whitelist_info);
}

?>