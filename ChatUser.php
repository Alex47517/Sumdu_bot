<?php

class ChatMember {
    public $chatMember;
    public function __construct($user_id, $chat_id) {
        $load_status = $this->load($user_id, $chat_id);
        if (!$load_status) {
            $this->newChatUser($user_id, $chat_id);
        }
        return true;
    }
    public function load($user_id, $chat_id) {
        $chatMember = R::findOne('chatmembers', 'user_id = ? AND chat_id = ?', [$user_id, $chat_id]);
        if ($chatMember) {
            $this->chatMember = $chatMember;
            return true;
        } else {
            return false;
        }
    }
    public function newChatUser($user_id, $chat_id) {
        $chatMember = R::findOne('chatmembers', 'user_id = ? AND chat_id = ?', [$user_id, $chat_id]);
        if ($chatMember) {
            $this->chatMember = $chatMember;
            return false;
        }
        $chatMember = R::dispense('chatmembers');
        $chatMember->user_id = $user_id;
        $chatMember->chat_id = $chat_id;
        $chatMember->is_admin = $is_admin;
        $chatMember->last_update_status = date('U');
        R::store($chatMember);
        $this->chatMember = $chatMember;
        return true;
    }
    public function update($param, $value = null) {
        $this->user[$param] = $value;
        R::store($this->user);
        return true;
    }
    private function getChatStatus() {
        
    }
}