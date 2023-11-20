<?php
//
// Command: Довідка #
// Text: !help /help !справка !помощь !допомога #
// Info: Виводить посилання на довідку #
// Syntax: /help #
// Args: 0 #
// Rank: USER #
//
$keyboard[0][0]['text'] = "🔗 Відкрити WIKI";
$keyboard[0][0]['url'] = "https://".DOMAIN."/wiki";
$chat->sendMessage("📚 <b>Довідка по боту</b>", null, ["inline_keyboard" => $keyboard]);