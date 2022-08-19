<?php
namespace api;
class Log extends chat {
    public static function admin($type, $message) {
        $chat = new chat($GLOBALS['admin_user_id']);
    }
}