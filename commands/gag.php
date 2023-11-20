<?php
//
// Command: Кляп #
// Text: !кляп /gag #
// Callback: ungag #
// Info: Видає мут на 2 години користувачу, який він може зняти за монети #
// Syntax: !кляп [нік/відповідь на повідомлення*] #
// Args: 0 #
// Rank: USER #
//
use api\{update as update, ChatMember as ChatMember, AutoClean as AutoClean};
if ($ex_callback[0] == 'ungag') {
    $s_user = R::load('users', $ex_callback[1]);
    if ($s_user) {
        if ($user->user['balance'] < 2000) {
            $chat->answerCallbackQuery('💢 Недостатньо коштів! Необхідно: 2000💰, у тебе: '.$user->user['balance'].'💰</b>', true);
            die();
        }
        $s_user_cl = new User();
        $s_user_cl->loadByID($s_user['id']);
        $user->addBal(-2000);
        unmute($s_user['id'], '[GAG-MODULE]', false);
        AutoClean::save();
        $chat->deleteMessage(update::$btn_id);
        $chat->sendMessage('🎉 <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a> дістав кляп із рота <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a>

З балансу <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a> списано <b>2000💰</b>', update::$message_id, null);
    }
    die();
}
if (update::$reply_user_id) {
    $find = update::$reply_user_id;
    $col = 'tg_id';
} else {
    $find = $cmd[1];
    $col = 'nick';
}
$s_user = R::findOne('users', $col.' = ?', [$find]);
if ($s_user['id']) {
    if ($user->user['diamonds'] < 1) custom_error('Недостатньо коштів!', 'Необхідно: <b>1💎</b>
У тебе: <b>'.$user->user['diamonds'].'💎</b>');
    $time = 7200; //2 часа
    mute($s_user['id'], $time, 'Кляп', '[GAG-MODULE]', false);
    $user->update('diamonds', ($user->user['diamonds']-1));
    $keyboard[0][0]['text'] = '🎁 Розкляпити (2000💰)';
    $keyboard[0][0]['callback_data'] = 'ungag_'.$s_user['id'];
    AutoClean::save();
    $chat->sendMessage('🤐 <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a> засунув кляп у рота <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a>

З балансу <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a> списано <b>1💎</b>', update::$message_id, ['inline_keyboard' => $keyboard]);
} else custom_error('Помилка 404', 'Користувач не знайдений');