<?php
use api\chat as chat;
if (date('d.m.y') != $user->user['ev_bonus'] && !$user->user['unsub_bonus']) {
    $user->update('ev_bonus', date('d.m.y'));
    $user->addBal('200');
    $ls = new chat($user->user['tg_id']);
    $keyboard[0][0]['text'] = '🔕 Не сповіщати';
    $keyboard[0][0]['callback_data'] = 'notify_bonus';
    $ls->sendMessage('💸 <b>Вітаємо!</b>
Ти отримав(ла) щоденний бонус <b>200💰</b> за перше повідомлення сьогодні!
Не проґав можливість отримати ще один, команда: <code>!бонус</code>

<a href="https://sumdubot.pp.ua/wiki/commands">Довідка по боту</a>', null, ['inline_keyboard' => $keyboard]);
}