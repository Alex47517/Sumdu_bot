<?php
namespace api;
use R;

class chat {
    public $chat;
    public $chat_id;
    function __construct($chat) {
        $this->chat_id = $chat['id'];
        $chat = R::findOne('chats', 'tg_id = ?', [$chat['id']]);
        if ($chat) {
            $this->chat = $chat;
            return true;
        } else return false;
    }
    public function storeChat($chat) {
        if ($chat['type'] == 'group' or $chat['type'] == 'supergroup') {
            $this->sendMessage('👋 Вас вітає <b>СумДУ бот</b>

<em>Для корректної роботи надайте права адміністратора боту</em>');
        } elseif ($chat['type'] == 'private') {
            $chat['title'] = 'PM';
            $this->sendMessage('👋 Вас вітає <b>СумДУ бот</b>');
        } else {
            die('Invalid chat type');
        }
        $new_chat = R::dispense('chats');
        $new_chat->tg_id = $chat['id'];
        $new_chat->title = $chat['title'];
        $new_chat->type = $chat['type'];
        R::store($new_chat);
        $this->chat = $new_chat;
        return true;
    }
    function sendMessage($text, $reply_to_message_id = null, $reply_markup = null, $disable_notification = true, $protect_content = false) {
        $params = [
            'text' => $text,
            'chat_id' => $this->chat_id,
            'reply_to_message_id' => $reply_to_message_id,
            'answer_markup' => $reply_markup,
            'disable_notification' => $disable_notification,
            'allow_sending_without_reply' => true,
            'protect_content' => $protect_content,
            'parse_mode' => 'html',
        ];
        return Bot::request('sendMessage', $params);
    }
}