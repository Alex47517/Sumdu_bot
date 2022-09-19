<?php
//
// Command: Казино #
// Text: !казино !к /k /c /casino #
// Info: Казино (треба вгадати яке число випаде: більше 50, меньше, або 50) #
// Syntax: !к [сума ставки] ["&#62;" або "&#60;" або "="] #
// Args: 2 #
// Rank: USER #
//
use api\update as update;
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
$chat->sendMessage("🎉 Вітаємо, ти виграв <b>".($bet*2)."💰</b>

Випало число: ".$mt."
Ваш баланс: <b>".$user->user['balance']."💰</b>", update::$message_id); die();
}
function loss() {
global $user;
global $bet;
global $chat;
global $mt;
$user->addBal($bet*-1);
$chat->sendMessage("👹 Вітаємо, ти програв <b>".($bet)."💰</b>

Випало число: ".$mt."
Ваш баланс: <b>".$user->user['balance']."💰</b>", update::$message_id); die();
}
if (($mt < 50 && ($on == "<" or $on == "&lt;")) or ($mt > 50 && ($on == ">" or $on == "&gt;"))) {
win();
} elseif ($mt == 50 && $on == "=") {
win(19);
} else {
loss();
}