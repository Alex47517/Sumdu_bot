<?php
//
// Command: UNO #
// Text: !uno /uno #
// Callback: uno #
// Display: uno #
// Info: Запускає гру "UNO" #
// Syntax: !uno [ставка*] #
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
$starter = ['!uno', '/uno'];
if (in_array($cmd[0], $starter)) {
    AutoClean::save();
    $bet = $cmd[1];
    if (!$bet or $bet < 0 or $bet > 1000000) $bet = 0;
    $bet = round($bet);
    $result = $chat->sendMessage('⏳ <b>Гра "UNO"</b>

♦ Ставка: <b>'.$bet.'💰</b>

<em>Запуск NodeJS...</em>

⚠ <em>Якщо нема реакції => хтось зараз вже грає, спробуйте пізніше</em>');
    $edit = $result->result->message_id;
    $command = 'echo "' . $nodeJS_password . '" | su -l ' . $nodeJS_username . ' -c "node ' . __DIR__ . '/../NodeJS/uno.js ' . $chat->chat_id . ' ' . $edit . ' '.$bet.'"';
    $process = new Process($command);
//$processId = $process->getPid();
    Log::admin('UNO', $command);
}
if ($ex_display[0] == 'uno' or $ex_callback[0] == 'uno' or ($cmd[0] == '/start' && $cmd[1] == 'uno')) {
    //$chat->sendMessage('[LOG/UNO] Resending to NodeJS');
    $result = resend('http://localhost:7000/bot'.$bot_token.'/');
    die();
}