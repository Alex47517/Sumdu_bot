<?php
use api\{update as update, chat as chat};

$initiators = ['!створити команду', '!создать команду', '/create_command'];
if (in_array($msg, $initiators)) {
    Permissions::Admin($user->user);
    $user->LocalStorageClear();
    $user->update('display', 'creating_command_part1');
    $chat->sendMessage('✏ <b>Напишіть назву команди</b>

<em>Приклад: test</em>'); die();
}
if ($user->user['display'] == 'creating_command_part1' && $msg) {
    Permissions::Admin($user->user);
    if (R::count('commandfiles', 'name = ?', [$msg])) {
        custom_error('Команда с такою назвою вже існує', 'Придумайте іншу', true);
    }
    $user->LocalStorageSet('name', $msg);
    $user->update('display', 'creating_command_part2');
    $chat->sendMessage('✏ <b>Напишіть код команди</b>

<em>Мова програмування: PHP</em>
<em>Інформацію щодо зумовлених змінних можете знайти у довідці до цієї команди</em>'); die();
}
if ($user->user['display'] == 'creating_command_part2' && $msg) {
    Permissions::Admin($user->user);
    $user->LocalStorageSet('code', $msg);
    $user->update('display', 'creating_command_part3');
    $chat->sendMessage('✏ <b>Напишіть текстові ініціатори</b>

Якщо треба декілька - перерахуйте їх через пробіл (space)

<em>Приклад: !тест /test</em>

<em>Для пропуску - /next</em>'); die();
}
if ($user->user['display'] == 'creating_command_part3' && $msg) {
    Permissions::Admin($user->user);
    if ($msg != '/next' && $msg != '/next@'.$bot_username) {
        $user->LocalStorageSet('text', $msg);
    }
    $user->update('display', 'creating_command_part4');
    $chat->sendMessage('✏ <b>Напишіть display ініціатори</b>

Якщо треба декілька - перерахуйте їх через пробіл (space)

<em>Приклад: test_part1 test_part2</em>

<em>Для пропуску - /next</em>'); die();
}
if ($user->user['display'] == 'creating_command_part4' && $msg) {
    Permissions::Admin($user->user);
    if ($msg != '/next' && $msg != '/next@'.$bot_username) {
        $user->LocalStorageSet('display', $msg);
    }
    $user->update('display', 'creating_command_part5');
    $chat->sendMessage('✏ <b>Напишіть callback ініціатори</b>

Якщо треба декілька - перерахуйте їх через пробіл (space)

<em>Приклад: test_button1 test_button2</em>

<em>Для пропуску - /next</em>'); die();
}
if ($user->user['display'] == 'creating_command_part5' && $msg) {
    Permissions::Admin($user->user);
    if ($msg != '/next' && $msg != '/next@'.$bot_username) {
        $user->LocalStorageSet('callback', $msg);
    }
    $user->update('display', 'creating_command_part6');
    $chat->sendMessage('✏ <b>Напишіть которкий опис команди</b>

⚠ Увага! Заборонено використовувати перенесення рядків

<em>Приклад: Тест працездатності бота</em>'); die();
}
if ($user->user['display'] == 'creating_command_part6' && $msg) {
    Permissions::Admin($user->user);
    $user->LocalStorageSet('info', str_replace(PHP_EOL, ' ', $msg));
    $user->update('display', 'creating_command_part7');
    $chat->sendMessage('✏ <b>Напишіть мінімальну кількість аргументів команди</b>

<em>Приклад: 1</em>
<em>Якщо Ви сворюєте команду: !тест [нік] і "нік" користувач повинен обов\'язково вказати => 1 аргумент</em>'); die();
}
if ($user->user['display'] == 'creating_command_part7' && ($msg or $msg == 0)) {
    Permissions::Admin($user->user);
    $user->LocalStorageSet('args', $msg);
    $user->update('display', 'creating_command_part8');
    $chat->sendMessage('✏ <b>Напишіть синтаксис команди</b>

<em>Приклад: !тест [нік]</em>'); die();
}
if ($user->user['display'] == 'creating_command_part8' && ($msg or $msg == 0)) {
    Permissions::Admin($user->user);
    $user->LocalStorageSet('syntax', $msg);
    $user->update('display', 'creating_command_part9');
    $chat->sendMessage('✏ <b>Напишіть мінімальний ранг для запуску команди</b>

<em>Приклад: ADMIN</em>

<code>ChatAdmin</code><em> - Дозволити адміністраторам чату та ADMIN</em>

<em>Дозволити всім - /next</em>'); die();
}
if ($user->user['display'] == 'creating_command_part9' && $msg) {
    Permissions::Admin($user->user);
    if ($msg == '/next' or $msg == '/next@'.$bot_username) $msg = 'USER';
    $user->LocalStorageSet('rank', $msg);
    $user->update('display');
    if (!Permissions::Owner($user->user, true)) {
        $command = R::dispense('checkingcommands');
        $command->info = $user->user['tmp'];
        $command->user_id = $user->user['id'];
        $command->chat_id = $chat->chat['id'];
        R::store($command);
        $chat->sendMessage('⏳ <b>Команда #'.$command['id'].' перевіряється...</b>

<em>Бот повідомить Вас про результат</em>');
        $owner = new \api\chat($chat_for_checkcodes);
        $keyboard[0][0]['text'] = '❌ Заборонити';
        $keyboard[0][0]['callback_data'] = 'blockcustomcommand_'.$command['id'];
        $keyboard[0][1]['text'] = '✅ Дозволити';
        $keyboard[0][1]['callback_data'] = 'allowcustomcommand_'.$command['id'];
        $owner->sendMessage('⚠ <b><a href="tg://user?id='.$user->user['id'].'">'.$user->user['nick'].'</a> хоче створити глобальну команду</b>

<b>Ім\'я: </b>'.$user->LocalStorageGet('name').'
<b>Інфо: </b>'.$user->LocalStorageGet('info').'

<b>== Ініціатори ==</b>

Text:
'.$user->LocalStorageGet('text').'

Display:
'.$user->LocalStorageGet('display').'

Callback:
'.$user->LocalStorageGet('callback').'

<b>== Інша інформація ==</b>

ARGS:
'.$user->LocalStorageGet('args').'

SYNTAX:
'.$user->LocalStorageGet('syntax').'

RANK:
'.$user->LocalStorageGet('rank').'

<b>Код:</b>');
        $owner->sendMessage(''.$user->LocalStorageGet('code').'');
        $owner->sendMessage('❓ <b>Дозволити створити команду?</b>
#'.$command['id'], null, ['inline_keyboard' => $keyboard]);
        die();
    } else {
        new_command(json_decode($user->user['tmp'], true));
        $chat->sendMessage('✅ <b>Команда успішно створена!</b>

Не забудьте виконати: <code>!оновити команди</code>'); die();
    }
}
$ex_callback = explode('_', update::$callback_data);
if ($ex_callback[0] == 'blockcustomcommand' or $ex_callback[0] == 'allowcustomcommand') {
    $command_db = R::load('checkingcommands', $ex_callback[1]);
    $client = R::load('users', $command_db['user_id']);
    $client_chat = R::load('chats', $command_db['chat_id']);
    if ($command_db && $client && $client_chat) {
        $command = json_decode($command_db['info'], true);
        if ($ex_callback[0] == 'allowcustomcommand') {
            $client_chat = new chat($client_chat['tg_id']);
            new_command($command);
            $client_chat->sendMessage('✅ <b><a href="tg://user?id='.$client->user['id'].'">'.$client->user['nick'].'</a>, Ваша команда #'.$command_db['id'].' успішно створена!</b>

Не забудьте виконати: <code>!оновити команди</code>');
            $chat->editMessageText('✅ <b>Команда створена!</b>', null, update::$btn_id);
            R::trash($command_db);
            die();
        } else {
            $client_chat->sendMessage('💢 <b><a href="tg://user?id='.$client->user['id'].'">'.$client->user['nick'].'</a>, Ваша команда #'.$command_db['id'].' не пройшла перевірку</b>');
            $chat->editMessageText('✅ <b>Заборонено!</b>', null, update::$btn_id);
            R::trash($command_db); die();
        }
    } else {
        custom_error('Помилка 404', 'Не знайдено');
    }
}