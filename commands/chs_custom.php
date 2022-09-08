<?php
//
// Command: ЧС #
// Text: !чс /blacklist #
// Info: Додає/видаляє користувача до/із чорного списку бота #
// Syntax: !чс [all*] [нік/відповідь на повідомлення*] [срок*] ["розблокувати"/"unblock"*] #
// Args: 1 #
// Rank: ChatAdmin #
//
use api\{update as update, ChatMember as ChatMember};
if ($cmd[1] == 'розблокувати' or $cmd[2] == 'розблокувати' or $cmd[1] == 'unblock' or $cmd[2] == 'unblock') {
    if($cmd[1] == 'all') {
        Permissions::Admin($user->user);
        $chat_only = false;
        $msg = str_replace(' '.$cmd[1].'', '', $msg);
        $cmd = explode(' ', $msg);
        $type = '[глобально]';
    } else {
        $type = 'у цьому чаті';
        $chat_only = true;
    }
    if (update::$reply_user_id) {
        $col = 'tg_id';
        $find = update::$reply_user_id;
    } else {
        $col = 'nick';
        $find = $cmd[1];
    }
    $s_user = R::findOne('users', $col.' = ?', [$find]);
    if (!$s_user) custom_error('Помилка 404', 'Користувач не знайдений');
    if ($chat_only) {
        $s_chatMember = new ChatMember($s_user['id'], $chat->chat['id']);
        $s_chatMember->update('blacklist');
    } else {
        $s_user_cl = new User();
        $s_user_cl->loadByID($s_user['id']);
        $s_user_cl->update('blacklist');
    }
    $chat->sendMessage('✅ <b>Користувача <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a> видалено із чорного списку бота '.$type.'</b>

<b>Адміністратор: </b>'.$user->user['nick']); die();
}
if($cmd[1] == 'all') {
Permissions::Admin($user->user);
$chat_only = false;
$msg = str_replace(' '.$cmd[1].'', '', $msg);
$cmd = explode(' ', $msg);
$type = '[глобально]';
} else {
$type = 'у цьому чаті';
$chat_only = true;
}
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
if ($reason) $reason = mb_substr($reason, 1); else $reason = '[не вказана]';
$sec_time = Time::toTimestamp($str_time);
if (!$sec_time) custom_error('Некоректний формат часу', 'Пишіть час у вигляді:

4d5m7s => <em>4 дні, 5 хвилин, 7 секунд</em>');
$s_user = R::findOne('users', $col.' = ?', [$find]);
if (!$s_user) custom_error('Помилка 404', 'Користувач не знайдений');
if ($chat_only) {
$s_chatMember = new ChatMember($s_user['id'], $chat->chat['id']);
$s_chatMember->addToBlackList($sec_time);
} else {
$s_user_cl = new User();
$s_user_cl->loadByID($s_user['id']);
$s_user_cl->addToBlackList($sec_time);
}
$chat->sendMessage('👺 <b>Користувач <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a> доданий до чорного списку бота '.$type.'</b>

<b>Адміністратор: </b>'.$user->user['nick'].'
<b>Срок: </b>'.Time::sec2time_txt($sec_time));