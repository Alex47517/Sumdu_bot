<?php
//
// Command: Кубик #
// Text: !кубик /cube #
// Callback: cube #
// Info: Гра "Кубик" #
// Syntax: !кубик [ставка] #
// Args: 1 #
// Rank: USER #
//
use api\update as update;
if ($cmd[0]) {
if (!is_numeric($cmd[1]) or $cmd[1] < 10) custom_error("Помилка", "Мінімальна ставка - 10💰");
$bet = floor($cmd[1]);
if ($user->user["balance"] < $bet) {
custom_error("Недостатньо коштів!", "Ваш баланс: <b>".$user->user["balance"]."💰</b>");
}
$keyboard[0][0]["text"] = "1";
$keyboard[0][0]["callback_data"] = "cube_1_".$user->user['id'];
$keyboard[0][1]["text"] = "2";
$keyboard[0][1]["callback_data"] = "cube_2_".$user->user['id'];
$keyboard[0][2]["text"] = "3";
$keyboard[0][2]["callback_data"] = "cube_3_".$user->user['id'];
$keyboard[0][3]["text"] = "4";
$keyboard[0][3]["callback_data"] = "cube_4_".$user->user['id'];
$keyboard[0][4]["text"] = "5";
$keyboard[0][4]["callback_data"] = "cube_5_".$user->user['id'];
$keyboard[0][5]["text"] = "6";
$keyboard[0][5]["callback_data"] = "cube_6_".$user->user['id'];
$keyboard[1][0]["text"] = "1-2";
$keyboard[1][0]["callback_data"] = "cube_12_".$user->user['id'];
$keyboard[1][1]["text"] = "3-4";
$keyboard[1][1]["callback_data"] = "cube_34_".$user->user['id'];
$keyboard[1][2]["text"] = "5-6";
$keyboard[1][2]["callback_data"] = "cube_56_".$user->user['id'];
$keyboard[2][0]["text"] = "1-3";
$keyboard[2][0]["callback_data"] = "cube_123_".$user->user['id'];
$keyboard[2][1]["text"] = "4-6";
$keyboard[2][1]["callback_data"] = "cube_456_".$user->user['id'];
$chat->sendMessage("🎲 <b>Кубик</b>

На що ставимо?", null, ["inline_keyboard" => $keyboard]);
$user->addBal($bet*-1);
$user->LocalStorageClear();
$user->LocalStorageSet("game", "cube");
$user->LocalStorageSet("bet", $bet);
} elseif ($user->LocalStorageGet("game") == "cube") {
    if ($user->user['id'] != $ex_callback[2]) {
        $chat->answerCallbackQuery('♨ Це не твоя гра. Запусти свою: !кубик [ставка]'); die();
    }
    $chat->deleteMessage(update::$btn_id);
    $result = $chat->sendDice("🎲");
    $number = $result->result->dice->value;
    sleep(2);
    if ($ex_callback[1] == $number or mb_stristr($ex_callback[1], $number)) {
    if (iconv_strlen($ex_callback[1]) == 1) $coef = 3;
    if (iconv_strlen($ex_callback[1]) == 2) $coef = 2;
    if (iconv_strlen($ex_callback[1]) == 3) $coef = 1.6;
    $user->addBal($user->LocalStorageGet("bet")*$coef);
    $chat->sendMessage("🎉 Ви виграли <b>".($user->LocalStorageGet("bet")*$coef)."💰</b>
Ставка на: <b>".$ex_callback[1]."</b>

Ваш баланс: <b>".$user->user["balance"]."💰</b>");
} else {
$chat->sendMessage("🎭 Вітаємо, ви програли <b>".$user->LocalStorageGet("bet")."💰</b>
Ставка на: <b>".$ex_callback[1]."</b>

Ваш баланс: <b>".$user->user["balance"]."💰</b>");
}
$user->LocalStorageClear();
} elseif ($ex_callback[2]) {
    if ($user->user["id"] != $ex_callback[2]) {
        $chat->answerCallbackQuery('♨ Це не твоя гра. Запусти свою: !кубик [ставка]'); die();
    }
}