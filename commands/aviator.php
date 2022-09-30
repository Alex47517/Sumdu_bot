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
            $chat->editMessageText('<b>🚀 Авіатор [завершено]</b>

<b>🎉 Виграш отримано🎉</b>

Ставка: <b>'.$user->LocalStorageGet('bet').'💰</b>
Виграш: <b>'.round($ex_callback[3]).'💰</b>

✈ Ходів: <b>'.$ex_callback[4].'</b>
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
    $process = new Process('php -f ' . __DIR__ . '/../daemons/aviator.php ' . $user->user['id'] . '');
    $processId = $process->getPid();
    $user->LocalStorageSet('game_pid', $processId);
}