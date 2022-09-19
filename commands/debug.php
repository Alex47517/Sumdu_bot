<?php
//
// Command: debug #
// Text: !отладка !налагодження /debug #
// Info: Активує режим дебагінгу, показуючи запити які надіслав Telegram #
// Syntax: /debug [режим] #
// Args: 1 #
// Rank: OWNER #
//
if ($cmd[1] == 'all') {
    $debug = R::load('settings', 3);
    if ($debug['value']) {
        $debug->value = 0;
        $status = 'вимкнено';
    } else {
        $debug->value = 1;
        $status = 'активовано';
    }
    R::store($debug);
    $chat->sendMessage('✅ Режим дебагінгу <b>'.$status.'</b>');
}

else custom_error('Такого режиму не існує', 'Доступні режими: all');