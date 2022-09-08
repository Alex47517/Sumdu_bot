<?php
namespace api;
use R;

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
        $is_admin = $this->getChatStatus($user_id);
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
        $this->chatMember[$param] = $value;
        R::store($this->chatMember);
        return true;
    }

    public function getChatStatus($user_id) {
        global $chat;
        $user = R::load('users', $user_id);
        $chat_user = $chat->getChatMember($user['tg_id']);
        if ($chat_user->result->status == 'administrator' or $chat_user->result->status == 'creator') $is_admin = true; else $is_admin = false;
        return $is_admin;
    }

    public function addToBlackList($time) {
        if ($time == 0) $store = 1; else $store = date('U')+round($time);
        $this->update('blacklist', ($store));
        return true;
    }
}