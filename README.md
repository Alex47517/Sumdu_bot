# Базовий принцип роботи та створення команд для @Sumdu_bot

## 1. База Даних

Основа всієї інформаційної системи — це база даних MySQL.  
Для безпосередньої взаємодії з нею необхідно використовувати ORM [RedBeanPHP](https://redbeanphp.com/index.php).

Схема БД зображена на малюнку:  
![img.png](https://telegra.ph/file/953c0b2ceeed25ff5a99f.png)

## 2. Ядро Бота

Схема ядра бота:  
![img_1.png](https://telegra.ph/file/ef03669d1506fa38fc488.png)

Запити від Telegram приймає `bot.php`, який налаштовує зв'язок з базою даних, завантажує всі класи та бібліотеки. Після цього виконується завантаження профілю користувача або його реєстрація (клас `User` в `$user`).  
Далі завантажується клас `Chat` у змінну `$chat`, який, власне, представляє чат, звідки надійшов запит, та `ChatMember` у `$chatMember`, який, у свою чергу, представляє користувача як учасника чату.

Тобто, після цього ми маємо три сутності: "Чат", "Користувач" та "Учасник чату". Інформація про них розписана у п. 2.1.

Після ініціалізації класів ядро виконує обов'язкові команди (core_commands) та починає обробляти ініціатори (п. 2.2).

### 2.1 Огляд основних класів

#### 2.1.1 `$user`

- `$user->user` — асоціативний масив, сховище даних.
- `loadByTgID(tg_id)` — завантажує користувача за його TelegramID.
- `loadByID(id)` — завантажує користувача за його ID у БД.
- `loadByNick(nick)` — завантажує користувача за його ніком.
- `loadByUsername(username)` — завантажує користувача за його TelegramUsername (зауважте, що його може взагалі не бути у деяких користувачів).
- `newUser(tg_from)` — реєструє користувача, отримуючи об'єкт "from", переданий Telegram.
- `update(param, value = null)` — оновлює дані у БД та сховищі класу.
- `LocalStorageSet(key, value)` — зберігає пару key => value у сховищі користувача; можна зберегти довільну кількість пар даних (у БД це виглядає як JSON формат у стовпці "tmp").
- `LocalStorageGet(key)` — повертає value зі сховища користувача.
- `LocalStorageClear()` — очищує сховище користувача.
- `addBal(sum)` — поповнює баланс монет користувача, списуючи їх у банку (для списання монет використовуйте від'ємні числа).
- `addToBlackList($time)` — приймає час у секундах (якщо час == 0, це вважається нескінченністю) та додає користувача до чорного списку (бот буде ігнорувати команди користувача у будь-якому чаті, навіть у приватному).
- `getAchievement(achievement_id)` — видає користувачу досягнення.

#### 2.1.2 `$chat` (у namespace "api")

> КОНСТРУКТОР (`chat_id`) — ідентифікатор чату від Telegram.

- `$chat->chat` — асоціативний масив, сховище даних.
- `$chat->chat_id` — ідентифікатор чату від Telegram.
- `storeChat(chat)` — отримує об'єкт "chat" від Telegram та зберігає інформацію про нього у БД.
- `update(param, value)` — оновлює дані у БД та сховищі класу.

> Далі представлені методи API Telegram (детальна інформація про змінні, які вони приймають, можна знайти в [TelegramBotAPI](https://core.telegram.org/bots/api#available-methods); `reply_markup` приймає асоціативний масив, його не потрібно конвертувати в JSON).

- `sendMessage(text, reply_to_message_id = null, reply_markup = null, disable_notification = true, protect_content = false, associative = false)` — надсилає повідомлення у чат.
- `getChatMember(user_id)` — повертає інформацію від Telegram про користувача чату.
- `editMessageText(text, reply_markup, message_id, parse_mode = null)` — редагує повідомлення.
- `restrictChatMember(user_id, permissions, until_date)` — обмежує учасника чату (цей метод є низькорівневим, він не вносить інформацію до БД; для покарання учасника використовуйте функцію з розділу "Функції").
- `banChatMember(user_id, until_date, revoke_messages = false)` — банить користувача чату (цей метод є низькорівневим, він не вносить інформацію до БД; для покарання учасника використовуйте функцію з розділу "Функції").
- `unbanChatMember(user_id, only_if_banned = true)` — розбанює користувача чату (цей метод є низькорівневим, він не вносить інформацію до БД; для зняття покарання учасника використовуйте функцію з розділу "Функції").
- `createChatInviteLink(name, expire_date = null, member_limit = null, creates_join_request = null)` — створює посилання-запрошення до чату.
- `sendPhoto(photo, caption = null, reply_to_message_id = null, reply_markup = null, disable_notification = true, protect_content = false)` — надсилає фото у чат.
- `sendVideo(video, caption = null, reply_to_message_id = null, reply_markup = null, disable_notification = true, protect_content = false)` — надсилає відео у чат.
- `deleteMessage(message_id)` — видаляє повідомлення.
- `sendDice(emoji)` — відправляє Telegram гру з emoji у чат.
- `answerCallbackQuery(text, show_alert = false, url = null)` — відправляє відповідь на inline-звернення.

#### 2.1.3 `ChatMember` (у namespace "api")

> КОНСТРУКТОР (`user_id, chat_id`) — внутрішні ідентифікатори user.id та chat.id з бази даних.

- `$chatMember->chatMember` — асоціативний масив, сховище даних.
- `update(param, value)` — оновлює дані у БД та сховищі класу.
- `getChatStatus(user_id)` — приймає внутрішній ідентифікатор користувача та повертає його статус у чаті `$chat`, якщо він є адміністратором чи власником цього чату.
- `addToBlackList($time)` — приймає час у секундах (якщо час == 0, це вважається нескінченністю) та додає користувача до чорного списку у чаті `$chat` (бот буде ігнорувати команди користувача).

#### 2.1.4 `update` (у namespace "api")

Статичний клас `update` відповідає за обробку та структурування запитів, які надходять від Telegram API. Цей клас дозволяє зручно отримувати різні параметри з запитів, такі як інформація про повідомлення, користувачів, фото, документи, відео та інші дані, що містяться в запиті.

Основні властивості:

> КОНСТРУКТОР (`request`) — Запит від Telegram.
- `public static $update_id` — зберігає ідентифікатор оновлення.
- `public static $message` — зберігає інформацію про повідомлення.
- `public static $message_id` — ідентифікатор повідомлення.
- `public static $chat` — інформація про чат, з якого надійшов запит.
- `public static $from` — інформація про користувача, який надіслав повідомлення або callback запит.
- `public static $callback_data` — дані, передані через callback запит (наприклад, натискання inline кнопки).
- `public static $date` — час відправки повідомлення або callback запиту в Unix-форматі.
- `public static $reply` — інформація про повідомлення, на яке відповідає поточне повідомлення (якщо є).
- `public static $reply_user_id` — ідентифікатор користувача, який надіслав оригінальне повідомлення, на яке відповідають.
- `public static $btn_id` — ідентифікатор повідомлення, до якого була прив'язана inline кнопка (у випадку callback запиту).
- `public static $new_chat_user_id` — ідентифікатор нового учасника чату (якщо є).
- `public static $new_chat_member` — інформація про нового учасника чату.
- `public static $with_photo` — булеве значення, яке вказує, чи містить повідомлення фото.
- `public static $callback_id` — ідентифікатор callback запиту.
- `public static $photo_id` — ідентифікатор фото, надісланого у повідомленні (якщо є).
- `public static $document_id` — ідентифікатор документа, надісланого у повідомленні (якщо є).
- `public static $caption` — підпис до фото, відео або документа.
- `public static $video_id` — ідентифікатор відео, надісланого у повідомленні (якщо є).

### 2.2 Ініціатори

У боті є три типи ініціаторів:

#### 2.2.1 text

Text ініціатор — це перше слово у повідомленні користувача. Наприклад, якщо у команди текстовий ініціатор — це "!інфо", тоді, якщо користувач напише просто "інфо" або "!інфо будь-який текст далі", ядро викликає файл команди, який відповідає за цей ініціатор.

#### 2.2.2 callback

Callback ініціатор — це оголошення, так би мовити, "простору імен inline подій". Ядро визначає перший елемент `callback_data` за дeліметром "_", і цей елемент вважає ініціатором. Наприклад:

Ми у коді команди створили inline-кнопки:

```php
$keyboard[0][0]['text'] = 'Кнопка 1';
$keyboard[0][0]['callback_data'] = 'test_button1_'.$user_id;
$keyboard[1][0]['text'] = 'Кнопка 2';
$keyboard[1][0]['callback_data'] = 'test_button2_hi';
```
Тоді нам необхідно вказати callback ініціатор test для нашої команди, щоб отримувати інформацію про натиснуту кнопку:
```php
if ($ex_callback[1] == 'button1' && $ex_callback[2]) {
    $chat->sendMessage('Ти натиснув на першу кнопку, твій ID: '.$ex_callback[2]);
} elseif ($ex_callback[1] == 'button2' && $ex_callback[2]) {
    $chat->sendMessage('Ти натиснув другу кнопку: '.$ex_callback[2]); //Надішле: "Ти натиснув другу кнопку: hi"
}
```
Якщо ми не вкажемо callback ініціатор test, або вкажемо його неправильно — ядро не буде перенаправляти запит на нашу команду.
Для уникнення плутанини рекомендовано встановлювати схожі на назву команди ініціатори.
#### 2.2.3 display
Визначення: Стейт машина (машина станів) — це модель, яка допомагає керувати поведінкою програми шляхом переходу між різними станами. Кожен стан визначає, що саме робить програма в даний момент, а переходи між станами відбуваються, коли виконуються певні умови.

Ініціатор display — це конкретний стан у такій машині, коли, наприклад, команда повинна запросити у користувача ввести якийсь текст. Як тільки настає цей стан, система виконує відповідну дію (наприклад, зберігає інформацію та/або надсилає повідомлення), а потім, залежно від подальших умов, переходить до наступного стану.

Приклад "На практиці":

Для коректної роботи цього прикладу необхідно встановити display ініціатор nickChange та text ініціатор (він повинен бути у всіх командах, крім випадків коли це модуль, а не команда у звичайному розумінні):
```php
if (!$ex_display[0]) {
    $user->LocalStorageClear(); // Перед використанням LocalStorage завжди очищуйте його.
    $user->update('display', 'nickChange_input'); // Встановлюємо display nickChange як ініціатор, та через "_" будь-яку інформацію, яка нам знадобиться у коді команди.
    $chat->sendMessage('Напишіть свій новий нік');
    die(); // Команда - це кінцева точка виконання скрипту, тому якщо вам треба - ви можете його негайно завершити використавши die();
} else {
    if ($ex_display[1] == 'input') {
        $user->LocalStorageSet('nick', $msg);
        $user->update('display', 'confirmation');
        $chat->sendMessage('Ви дійсно бажаєте встановити нік "'.$nick.'"?

Напишіть ТАК або НІ');
        // Конкретно цю ситуацію краще зробити через inline кнопки та callback ініціатори, але це лише приклад використання саме display.
    } elseif ($ex_display[1] == 'confirmation') {
        if ($msg == 'ТАК') {
            $chat->sendMessage('Ви змінили нік з "'.$user->user['nick'].'" на "'.$user->LocalStorageGet('nick').'"');
            $user->update('nick', $user->LocalStorageGet('nick'));
        } else {
            $chat->sendMessage('Зміну ніку скасовано');
        }
        $user->update('display'); // УВАГА: Слідкуйте, щоб після виконання команди, ви "викинули" користувача на головний екран, інакше щоб вийти з команди йому необхідно буде написати "/start", про що він може не знати і вважати, що бот завис.
    }
}
```
З кодом вище, коли користувач напише ваш text ініціатор, наприклад `/nickChange`, буде відбуватися подібний діалог:
```
КОРИСТУВАЧ: /nickChange
БОТ: Напишіть свій новий нік
КОРИСТУВАЧ: New
БОТ: Ви дійсно бажаєте встановити нік "New"?
КОРИСТУВАЧ: ТАК
БОТ: Ви змінили нік з "Old" на "New"
```
### 3. Простір імен
Для використання деяких класів, необхідно оголосити простір імен на початку коду чи команди. Наприклад, якщо ми будемо створювати об'єкт чату та отримувати інформацію з update, нам необхідно написати на початку команди такий код:
```php
use api\{chat as chat, update as update};
```
Інакше наша команда при виклику буде класти бот у фатальну помилку.

Простір імен `api` можна переглянути на схемі ядра. Інших просторів імен на цей час не передбачено.
## 4. Загальні функції

### 4.1 Клас Time (об'єднує функції для роботи з часом)

- `Time::toTimestamp($str)` — Перетворює час виду "2d4m5s" (не ISO 8601) на секунди.
- `Time::sec2time_txt($time)` — Перетворює секунди у зручний для людини формат часу, враховуючи відмінок слів. Символ "e" перекладає як "вічність". Приклад: 2 доби, 6 годин, 7 хв. 9 сек.

### 4.2 Звичайні функції

- `menu()` — Переводить користувача на головний display та виводить повідомлення про завершення усіх сесій команд.
- `args_error(action)` — Приймає дію з ініціатором, виводить помилку про недостатність аргументів команди та підказує її синтаксис.
- `custom_error(title, description, exit = false)` — Виводить помилку з заданою назвою та описом. Якщо є `exit`, тоді підкаже, як завершити сесію (актуально для команд з display ініціаторами).
- `translit_sef(value)` — Перекладає текст кирилицею у трансліт (на латиницю).
- `new_command(command)` — Форматує хедер з коментарями для обробника команд та створює файл з кодом при використанні "!створити команду".
- `mute(user_id, time, reason, by, send_msg = true, custom_chat = null)` — Мутить користувача у чаті, приймає:
    - `user_id` — внутрішній ID користувача.
    - `time` — час муту у секундах (якщо 0, тоді вважається нескінченністю).
    - `reason` — причина муту.
    - `by` — текст, нік або назва модулю хто видав мут.
    - `send_msg` — чи необхідно надіслати повідомлення про мут.
    - `custom_chat` — TelegramID чату, де треба замутити користувача (якщо не вказано — мутиться у `$chat`).
- `unmute(user_id, by, send_msg = true)` — Знімає мут з користувача, приймає:
    - `user_id` — внутрішній ID користувача.
    - `by` — текст, нік або назва модулю хто зняв мут.
    - `send_msg` — чи необхідно надіслати повідомлення про зняття муту.
- `ban(user_id, time, reason, by)` — Банить користувача у чаті.
- `unban(user_id, by)` — Знімає бан з користувача у чаті.
- `gen_password(length = 6)` — Приймає кількість символів. Генерує пароль, але використовувати можна для чого завгодно, бо повертає набір символів.
- `replace_custom_info(custom_info, user)` — Формує кастомне інфо користувача, приймає текст, шаблон інфо та об'єкт користувача.
- `getEmojiNum(num)` — Повертає emoji згідно з номером, якщо такого нема, повертає текст виду "13." (додає крапку до цифри).
- `remove_emoji(text)` — Видаляє усі emoji з тексту.
# І це лише основи :)
Якщо тема створення власних команд буде цікава нашій спільноті - буде написана повна довідка для усіх функцій та класів. Наразі маючи базові принципи роботи - ви можете створювати більшість команд. А можливо самі зацікавитесь та подивитеся реалізацію не описаних у цій довідці модулів та команд. Усе в ваших руках. Творіть!