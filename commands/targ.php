<?php
//
// Command: Тарг #
// Text: !тарг /targ #
// Info: Секретна команда #
// Syntax: !тарг #
// Args: 0 #
// Rank: USER #
//
if ($user->user['id'] == 6) {
    if ($user->user['balance'] > 20000) {
        $chat->sendMessage('♨ <b>Досить вже :)</b>');
    } else {
        $user->addBal(5000);
        $chat->sendMessage('<em>*Непомітний кивок*</em>');
    }
} else {
    $chat->sendMessage('👺 Відчепися, ти не Тарг!');
}