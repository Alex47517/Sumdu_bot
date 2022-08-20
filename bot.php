<?php
require_once 'config/start.php';
require_once 'config/loader.php';

use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};

$request_json = file_get_contents('php://input');
$request = json_decode($request_json, true);

if (!$request['message']['chat']['id']) die('!chat');
if ($request['message']['date'] + 20 < date('U')) die('Timeout');

$bot = new Bot($bot_token);
$update = new update($request);

$chat = new chat(update::$chat['id']);
if (!$chat->chat['id']) {
    $chat->storeChat(update::$chat);
}

$user = new User();
if (!$user->loadByTGID(update::$from['id'])) {
    $user->newUser(update::$from);
}

$chatUser = new ChatMember($user->user['id'], $chat->chat['id']);

require_once 'functions.php';
require_once 'permissions.php';

$msg = update::$message['text'];
$cmd = explode(' ', $msg);

Log::admin('MSG', ''.$chat->chat['id'].' | '.$chat->chat['title'].' | <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a>: '.$msg);

//DEBUG:
if ($chat->chat_id == $admin_user_id) {
    $result = $chat->sendMessage(var_export($request, true));
}

//файли із core_commands будуть підключені усі без виключення
//це системні команди, котрі повинні постійно виконуватися
$dir = __DIR__.'/core_commands/';
$files = scandir($dir);
foreach($files as $file) {
    if (($file !== '.') and ($file !== '..'))
        include_once $dir.''.$file;
}

$action = R::findOne('actions', '`initiator` = ? AND `type` = ?', [$cmd[0], 'text']);
if ($action['args'] && !$cmd[$action['args']]) args_error($action);
$file = R::load('commandfiles', $action['file_id']);
if ($action) {
    require_once __DIR__.'/commands/'.$file['filename'];
}