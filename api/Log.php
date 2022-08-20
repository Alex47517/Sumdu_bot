<?php
namespace api;
class Log {
    public static function admin($type, $message) {
        $chat = new chat($GLOBALS['admin_user_id']);
        $chat->sendMessage('[LOG/'.$type.'] '.$message);
        return true;
    }
}