<?php
require_once "BotKeyboard.php";
require_once "KeyboardButton.php";

class InlineOfferBotKeyboard extends BotKeyboard {
	public function __construct($offer, $whitelist_info, $is_show_phones = true){
		parent::__construct(1);
		
		$button = new KeyboardButton("🛄 Объект на сайте");
		$button->addData("url", $offer->getSiteUrl() . $offer->getIdOffer());
		$this->addButton($button, 0);
		
		if($whitelist_info->getIsAcceptBaseButton() == 1){
			$button = new KeyboardButton("💼 Объект в базе");
			$button->addData("url", "http://newcab.bee.th1.vps-private.net/node/" . $offer->getIdDatabase());
			$this->addButton($button, 0);
		}
		
		if(!is_null($offer->getCity()) && !is_null($offer->getStreet()) && !is_null($offer->getHouseNum())){
			if($offer->getCity() != "" && $offer->getStreet() != "" && $offer->getHouseNum() != ""){
				$this->addRow();
				$gmap_link = "https://www.google.com.ua/maps/place/" . preg_replace('/[ ]/','+',$offer->getStreet()) . "," . preg_replace('/[ ]/','+',$offer->getHouseNum()) . "," . preg_replace('/[ ]/','+',$offer->getCity());
				$button = new KeyboardButton("🗺 Посмотреть на карте");
				$button->addData("url", $gmap_link);
				$this->addButton($button);
			}
		}
		
		if($is_show_phones){
			$this->addRow();
			$button = new KeyboardButton("☎️ Телефоны");
			$button->addData("callback_data", $offer->getIdOffer());
			$this->addButton($button);
		}
	}
}

?>