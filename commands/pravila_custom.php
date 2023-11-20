<?php
//
// Command: Правила #
// Text: !правила /rules #
// Info: Правила чату #
// Syntax: !правила #
// Args: 0 #
// Rank: USER #
//
$keyboard[0][0]['text'] = 'Читати';
$keyboard[0][0]['url'] = 'https://sumdubot.pp.ua/wiki/rules';
$chat->sendMessage('<b>⚖ Загальні правила користування ботом</b>', null, ['inline_keyboard' => $keyboard]);
$keyboard = null;
if ($chat->chat["rules_text"]) {
if ($chat->chat["rules_photo"]) {
$chat->sendPhoto($chat->chat["rules_photo"], $chat->chat["rules_text"]);
} else {
$chat->sendMessage($chat->chat["rules_text"]);
}
} else custom_error("Помилка", "Правила для цього чату не встановлені");