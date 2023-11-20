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
if (in_array($cmd[1], ['встановити', 'установить', 'set'])) {
    if ($cmd[2]) {
        $s_user = R::findOne('users', 'username = ?', [$cmd[2]]);
        if (!$s_user) {
            $s_user = R::findOne('users', 'nick = ?', [$cmd[2]]);
            if (!$s_user) {
                $cmd[2] = str_replace(' ', '', $cmd[2]);
                if (!$cmd[2] or $cmd[2] == '' or mb_strlen($cmd[2], 'UTF-8') < 3 or preg_match('/^[a-zA-Zа-яА-ЯіІїЇєЄ0-9]+$/', $cmd[2]) !== 1) custom_error('Помилка', 'Такий нік неможливо встановити');
                $user->update('nick', remove_emoji($cmd[2]));
                $chat->sendMessage('✅ Ви змінили нік на <b>'.remove_emoji($cmd[2]).'</b>');
                die();
            }
        }
        custom_error('Помилка', 'Цей нік вже використовується');
    } else custom_error('Синтаксис:', '!нік встановити [новий нік]');
    die();
}
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