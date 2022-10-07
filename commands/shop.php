<?php
//
// Command: Магазин #
// Text: !магазин /shop #
// Callback: shop #
// Display: shop #
// Info: Відкриває магазин #
// Syntax: !магазин #
// Args: 0 #
// Rank: USER #
//
use api\update as update;
$info_price = 4; //ціна інфо
$photo_price = 3; //ціна фото в інфо
if ($ex_display[0]) {
    if ($ex_display[1] == 'customInfo') {
        $msg = strip_tags($msg);
        $store_text = $msg;
        $tags = ['b', 'em', 'u', 's', 'code'];
        foreach ($tags as $tag) {
            $opened = mb_substr_count($store_text, '['.$tag.']');
            $closed = mb_substr_count($store_text, '[/'.$tag.']');
            if ($opened != $closed) {
                custom_error('Помилка парсингу тега "['.$tag.']"', 'Ви відкрили тег '.$opened.' раз(iв)
А закрили: '.$closed.' раз(iв)

Виправте помилку або напишіть /start для виходу');
            }
            $store_text = str_replace('['.$tag.']', '<'.$tag.'>', $store_text);
            $store_text = str_replace('[/'.$tag.']', '</'.$tag.'>', $store_text);
        }
        $vars = ['id', 'nick', 'rank', 'balance', 'group', 'tg', 'date'];
        foreach ($vars as $var) {
            if (!strpos($store_text, '%'.$var)) {
                custom_error('Помилка', 'Змінна %'.$var.' не знайдена

Виправте помилку або напишіть /start для виходу');
            }
        }
        $user->update('tmp', $store_text);
        $user->update('display');
        $keyboard[0][0]['text'] = '❌ Видалити';
        $keyboard[0][0]['callback_data'] = 'shop_customInfo_delete';
        $keyboard[1][0]['text'] = '✅ Зберегти';
        $keyboard[1][0]['callback_data'] = 'shop_customInfo_save';
        $chat->sendMessage('⚠ <b>Перевірте своє !інфо</b>
Та натисніть на одну із кнопок нижче
<b>======</b>
'.replace_custom_info($store_text, $user->user), update::$message_id, ['inline_keyboard' => $keyboard]);
    } elseif ($ex_display[1] == 'photo') {
        if (update::$photo_id) {
            if ($user->user['diamonds'] < $photo_price) custom_error('Недостатньо коштів', 'Потрібно: <b>'.$photo_price.'</b>💎
У тебе: <b>'.$user->user['diamonds'].'</b>💎');
            $custominfo = R::load('custominfo', $user->user['custom_info']);
            if ($custominfo['text']) {
                $custominfo->photo = update::$photo_id;
                R::store($custominfo);
                if ($photo_price != 0) $user->update('diamonds', ($user->user['diamonds']-$photo_price));
                $user->update('display');
                $chat->sendMessage('✅ Фото встановлено!', update::$message_id);
            } else { $user->update('display'); custom_error('Помилка', 'Ви повинні спочатку купити "Своє оформлення інфо" щоб встановити фото', true); }
        } else custom_error('Помилка', 'Надішліть фото або напишіть /start для виходу');
    }
}
if ($ex_callback[0]) {
    if ($ex_callback[1] == 'customInfo') {
        if ($ex_callback[2] == 'delete') {
            $chat->answerCallbackQuery('✅ Скасовано', true);
            $chat->deleteMessage(update::$btn_id);
        } elseif ($ex_callback[2] == 'save') {
            if ($user->user['diamonds'] < $info_price) {
                $chat->editMessageText('💢 <b>Недостатньо коштів</b>
Потрібно: <b>'.$info_price.'</b>💎
У тебе: <b>'.$user->user['diamonds'].'</b>💎', null, update::$btn_id);
            } else {
                if ($info_price != 0) $user->update('diamonds', ($user->user['diamonds']-$info_price));
                $custominfo = R::dispense('custominfo');
                $custominfo->text = $user->user['tmp'];
                $custominfo->photo = null;
                R::store($custominfo);
                $user->update('custom_info', $custominfo['id']);
                $chat->editMessageText('✅ <b>Ваше оформлення !інфо успішно встановлено</b>
З балансу списано '.$info_price.'💎', null, update::$btn_id);
                die();
            }
        } else {
            if ($user->user['diamonds'] < $info_price) {
                $chat->answerCallbackQuery('💢 Недостатньо коштів. Потрібно: '.$info_price.'💎, у тебе: '.$user->user['diamonds'].'💎', true);
            } else {
                $user->update('display', 'shop_customInfo');
                $chat->editMessageText('📝 <b>Напишіть своє оформлення !інфо</b>
<b>Замість значень пишіть змінні, котрі починаються з %</b>
<code>%id</code> - id користувача
<code>%nick</code> - Нік
<code>%rank</code> - Ранг
<code>%balance</code> - Баланс
<code>%group</code> - Група
<code>%tg</code> - Телеграм id
<code>%date</code> - Дата реєстрації

<b>Щоб оформити текст:</b>
[b]<b>жирний</b>[/b]
[em]<em>курсив</em>[/em]
[u]<u>підкреслений</u>[/u]
[s]<s>закреслений</s>[/s]
[code]<code>моно</code>[/code]

<b>Приклад оформлення:</b>
<code>📌 [b]Інформація про аккаунт:[/b]

[b][*] Основне:[/b]
● ID: [b]%id[/b]
● Нік: [code]%nick[/code]
● Ранг: %rank
● Баланс: %balance💰
● Група: %group

[b][*] Інше:[/b]
● Telegram: %tg
● Дата реєстрації: %date</code>', null, update::$btn_id);
            }
        }
    } elseif ($ex_callback[1] == 'photo') {
        if ($user->user['diamonds'] < $photo_price) {
            $chat->answerCallbackQuery('💢 Недостатньо коштів. Потрібно: '.$photo_price.'💎, у тебе: '.$user->user['diamonds'].'💎', true);
        } else {
            $user->update('display', 'shop_photo');
            $chat->editMessageText('🖼 <b>Надішліть фотографію для свого !інфо</b>', null, update::$btn_id);
        }
    } elseif ($ex_callback[1] == 'diamondsinfo') {
        $chat->answerCallbackQuery('ℹ Є 3 способи отримання діамантів.
1: Отримувати за 7 комбо в !бонус.
2: Обміняти 💰 на 💎 за допомогою команди !конвертувати.
3: Обміняти 💰 на 💎 на біржі, команда: !біржа', true); die();
    }
} elseif (!$ex_display[1] && !$ex_callback[1]) {
    $keyboard[0][0]['text'] = '📝 Своє інфо ('.$info_price.'💎)';
    $keyboard[0][0]['callback_data'] = 'shop_customInfo';
    $keyboard[1][0]['text'] = '🖼 Фото в інфо ('.$photo_price.'💎)';
    $keyboard[1][0]['callback_data'] = 'shop_photo';
    $keyboard[2][0]['text'] = 'ℹ [Довідка] Як отримати 💎';
    $keyboard[2][0]['callback_data'] = 'shop_diamondsinfo';
    $photo = 'AgACAgIAAx0CR0W6wgABIFoyYyfr7TkwNXcbCMNpbuUjdKCsPlcAAve_MRtEmkFJtpYDS7xdZOUBAAMCAAN5AAMpBA';
    $chat->sendPhoto($photo, '<b>🚽 Магазин</b>', update::$message_id, ['inline_keyboard' => $keyboard]);
}