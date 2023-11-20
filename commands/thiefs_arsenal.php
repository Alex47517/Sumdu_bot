<?php
//
// Command: Домушники #
// Text: !арсенал /thief #
// Callback: thief #
// Info: Виводить арсенал домушника #
// Syntax: !арсенал домушника #
// Args: 1 #
// Rank: USER #
//
use api\update as update;
if ($msg == '!арсенал домушника' or $msg == '/thief arsenal') {
    $arsenal = R::getAll('SELECT * FROM arsenal WHERE `user` = ?', [$user->user['id']]);
    foreach ($arsenal as $item) {
        $item_info = R::load('items', $item['item_id']);
        $list .= $item_info['name'].' ('.$item['count'].' шт.)
';
    }
    $keyboard[0][0]['text'] = '💎 Купівля речей 💎';
    $keyboard[0][0]['callback_data'] = 'thief_shop';
    $chat->sendMessage('<b>🕵️‍♂️ Арсенал домушника</b>
=== Твій арсенал: ===
'.$list, update::$message_id, ['inline_keyboard' => $keyboard]);
}
if ($ex_callback[0] == 'thief') {
    if ($ex_callback[1] == 'shop') {
        $items = R::getAll('SELECT * FROM thiefshop');
        $i = 0;
        foreach ($items as $item) {
            $keyboard[$i][0] = $item['name'].' ['.$item['breaking_level'].';'.$item['damping_level'].'] - ';
            $i++;
        }
        $chat->editMessageText('');
    }
}