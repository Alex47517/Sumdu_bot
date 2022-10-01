<?php
//
// Command: Авіатор #
// Text: !авіатор !авиатор /aviator #
// Callback: aviator #
// Info: Запускає гру "Авіатор" #
// Syntax: !авіатор [сума ставки] #
// Args: 1 #
// Rank: USER #
//
require __DIR__.'/../lib/Process.php';
use api\update as update;
if ($ex_callback[0] == 'aviator') {
    if ($ex_callback[1] == 'get') {
        if ($ex_callback[2] == $user->user['id']) {
            if (!$user->LocalStorageGet('game')) {
                $chat->answerCallbackQuery('👿 На жаль ти не встиг(ла)', true); die();
            }
            $user->addBal(round($ex_callback[3]));
            $pid = $user->LocalStorageGet('game_pid');
            $process = new Process();
            $process->setPid($pid);
            $stopped = $process->stop(); // возвращает true или false
            $chat->update('aviator_started', 0);
            $all_moves = $user->LocalStorageGet('game_all_moves');
            if (mt_rand(0, 10) == 0) $all_moves = mt_rand(40, 50);
            $profit = $user->LocalStorageGet('bet');
            $cof = 1.1;
            for($i = 0; $i < $all_moves; $i++) $profit *= $cof;
            $chat->editMessageText('<b>🚀 Авіатор [завершено]</b>

<b>🎉 Виграш отримано🎉</b>

Ставка: <b>'.$user->LocalStorageGet('bet').'💰</b>
Виграш: <b>'.round($ex_callback[3]).'💰</b>

Можливий виграш: <b>'.round($profit).'💰</b>

🛩 Зупинений на <b>'.$ex_callback[4].'</b> ході
✈ Усього ходів: <b>'.$all_moves.'</b>
', null, update::$btn_id);
            $user->LocalStorageClear();
            die();
        } else $chat->answerCallbackQuery('💢 Це не твоя гра. Запусти свою: !авіатор [сума ставки]', true);
    }
    die();
}
if ($msg) {
    $bet = round($cmd[1]);
    if ($chat->chat['aviator_started']) custom_error('Зачекай 🤯', 'Тільки один гравець може грати одночасно у одному чаті');
    if ($bet < 150) custom_error('Авіатор', 'Мінімальна ставка <b>150💰</b>');
    if ($user->user['balance'] < $bet) custom_error('Недостатньо коштів!', 'Необхідно: <b>' . $bet . '💰</b>
У тебе: ' . $user->user['balance'] . '💰');
    $chat->update('aviator_started', 1);
    $user->addBal($bet * -1);
    $result = $chat->sendMessage('🚀 <b>Авіатор</b>

⏳ Завантаження...
<em>Запуск демона..</em>');
    $id = $result->result->message_id;
    $user->LocalStorageClear();
    $user->LocalStorageSet('game', 'aviator');
    $user->LocalStorageSet('bet', $bet);
    $user->LocalStorageSet('msg_tg_id', $id);
    $user->LocalStorageSet('chat_tg_id', $chat->chat_id);
    //Визначаємо скільки "ходів" зробе літак
    switch (mt_rand(0, 5)) {
        case 0: $all_moves = random_int(0, 1); break;
        case 1: $all_moves = random_int(2, 3); break;
        case 2: $all_moves = random_int(4, 5); break;
        case 3: $all_moves = random_int(6, 10); break;
        case 4: $all_moves = random_int(11, 15); break;
        case 5: $all_moves = random_int(21, 50); break;
        default:
            die('ERR');
    }
    $user->LocalStorageSet('game_all_moves', $all_moves);
    $process = new Process('php -f ' . __DIR__ . '/../daemons/aviator.php ' . $user->user['id'] . '');
    $processId = $process->getPid();
    $user->LocalStorageSet('game_pid', $processId);
}