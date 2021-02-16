<?php
include "givemyprecious.php";
include "NotificationBot.php";

$bot = new NotificationBot($token_test);

$whitelist_users_array = $bot->getWhitelistUsers(10);
foreach($whitelist_user in $whitelist_users_array){
	$offers_array = $bot->getOffersForWhitelistUser($whitelist_user);
	foreach($offer in $offers_array){
		$bot->showOffer($offer, $whitelist_user->getIdTelegram(), $whitelist_user->getWhitelistInfo());
	}
}

echo date('l jS \of F Y h:i:s A');
?>