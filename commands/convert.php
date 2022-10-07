<?php
//
// Command: Конвертування #
// Text: !конвертувати !конвертировать /convert #
// Info: Конвертує діаманти на монети та навпаки. Купівля діамантів: 5к💰, продаж: 3к💰 #
// Syntax: !конвертувати [монети/діаманти] [сума в діамантах] #
// Args: 2 #
// Rank: USER #
//
$sum = round($cmd[2]);
$price_buy = 5000; //ціна покупки діаманту
$price_sell = 3000; //сума за продаж діаманту
if ($sum < 1) custom_error('Помилка', 'Мінімальна сума покупки/продажу: 1');
$money = ['монети', 'монеты', 'money', 'coins'];
$diamonds = ['діаманти', 'алмази', 'алмазы', 'diamonds'];
if (in_array($cmd[1], $money)) {
    if ($user->user['balance'] < ($sum*$price_buy)) custom_error('Недостатньо коштів', 'Необхідно: '.($sum*$price_buy).'💰
У тебе: '.$user->user['balance'].'💰');
    $user->addBal(($sum*$price_buy)*-1);
    $user->update('diamonds', ($user->user['diamonds']+$sum));
    $chat->sendMessage('✅ Ви купили <b>'.$sum.'💎</b> за <b>'.($price_buy*$sum).'💰</b>');
} elseif (in_array($cmd[1], $diamonds)) {
    if ($user->user['diamonds'] < $sum) custom_error('Недостатньо діамантів', 'Необхідно: '.$sum.'💎
У тебе: '.$user->user['diamonds'].'💎');
    $user->addBal($sum*$price_sell);
    $user->update('diamonds', ($user->user['diamonds']-$sum));
    $chat->sendMessage('✅ Ви продали <b>'.$sum.'💎</b> за <b>'.($price_sell*$sum).'💰</b>');
}