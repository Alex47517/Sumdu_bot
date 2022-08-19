<?php
require_once 'config/start.php';
require_once 'config/loader.php';

use api\update as update;

$request_json = file_get_contents('php://input');
$request = json_decode($request_json, true);

if (!$request['message']['chat']['id']) die('!chat');

use api\Bot as Bot;

$bot = new Bot($bot_token);
$update = new update($request);

use api\chat as chat;

$chat = new chat(update::$chat);
if (!$chat->chat['id']) {
    $chat->storeChat(update::$chat);
}
if ($chat->chat_id == $admin_user_id) {
    $chat->sendMessage('test!');
}