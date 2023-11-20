<?php
sleep(1);
set_time_limit(0); //Знімаємо обмеження по часу на виконання скрипту
require __DIR__.'/../config/start.php';
require __DIR__.'/../config/loader.php';
use api\{chat as chat, Bot as Bot, stats as stats};
pcntl_fork();
$bot = new Bot($bot_token);
$sec = 2; //Затримка
$cof = 1.1; //Множний за кожний хід
$user = new User();
if(!$user->loadByID($argv[1])) die('[DAEMON/AVIATOR] User not found');
if ($user->LocalStorageGet('game') != 'aviator') die('[DAEMON/AVIATOR] Aviator don`t started');
$game['bet'] = $user->LocalStorageGet('bet');
$game['chat_tg_id'] = $user->LocalStorageGet('chat_tg_id');
$game['msg_tg_id'] = $user->LocalStorageGet('msg_tg_id');
$user->LocalStorageSet('pid', getmypid());
$chat = new chat($game['chat_tg_id']);
$blank = '⠀';
$airplane = '✈';
$result = $chat->editMessageText('<b>🚀 Авіатор</b>
'.$blank.'
'.$blank.'
'.$blank.'
'.$airplane.'
<em>Взлітаємо!!!</em>', null, $game['msg_tg_id']);
$moves = 0;
$profit = $game['bet'];
function writeMap($move) {
    global $blank;
    global $airplane;
    $x = 11; //кількість клітинок по X
    $y = 4; //кількість клітинок по Y
    $all = $x*$y-1;
    $out = '';
    for ($i = 0; $i < $all; $i++) $map[$i] = $blank;
    if ($move < 10) {
        if ($move < 3) $map[33+$move] = $airplane;
        elseif ($move < 6) $map[22+$move] = $airplane;
        elseif ($move < 8) $map[11+$move] = $airplane;
        else $map[0+$move] = $airplane;
    } else {
        $map[10] = $airplane;
    }
    $pos = 0;
    for ($i = 0; $i < $y; $i++) {
        for ($k = 0; $k < $x; $k++) {
            if ($k == 0) $out .= '
';
            $out .= $map[$pos];
            $pos++;
        }
    }
    return $out;
}
$all_moves = $user->LocalStorageGet('game_all_moves');
echo 'all_moves: '.$all_moves.PHP_EOL;
sleep($sec);
while (json_decode(R::load('users', $user->user['id'])['tmp'], true)['game'] == 'aviator' && $moves <= $all_moves) {
    if ($user->LocalStorageGet('game_pid') != getmypid()) die();
    if ($moves >= $all_moves) {
        //Завершення гри == програш
        stats::aviator(($game['bet']*-1), $moves);
        $chat->editMessageText('<b>🚀 Авіатор [улетів]</b>

<b>= Літак улетів [програш] =</b>

Ставка: <b>'.$game['bet'].'💰</b>
Можливий виграш: <b>'.round($profit).'💰</b>

✈ Ходів: <b>'.$moves.'</b>
', null, $game['msg_tg_id']);
        $game = null;
        $user->LocalStorageClear();
        $chat->update('aviator_started', 0);
        die('RESULT: 0');
    }
    //хід
    $profit *= $cof;
    $moves++;
    $map = writeMap($moves);
    $keyboard[0][0]['text'] = 'Забрати '.round($profit).'💰';
    $keyboard[0][0]['callback_data'] = 'aviator_get_'.$user->user['id'].'_'.round($profit).'_'.$moves;
    $chat->editMessageText('<b>🚀 Авіатор</b>'.$map.'
<em>Ходів: '.$moves.'</em>', ['inline_keyboard' => $keyboard], $game['msg_tg_id']);
    sleep($sec);
}