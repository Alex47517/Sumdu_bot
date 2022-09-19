<?php
//
// Command: Бонус #
// Text: !бонус /bonus #
// Info: Дає посилання на отримання бонусу #
// Syntax: !бонус #
// Args: 0 #
// Rank: USER #
//
if ($user->user['next_bonus']-date('U') < 0) {
$keyboard[0][0]['text'] = 'Отримати бонус';
$keyboard[0][0]['url'] = 'https://sumdu.fun/gifts';
$chat->sendMessage('🎁 <b>Отримай свій бонус на порталі</b>', null, ['inline_keyboard' => $keyboard]);
} else custom_error('Зажди!', 'Ти зможеш отримати свій бонус через: '.Time::sec2time_txt($user->user['next_bonus']-date('U')));