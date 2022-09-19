<?php
namespace api;
use R;
use api\update as update;

class chat {
    public $chat;
    public $chat_id; //TELEGRAM ID
    function __construct($chat_id) {
        $this->chat_id = $chat_id;
        $chat = R::findOne('chats', 'tg_id = ?', [$chat_id]);
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
    public function sendMessage($text, $reply_to_message_id = null, $reply_markup = null, $disable_notification = true, $protect_content = false, $associative = false) {
        if ($reply_markup) $reply_markup = json_encode($reply_markup);
        $params = [
            'text' => $text,
            'chat_id' => $this->chat_id,
            'reply_to_message_id' => $reply_to_message_id,
            'reply_markup' => $reply_markup,
            'disable_notification' => $disable_notification,
            'allow_sending_without_reply' => true,
            'protect_content' => $protect_content,
            'parse_mode' => 'html',
        ];
        return Bot::request('sendMessage', $params, $associative);
    }
    public function update($param, $value = null) {
        $this->chat[$param] = $value;
        R::store($this->chat);
        return true;
    }
    public function getChatMember($user_id) {
        $params = [
            'user_id' => $user_id,
            'chat_id' => $this->chat_id,
        ];
        return Bot::request('getChatMember', $params);
    }
    public function editMessageText($text, $reply_markup, $message_id) {
        if (update::$with_photo) {
            $this->deleteMessage($message_id);
            return $this->sendMessage($text, null, $reply_markup);
        }
        if ($reply_markup) $reply_markup = json_encode($reply_markup);
        $params = [
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'html',
            'chat_id' => $this->chat_id,
            'reply_markup' => $reply_markup
        ];
        return Bot::request('editMessageText', $params);
    }
    public function restrictChatMember($user_id, $permissions, $until_date) {
        $params = [
            'user_id' => $user_id,
            'chat_id' => $this->chat_id,
            'permissions' => json_encode($permissions),
            'until_date' => $until_date,
        ];
        return Bot::request('restrictChatMember', $params);
    }
    public function banChatMember($user_id, $until_date, $revoke_messages = false) {
        $params = [
            'user_id' => $user_id,
            'chat_id' => $this->chat_id,
            'revoke_messages' => $revoke_messages,
            'until_date' => $until_date,
        ];
        return Bot::request('banChatMember', $params);
    }
    public function unbanChatMember($user_id, $only_if_banned = true) {
        $params = [
            'user_id' => $user_id,
            'chat_id' => $this->chat_id,
            'only_if_banned' => $only_if_banned,
        ];
        return Bot::request('unbanChatMember', $params);
    }
    public function createChatInviteLink($name, $expire_date = null, $member_limit = null, $creates_join_request = null) {
        $params = [
            'name' => $name,
            'chat_id' => $this->chat_id,
            'expire_date' => $expire_date,
            'member_limit' => $member_limit,
            'creates_join_request' => $creates_join_request,
        ];
        return Bot::request('createChatInviteLink', $params);
    }
    public function sendPhoto($photo, $caption = null, $reply_to_message_id = null, $reply_markup = null, $disable_notification = true, $protect_content = false) {
        if ($reply_markup) $reply_markup = json_encode($reply_markup);
        $params = [
            'photo' => $photo,
            'caption' => $caption,
            'reply_to_message_id' => $reply_to_message_id,
            'reply_markup' => $reply_markup,
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
            'parse_mode' => 'html',
            'chat_id' => $this->chat_id,
            'allow_sending_without_reply' => true,
        ];
        return Bot::request('sendPhoto', $params);
    }
    public function deleteMessage($message_id) {
        $params = [
            'message_id' => $message_id,
            'chat_id' => $this->chat_id,
        ];
        return Bot::request('deleteMessage', $params);
    }
    public function answerCallbackQuery($text, $show_alert = false, $url = null) {
        $params = [
            'text' => $text,
            'callback_query_id' => update::$callback_id,
            'chat_id' => $this->chat_id,
            'show_alert' => $show_alert,
            'URL' => $url,
        ];
        return Bot::request('answerCallbackQuery', $params);
    }
}
