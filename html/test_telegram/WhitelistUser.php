<?php

class WhitelistUser{
	private $id_telegram;
	private $whitelist_info;
	private $is_exist;
	
	public function __construct($id_telegram, $whitelist_info, $is_exist){
		$this->id_telegram = $id_telegram;
		$this->whitelist_info = $whitelist_info;
		$this->is_exist = $is_exist;
	}
	
	public function getIdTelegram(){
		return $this->id_telegram;
	}
	
	public function getWhitelistInfo(){
		return $this->whitelist_info;
	}
	
	public function getIsExist(){
		return $this->is_exist;
	}
}

?>