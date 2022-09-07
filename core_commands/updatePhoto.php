<?php
use api\Bot as Bot;
if (!$user->user['avatar'] or $user->user['last_update_avatar']+864000 < date('U')) {
    $user->update('last_update_avatar', date('U'));
    $photos = Bot::getUserProfilePhotos($user->user['tg_id']);
    if ($photos['result']['total_count']) {
        //load TG avatar
        $photo_tg_id = $photos['result']['photos'][0][0]['file_id'];
        $tg_file_response = Bot::getFile($photo_tg_id);
        $path = $tg_file_response['result']['file_path'];
        $store_path = __DIR__.'/../../../sumdu.fun/img/profile';
        $filename = Bot::storeFile($path, $store_path, $user->user['nick']);
        $user->update('avatar', $filename);
    } else {
        //setting default avatar
        $user->update('avatar', 'login.jpg');
    }
}