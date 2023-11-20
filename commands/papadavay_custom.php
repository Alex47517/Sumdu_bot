<?php
//
// Command: Пападавай #
// Text: !пападавай #
// Info: Секретна команда від Кирила Балаценко😏 #
// Syntax: !пападавай #
// Args: 0 #
// Rank: USER #
//

use api\update as update;
if ($user->user['balance'] > 5000) {
    $user->addBal(-5000);
    $chat->sendMessage('Папа дав');
} else {
    $chat->sendMessage('Папі не потрібен такий бомж');
}
die();
ban($user->user["id"], 30, "Папа дав", $user->user["nick"]);