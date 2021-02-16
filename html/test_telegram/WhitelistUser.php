<?php

class WhitelistUser{
	private $id_telegram;
	private $whitelist_info;
	
	public function __construct($id_telegram, $whitelist_info){
		$this->id_telegram = $id_telegram;
		$this->whitelist_info = $whitelist_info;
	}
	
	public function getIdTelegram(){
		return $this->id_telegram;
	}
	
	public function getWhitelistInfo(){
		return $this->whitelist_info;
	}
}

?>