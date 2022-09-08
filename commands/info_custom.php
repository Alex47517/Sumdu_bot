<?php
//
// Command: Інфо #
// Text: /info !інфо !инфо #
// Info: Виводить інформацію користувача #
// Syntax: !інфо [нік/відповідь на повідомлення*] #
// Args: 0 #
// Rank: USER #
//
use api\{update as update, ChatMember as ChatMember};
if (update::$reply_user_id) {
    $find = update::$reply_user_id;
    $col = 'tg_id';
} else {
    if (explode(':', $cmd[1])[0] == 'id' && is_numeric(explode(':', $cmd[1])[1])) {
        $find = explode(':', $cmd[1])[1];
        $col = 'id';
    } else {
        $find = $cmd[1];
        $col = 'nick';
    }
}
$s_user = R::findOne('users', $col.' = ?', [$find]);
if (!$s_user) custom_error('Помилка 404', 'Користувач не знайдений');
$s_chatMember = new ChatMember($s_user['id'], $chat->chat['id']);
if (($s_chatMember->chatMember['blacklist'] - date('U')) > 0 or $s_chatMember->chatMember['blacklist'] == 1) {
    if ($s_chatMember->chatMember['blacklist'] == 1) $d_time = 'вічність'; else {
        $d_time = Time::sec2time_txt(($s_chatMember->chatMember['blacklist'] - date('U')));
    }
    $alerts .= '👺 Доданий до ЧС бота у цьому чаті (ще '.$d_time.')
';
}
if (($s_user['blacklist'] - date('U') > 0) or $s_user['blacklist'] == 1) {
    if ($s_user['blacklist'] == 1) $d_time = 'вічність'; else {
        $d_time = Time::sec2time_txt(($s_user['blacklist'] - date('U')));
    }
    $alerts .= '👺 Доданий до ЧС бота [глобально] (ще '.$d_time.')
';
}
if ($s_user['ban']) {
    $ban = explode('@;', $s_user['ban']);
    $admin = R::load('users', $ban[0]);
    if (date('U') < $ban[1] or $ban[1] == 'e') {
        if ($ban[1] != 'e') {
            $time = $ban[1] - date('U');
            $d_time = Time::sec2time_txt($time);
        } else $d_time = 'вічність';
        $alerts .= '📛 Заблокований на порталі (ще ' . $d_time . ')
';
    }
}
if($alerts) $alerts = '

<b>[*] Важливе:</b>
'.$alerts;
if (!$s_user['grp']) $s_user['grp'] = '[не вказана]';
if ($s_user['custom_info'] && $cmd[1] != '-default' && $cmd[2] != '-default') {
    $custom_info = R::load('custominfo', $s_user['custom_info']);
    if ($custom_info) {
        $text = replace_custom_info($custom_info, $s_user).$alerts;
        $chat->sendMessage($text); die();
    }
}
$text = '📌 <b>Інформація про аккаунт:</b>

<b>[*] Основне:</b>
● ID: <b>'.$s_user['id'].'</b>
● Нік: <code>'.$s_user['nick'].'</code>
● Ранг: '.$s_user['rank'].'
● Баланс: '.$s_user['balance'].'💰
● Група: '.$s_user['grp'].'

<b>[*] Інше:</b>
● Telegram: <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['tg_id'].'</a>
● Дата реєстрації: '.$s_user['reg_date'].''.$alerts;
$chat->sendMessage($text);