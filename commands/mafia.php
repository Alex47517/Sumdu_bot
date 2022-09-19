<?php
//
// Command: Мафія #
// Text: !мафія !мафия /mafia #
// Info: Запускає гру "мафія" #
// Syntax: !мафія #
// Args: 0 #
// Rank: USER #
//
$last = R::getAll('SELECT * FROM mafia WHERE `chat_id` = ? ORDER BY `id` DESC LIMIT 1', [$chat->chat['id']])[0];
if ($last['status'] != 'end' && $last['status']) custom_error('Помилка!', 'У цьому чаті вже запущена гра');
$mafia = R::dispense('mafia');
$mafia->chat_id = $chat->chat['id'];
$mafia->date = date('U');
$mafia->status = 'waiting';
R::store($mafia);
$mafia_player = R::dispense('mafiaplayers');
$mafia_player->game = $mafia['id'];
$mafia_player->user_id = $user->user['id'];
$mafia_player->role = null;
R::store($mafia_player);
$chat->sendMessage('⏳ <b>Гру запущено!</b>
Очікуємо гравців...');
