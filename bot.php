<?php
require_once 'config/start.php';
require_once 'config/loader.php';

use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};

$request_json = file_get_contents('php://input');
$request = json_decode($request_json, true);

$bot = new Bot($bot_token);
$update = new update($request);
if (!update::$chat['id']) die('!chat');
if (update::$date + 20 < date('U')) die('Timeout');

$chat = new chat(update::$chat['id']);
if (!$chat->chat['id']) {
    $chat->storeChat(update::$chat);
}

$user = new User();
if (!$user->loadByTGID(update::$from['id'])) {
    $user->newUser(update::$from);
}

$chatMember = new ChatMember($user->user['id'], $chat->chat['id']);

require_once 'functions.php';
require_once 'permissions.php';

$msg = update::$message['text'];
if ($msg[0] == '/') $msg = str_replace('@'.$bot_username, '', $msg);
if ($msg == '0' && !$user->user['display']) die();
$cmd = explode(' ', $msg);
$callback_data = update::$callback_data;
$display = $user->user['display'];

Log::admin('MSG', ''.$chat->chat['id'].' | '.$chat->chat['title'].' | <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a>: '.$msg);

//DEBUG:
if ($chat->chat_id == $admin_user_id) {
    $result = $chat->sendMessage(var_export($request, true));
}

if ($msg == '/start' or $msg == '/start@'.$bot_username) {
    menu();
}
//файли із core_commands будуть підключені усі без виключення
//це системні команди, котрі повинні постійно виконуватися
$dir = __DIR__.'/core_commands/';
$files = scandir($dir);
foreach($files as $file) {
    if (($file !== '.') and ($file !== '..'))
        include_once $dir.''.$file;
}
$init = ['text' => $cmd[0], 'callback' => explode('_', $callback_data)[0], 'display' => explode('_', $display)[0]];
foreach ($init as $key => $in) {
    if ($action) break;
    if ($in) $action = R::findOne('actions', '`initiator` = ? AND `type` = ?', [$in, $key]);
}
if (!$action['file_id']) die();
$file = R::load('commandfiles', $action['file_id']);
switch ($file['rank']) {
    case 'OWNER': Permissions::Owner($user->user); break;
    case 'ADMIN': Permissions::Admin($user->user); break;
    case 'MODER': Permissions::Moder($user->user); break;
    case 'ChatAdmin': Permissions::ChatAdmin($user->user); break;
}
if ($action['args'] && !$cmd[$action['args']]) args_error($action);
if ($action) {
    require_once __DIR__.'/commands/'.$file['filename'];
}