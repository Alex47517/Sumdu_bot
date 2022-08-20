<?php
class Permissions {
    public static function Owner($user) {
        global $admin_user_id;
        global $chat;
        if ($user['tg_id'] != $admin_user_id) {
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$user['rank'].'</b>
Необхідний ранг: <b>OWNER</b>');
            die();
        }
    }
    public static function Admin($user) {
        global $admin_user_id;
        global $chat;
        if ($user['tg_id'] != $admin_user_id && $user['rank'] != 'ADMIN') {
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$user['rank'].'</b>
Необхідний ранг: <b>ADMIN</b>');
            die();
        }
    }
    public static function Moder($user) {
        global $admin_user_id;
        global $chat;
        if ($user['tg_id'] != $admin_user_id && $user['rank'] != 'ADMIN') {
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$user['rank'].'</b>
Необхідний ранг: <b>MODER</b>');
            die();
        }
    }
}