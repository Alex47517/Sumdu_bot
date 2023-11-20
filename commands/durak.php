<?php
//
// Command: Дурак #
// Text: !дурак /durak #
// Callback: durak #
// Display: durak #
// Info: Запускає гру "Дурак" #
// Syntax: !durak [ставка*] #
// Args: 0 #
// Rank: USER #
//
require __DIR__.'/../lib/Process.php';
use api\{update as update, Log as Log, AutoClean as AutoClean};
function resend($url) {
    global $request_json;
    $myCurl = curl_init();
    curl_setopt_array($myCurl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Content-Length: '.strlen($request_json)],
        CURLOPT_POSTFIELDS => $request_json
    ));
    $response = curl_exec($myCurl);
    curl_close($myCurl);
    return $response;
}
$starter = ['!durak', '/durak', '!дурак'];
if (in_array($cmd[0], $starter)) {
    AutoClean::save();
    $bet = $cmd[1];
    if (!$bet or $bet < 0 or $bet > 1000000) $bet = 0;
    $bet = round($bet);
    $result = $chat->sendMessage('⏳ <b>Гра "Дурак"</b>

♦ Ставка: <b>'.$bet.'💰</b>

<em>Запуск NodeJS...</em>

⚠ <em>Якщо нема реакції => хтось зараз вже грає, спробуйте пізніше</em>');
    $edit = $result->result->message_id;
    $command = 'echo "' . $nodeJS_password . '" | su -l ' . $nodeJS_username . ' -c "node ' . __DIR__ . '/../NodeJS/durak.js ' . $chat->chat_id . ' ' . $edit . ' '.$bet.'"';
    $process = new Process($command);
//$processId = $process->getPid();
    Log::admin('DURAK', $command);
}
$chat->sendMessage('[DEBUGING] $cms is: '.var_export($cmd, true).'');
if ($ex_display[0] == 'durak' or $ex_callback[0] == 'durak' or ($cmd[0] == '/start' && $cmd[1] == 'durak')) {
    $chat->sendMessage('[LOG/UNO] Resending to NodeJS');
    $result = resend('http://localhost:7001/bot' . $bot_token . '/');
    die();
}
