<?php
//
// Command: Сей #
// Text: !сей #
// Info: Надіслати текст у чат по його id #
// Syntax: !сей [id чату] [повідомлення] #
// Args: 2 #
// Rank: ADMIN #
//
use api\chat as chat;
$c = R::load("chats", $cmd[1]);
if ($c) {
$text = str_replace($cmd[0]." ".$cmd[1]." ", "", $msg);
$to_chat = new chat($c["tg_id"]);
$to_chat->sendMessage($text);
$chat->sendMessage("✅ Надіслано");
} else custom_error("Помилка 404", "чат не знайдений");