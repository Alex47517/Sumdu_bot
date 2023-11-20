<?php
if ($cmd[0] == '!delete_article') {
    Permissions::Admin($user->user);
    if (!$cmd[1]) {
        $chat->sendMessage('❓ Вкажіть ID'); die();
    }
    $article = R::load('articles', $cmd[1]);
    if (!$article) {
        $chat->sendMessage('❌ Підказки з таким ID не існує!'); die();
    }
    R::trash($article);
    $chat->sendMessage('✅ Видалено');
}
if ($cmd[0] == '!spam_article') {
    Permissions::Admin($user->user);
    if (!$cmd[1]) {
        $chat->sendMessage('❓ Вкажіть ID'); die();
    }
    $article = R::load('articles', $cmd[1]);
    if (!$article) {
        $chat->sendMessage('❌ Підказки з таким ID не існує!'); die();
    }
    $poster = R::load('users', $article['user_id']);
    $poster->can_articles = 0;
    R::store($poster);
    R::trash($article);
    $chat->sendMessage('✅ Видалено та заборонено користувачу <a href="tg://user?id='.$poster['tg_id'].'">'.$poster['nick'].'</a> створювати нові підказки');
}
