<?php
//
// Command: Статистика #
// Text: !інфографіка !статистика !инфографика /stats #
// Info: Надає лінк на сторінку "Статистика", на студ. порталі СумДУ #
// Syntax: !статистика #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
$keyboard[0][0]['text'] = '🔗 Перейти до інфографіки';
$keyboard[0][0]['url'] = 'https://sumdubot.pp.ua/stats';
$chat->sendMessage('📊 <b>Статистика по іграм</b>

<em>Зверніть увагу, на мобільних присторях може відображатися некорректно</em>', update::$message_id, ['inline_keyboard' => $keyboard]);