<?php
//
// Command: Промо #
// Text: !промо /promo #
// Info: Активує промо-код #
// Syntax: !промо [код] #
// Args: 1 #
// Rank: USER #
//
if ($user->user['tmp'] != 'promo4') {
    $promo = 'TACHKA';
    if ($cmd[1] == $promo) {
        $setting = R::load('settings', 4);
        if ($setting['value'] == $promo) {
            custom_error('Помилка', 'А все вже, ти не встиг :)'); die();
        }
        if ($user->user['balance'] < 3000) {
            $chat->sendMessage('Вибач, але ти бомж'); die();
        }
        //$user->update('diamonds', ($user->user['diamonds']+1));
        //$chat->sendMessage('[Дідусь] Звісно, тримай <3');
//        $chat->sendMessage('[Дідусь] Внучара, ти шо, ох**в?');
//        $chat->sendMessage('{Дідусь} *Вдарив павкою по голові*');
//        $chat->sendMessage('{Дідусь} *Витягнув 2000💰 з кишені*');
        $user->addBal(-3000);
        $chat->sendMessage('[Податкова] З вас стягнуто 3000💰 податку за розмитнення авто');
        //$user->update('next_bonus', date('U'));
        //$chat->sendMessage('[PROMO] Користувачу '.$user->user['nick'].' видано <b>2000💰</b>');
        //$chat->sendMessage('[Мама] Тримай, Синуля');
        //$chat->sendMessage('['.$user->user['id'].'/LOG] Триває переоформлення авто (цей процесс займає декілька хвилин)');
        $setting->value = $promo;
        R::store($setting);
        $chat->sendMessage('✅ Промо-код активований');
    } else {
        custom_error('Помилка', 'Промо-код застарів або не існує');
    }
} else custom_error('Код використаний', 'Ви не можете активувати цей промо-код');