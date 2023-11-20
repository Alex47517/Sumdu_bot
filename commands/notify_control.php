<?php
//
// Command: Підписки #
// Syntax: notify #
// Callback: notify #
// Info: Керування підписками #
// Syntax: notify #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
if ($ex_callback[1] == 'bonus') {
    if (!$user->user['unsub_bonus']) {
        $user->update('unsub_bonus', 1);
        $keyboard[0][0]['text'] = '🔔 Оповіщувати';
        $keyboard[0][0]['callback_data'] = 'notify_bonus';
        $chat->editMessageText('✅ <b>Ви відписалися від сповіщень бонусу</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    } else {
        $user->update('unsub_bonus', 0);
        $keyboard[0][0]['text'] = '🔕 Не сповіщати';
        $keyboard[0][0]['callback_data'] = 'notify_bonus';
        $chat->editMessageText('✅ <b>Ви підписалися на сповіщення бонусу</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    }
}