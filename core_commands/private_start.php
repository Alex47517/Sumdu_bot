<?php
if ($chat->chat['tg_id'] == $user->user['tg_id']) {
    if ($msg == '/start') {
        $text = '👋 <b>Вас вітає СумДУ бот!</b>';
        $i = 0;
        if (!$user->user['grp']) {
            $keyboard[$i][0] = '🎓 Я тільки вступив';
            $i++;
        }
        $keyboard[$i][0] = '🔐 Авторизація на порталі';
        $i++;
        $chat->sendMessage($text, null, ['keyboard' => $keyboard]);
    } elseif ($msg == '🔐 Авторизація на порталі') {
        $code = gen_password();
        $auth = R::dispense('auth');
        $auth->user_id = $user->user['id'];
        $auth->code = $code;
        $auth->date = date('U');
        R::store($auth);
        $text = '🔐 <b>Авторизація на порталі</b>
Використоввуйте це посилання, щоб увійти на портал

⚠ <em>Воно одноразове та діє 5 хвилин</em>

<code>https://sumdu.fun/auth/'.$code.'</code>';
        $keyboard[0][0]['text'] = '🔗 Увійти';
        $keyboard[0][0]['url'] = 'https://sumdu.fun/auth/'.$code;
        $chat->sendMessage($text, null, ['inline_keyboard' => $keyboard, 'resize_keyboard' => true]);
    }
}