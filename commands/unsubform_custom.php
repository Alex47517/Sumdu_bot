<?php
//
// Command: UnsubForm #
// Callback: UnsubFormMailing #
// Info: Відписатися від розсилки з форми #
// Syntax: Кнопка "Відписатися" під повідомленням з розсилки #
// Args: 1 #
// Rank: USER #
//
use api\update as update; 
$resp = R::findOne('formresponses', 'form_id = ? AND user_id = ?', [$ex_callback[1], $user->user['id']]);
if ($resp) {
$form = R::load('forms', $ex_callback[1]);
$resp->mailing = 0;
R::store($resp);
$message = '✅ <b>Ви відписалися від розсилки із форми "'.$form['name'].'"</b>';
$chat->editMessageText($message, null, update::$btn_id);
} else custom_error('Помилка 404', 'Форму не знайдено');