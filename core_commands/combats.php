<?php

use api\{update as update, chat as chat};
if (update::$callback_data) $ex_callback = explode('_', update::$callback_data);
if ($user->user['display']) $ex_display = explode('_', $user->user['display']);
if ($msg == '⚔ Combats' or update::$callback_data == 'combats') {
    if (update::$callback_data) $chat->deleteMessage(update::$btn_id);
    $warriors = R::find('combatwarriors', 'user_id = ?', [$user->user['id']]);
    if (count($warriors) < 1) {
        $warrior = R::dispense('combatwarriors');
        $warrior->type = 'infantry';
        $warrior->name = 'Стартовий';
        $warrior->health = 100;
        $warrior->shield = 0;
        $warrior->custom = null;
        $warrior->level = 0;
        $warrior->user_id = $user->user['id'];
        R::store($warrior);
        $chat->sendMessage('ℹ <b>Ти отримав першого воїна (Піхотинець) безкоштовно!</b>

Радіус атаки: <b>1</b>
Сила атаки: <b>5 - 10</b>
Рівень: <b>0</b>');
    }
    $keyboard[0][0]['text'] = '🔪 В бій';
    $keyboard[0][0]['callback_data'] = 'combats_attack';
    $keyboard[1][0]['text'] = '📈 Статистика';
    $keyboard[1][0]['callback_data'] = 'combats_stats';
    $keyboard[2][0]['text'] = '👨‍✈️ Воїни';
    $keyboard[2][0]['callback_data'] = 'combats_warriors';
    $chat->sendMessage('⚔ <b>Combats</b>', update::$message_id, ['inline_keyboard' => $keyboard]);
}
if ($ex_callback[0] == 'combats') {
    if ($ex_callback[1] == 'warriors') {
        $warriors = R::find('combatwarriors', 'user_id = ?', [$user->user['id']]);
        $i = 0;
        foreach ($warriors as $warrior) {
            if ($warrior['type'] == 'infantry') $emoji = '🗡';
            if ($warrior['type'] == 'archer') $emoji = '🎯';
            $keyboard[$i][0]['text'] = $emoji.' '.$warrior['name'];
            $keyboard[$i][0]['callback_data'] = 'combats_warrior_'.$warrior['id'];
            $i++;
        }
        $keyboard[$i][0]['text'] = '💰 Найняти воїна';
        $keyboard[$i][0]['callback_data'] = 'combats_getwarrior';
        $keyboard[($i+1)][0]['text'] = '🔙 Назад 🔙';
        $keyboard[($i+1)][0]['callback_data'] = 'combats';
        $chat->editMessageText('⚔ <b>Combats</b> - Воїни', ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'getwarrior' && !$ex_callback[2]) {
        $keyboard[0][0]['text'] = '🗡 Піхотинець (10к💰)';
        $keyboard[0][0]['callback_data'] = 'combats_getwarrior_infantry';
        $keyboard[1][0]['text'] = '🎯 Стрілець (15к💰)';
        $keyboard[1][0]['callback_data'] = 'combats_getwarrior_archer';
        $keyboard[2][0]['text'] = '🔙 Назад 🔙';
        $keyboard[2][0]['callback_data'] = 'combats';
        $chat->editMessageText('⚔ <b>Combats</b> - Оберіть тип воїна', ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'getwarrior' && $ex_callback[2]) {
        if ($ex_callback[2] == 'infantry') $price = 10000; elseif ($ex_callback[2] == 'archer') $price = 15000;
        if ($user->user['balance'] < $price) { $chat->answerCallbackQuery('💢 Недостатньо грошей. В тебе: '.$user->user['balance'].'💰', true); die(); }
        $chat->editMessageText('⚔ <b>Combats</b> - Напиши Ім\'я для свого воїна', null, update::$btn_id);
        $user->update('display', 'combats_getwarrior_'.$ex_callback[2]);
    }
    if ($ex_callback[1] == 'healwarrior') {
        $warrior = R::load('combatwarriors', $ex_callback[2]);
        if (!$warrior) { $chat->answerCallbackQuery('♨ Воїна не знайдено'); die(); }
        if ($user->user['balance'] < 500) { $chat->answerCallbackQuery('💢 Недостатньо грошей. В тебе: '.$user->user['balance'].'💰', true); die(); }
        $user->addBal(-500);
        $warrior->health = 100;
        R::store($warrior);
        $ex_callback[1] = 'warrior';
    }
    if ($ex_callback[1] == 'shield') {
        $warrior = R::load('combatwarriors', $ex_callback[2]);
        if (!$warrior) { $chat->answerCallbackQuery('♨ Воїна не знайдено'); die(); }
        if ($user->user['balance'] < 100) { $chat->answerCallbackQuery('💢 Недостатньо грошей. В тебе: '.$user->user['balance'].'💰', true); die(); }
        $user->addBal(-100);
        if ($warrior['shield'] > 90) $warrior->shield = 100;
        else $warrior->shield += 10;
        R::store($warrior);
        $ex_callback[1] = 'warrior';
    }
    if ($ex_callback[1] == 'warrior') {
        $warrior = R::load('combatwarriors', $ex_callback[2]);
        if (!$warrior) { $chat->answerCallbackQuery('♨ Воїна не знайдено'); die(); }
        if ($warrior['type'] == 'infantry') $type = 'піхота 🗡';
        if ($warrior['type'] == 'archer') $type = 'лучник 🎯';
        if (!$warrior['custom']) $custom = 'відсутня';
        $i = 0;
        if ($warrior['health'] < 100) {
            $keyboard[$i][0]['text'] = '🩹 Вилікувати (500💰)';
            $keyboard[$i][0]['callback_data'] = 'combats_healwarrior_'.$warrior['id'];
            $i++;
        }
        if ($warrior['shield'] < 100 && $warrior['name'] != 'Стартовий') {
            $keyboard[$i][0]['text'] = '🛡 Щит +10 (100💰)';
            $keyboard[$i][0]['callback_data'] = 'combats_shield_'.$warrior['id'];
            $i++;
        }
        $keyboard[$i][0]['text'] = '🔙 Назад 🔙';
        $keyboard[$i][0]['callback_data'] = 'combats_warriors';
        $chat->editMessageText('⚔ <b>Combats</b> - Інформація про воїна
Ім\'я: <b>'.$warrior['name'].'</b>

<b>Тип: </b>'.$type.'
<b>HP: </b>'.$warrior['health'].'
<b>Щит: </b>'.$warrior['shield'].'
<b>Рівень: </b>'.$warrior['level'].'
<b>Сила атаки: </b>'.($warrior['level']*5+5).' - '.($warrior['level']*6+10).'
<b>Супер-здатність: </b>'.$custom, ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'attack') {
        $keyboard[0][0]['text'] = '👥 Проти гравця';
        $keyboard[0][0]['callback_data'] = 'combats_multiplayer';
        $keyboard[1][0]['text'] = '🦾 Проти бота';
        $keyboard[1][0]['callback_data'] = 'combats_singleplayer';
        $keyboard[2][0]['text'] = '🔙 Назад 🔙';
        $keyboard[2][0]['callback_data'] = 'combats';
        $chat->editMessageText('⚔ <b>Combats</b> - розпочати бій', ['inline_keyboard' => $keyboard], update::$btn_id); die();
    }
    if ($ex_callback[1] == 'singleplayer') {
        $keyboard[0][0]['text'] = '✔ Далі';
        $keyboard[0][0]['callback_data'] = 'combat-bot_start';
        $chat->editMessageText('⚔ <b>Combats</b> - супротивника знайдено!', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    }
    if ($ex_callback[1] == 'multiplayer') {
        $chat->answerCallbackQuery('⏳ Наразі недоступно', true); die();
    }
    if ($ex_callback[1] == 'stats') {
        $chat->answerCallbackQuery('⏳ Наразі недоступно', true); die();
    }
}
if ($ex_display[0] = 'combats') {
    if ($ex_display[1] == 'getwarrior' && $msg) {
        if ($ex_display[2] == 'infantry') $price = 10000; elseif ($ex_display[2] == 'archer') $price = 15000;
        if ($user->user['balance'] < $price) { $chat->sendMessage('💢 Недостатньо грошей. В тебе: '.$user->user['balance'].'💰'); $user->update('display'); die(); }
        if (strlen($msg) > 40) {
            $chat->sendMessage('🙄 <b>Занадто длинне ім\'я, обери коротше</b>'); die();
        }
        $user->addBal($price*-1);
        $type = $ex_display[2];
        $warrior = R::dispense('combatwarriors');
        $warrior->type = $type;
        $warrior->name = $msg;
        $warrior->health = 100;
        $warrior->shield = 0;
        $warrior->custom = null;
        $warrior->level = 0;
        $warrior->user_id = $user->user['id'];
        R::store($warrior);
        $user->update('display');
        $keyboard[0][0]['text'] = 'Ok';
        $keyboard[0][0]['callback_data'] = 'combats_warrior_'.$warrior['id'];
        $chat->sendMessage('✅ <b>Ви найняли воїна за '.$price.'💰</b>', null, ['inline_keyboard' => $keyboard]);
    }
}