<?php
//
// Command: code #
// Text: !код /code #
// Info: /next #
// Syntax: !код [PHP code] #
// Args: 1 #
// Rank: OWNER #
//
eval(str_replace($cmd[0], '', $msg));
$chat->sendMessage("Код виконаний!");