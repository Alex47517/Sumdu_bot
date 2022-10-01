<?php
//
// Command: Промо #
// Text: !промо /promo #
// Info: Активує промо-код #
// Syntax: !промо [код] #
// Args: 1 #
// Rank: USER #
//
if ($user->user['tmp'] != 'promo2') {
    if ($cmd[1] == 'jw73Jsyy9Ks') {
        $user->update('tmp', 'promo2');
        $user->addBal(5000);
        $chat->sendMessage('✅ Промо-код активований');
    } else {
        custom_error('Помилка', 'Промо-код застарів або не існує');
    }
} else custom_error('Код використаний', 'Ви не можете активувати цей промо-код');