<?php
include "givemyprecious.php";
include "NotificationBot.php";

$bot = new NotificationBot($token);

$whitelist_users_array = $bot->getWhitelistUsers();
foreach($whitelist_users_array as $whitelist_user){
	$offers_array = $bot->getOffersForWhitelistUser($whitelist_user);
	try{
		$count_array = count($offers_array);
		if($count_array > 0) {
		$bot->sendStartMessage($whitelist_user);
			foreach($offers_array as $offer){
				$bot->showOffer($offer, $whitelist_user->getIdTelegram(), $whitelist_user->getWhitelistInfo());
				usleep(200000);
			}
			
			$bot->sendEndMessage($count_array, $whitelist_user);
			if($whitelist_user->getIsExist() == 0) $bot->setIsExist($whitelist_user, 1);
		}
	}
	catch(Exception $ex){
		$bot->setIsExist($whitelist_user, 0);
	}
}

echo date('l jS \of F Y h:i:s A');
?>