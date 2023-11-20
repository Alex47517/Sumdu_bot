<?php
//
// Command: Баланс #
// Text: !баланс /balance #
// Info: Виводить інформацію про баланс #
// Syntax: !баланс #
// Args: 0 #
// Rank: USER #
//
if ($cmd[1]) {
Permissions::Admin($user->user);
Permissions::Owner($user->user);
$s_user = new User();
if ($s_user->loadByNick($cmd[1])) {
$s_user->addBal(round($cmd[2]));
$chat->sendMessage('✅ Користувачу <a href="tg://user?id='.$s_user->user['tg_id'].'">'.$s_user->user['nick'].'</a> видано <b>'.round($cmd[2]).'💰</b>');
} else custom_error('Помилка 404', 'Користувач не знайдений');
} else {
    $achievements_count = R::count('userachievements', '`user_id` = ?', [$user->user['id']]);
    if ($achievements_count) {
        $text = '

Досягнень: <b>'.$achievements_count.'🏆 </b>';
    }
$chat->sendMessage('🕯 <b>Твій баланс:</b>
<b>'.$user->user['balance'].'💰</b>
<b>'.$user->user['diamonds'].'💎</b>'.$text);
}