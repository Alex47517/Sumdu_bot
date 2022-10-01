<?php
//
// Command: Топ #
// Text: !топ /top #
// Info: Виводить топ, доступні топи: баланс/balance #
// Syntax: !топ [назва] [кількість користувачів*] #
// Args: 1 #
// Rank: USER #
//
if ($cmd[1] == 'баланс' or $cmd[1] == 'balance') {
    $default_limit = 5;
    if ($cmd[2]) $limit = round($cmd[2]); else $limit = $default_limit;
    if (!$limit) $limit = $default_limit;
    $top_users = array_values(R::find('users', 'ORDER BY `balance` DESC LIMIT ?', [$limit]));
    $text = '💰 <b>Топ по балансу:</b>
';
    foreach ($top_users as $key => $top_user) {
        $text .= getEmojiNum(($key+1)).' <a href="tg://user?id='.$top_user['tg_id'].'">'.$top_user['nick'].'</a> -> <b>'.$top_user['balance'].'💰</b>
';
    }
    $chat->sendMessage($text);
}