<?php
//
// Command: Ban #
// Text: !бан /ban #
// Info: Банить користувача у чаті (якщо термін < 30 сек => бан назавжди) #
// Syntax: !бан [нік/відповідь на повідомлення*] [термін] [причина] #
// Args: 1 #
// Rank: ChatAdmin #
//
use api\update as update;
if (update::$reply_user_id) {
$col = 'tg_id';
$find = update::$reply_user_id;
$str_time = $cmd[1];
$reason = str_replace($cmd[0].' '.$cmd[1], '', $msg);
} else {
$col = 'nick';
$find = $cmd[1];
$str_time = $cmd[2];
$reason = str_replace($cmd[0].' '.$cmd[1].' '.$cmd[2], '', $msg);
}
if ($reason) $reason = mb_substr($reason, 1);
$sec_time = Time::toTimestamp($str_time);
if (!$sec_time) custom_error('Некоректний формат часу', 'Пишіть час у вигляді:

4d5m7s => <em>4 дні, 5 хвилин, 7 секунд</em>');
if ($sec_time < 30) $sec_time = 0;
$s_user = R::findOne('users', $col.' = ?', [$find]);
if (!$s_user) custom_error('Помилка 404', 'Користувача не знайдено');
ban($s_user['id'], $sec_time, $reason, $user->user['nick']);