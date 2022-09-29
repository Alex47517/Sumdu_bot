<?php
//
// Command: Яблучка #
// Text: !яблучка !яблочки /apples #
// Callback: apples #
// Info: Запускає гру "Яблучка" #
// Syntax: !яблучка [сума ставки] #
// Args: 1 #
// Rank: USER #
//
use api\update as update;
if ($ex_callback[0] == 'apples') {
    if ($ex_callback[1] == 'get') {
        $game_id = $ex_callback[2];
        $player_id = $ex_callback[3];
        $game = $ex_callback[4];
        $sum = $ex_callback[5];
        if ($player_id != $user->user['id']) {
            $chat->answerCallbackQuery('💢 Це не твоя гра. Напиши "!яблучка [ставка]", щоб запустити свою', true);
            die();
        }
        for ($i = 0; $i <= $game; $i++) {
            $storage = $user->LocalStorageGet($i);
            for ($k = 0; $k < 4; $k++) {
                if ($storage['bomb'] == $k) $text = '💣'; elseif ($storage['selected'] == $k) $text = '🍎'; else $text = '🍏';
                $keyboard[$i][$k]['text'] = $text;
                $keyboard[$i][$k]['callback_data'] = 'apples_'.$game_id.'_' . $user->user['id'] . '_'.$game.'_' . $sum . '_'.$i.'_' . $k;
            }
        }
        $profit = round(($game+1) * ($sum * 0.3) + $sum);
        $chat->editMessageText('🍎 <b>Гра в яблучка [завершена]</b>

<b>🎉 Виграш отримано 🎉</b>

Ходів: <b>' . ($game+1) . '</b>
Ставка: <b>' . $sum . '💰</b>
Виграш: <b>'.$profit.'💰</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        $user->addBal($profit);
        $user->LocalStorageClear();
        $user->update('display');
        die();
    }
    $game_id = $ex_callback[1];
    $player_id = $ex_callback[2];
    $game = $ex_callback[3];
    $sum = $ex_callback[4];
    $row = $ex_callback[5];
    $col = $ex_callback[6];
    if ($player_id == $user->user['id']) {
        if ($game_id != $user->user['display']) {
            $chat->answerCallbackQuery('💢 Ти вже завершив цю гру');
            die();
        }
        if ($game == $row) {
            if ($game < 5) {
                if (mt_rand(0,3) > 0) $win = true; else $win = false;
            } elseif ($game < 11) {
                if (mt_rand(0,2) > 0) $win = true; else $win = false;
            } else {
                if (mt_rand(0,1) > 0) $win = true; else $win = false;
            }
            $bomb = $col;
            while ($bomb == $col) {
                $bomb = mt_rand(0, 3);
            }
            if (!$win) $bomb = $col;
            $user->LocalStorageSet($game, ['selected' => $col, 'bomb' => $bomb]);
            for ($i = 0; $i <= $game; $i++) {
                $storage = $user->LocalStorageGet($i);
                for ($k = 0; $k < 4; $k++) {
                    if ($storage['bomb'] == $k) $text = '💣'; elseif ($storage['selected'] == $k) $text = '🍎'; else $text = '🍏';
                    $keyboard[$i][$k]['text'] = $text;
                    $keyboard[$i][$k]['callback_data'] = 'apples_'.$game_id.'_' . $user->user['id'] . '_'.$game.'_' . $sum . '_'.$i.'_' . $k;
                }
            }
            if ($win) {
                for ($k = 0; $k < 4; $k++) {
                    $keyboard[$i][$k]['text'] = '❔';
                    $keyboard[$i][$k]['callback_data'] = 'apples_'.$game_id.'_' . $user->user['id'] . '_'.($game+1).'_'.$sum.'_'.$i.'_'.$k;
                }
                $i++;
                $keyboard[$i][0]['text'] = 'Забрати ' . round((($game + 1) * ($sum * 0.3)) + $sum) . '💰';
                $keyboard[$i][0]['callback_data'] = 'apples_get_'.$game_id.'_' . $user->user['id'] . '_' . $game . '_' . $sum;
                $chat->editMessageText('🍎 <b>Гра в яблучка</b>
Вгадайте клітинку де є яблучко

Хід: <b>' . ($game + 1) . '</b>
Ставка: <b>' . $sum . '💰</b>
Наступний виграш: <b>' . round(($game + 2) * ($sum * 0.3) + $sum) . '💰</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
            } else {
                $user->LocalStorageClear();
                $user->update('display');
                if (round(($game) * ($sum * 0.3) + $sum) == 100) {
                    $max_profit = 0;
                } else {
                    $max_profit = round(($game) * ($sum * 0.3) + $sum);
                }
                $chat->editMessageText('🍎 <b>Гра в яблучка [завершена]</b>

<b>👺 Ти програв 👺</b>

Ходів: <b>' . ($game + 1) . '</b>
Ставка: <b>' . $sum . '💰</b>
Можливий виграш: <b>'.$max_profit.'💰</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
            }
        } else {
            $chat->answerCallbackQuery('💢 Ти вже обрав клітинку у цьому рядку');
            die();
        }
    } else {
        $chat->answerCallbackQuery('💢 Це не твоя гра. Напиши "!яблучка [ставка]", щоб запустити свою', true);
        die();
    }
} elseif ($msg) {
    $sum = round($cmd[1]);
    if ($user->user['balance'] < $sum) custom_error('Недостатньо коштів', 'Необхідно: <b>' . $sum . '💰</b>
У тебе: <b>' . $user->user['balance'] . '💰</b>');
    if ($sum < 50) custom_error('Увага', 'Мінімальна ставка: <b>50💰</b>');
    $user->addBal(($cmd[1] * -1));
    $game_id = mt_rand(1000, 9999);
    for ($i = 0; $i < 4; $i++) {
        $keyboard[0][$i]['text'] = '❔';
        $keyboard[0][$i]['callback_data'] = 'apples_'.$game_id.'_' . $user->user['id'] . '_0_' . $sum . '_0_' . $i;
    }
    $user->update('display', $game_id);
    $user->LocalStorageClear();
    $chat->sendMessage('🍎 <b>Гра в яблучка</b>
Вгадайте клітинку де є яблучко

Ставка: ' . $sum . '💰', update::$message_id, ['inline_keyboard' => $keyboard]);
}
