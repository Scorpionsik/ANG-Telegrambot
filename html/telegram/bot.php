<?php
$root_dir = explode('html',__DIR__)[0];
$root_dir = "${root_dir}html";

include "givemyprecious.php";
require_once "${root_dir}/vendor/autoload.php";

$bot = new \TelegramBot\Api\Client(${token});

//команда Start
$bot->command('start', function ($message) use ($bot) {
    $bot->sendMessage($message->getChat()->getId(), 'start');
});

//команда Help
$bot->command('help', function ($message) use ($bot) {
    $bot->sendMessage($message->getChat()->getId(), 'help');
});

//команда Id
$bot->command('id', function ($message) use ($bot) {
    $id = $message->getChat()->getId();
    $bot->sendMessage($id,$id);
});

//Обработка введенного текста
$bot->on(function ($Update) use ($bot) {
    $message = $Update->getMessage();
    $msg_text = $message->getText();

    $bot->sendMessage($message->getChat()->getId(), "Ты написал: " . $msg_text);
}, function () { return true; });

$bot->run();