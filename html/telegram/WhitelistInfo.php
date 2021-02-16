<?php

class WhitelistInfo{
	private $id_whitelist;
	private $phonenumbers;
	private $username;
	private $is_banned;
	private $is_locked;
	private $is_accept_base_button;
	private $is_get_new_offers;
	private $is_get_edit_offers;
	private $turn_page;

	public function __construct($id_whitelist, $phonenumbers, $username, $is_banned, $is_locked, $is_accept_base_button, $is_get_new_offers, $is_get_edit_offers, $turn_page){
		$this->id_whitelist = $id_whitelist;
		$this->phonenumbers = $phonenumbers;
		$this->username = $username;
		$this->is_banned = $is_banned;
		$this->is_locked = $is_locked;
		$this->is_accept_base_button = $is_accept_base_button;
		$this->is_get_new_offers = $is_get_new_offers;
		$this->is_get_edit_offers = $is_get_edit_offers;
		$this->turn_page = $turn_page;
	}

	public function getIdWhitelist(){
		return $this->id_whitelist;
	}

	public function getPhonenumbers(){
		return $this->phonenumbers;
	}

	public function getUsername(){
		return $this->username;
	}

	public function getIsBanned(){
		return $this->is_banned;
	}
	
	public function getIsLocked(){
		return $this->is_locked;
	}

	public function getIsAcceptBaseButton(){
		return $this->is_accept_base_button;
	}

	public function getIsGetNewOffers(){
		return $this->is_get_new_offers;
	}

	public function getIsGetEditOffers(){
		return $this->is_get_edit_offers;
	}

	public function getTurnPage(){
		return $this->turn_page;
	}
}
?>