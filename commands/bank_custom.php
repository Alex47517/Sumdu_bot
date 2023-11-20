<?php
//
// Command: Банк #
// Text: !банк /bank #
// Info: Виводить кількість монет в банку #
// Syntax: !банк #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
$chat->sendMessage('Зараз в банку <b>'.Bank::get().'💰</b>', update::$message_id);