<?php
//
// Command: code #
// Text: !код /code #
// Callback: blockcustomcode allowcustomcode #
// Info: Виконує PHP код #
// Syntax: !код [PHP code] #
// Args: 1 #
// Rank: STAR #
//
use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};
$lst_code_id = R::load('settings', 2);
function codeVerify($current_id) {
    global $lst_code_id;
    if ($lst_code_id['value'] == $current_id) custom_error('[ANTI-LOOPing]', 'У Вашому коді виявлена помилка');
    $lst_code_id->value = $current_id;
    R::store($lst_code_id);
}
if ($cmd[1] == 'виконати') {
    if ($cmd[2]) {
        $checkscript = R::load('checkscript', $cmd[2]);
        if ($checkscript) {
            if ($checkscript['user_id'] == $user->user['id'] && $checkscript['chat_id'] == $chat->chat['id']) {
                if ($checkscript->confirmed) {
                    codeVerify(update::$message_id);
                    $chat->sendMessage("<b>Ваш код:</b>
".$checkscript['code']);
                    eval($checkscript['code']);
                    $chat->sendMessage("Код виконаний!");
                    R::trash($checkscript);
                    die();
                } else {
                    custom_error('Помилка 403', 'Цей код ще не завершив перевірку CheckScript');
                }
            } else {
                custom_error('Помилка 403', 'Цей код дозволено виконувати тільки у чаті звідки запитували перевірку та тільки той хто запитував');
            }
        } else {
            custom_error('Помилка 404', 'Коду з таким id не існує');
        }
    } else {
        custom_error('Недостатньо аргументів', 'Пишіть:
!код виконати [іd коду]');
    }
    die();
}
$code = str_replace($cmd[0], '', $msg);
//$code = 'use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update}; '.$code;
if (Permissions::Owner($user->user, true)) {
    codeVerify(update::$message_id);
    $chat->sendMessage("<b>Ваш код:</b>
".$code);
    eval($code);
    $chat->sendMessage("Код виконаний!");
} else {
    $chat->sendMessage('⏳ <b>[CheckScript v2.0F]</b>
Ваш код передано модулю CheckScript, зачекайте хвилинку'); //угу, саме так
    $checkscript = R::dispense('checkscript');
    $checkscript->user_id = $user->user['id'];
    $checkscript->chat_id = $chat->chat['id'];
    $checkscript->code = $code;
    $checkscript->confirmed = 0;
    R::store($checkscript);
    start_checkScriptNodeJSModule();
}