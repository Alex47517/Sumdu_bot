<?php
require_once 'rb.php';
require_once __DIR__.'/../../tokens.php';
// Вміст файлу tokens.php:
// $bot_token = 'BOT_TOKEN';
// $admin_user_id = 458746251;
// $db_host = 'DB_HOST';
// $db_name = 'DB_NAME';
// $db_login = 'DB_LOGIN';
// $db_pass = 'DB PASSWORD';
const DOMAIN = 'sumdubot.pp.ua';
$bot_username = 'Sumdu_bot';
$chat_for_checkcodes = -1001399681943;
R::setup( 'mysql:host='.$db_host.';dbname='.$db_name, $db_login, $db_pass);
