<?php
//
// Command: Інфо #
// Text: /report /rep !репорт !реп !пред #
// Callback: moders #
// Info: Поскажитися на порушення у чаті ЕлІТ #
// Syntax: !report [відповідь на повідомлення*] #
// Args: 0 #
// Rank: USER #
//
use api\{update as update, ChatMember as ChatMember, chat as chat};
if (($ex_callback[0] == 'moders' or $cmd[0] == '!пред') && update::$chat['id'] == -1001176334270) {
    $intruder = new User();
    if ($msg) {
        if (!$intruder->loadByNick($cmd[1])) {
            $chat->sendMessage('♨ <b>Користувач не знайдений</b>');
            die();
        }
    } else {
        if (!$intruder->loadByID($ex_callback[2])) {
            $chat->sendMessage('ERR 404');
            die();
        }
    }
    if ($ex_callback[1] == 'warn' or $cmd[0] == '!пред') {
        $intruder_chat = new chat($intruder->user['tg_id']);
        $result = $intruder_chat->sendMessage('👺 <b>Вітаємо, '.$intruder->user['nick'].'!</b>

Ми помітили, що ви порушили правила ЕлІТ чату. Просимо дотримуватися встановлених правил, щоб створити комфортне та продуктивне середовище для всіх учасників.

Якщо у вас є запитання щодо правил, будь ласка, зверніться до адміністрції або використайте команду <code>!правила</code>. Повторне порушення може призвести до тимчасового або постійного блокування повідомлень у чаті.

Дякуємо за розуміння!
===
<em>Адміністрація чату ЕлІТ</em>');
        if ($ex_callback[3]) {
            $add = '<a href="https://t.me/c/1195752130/'.$ex_callback[3].'">Посилання на порушення</a>';
        } else $add = null;
        if ($result) {
            $keyboard[0][0]['text'] = '⛔ Видати мут';
            $keyboard[0][0]['callback_data'] = 'moders_mute_' . $intruder->user['id'].'_'.$ex_callback[3];
            $text = '✅ Користувачу <code>'.$intruder->user['nick'].'</code> відправлено попередження!

Адміністратор: <b>'.$user->user['nick'].'</b>
'.$add;
            if (!$msg) $chat->editMessageText($text, ['inline_keyboard' => $keyboard], update::$btn_id);
            else $chat->sendMessage($text, update::$message_id, ['inline_keyboard' => $keyboard]);
        } else {
            mute($intruder->user['id'],  600, 'Порушення правил чату (Бот не зміг відправити попередження)', '[BOT]', true, -1001195752130);
            $text = '⚠ <b>Не вдалося відправити попередження</b>
Користувачу <code>'.$intruder->user['nick'].'</code> видано мут на 10 хвилин

Адміністратор: <b>'.$user->user['nick'].'</b>
'.$add;
            if (!$msg) $chat->editMessageText($text, null, update::$btn_id);
            else $chat->sendMessage($text, update::$message_id);
        }
    }
    if ($ex_callback[1] == 'mute') {
        $intruder = new User();
        if (!$intruder->loadByID($ex_callback[2])) {
            $chat->sendMessage('ERR 404');
            die();
        }
        $keyboard[0][0]['text'] = '5 хв';
        $keyboard[0][0]['callback_data'] = 'moders_mute-confirmed_300_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[0][1]['text'] = '10 хв';
        $keyboard[0][1]['callback_data'] = 'moders_mute-confirmed_600_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[0][2]['text'] = '20 хв';
        $keyboard[0][2]['callback_data'] = 'moders_mute-confirmed_1200_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[0][3]['text'] = '30 хв';
        $keyboard[0][3]['callback_data'] = 'moders_mute-confirmed_1800_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[1][0]['text'] = '1 год';
        $keyboard[1][0]['callback_data'] = 'moders_mute-confirmed_3600_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[1][1]['text'] = '3 год';
        $keyboard[1][1]['callback_data'] = 'moders_mute-confirmed_10800_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[1][2]['text'] = '6 год';
        $keyboard[1][2]['callback_data'] = 'moders_mute-confirmed_21600_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[1][3]['text'] = '12 год';
        $keyboard[1][3]['callback_data'] = 'moders_mute-confirmed_43200_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[2][0]['text'] = '1 д';
        $keyboard[2][0]['callback_data'] = 'moders_mute-confirmed_86400_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[2][1]['text'] = '2 д';
        $keyboard[2][1]['callback_data'] = 'moders_mute-confirmed_172800_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[2][2]['text'] = '3 д';
        $keyboard[2][2]['callback_data'] = 'moders_mute-confirmed_259200_' . $intruder->user['id'].'_'.$ex_callback[3];
        $keyboard[2][3]['text'] = '5 д';
        $keyboard[2][3]['callback_data'] = 'moders_mute-confirmed_432000_' . $intruder->user['id'].'_'.$ex_callback[3];
        $chat->editMessageText('♨ <b>Мутимо '.$intruder->user['nick'].'</b>
Оберіть термін муту', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    }
    if ($ex_callback[1] == 'mute-confirmed') {
        $intruder = new User();
        if (!$intruder->loadByID($ex_callback[3])) {
            $chat->sendMessage('ERR 404');
            die();
        }
        mute($intruder->user['id'],  $ex_callback[2], 'Порушення правил чату', '[Адміністрація]', true, -1001195752130);
        if ($ex_callback[4]) {
            $add = '<a href="https://t.me/c/1195752130/'.$ex_callback[4].'">Посилання на порушення</a>';
        }
        $text = '✅ Користувачу <code>'.$intruder->user['nick'].'</code> видано мут

Термін: <b>'.Time::sec2time_txt($ex_callback[2]).'</b>
Адміністратор: <b>'.$user->user['nick'].'</b>
'.$add;
        $chat->editMessageText($text, null, update::$btn_id);
        die();
    }
} else {
    if (update::$chat['id'] != -1001195752130) die();
    if (!update::$reply['message_id']) {
        $chat->sendMessage('♨ Напишіть цю команду у відповідь на повідомлення порушника');
    } else {
        $moders_chat = new chat(-1001176334270);
        $intruder = new User();
        if (!$intruder->loadByTGID(update::$reply_user_id)) {
            $chat->sendMessage('ERR 404');
            die();
        }
        $keyboard[0][0]['text'] = '🔗 Перейти до повідомлення';
        $keyboard[0][0]['url'] = 'https://t.me/c/1195752130/' . update::$reply['message_id'];
        $keyboard[1][0]['text'] = '⚠ Попередити порушника';
        $keyboard[1][0]['callback_data'] = 'moders_warn_' . $intruder->user['id'].'_'.update::$reply['message_id'];
        $moders_chat->sendMessage('💡 <b>[REPORT]</b> Користувач <code>' . $user->user['nick'] . '</code> відправив репорт', null, ['inline_keyboard' => $keyboard]);
        $chat->sendMessage('✅ <b>Репорт відправлено!</b>');
    }
}
