<?php
//
// Command: Переказ #
// Text: !переказ !перевод /transfer /pay #
// Info: Переказ коштів між користувачами, коміссія - 20% #
// Syntax: !переказ [нік/відповідь на повідомлення*] [сума] #
// Args: 1 #
// Rank: USER #
//
use api\update as update;
if (update::$reply_user_id) {
    $col = 'tg_id';
    $find = update::$reply_user_id;
    $sum = $cmd[1];
} else {
    $col = 'nick';
    $find = $cmd[1];
    $sum = $cmd[2];
}
if (!is_numeric($sum)) custom_error('Помилка', 'Сума має бути числом');
$sum = floor($sum);
$comission = floor($sum*0.2);
$s_user = R::findOne('users', $col.' = ?', [$find]);
if ($s_user) {
    if ($sum < 20) custom_error('Помилка', 'Мінімальна сума переказу: 20💰');
    if ($user->user['balance'] < ($sum+$comission)) custom_error('Недостатньо коштів', 'Потрібна сума: '.$sum.'+'.$comission.'💰 (комісія)
Ваш баланс: '.$user->user['balance'].'💰');
    $s_user->balance += $sum;
    R::store($s_user);
    $user->update('balance', ($user->user['balance']-($sum+$comission)));
    Bank::add($comission);
    if ($user->user['id'] == $s_user['id']) {
        $chat->sendMessage('✅ Користувач <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a> переказав <b>'.$sum.'💰</b> сам собі :/

Коміссія склала: <b>'.($comission).'💰</b>'); die();
    }
    if ($s_user['nick'] == 'Sumdu_bot') {
        $chat->sendMessage('✅ Ви переказали <b>'.$sum.'💰</b> користувачу <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a>

Коміссія склала: <b>'.($comission).'💰</b>
Коментар: <b>На благодійність та підтримку економіки</b>'); die();
    }
    $chat->sendMessage('✅ Ви переказали <b>'.$sum.'💰</b> користувачу <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a>

Коміссія склала: <b>'.($comission).'💰</b>');
} else custom_error('Помилка 404', 'Користувач не знайдений');