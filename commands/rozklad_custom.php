<?php
//
// Command: Розклад #
// Text: !розклад !пари !пары /sh #
// Info: Виводить розклад #
// Syntax: !розклад [дата*] #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
$grp = $user->user['grp'];
if (!$cmd[1]) $date = date('d.m.Y');
else $date = $cmd[1];
$result = $chat->sendMessage('⏳ <b>Розклад</b> - Запуск NodeJS', update::$message_id);
$edit = $result->result->message_id;
$group = R::findOne('groups', 'grp = ?', [$grp])['gcode'];
if (!$group) custom_error('Помилка 404', 'Код групи "'.$user->user['grp'].'" не знайдений');
require __DIR__.'/../lib/Process.php';
//echo "Jelya123" | su -l alex -c "node /home/alex/websites/bot.sumdu.fun/test/NodeJS/getscreen.js 03.10.2022 03.10.2022 1002492 458746251 2111895 2111899"
$command = 'echo "'.$nodeJS_password.'" | su -l '.$nodeJS_username.' -c "node '.__DIR__.'/../NodeJS/getscreen.js '.$date.' '.$date.' '.$group.' '.$chat->chat_id.' '.update::$message_id.' '.$edit.'"';
//$chat->sendMessage($command);
$process = new Process($command);
//$chat->sendMessage($process->getPid());
die();