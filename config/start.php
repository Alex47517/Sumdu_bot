<?php
require_once 'rb.php';
require_once __DIR__.'/../../tokens.php';
// Вміст файлу tokens.php:
// $bot_token = 'BOT_TOKEN';
// $admin_user_id = 458746251;
// $db_name = 'DB_NAME';
// $db_login = 'DB_LOGIN';
// $db_pass = 'DB PASSWORD';
R::setup( 'mysql:host=localhost;dbname='.$db_name, $db_login, $db_pass);