<?php
//
// Command: Ранг #
// Text: !ранг /rank #
// Info: Змінює ранг користувача #
// Syntax: !ранг [нік/username користувача] [новий ранг] #
// Args: 2 #
// Rank: ADMIN #
//
$s_user = new User();
$s_user->loadByNick($cmd[1]);
if (!$s_user->user) $s_user->loadByUsername($cmd[1]);
if (!$s_user->user) custom_error("Помилка 404", "Користувач не знайдений");
if ($cmd[2] == "ADMIN" or $cmd[2] == "*") Permissions::Owner($user->user);
$s_user->update("rank", $cmd[2]);
$chat->sendMessage('✅ Користувачу <a href="tg://user?id='.$s_user->user['tg_id'].'">'.$s_user->user['nick'].'</a> видано ранг <b>'.$cmd[2].'</b>');