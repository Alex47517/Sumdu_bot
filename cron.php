<?php
//Крон, запускити кожну хвилину
require_once 'config/start.php';
require_once 'config/loader.php';
require_once 'functions.php';

use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};

echo 'cron started!';

$bot = new Bot($bot_token);
$botchecks = R::getAll('SELECT * FROM `botcheck`');
foreach ($botchecks as $botcheck) {
    echo '$botcheck: '.$botcheck['id'].PHP_EOL;
    if (!$botcheck['checked'] && ($botcheck['date']+300) < date('U')) {
        $user = R::load('users', $botcheck['user_id']);
        echo '$user: '.$user['id'].PHP_EOL;
        if ($user['botcheck'] < 1) {
            $chat_db = R::load('chats', $botcheck['chat_id']);
            $chat = new chat($chat_db['tg_id']);
            $botcheck_db = R::load('botcheck', $botcheck['id']);
            R::trash($botcheck_db);
            echo 'Trying ban user: ' . $user['id'] . ' in chat ' . $chat->chat['id'] . PHP_EOL;
            $result = ban($user['id'], 2592000, 'Не пройдена перевірка на бота', '[ANTI-BOT]');
            var_dump($result);
        }
    }
}