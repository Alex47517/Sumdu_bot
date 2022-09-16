<?php
use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};
if ($msg == '!встановити як чат групи') {
    $curator = Permissions::Curator($user->user);
    $grp = $curator['grp'];
    $curator->chat_id = $chat->chat['id'];
    R::store($curator);
    $chat->update('botcheck', 0);
    $chat->sendMessage('✅ <b>Цей чат встановлений як чат групи '.$grp.'</b>

<em>Всі користувачі які сюди вступають - автоматично отримають групу в профіль та не будуть залучені до перевірки ANTI-BOT</em>');
    //Закрепы и т.п.
}