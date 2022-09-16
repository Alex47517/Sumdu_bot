<?php
class Permissions {
    public static function Owner($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        global $user;
        if ($c_user['tg_id'] != $admin_user_id) {
            $user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$c_user['rank'].'</b>
Необхідний ранг: <b>OWNER</b>');
            die();
        }
        return true;
    }
    public static function Admin($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        global $user;
        if ($c_user['tg_id'] != $admin_user_id && $c_user['rank'] != 'ADMIN') {
            $user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$c_user['rank'].'</b>
Необхідний ранг: <b>ADMIN</b>');
            die();
        }
        return true;
    }
    public static function Star($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        global $user;
        if ($c_user['tg_id'] != $admin_user_id && $c_user['rank'] != 'ADMIN' && $c_user['rank'] != '*') {
            $user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$c_user['rank'].'</b>
Необхідний ранг: <b>ADMIN</b> або <b>*</b>');
            die();
        }
        return true;
    }
    public static function Moder($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        global $user;
        if ($c_user['tg_id'] != $admin_user_id && $c_user['rank'] != 'ADMIN' && $c_user['rank'] != 'MODER') {
            $user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$c_user['rank'].'</b>
Необхідний ранг: <b>MODER</b>');
            die();
        }
        return true;
    }
    public static function ChatAdmin($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        global $user;
        global $chatMember;
        $is_admin = $chatMember->getChatStatus($c_user['id']);
        if ($c_user['tg_id'] != $admin_user_id && $c_user['rank'] != 'ADMIN' && !$is_admin) {
            $c_user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>Учасник чату</b>
Необхідний ранг: <b>Адміністратор чату</b>');
            die();
        }
        return true;
    }
    public static function Curator($c_user, $return = false) {
        global $admin_user_id;
        global $chat;
        $curator = R::findOne('curators', 'user_id = ?', [$c_user['id']]);
        if ($c_user['tg_id'] != $admin_user_id && $c_user['rank'] != 'ADMIN' && !$curator) {
            $c_user->update('display');
            if ($return) return false;
            $chat->sendMessage('📛 <b>У Вас недостатньо повноважень для виконання цієї дії</b>

Ваш ранг: <b>'.$c_user['rank'].'</b>
Необхідний ранг: <b>CURATOR</b>');
            die();
        }
        return $curator;
    }
}