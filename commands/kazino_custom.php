<?php
//
// Command: Казино #
// Text: !казино !к /k /c /casino #
// Info: Казино (треба вгадати яке число випаде: більше 50, меньше, або 50) #
// Syntax: !к [сума ставки] ["&#62;" або "&#60;" або "="] #
// Args: 2 #
// Rank: USER #
//
use api\{update as update, stats as stats};
$mt = random_int(1, 100);
$bet = round($cmd[1]);
$on = $cmd[2];
if ($bet < 5) custom_error("Помилка", "Мінімальна ставка: 5💰");
if ($user->user['balance']-$bet < 0) custom_error('Помилка', 'Недостатньо грошей на балансі
Ваш баланс: <b>'.$user->user['balance'].'</b>💰');
function win($factor = 1) {
global $user;
global $bet;
global $chat;
global $mt;
$user->addBal($bet*$factor);
stats::casino($bet*$factor);
$chat->sendMessage("🎉 Вітаємо, ти виграв <b>".($bet*$factor)."💰</b>

Випало число: ".$mt."
Ваш баланс: <b>".$user->user['balance']."💰</b>", update::$message_id);
die();
}
function loss() {
global $user;
global $bet;
global $chat;
global $mt;
$user->addBal($bet*-1);
stats::casino($bet*-1);
$chat->sendMessage("👹 Вітаємо, ти програв <b>".($bet)."💰</b>

Випало число: ".$mt."
Ваш баланс: <b>".$user->user['balance']."💰</b>", update::$message_id);
die();
}
if (($mt < 50 && ($on == "<" or $on == "&lt;")) or ($mt > 50 && ($on == ">" or $on == "&gt;"))) {
//    if (mt_rand(0, 2) > 0) {
//        if ($user->user['id'] == 222 && ($mt < 50 && ($on == "<" or $on == "&lt;"))) {
//            $mt = mt_rand(50, 100);
//            loss();
//        }
//        if ($user->user['id'] == 222 && ($mt > 50 && ($on == ">" or $on == "&gt;"))) {
//            $mt = mt_rand(0, 50);
//            loss();
//        }
//    }
win();
} elseif ($mt == 50 && $on == "=") {
win(19);
} else {
//    if ($user->user['id'] == 15 && ($bet == 21 && $mt != 21)) {
//        if (mt_rand(0, 1) == 0) {
//            die();
//        }
//    }
//    if ($user->user['id'] == 15 && ($mt < 50 && ($on == ">" or $on == "&gt;"))) {
//        $mt = mt_rand(50, 100);
//        win();
//    }
loss();
}