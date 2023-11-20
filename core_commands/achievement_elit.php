<?php
if ($chat->chat['id'] == 2) {
    if ($chatMember->chatMember['message_counter'] == 1000) {
        $user_achievement = R::dispense('userachievements');
        $user_achievement->user_id = $user->user['id'];
        $user_achievement->achievement_id = 6;
        $user_achievement->date = date('U');
        R::store($user_achievement);
        $chat->sendMessage('🎓 Вітаємо <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a> з отриманням нового досягнення!');
    }
}