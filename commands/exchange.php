<?php
//
// Command: Біржа #
// Text: !біржа !биржа /exchange #
// Callback: exchange #
// Display: exchange #
// Info: Відкрива біржу #
// Syntax: !біржа #
// Args: 0 #
// Rank: USER #
//
$text = ['!біржа', '!биржа', '/exchange'];
use api\{update as update, chat as chat};
//=========
function close() {
    global $chat;
    global $user;
    $chat->sendMessage('💱 <b>Біржа</b>

Невідома дія. Ви вийшли з біржи', update::$message_id, ['remove_keyboard' => true, 'selective' => true]);
    $user->update('display');
    die();
}
function sendOffers($type, $page) {
    global $chat;
    global $user;
    if ($page < 0 && update::$btn_id) { $chat->answerCallbackQuery('💢 Сторінка недоступна', true); die(); }
    $offset = ($page*5)-5;
    $offers = R::getAll('SELECT * FROM offers WHERE `type` = ? ORDER BY `price` DESC LIMIT 6 OFFSET ?', [$type, $offset]);
    if ($offers%5 < $page && update::$btn_id) { $chat->answerCallbackQuery('💢 Ви переглянули усі сторінки', true); die(); }
    $echo = 0;
    foreach ($offers as $offer) {
        $owner = R::load('users', $offer['user_id']);
        if ($offer['type'] == 'sell') {
            $offer_type = 'Продаж';
            $check = 'diamonds';
            $min = 1;
            $max = $owner['diamonds'];
        } else {
            $offer_type = 'Купівля';
            $check = 'balance';
            $min = $offer['price'];
            $max = floor($owner['balance']/$offer['price']);
        }
        if ($owner[$check] < $min) {
            $offer = R::load('offers', $offer['id']);
            $owner_pm = new chat($owner['tg_id']);
            $owner_pm->sendMessage('⚠ <b>[Біржа] Оголошення видалено, недостатньо коштів</b>
#'.$offer['id'].'
Тип: <b>'.$offer_type.'</b>
Ціна за 1💎: <b>'.$offer['price'].'💰</b>
Оберт: <b>'.$offer['turnover'].'💎</b>');
            R::trash($offer);
        } else {
            if ($echo == 5) break; else $short = true;
            $keyboard[$echo][0]['text'] = 'До '.$max.'💎, '.$offer['price'].'💰 - '.$owner['nick'].'';
            $keyboard[$echo][0]['callback_data'] = 'exchange_'.$user->user['id'].'_offer-info_'.$offer['id'];
            $echo++;
        }
    }
    if (!$offers) $add = '♨ Наразі оголошень нема. Скористуйтеся командою <code>!конвертувати</code> або створіть своє оголошення'; else $add = null;
    if (!$short) {
        $keyboard[$echo][0]['text'] = '⬅';
        $keyboard[$echo][0]['callback_data'] = 'exchange_'.$user->user['id'].'_offers_'.$type.'_'.($page-1);
        $keyboard[$echo][1]['text'] = '['.$page.']';
        $keyboard[$echo][1]['callback_data'] = 'exchange_'.$user->user['id'].'_offers_'.$type.'_'.$page;
        $keyboard[$echo][2]['text'] = '➡';
        $keyboard[$echo][2]['callback_data'] = 'exchange_'.$user->user['id'].'_offers_'.$type.'_'.($page+1);
    }
    if ($type == 'buy') $echo_type = 'продаж'; else $echo_type = 'купівля';
    if (update::$btn_id) {
        $chat->editMessageText('💱 <b>Біржа - Список оголошень</b>
Тип: <b>'.$echo_type.'</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
    } else {
        $chat->sendMessage('💱 <b>Біржа - Список оголошень</b>
Тип: <b>'.$echo_type.'</b>
'.$add, update::$message_id, ['inline_keyboard' => $keyboard]);
    }
    die();
}
//=========
if ($ex_callback[0] == 'exchange') {
    if ($user->user['id'] != $ex_callback[1]) { $chat->answerCallbackQuery('💢 Це не твоє меню біржи. Відкрий своє за допомогою команди !біржа', true); die(); }
    if ($ex_callback[2] == 'remove-offer') {
        $offer = R::load('offers', $ex_callback[3]);
        if ($offer['type'] == 'sell') $type = 'Продаж'; else $type = 'Купівля';
        $keyboard[0][0]['text'] = '❌ Ні';
        $keyboard[0][0]['callback_data'] = 'exchange_'.$user->user['id'].'_remove-offer-cancel_'.$offer['id'];
        $keyboard[0][1]['text'] = '✅ Так';
        $keyboard[0][1]['callback_data'] = 'exchange_'.$user->user['id'].'_remove-offer-confirmed_'.$offer['id'];
        $chat->editMessageText('❓ Ви дійсно бажаєте видалити оголошення <b>#'.$offer['id'].'</b>
Тип: <b>'.$type.'</b>
Ціна за 1💎: <b>'.$offer['price'].'💰</b>
Оберт: <b>'.$offer['turnover'].'💎</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    } elseif ($ex_callback[2] == 'remove-offer-cancel') {
        $chat->editMessageText('♨ <b>Дію скасовано</b>', null, update::$btn_id); die();
    } elseif ($ex_callback[2] == 'remove-offer-confirmed') {
        $offer = R::load('offers', $ex_callback[3]);
        R::trash($offer);
        $chat->editMessageText('✅ <b>Оголошення видалено</b>', null, update::$btn_id); die();
    } elseif ($ex_callback[2] == 'create-offer') {
        $chat->editMessageText('💱 <b>Біржа - Створення оголошення</b>

Введіть вартість 1💎', null, update::$btn_id);
        $user->update('display', 'exchange_create-order_'.$ex_callback[3]);
        die();
    } elseif ($ex_callback[2] == 'offers') {
        if (!$ex_callback[4]) $ex_callback[4] = 1;
        sendOffers($ex_callback[3], $ex_callback[4]);
    } elseif ($ex_callback[2] == 'offer-info') {
        $offer = R::load('offers', $ex_callback[3]);
        if (!$offer) { $chat->answerCallbackQuery('💢 Це оголошення вже не актуальне', true); die(); }
        $owner = R::load('users', $offer['user_id']);
        if ($offer['type'] == 'sell') {
            $type = 'продаж діамантів';
            $us = 'Продавець';
            $max = $owner['diamonds'];
        } else {
            $type = 'купівля діамантів';
            $us = 'Покупець';
            $max = floor($owner['balance']/$offer['price']);
        }
        $keyboard[0][0]['text'] = '💱 Почати угоду 💱';
        $keyboard[0][0]['callback_data'] = 'exchange_'.$user->user['id'].'_start-deal_'.$offer['id'];
        $chat->editMessageText('💱 <b>Оголошення #'.$offer['id'].'</b>
📌 Тип оголошення: <b>'.$type.'</b>
👤 '.$us.': <code>'.$owner['nick'].'</code>
💎 Ціна за 1 діамант: <b>'.$offer['price'].'💰</b>
📏 Максимальна сума купівлі/продажу: <b>'.$max.'💎</b>
📈 Оберт: <b>'.$offer['turnover'].'💎</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        die();
    } elseif ($ex_callback[2] == 'start-deal') {
        $offer = R::load('offers', $ex_callback[3]);
        if (!$offer) { $chat->answerCallbackQuery('💢 Це оголошення вже не актуальне', true); die(); }
        $owner = R::load('users', $offer['user_id']);
        if ($offer['type'] == 'sell') {
            $type = 'продаж діамантів';
            $us = 'Продавець';
            $action = 'купівлі';
            $max = $owner['diamonds'];
        } else {
            $type = 'купівля діамантів';
            $us = 'Покупець';
            $action = 'продажу';
            $max = floor($owner['balance']/$offer['price']);
        }
        $user->update('display', 'exchange_deal_'.$offer['id']);
        $chat->editMessageText('💱 <b>Угода по #'.$offer['id'].'</b>
📌 Тип оголошення: <b>'.$type.'</b>
👤 '.$us.': <code>'.$owner['nick'].'</code>
💎 Ціна за 1 діамант: <b>'.$offer['price'].'💰</b>
📏 Максимальна сума купівлі/продажу: <b>'.$max.'💎</b>
📈 Оберт: <b>'.$offer['turnover'].'💎</b>
=====
<b>Введіть кількість 💎 для '.$action.'</b>', null, update::$btn_id);
        die();
    }
} elseif ($ex_display[0] == 'exchange') {
    if ($msg && $ex_display[1] == '0') {
        if ($msg == '🔙 Закрити біржу 🔙') {
            $user->update('display');
            $chat->sendMessage('✅ <b>Біржа закрита</b>', update::$message_id, ['remove_keyboard' => true, 'selective' => true]); die();
        } elseif ($msg == '🪛 Мої оголошення 🪛') {
            $offers = R::getAll('SELECT * FROM offers WHERE `user_id` = ? ORDER BY `date` DESC', [$user->user['id']]);
            if (!$offers) custom_error('У тебе нема активних оголошень', 'Пиши /start або натисни на кнопку для виходу');
            $i = 0;
            foreach ($offers as $offer) {
                if ($offer['type'] == 'sell') $type = 'Продаж'; else $type = 'Купівля';
                $keyboard[$i][0]['text'] = $type.' 1💎 за '.$offer['price'].'💰';
                $keyboard[$i][0]['callback_data'] = 'exchange_'.$user->user['id'].'_remove-offer_'.$offer['id'];
            }
            $chat->sendMessage('💱 <b>Біржа</b>
Ось список твоїх оголошень:', update::$message_id, ['inline_keyboard' => $keyboard]);
            die();
        } elseif ($msg == '🧾 Створити оголошення 🧾') {
            $keyboard[0][0]['text'] = '📥 Купівля 💎';
            $keyboard[0][0]['callback_data'] = 'exchange_'.$user->user['id'].'_create-offer_buy';
            $keyboard[0][1]['text'] = '📤 Продаж 💎';
            $keyboard[0][1]['callback_data'] = 'exchange_'.$user->user['id'].'_create-offer_sell';
            $chat->sendMessage('💱 <b>Біржа - Створення оголошення</b>

Оберіть тип оголошення', update::$message_id, ['inline_keyboard' => $keyboard]);
            die();
        } elseif ($msg == '💎 Купити 💎') {
            sendOffers('sell', 1);
        } elseif ($msg == '💎 Продати 💎') {
            sendOffers('buy', 1);
        } else close();
    } elseif ($ex_display[1] == 'create-order') {
        if ($price < 1) custom_error('Помилка', 'Мінімальна ціна: 1💰');
        $type = $ex_display[2];
        $price = round($msg);
        $offers = R::getAll('SELECT * FROM offers WHERE `user_id` = ? ORDER BY `date` DESC', [$user->user['id']]);
        if (count($offers) >= 2) { $user->update('display', 'exchange_0'); custom_error('Помилка', 'Одночасно можна створити тільки 2 оголошення'); }
        $offer = R::dispense('offers');
        $offer->user_id = $user->user['id'];
        $offer->price = $price;
        $offer->type = $type;
        $offer->turnover = 0;
        $offer->date = date('U');
        R::store($offer);
        if ($type == 'sell') $type = 'продаж'; else $type = 'купівля';
        $chat->sendMessage('✅ <b>Оголошення створено!</b>
#'.$offer['id'].'
Тип: <b>'.$type.'</b>
Ціна за 1💎: <b>'.$offer['price'].'💰</b>', update::$message_id);
        $user->update('display', 'exchange_0');
        die();
    } elseif ($ex_display[1] == 'deal') {
        $offer = R::load('offers', $ex_display[2]);
        if (!$offer) { $chat->sendMessage('💢 Це оголошення вже не актуальне', update::$message_id); $user->update('display', 'exchange_0'); die(); }
        $owner = R::load('users', $offer['user_id']);
        $sum = round($msg);
        if ($offer['type'] == 'sell') {
            if (round($offer['price']*$sum) > $user->user['balance']) custom_error('Недостатньо коштів', 'Необхідно: '.round($offer['price']*$sum).'💰
У тебе: '.$user->user['balance'].'💰
Для виходу - /start');
            $type = 'продаж діамантів';
            $us = 'Покупець';
            $end = 'купили';
            $max = $owner['diamonds'];
        } else {
            if ($sum > $user->user['diamonds']) custom_error('Недостатньо коштів', 'Необхідно: '.$sum.'💎
У тебе: '.$user->user['diamonds'].'💎
Для виходу - /start');
            $type = 'купівля діамантів';
            $us = 'Продавець';
            $end = 'продали';
            $max = floor($owner['balance']/$offer['price']);
        }
        if ($sum < 1) custom_error('Помилка', 'Мінімальна сума покупки/продажу: 1💎');
        if ($sum > $max) custom_error('Помилка', 'Максимальна сума покупки/продажу у цьому оголошенні: '.$max.'💎');
        if ($offer['type'] == 'sell') {
            $order_sum = round($offer['price']*$sum);
            $user->update('balance', ($user->user['balance'] - $order_sum));
            $user->update('diamonds', ($user->user['diamonds'] + $sum));
            $owner->balance += $order_sum;
            $owner->diamonds -= $sum;
        } else {
            $order_sum = round($offer['price']*$sum);
            $user->update('balance', ($user->user['balance'] + $order_sum));
            $user->update('diamonds', ($user->user['diamonds'] - $sum));
            $owner->balance -= $order_sum;
            $owner->diamonds += $sum;
        }
        $offer->turnover += $sum;
        R::store($offer);
        R::store($owner);
        $pm_owner = new chat($owner['tg_id']);
        $pm_owner->sendMessage('💡 <b>Виконана угода по оголошенню #'.$offer['id'].'</b>
📌 Тип оголошення: <b>'.$type.'</b>
👤 '.$us.': <code>'.$user->user['nick'].'</code>
💎 Ціна за 1 діамант: <b>'.$offer['price'].'💰</b>
📈 Оберт: <b>'.$offer['turnover'].'💎</b>
=====
<b>Сума угоди:</b>
'.$sum.'💎
'.$order_sum.'💰');
        $chat->sendMessage('✅ <b>Біржа - угода завершена!</b>
Ви '.$end.' <b>'.$sum.'💎</b> за <b>'.$order_sum.'💰</b> у <a href="tg://user?id='.$owner['tg_id'].'">'.$owner['nick'].'</a>');
        die();
    } else {
        close();
    }
} else {
    if (in_array($msg, $text)) {
        $keyboard[0][0]['text'] = '💎 Купити 💎';
        $keyboard[1][0]['text'] = '💎 Продати 💎';
        $keyboard[2][0]['text'] = '🪛 Мої оголошення 🪛';
        $keyboard[3][0]['text'] = '🧾 Створити оголошення 🧾';
        $keyboard[4][0]['text'] = '🔙 Закрити біржу 🔙';
        $user->update('display', 'exchange_0');
        $chat->sendMessage('💱 <b>Вітаємо на біржі</b>
Що хочете зробити?', update::$message_id, ['keyboard' => $keyboard, 'resize_keyboard' => true, 'selective' => true]);
    } else {
        close();
    }
}