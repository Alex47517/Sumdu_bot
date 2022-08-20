<?php
function menu() {
    global $user;
    global $chat;
    $user->update('display');
    $chat->sendMessage('🔅 <b>Ви завершили усі активні сессії</b>'); die();
}
function args_error($action) {
    global $chat;
    $file = R::load('commandfiles', $action['file_id']);
    $chat->sendMessage('♨ <b>Недостатньо аргументів</b>

<b>Команда: </b>'.$file['name'].'
<b>Синтаксис: </b>'.$file['syntax'].'

<em>'.$file['info'].'</em>'); die();
}