<?php
namespace api;
use R;

class MessageController {
    static function addToRemove($chat, $message_id) {
        if ($chat['autoclean']) {
            $message = R::dispense('autoclean');
            $message->chat = $chat['id'];
            $message->message_id = $message_id;
            $message->date = date('U');
            R::store($message);
            return true;
        } else return false;
    }
}