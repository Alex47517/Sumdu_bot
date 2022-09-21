<?php
//
// Command: Налаштування чату #
// Text: !налаштування /settings #
// Callback: chatsettings chatsettings-main #
// Display: chatsettings #
// Info: Відкриває меню налаштування чата #
// Syntax: !налаштування #
// Args: 0 #
// Rank: ChatAdmin #
//
use api\update as update;
if ($ex_callback[0] == 'chatsettings') {
    if ($ex_callback[1] == 'welcome-text') {
        if ($ex_callback[2] == 'view') {
            if ($chat->chat['welcome_text']) {
                if ($chat->chat['welcome_photo']) {
                    $keyboard[0][0]['text'] = '🔙 Назад';
                    $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
                    $chat->deleteMessage(update::$btn_id);
                    $chat->sendPhoto($chat->chat['welcome_photo'], $chat->chat['welcome_text'], null, ['inline_keyboard' => $keyboard]);
                } else {
                    $keyboard[0][0]['text'] = '🔙 Назад';
                    $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
                    $chat->editMessageText($chat->chat['welcome_text'], ['inline_keyboard' => $keyboard], update::$btn_id);
                }
            } else {
                $keyboard[0][0]['text'] = '🔙 Назад';
                $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
                $chat->editMessageText('♨ <b>Вступний текст не встановлений</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
            }
        } elseif ($ex_callback[2] == 'set') {
            $user->update('display', 'chatsettings_welcome-text_set');
            $keyboard[0][0]['text'] = '🔙 Назад';
            $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
            $chat->editMessageText('📌 <b>Напишіть новий вступний текст для цього чату</b>
Змінна <code>%user</code> виведе тег вступника
===
<b>Щоб оформити текст:</b>
[b]<b>жирний</b>[/b]
[em]<em>курсив</em>[/em]
[u]<u>підкреслений</u>[/u]
[s]<s>закреслений</s>[/s]
[code]<code>моно</code>[/code]', ['inline_keyboard' => $keyboard], update::$btn_id);
        } else {
            $user->update('display');
            $keyboard[0][0]['text'] = '🤳 Переглянути встановлене';
            $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text_view';
            $keyboard[1][0]['text'] = '📌 Встановити нове';
            $keyboard[1][0]['callback_data'] = 'chatsettings_welcome-text_set';
            $keyboard[2][0]['text'] = '🔙 Назад';
            $keyboard[2][0]['callback_data'] = 'chatsettings-main';
            $chat->editMessageText('👋 <b>Налаштування вступного тексту</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        }
    } elseif ($ex_callback[1] == 'welcome-photo') {
        if ($ex_callback[2] == 'skip') {
            $user->update('display');
            $keyboard[0][0]['text'] = '🔙 Ok';
            $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
            $chat->editMessageText('✅ <b>Вступний текст встановлений</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        } elseif ($ex_callback[2] == 'set') {
            $user->update('display', 'chatsettings_welcome-photo_set');
            $chat->editMessageText('🏞 <b>Завантажте зображення для вступного тексту</b>', null, update::$btn_id);
        }
    } elseif ($ex_callback[1] == 'rules') {
        if ($ex_callback[2] == 'view') {
            if ($chat->chat['rules_text']) {
                if ($chat->chat['rules_photo']) {
                    $keyboard[0][0]['text'] = '🔙 Назад';
                    $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
                    $chat->deleteMessage(update::$btn_id);
                    $chat->sendPhoto($chat->chat['rules_photo'], $chat->chat['rules_text'], null, ['inline_keyboard' => $keyboard]);
                } else {
                    $keyboard[0][0]['text'] = '🔙 Назад';
                    $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
                    $chat->editMessageText($chat->chat['rules_text'], ['inline_keyboard' => $keyboard], update::$btn_id);
                }
            } else {
                $keyboard[0][0]['text'] = '🔙 Назад';
                $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
                $chat->editMessageText('♨ <b>Правила не встановлені</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
            }
        } elseif ($ex_callback[2] == 'set') {
            $user->update('display', 'chatsettings_rules_set');
            $keyboard[0][0]['text'] = '🔙 Назад';
            $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
            $chat->editMessageText('📌 <b>Напишіть правила для цього чату</b>

<b>Щоб оформити текст:</b>
[b]<b>жирний</b>[/b]
[em]<em>курсив</em>[/em]
[u]<u>підкреслений</u>[/u]
[s]<s>закреслений</s>[/s]
[code]<code>моно</code>[/code]', ['inline_keyboard' => $keyboard], update::$btn_id);
        } else {
            $user->update('display');
            $keyboard[0][0]['text'] = '🤳 Переглянути встановлені';
            $keyboard[0][0]['callback_data'] = 'chatsettings_rules_view';
            $keyboard[1][0]['text'] = '📌 Встановити нові';
            $keyboard[1][0]['callback_data'] = 'chatsettings_rules_set';
            $keyboard[2][0]['text'] = '🔙 Назад';
            $keyboard[2][0]['callback_data'] = 'chatsettings-main';
            $chat->editMessageText('📕 <b>Налаштування правил чату</b>
Відкривається за допомогою команди <code>!правила</code>', ['inline_keyboard' => $keyboard], update::$btn_id);
        }
    } elseif ($ex_callback[1] == 'rules-photo') {
        if ($ex_callback[2] == 'skip') {
            $user->update('display');
            $keyboard[0][0]['text'] = '🔙 Ok';
            $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
            $chat->editMessageText('✅ <b>Правила встановлені</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
        } elseif ($ex_callback[2] == 'set') {
            $user->update('display', 'chatsettings_rules-photo_set');
            $chat->editMessageText('🏞 <b>Завантажте зображення для правил чату</b>', null, update::$btn_id);
        }
    }
} elseif ($ex_display[0] == 'chatsettings') {
    if ($ex_display[1] == 'welcome-text') {
        if ($ex_display[2] == 'set') {
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
            $chat->update('welcome_text', $store_text);
            $keyboard[0][0]['text'] = '❌ Ні';
            $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-photo_skip';
            $keyboard[0][1]['text'] = '✅ Так';
            $keyboard[0][1]['callback_data'] = 'chatsettings_welcome-photo_set';
            $chat->sendMessage('🏞 <b>Бажаєте встановити зображення для вступного тексту?</b>', null, ['inline_keyboard' => $keyboard]);
        }
    } elseif ($ex_display[1] == 'welcome-photo') {
        if ($ex_display[2] == 'set') {
            if (update::$photo_id) {
                $chat->update('welcome_photo', update::$photo_id);
                $user->update('display');
                $keyboard[0][0]['text'] = '🔙 Ok';
                $keyboard[0][0]['callback_data'] = 'chatsettings_welcome-text';
                $chat->sendMessage('✅ <b>Вступний текст та фото встановлені</b>', null, ['inline_keyboard' => $keyboard]);
            } else custom_error('Помилка', 'Надішліть фото або напишіть /start для виходу');
        }
    } elseif ($ex_display[1] == 'rules') {
        if ($ex_display[2] == 'set') {
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
            $chat->update('rules_text', $store_text);
            $keyboard[0][0]['text'] = '❌ Ні';
            $keyboard[0][0]['callback_data'] = 'chatsettings_rules-photo_skip';
            $keyboard[0][1]['text'] = '✅ Так';
            $keyboard[0][1]['callback_data'] = 'chatsettings_rules-photo_set';
            $chat->sendMessage('🏞 <b>Бажаєте встановити зображення для правил чату?</b>', null, ['inline_keyboard' => $keyboard]);
        }
    } elseif ($ex_display[1] == 'rules-photo') {
        if ($ex_display[2] == 'set') {
            if (update::$photo_id) {
                $chat->update('rules_photo', update::$photo_id);
                $user->update('display');
                $keyboard[0][0]['text'] = '🔙 Ok';
                $keyboard[0][0]['callback_data'] = 'chatsettings_rules';
                $chat->sendMessage('✅ <b>Текст правил та фото встановлені</b>', null, ['inline_keyboard' => $keyboard]);
            } else custom_error('Помилка', 'Надішліть фото або напишіть /start для виходу');
        }
    }
} else {
    $i = 0;
    if ($chat->chat_id != $user->user['tg_id']) {
        $keyboard[$i][0]['text'] = '👋 Вступний текст';
        $keyboard[$i][0]['callback_data'] = 'chatsettings_welcome-text';
        $i++;
        $keyboard[$i][0]['text'] = '📕 Правила';
        $keyboard[$i][0]['callback_data'] = 'chatsettings_rules';
        $i++;
        $keyboard[$i][0]['text'] = '🔒 ANTI-BOT';
        $keyboard[$i][0]['callback_data'] = 'chatsettings_antibot';
        $i++;
    } else custom_error('Помилка', 'Ця команда доступна тільки в групових чатах');
    if (update::$callback_id) {
        $chat->editMessageText('⚙ <b>Налаштування чату</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
    } else {
        $chat->sendMessage('⚙ <b>Налаштування чату</b>', update::$message_id, ['inline_keyboard' => $keyboard]);
    }
}