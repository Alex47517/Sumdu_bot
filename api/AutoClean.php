<?php
namespace api;
use R;
use api\{chat as chat, update as update};

class AutoClean {
    static bool $save = false;
    //Додає повідомлення в чергу автовидалення
    static function addToRemove($chat, $message_id) {
        if ($chat['autoclean'] && !self::$save) {
            $message = R::dispense('autoclean');
            $message->chat_id = $chat['id'];
            $message->tg_id = update::$message_id;
            $message->date = date('U');
            R::store($message);
            $message = R::dispense('autoclean');
            $message->chat_id = $chat['id'];
            $message->tg_id = $message_id;
            $message->date = date('U');
            R::store($message);
            return true;
        } else return false;
    }
    //Видаляє усі повідомлення згідно з налаштуваннями чатів
    static function start() {
        $messages = R::getAll('SELECT * FROM autoclean WHERE `date` <= ?', [date('U')]);
        foreach ($messages as $message) {
            $message = R::load('autoclean', $message['id']);
            $chat_db = R::load('chats', $message['chat_id']);
            if ($message['date']+$chat_db['autoclean_delay'] < date('U')) {
                $chat = new Chat($chat_db['tg_id']);
                $chat->deleteMessage($message['tg_id']);
                R::trash($message);
            }
        }
        return true;
    }
    //Захищає сесію від автовидалення
    static function save() {
        self::$save = true;
    }
}