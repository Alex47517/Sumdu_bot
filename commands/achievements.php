<?php
//
// Command: Досягнення #
// Text: !досягнення !достижения /achievements #
// Callback: achievements achievement #
// Info: Відображає досягнення користувача #
// Syntax: !досягнення [нік*/створити*/видати*/відповідь на повідомлення*] #
// Args: 0 #
// Rank: USER #
//
use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};
if ($cmd[1] == 'створити') {
    Permissions::Admin($user->user);
    if (!$cmd[4]) custom_error('Помилка', '!досягнення створити [emoji] [назва] [опис]');
    $emoji = $cmd[2];
    $name = $cmd[3];
    $description = str_replace($cmd[0].' '.$cmd[1].' '.$cmd[2].' '.$cmd[3].' ', '', $msg);
    $achievement = R::findOne('achievements', 'emoji = ?', [$emoji]);
    if ($achievement['id']) custom_error('Помилка', 'Досягнення з таким emoji вже є');
    $achievement = R::dispense('achievements');
    $achievement->emoji = $emoji;
    $achievement->name = $name;
    $achievement->description = $description;
    R::store($achievement);
    $chat->sendMessage('<b>✅ Досягнення додано!</b>

'.$achievement['emoji'].' '.$achievement['name'].'

<em>'.$achievement['description'].'</em>');
} elseif ($cmd[1] == 'видати') {
    Permissions::Admin($user->user);
    $s_user = new User();
    if (!$cmd[3]) custom_error('Помилка', '!досягнення видати [нік/відповідь на повідомлення*] [emoji/назва]');
    if ($cmd[1]) $s_user->loadByNick($cmd[2]);
    elseif (update::$reply_user_id) $s_user->loadByTGID(update::$reply_user_id);
    if (!$s_user->user['id']) custom_error('Помилка 404', 'Користувач не знайдений');
    $achievement = R::findOne('achievements', 'emoji = ?', [$cmd[3]]);
    if (!$achievement['id']) $achievement = R::findOne('achievements', 'name = ?', [$cmd[3]]);
    if (!$achievement['id']) custom_error('Помилка 404', 'Досягнення не знайдено');
    $user_achievement = R::dispense('userachievements');
    $user_achievement->user_id = $s_user->user['id'];
    $user_achievement->achievement_id = $achievement['id'];
    $user_achievement->date = date('U');
    R::store($user_achievement);
    $chat->sendMessage('✅ Користувачу <b><a href="tg://user?id='.$s_user->user['tg_id'].'">'.$s_user->user['nick'].'</a></b> видано досягнення <b>'.$achievement['emoji'].' '.$achievement['name'].'</b>');
} elseif (is_numeric($ex_callback[1]) && !$cmd[0]) {
    $achievement = R::load('achievements', $ex_callback[1]);
    $chat->answerCallbackQuery('❗Надіслано новим повідомленням', true);
    $chat->sendMessage('<b>ℹ Інформація про досягнення для <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a></b>

<b>'.$achievement['emoji'].' '.$achievement['name'].':</b>

<em>'.$achievement['description'].'</em>');
} else {
    $s_user = new User();
    if ($cmd[1]) $s_user->loadByNick($cmd[1]);
    elseif (!$cmd[1] && !update::$reply_user_id) $s_user = $user;
    elseif (update::$reply_user_id) $s_user->loadByTGID(update::$reply_user_id);
    if (!$s_user->user['id']) custom_error('Помилка 404', 'Користувач не знайдений');
    $user_achievements = R::getAll('SELECT * FROM userachievements WHERE `user_id` = ?', [$s_user->user['id']]);
    if (count($user_achievements) < 1) {
        $chat->sendMessage('<b>🙄 Користувач не має досягнень</b>');
    } else {
        $line = 0;
        $col = 0;
        foreach ($user_achievements as $key => $user_achievement) {
            $achievement = R::load('achievements', $user_achievement['achievement_id']);
            if ($achievement) {
                $keyboard[$line][$col]['text'] = $achievement['emoji'];
                $keyboard[$line][$col]['url'] = $achievement['telegraph'];
                $col++;
                if ($col >= 6) { $col = 0; $line++; }
            }
        }
        $chat->sendMessage('<b>🏆 Досягнення користувача <a href="tg://user?id='.$s_user->user['tg_id'].'">'.$s_user->user['nick'].'</a>:</b>', null, ['inline_keyboard' => $keyboard]);
    }
}