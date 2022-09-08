<?php
//
// Command: UnBan #
// Text: /unban !разбан !розбан #
// Info: Розбанює користувача у чаті #
// Syntax: !розбан [нік/відповідь на повідомлення*] #
// Args: 0 #
// Rank: ChatAdmin #
//
use api\update as update;
if (update::$reply_user_id) {
$col = 'tg_id';
$find = update::$reply_user_id;
} else {
$col = 'nick';
$find = $cmd[1];
}
$s_user = R::findOne('users', $col.' = ?', [$find]);
if (!$s_user) custom_error('Помилка 404', 'Користувача не знайдено');
unban($s_user['id'], $user->user['nick']);