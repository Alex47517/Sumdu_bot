<?php
//
// Command: code #
// Text: !код /code #
// Callback: blockcustomcode allowcustomcode #
// Info: /next #
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
if ($chat->chat['tg_id'] == $chat_for_checkcodes) {
    $ex_callback = explode('_', update::$callback_data);
    if ($ex_callback[0] == 'blockcustomcode' or $ex_callback[0] == 'allowcustomcode') {
        $checkscript = R::load('checkscript', $ex_callback[1]);
        $client = R::load('users', $checkscript['user_id']);
        $client_chat = R::load('chats', $checkscript['chat_id']);
        if ($checkscript && $client && $client_chat) {
            $command = json_decode($checkscript['info'], true);
            $client_chat = new chat($client_chat['tg_id']);
            if ($ex_callback[0] == 'allowcustomcode') {
                $checkscript->confirmed = 1;
                R::store($checkscript);
                $client_chat->sendMessage('✅ <b>[CheckScript v2.0F]</b>
<a href="tg://user?id='.$client['tg_id'].'">'.$client['nick'].'</a>, Ваш код #'.$checkscript['id'].' дозволений до виконання

Пишіть: <code>!код виконати '.$checkscript['id'].'</code>');
                $chat->editMessageText('✅ <b>Виконання дозволено!</b>', null, update::$btn_id);
                die();
            } else {
                //v2.0F, F значить Fake :)
                $client_chat->sendMessage('💢 <b>[CheckScript v2.0F]</b>
<a href="tg://user?id='.$client['tg_id'].'">'.$client['nick'].'</a>, Ваш код #'.$checkscript['id'].' не пройшов перевірку');
                $chat->editMessageText('✅ <b>Заборонено!</b>', null, update::$btn_id);
                R::trash($checkscript); die();
            }
        } else {
            custom_error('Помилка 404', 'Не знайдено');
        }
        die();
    }
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
    $owner = new chat($chat_for_checkcodes);
    $keyboard[0][0]['text'] = '❌ Заборонити';
    $keyboard[0][0]['callback_data'] = 'blockcustomcode_'.$checkscript['id'];
    $keyboard[0][1]['text'] = '✅ Дозволити';
    $keyboard[0][1]['callback_data'] = 'allowcustomcode_'.$checkscript['id'];
    $owner->sendMessage('⚠ <b><a href="tg://user?id='.$user->user['id'].'">'.$user->user['nick'].'</a> хоче виконати код із чату '.$chat->chat['name'].'</b>
<code>'.$code.'</code>', null, ['inline_keyboard' => $keyboard]); die();
}