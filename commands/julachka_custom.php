<?php
//
// Command: Julachka #
// Text: !julachka #
// Info: Команда Юлі #
// Syntax: !julachka #
// Args: 0 #
// Rank: USER #
//
if ($user->user["tg_id"] == 5740653044) {
    $chat->sendMessage("Юля");
} else {
    $chat->sendMessage("Не Юля");
}