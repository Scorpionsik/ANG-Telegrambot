<?php

abstract class BotModule{
	protected $main_bot;

	public function __construct($main_bot){
		$this->main_bot = $main_bot;
	}

	public function start($request_info, $whitelist_info){
		try{
			if(is_null($request_info->getCallbackData())) {
				try{
					$this->main_bot->deleteMessage($request_info->getMessageData());
				}
				catch{}
				$this->forMessages($request_info, $whitelist_info);
			}
			else $this->forCallbacks($request_info, $whitelist_info);
		}
		catch(Exception $e){
			$this->main_bot->sendException($e, $request_info, $whitelist_info);
		}
	}
	
	protected function resetToDefaultMode($request_info, $whitelist_info, $is_distribute = false){
		$this->main_bot->changeMode($request_info, $whitelist_info, 0, 0, $is_distribute);
	}
	
	abstract protected function forMessages($request_info, $whitelist_info);
	
	abstract protected function forCallbacks($request_info, $whitelist_info);
}

?>