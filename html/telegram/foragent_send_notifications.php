<?php
include "givemyprecious.php";
include "NotificationBot.php";

$bot = new NotificationBot($token_test);

$whitelist_users_array = $bot->getWhitelistUsers(10);
foreach($whitelist_users_array as $whitelist_user){
	$offers_array = $bot->getOffersForWhitelistUser($whitelist_user);
	try{
		foreach($offers_array as $offer){
			$bot->showOffer($offer, $whitelist_user->getIdTelegram(), $whitelist_user->getWhitelistInfo());
		}
	}
	catch(Exception $ex){
	}
}

echo date('l jS \of F Y h:i:s A');
?>