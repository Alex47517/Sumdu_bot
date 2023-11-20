<?php
namespace api;
class Log {
    public static function admin($type, $message) {
        $chat = new chat($GLOBALS['admin_user_id'][0]);
        $chat->sendMessage('[LOG/'.$type.'] '.$message);
        return true;
    }
}