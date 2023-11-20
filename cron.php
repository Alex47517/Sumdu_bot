<?php
//Крон, запускити кожну хвилину
require_once 'config/start.php';
require_once 'config/loader.php';
require_once 'functions.php';

use api\{Bot as Bot, chat as chat, AutoClean as AutoClean};

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
//=========
//AUTOCLEAN
//=========
AutoClean::start();
//=========
//ANTI-TOP
//=========
$tax_sum = 50000;
$tax = 0.0005;
$top = R::getAll('SELECT * FROM users WHERE `balance` > ? ORDER BY `balance` DESC', [$tax_sum]);
foreach ($top as $top_user) {
    $top_user = R::load('users', $top_user['id']);
    $bank = R::load('settings', 1);
    echo '<br>';
    echo $top_user['nick'];
    $minus = floor($top_user['balance']*$tax);
    $top_user->balance -= $minus;
    $bank->value += $minus;
    R::store($bank);
    if ($top_user['tax_inform'] < (date('U')-86400)) {
        $top_user->tax_inform = date('U');
        $chat = new chat($top_user['tg_id']);
        $chat->sendMessage('📜 <b>[Податкова система]</b>
Усі користувачі, у яких на балансі більше <b>'.$tax_sum.'💰</b> сплачують податок у розмірі <b>0.005%</b> від свого балансу кожну хвилину.

<b>Так як ти входиш у цю категорію - з тебе зараз стягуються податки</b>');
    }
    R::store($top_user);
}