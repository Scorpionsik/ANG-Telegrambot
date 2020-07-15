<?php
$root_dir = explode('html',__DIR__)[0] . 'html';
include "givemyprecious.php";
require_once $root_dir . "/vendor/autoload.php";

function logicMethod($bot, $message)
{
	$chat_id = $message->getChat()->getId();
	$message_text = htmlentities($message->getText());
	if($message_text == "/start")
	{
		$bot->sendMessage($chat_id, 'On after /start');
	}
	else $bot->sendMessage($chat_id, 'You enter: ' . $message_text);
}

$bot = new \TelegramBot\Api\Client($token_test);

//command /start
$bot->command('start', function ($message) use ($bot) {
		$chat_id = $message->getChat()->getId();
        $bot->sendMessage($chat_id, 'Start!');
		logicMethod($bot, $message);
    });
	
//command /help
$bot->command('help', function ($message) use ($bot) {
		$chat_id = $message->getChat()->getId();
        $bot->sendMessage($chat_id, 'Help!');
    });

//event on after /start or other input except other commands
$bot->on(function ($Update) use ($bot) {
	logicMethod($bot, $Update->getMessage());
	}, function ($Update)
		{ 
		
			$callback = $Update->getCallbackQuery();
			if (is_null($callback)) 
			{
				$message = $Update->getMessage();
				if(!is_null($message))
				{
					$message_text = $message->getText();
					if(preg_match("/^[^/].*$/", $message_text)) return true;
					else return false;
				}
				else return false;
			}
			else return false;
		});

$bot->run();
?>