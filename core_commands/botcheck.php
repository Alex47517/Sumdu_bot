<?php
use api\update as update;
//update::$new_chat_user_id or
if (!$user->user['botcheck'] && $user->user['tg_id'] != $chat->chat['tg_id'] && $chat->chat['botcheck']) {
        $result = mute($user->user['id'], 0, 'Анти-Бот перевірка', '[Anti-BOT]');
        $botcheck = R::dispense('botcheck');
        $botcheck->user_id = $user->user['id'];
        $botcheck->chat_id = $chat->chat['id'];
        $botcheck->checked = 0;
        $botcheck->date = date('U');
        R::store($botcheck);
        $keyboard[0][0]['text'] = 'Перейти до бота';
        $keyboard[0][0]['url'] = 'https://t.me/Sumdu_bot';
        $chat->sendMessage('💣 <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a>, тобі тимчасово обмежено доступ до чату

Ми повинні переконатися що ти не бот, для цього напиши у приватні повідомлення боту /start, натисни кнопку "🔐 Авторизація на порталі" та дій по інструкції

⚠ <b>Якщо ти це не зробиш протягом 5 хвилин, ми будемо вимушені заблокувати тебе</b>', update::$message_id, ['inline_keyboard' => $keyboard]);
        if ($user->user['tg_id'] != '777000') $chat->deleteMessage(update::$message_id);
}