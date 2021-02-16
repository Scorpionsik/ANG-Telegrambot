<?php

class Offer{
	private $offer_description;
	private $id_offer;
	private $id_database;
	private $image_url;
	private $site_url;
	private $city;
	private $street;
	private $house_num;
	private $id_user;
	
	public function __construct($offer_description, $id_offer, $id_database, $image_url, $site_url, $city, $street, $house_num, $id_user)
	{
		$this->offer_description = $offer_description;
		$this->id_offer = $id_offer;
		$this->id_database = $id_database;
		$this->image_url = $image_url;
		$this->site_url = $site_url;
		$this->city = $city;
		$this->street = $street;
		$this->house_num = $house_num;
		$this->id_user = $id_user;
	}
	
	public function getOfferDescription()
	{
		return $this->offer_description;
	}
	
	public function getIdOffer()
	{
		return $this->id_offer;
	}
	
	public function getIdDatabase()
	{
		return $this->id_database;
	}
	
	public function getImageUrl()
	{
		return $this->image_url;
	}
	
	public function getSiteUrl()
	{
		return $this->site_url;
	}
	
	public function getCity()
	{
		return $this->city;
	}
	
	public function getStreet()
	{
		return $this->street;
	}
	
	public function getHouseNum()
	{
		return $this->house_num;
	}
	
	public function getIdUser(){
		return $this->id_user;
	}
}

?>