<?php
//
// Command: Новий куратор #
// Text: !куратор /curator #
// Info: Встановлює куратора групи #
// Syntax: !куратор [група] [нік] #
// Args: 2 #
// Rank: ADMIN #
//
$grp = R::findOne('groups', 'grp = ?', [$cmd[1]]);
if (!$grp) custom_error('Помилка 404', 'Група не знайдена!');
$s_user = R::findOne('users', 'nick = ?', [$cmd[2]]);
if (!$s_user) custom_error('Помилка 404', 'Користувач не знайдений');
$curator = R::dispense('curators');
$curator->user_id = $s_user['id'];
$curator->grp = $grp['grp'];
R::store($curator);
if ($s_user['rank'] == 'USER') $s_user->rank = 'CURATOR';
R::store($s_user);
$chat->sendMessage('✅ <b>Користувачу <a href="tg://user?id='.$s_user['tg_id'].'">'.$s_user['nick'].'</a> видані права на кураторство групи '.$grp['grp'].'</b>');