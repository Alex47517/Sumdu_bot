<?php
//
// Command: Nick #
// Text: !нік !ник /nick #
// Info: Виводить нік користувача #
// Syntax: !нік [id/відповідь на повідомлення] #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
$s_user = new User();
if (update::$reply_user_id) {
$s_user->loadByTGID(update::$reply_user_id);
} elseif (is_numeric($cmd[1])) {
$s_user->loadByID(round($cmd[1]));
} else {
    $chat->sendMessage("👤 Ваш нік: <code>".$user->user["nick"]."</code>"); die();
}
if (!$s_user->user) custom_error("Помилка 404", "Користувач не знайдений");
$chat->sendMessage("👤 Нік користувача: <code>".$s_user->user["nick"]."</code>");