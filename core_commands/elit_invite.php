<?php
use api\chat as chat;
if ($cmd[0] == '/start' && $cmd[1] == 'ElitChat') {
    if ($user->user['tg_id'] == $chat->chat['tg_id']) {
        $chatMember = R::findOne('chatmembers', 'user_id = ? AND chat_id = ?', [$user->user['id'], 2]);
        if ($chatMember) {
            $chat->sendMessage('💢 <b>Помилка</b>

Ця команда створена для абітурієнтів які ніколи не були в Еліт чаті');
        } else {
            $elit = new chat(-1001195752130);
            $exp = date('U')+300;
            $result = $elit->createChatInviteLink('Абіт: '.$user->user['nick'], $exp, 1);
            if ($result->result->invite_link) {
                $user->update('botcheck', 1);
                $keyboard[0][0]['text'] = 'Увійти в ЕлІТ: Чат 🇺🇦';
                $keyboard[0][0]['url'] = $result->result->invite_link;
                $chat->sendMessage('👋 <b>Привіт, вступник!</b>

Натисни на кнопку нижче, щоб увійти в <b>Еліт: Чат</b>

⚠ Зверни увагу!
<em>Твоє посилання діє 5 хвилин та може бути використано лише 1 раз</em>', null, ['inline_keyboard' => $keyboard]); die();
            } else {
                $chat->sendMessage('♨ <b>Привіт, вступник!</b>

Вибач що так сталося, але мені не вдалося створити посилання :(

Тобі з радістю допоможе @alex47517 - просто перешли йому це повідомлення

<b>[*] Технічна інформація:</b>
<code>'.var_export($result, true).'</code>
ID: '.$user->user['id']); die();
            }
        }
    }
}