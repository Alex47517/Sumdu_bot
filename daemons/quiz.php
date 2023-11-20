<?php
require __DIR__.'/../config/start.php';
require __DIR__.'/../config/loader.php';
require_once '/home/alex/websites/bot.sumdubot.pp.ua/test/vendor/autoload.php';
set_time_limit(0); //Знімаємо обмеження по часу на виконання скрипту
use api\{chat as chat, Bot as Bot, stats as stats, Log as Log};
use Orhanerday\OpenAi\OpenAi;
$user = new User();
$bot = new Bot($bot_token);
if(!$user->loadByID($argv[1])) die('[DAEMON/AVIATOR] User not found');
$chat = new chat($user->user['tg_id']);
$userId = $argv[1]; // ID пользователя
$theme = $argv[2]; // Тема викторины
$question = $argv[3]; // Номер вопроса
//$chat->sendMessage($question);
$themes = [
    1 => 'програмування',
    2 => 'моделювання',
    3 => 'телеком',
    4 => 'математика',
    5 => 'електроніка',
];
$letters = ['А', 'Б', 'В', 'Г'];
echo '[1:'.$user->user['nick'].'] ok!';
// Запрос для получения ID викторин, которые пользователь еще не решал по заданной теме
$sql = "SELECT q.id 
        FROM quiz q 
        LEFT JOIN quizresults qr ON q.id = qr.question AND qr.user = ?
        WHERE qr.id IS NULL AND q.theme = ?";
$quizIds = R::getCol($sql, [$userId, $theme]);
echo '[2] ok!';
if (count($quizIds) > 0) {
    $randomKey = array_rand($quizIds);
    $randomQuizId = $quizIds[$randomKey];

    $quiz = R::load('quiz', $randomQuizId); // Загружаем объект викторины
    $answers = R::getAll('SELECT * FROM `quizanswers` WHERE `quiz` = ? ORDER BY `id` ASC', [$quiz['id']]);
} else {
    echo '[3] ok!';
    //ssage('TEST!');
    echo '[3.5] ok!';
    $chat->editMessageText('<b>🙀 Ого! Ти відповів на усі питання з бази. Але не засмучуйся, зараз я придумаю щось новеньке :)</b>', null, $user->LocalStorageGet('msg_id'));
    echo '[4] GPT STARTED!';
    $openai = new OpenAI('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');
    $complete = $openai->chat([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                "role" => "system",
                "content" => "Твоє завдання - створити складне теоретичне питання з електроніки для студентів університету з рівнем складності 10, яке вимагає глибоких знань у цій області. Подбай про те, щоб 4 варіанти відповідей не містили фактичних помилок і були логічно коректними. В кінці чітко вкажи правильну букву у квадратних дужках, не допускаючи помилок.
Питання: [Задай складне теоретичне питання, яке потребує використання глибоких знань]
А) [варіант відповіді] Б) [варіант відповіді] 
В) [варіант відповіді] Г) [варіант відповіді] 
[Правильна літера]"
            ],
            [
                "role" => "user",
                "content" => 'Створи дуже складне завдання. Тема: '.$themes[$theme].'. Обов\'язково вкажи правильну літеру у кінці повідомлення. Тобто, якщо правильна літера А (але не зосереджуйся саме на А), то ти повинен вказати її ось так: [А]',
            ]
        ],
        'temperature' => 1.0,
        'max_tokens' => 1000,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
    ]);
    //$text = end($complete)['text'];
    $text = json_decode($complete, true)['choices'][0]['message']['content'];
    if (!$text) {
        $file = fopen('log.txt', 'w+');
        fwrite($file, var_export($openai->getCURLInfo(),true));
        fclose($file);
        $user->addBal(100);
        //$chat->sendMessage('[INFO] Ваш баланс поповнено на 300💰');
        $keyboard[0][0]['text'] = '🔜 Далі 🔜';
        $keyboard[0][0]['callback_data'] = 'quiz_next_'.($question+1);
        $chat->editMessageText('<b>💢 Схоже, що в нас виникла проблема. Давай домовимося так: Я дам тобі 100💰, але ти надішлеш @alex47517 цей текст:</b>

<code>'.var_export(json_decode($complete, true),true).'</code>', ['inline_keyboard' => $keyboard], $user->LocalStorageGet('msg_id')); die();
    }
    //filters
    $question_tmp = trim(explode('А)', $text)[0]);
    $answers_text = explode('А)', $text)[1];
    $answers[0] = trim(str_replace('Б)', '', trim(explode('Б)', $answers_text)[0])));
    $answers[1] = trim(str_replace('В)', '', trim(explode('В)', explode('Б)', $answers_text)[1])[0])));
    $answers[3] = trim(str_replace('Г)', '', trim(explode('Г)', explode('В)', $answers_text)[1])[0])));
    $answers[4] = trim(str_replace('[', '', trim(explode('[', explode('Г)', $answers_text)[1])[0])));
    $correctAnswerLetter = mb_substr($text, mb_strrpos($text, '[') + 1, 1);
    $correctAnswerIndex = mb_strpos('АБВГ', $correctAnswerLetter);
    if (empty($question_tmp) || count($answers) != 4 || $correctAnswerIndex === false) {
        echo "Помилка: не вдалося розібрати текст за вказаним форматом.\n";
        $keyboard[0][0]['text'] = '🔜 Далі 🔜';
        $keyboard[0][0]['callback_data'] = 'quiz_next_'.$question;
        $chat->editMessageText('<b>💢 Схоже, що в нас виникла проблема. Давай домовимося так: Я дам тобі 100💰, але ти надішлеш @alex47517 цей текст:</b>

<code>[ERR] Failed to parse ChatGPT response</code>
<code>'.$text.'</code>', ['inline_keyboard' => $keyboard], $user->LocalStorageGet('msg_id')); die();
    } else {
        // Вивід результатів
        echo "Питання: $question_tmp\n";
        echo "Варіанти відповідей: " . implode(", ", array_map('trim', $answers)) . "\n";
        echo "Правильна відповідь: $correctAnswerIndex\n";
        // Вивід результатів
        $quiz = R::dispense('quiz');
        $quiz->question = $question_tmp;
        $quiz->theme = $theme;
        R::store($quiz);
        Log::admin('QUIZ/SUCCESS-GPT', ' | <a href="tg://user?id='.$user->user['tg_id'].'">'.$user->user['nick'].'</a>: <code>'.$text.'</code>');
        var_dump($answers);
        $is_correct = false;
        foreach ($answers as $key => $ans) {
            $answer = R::dispense('quizanswers');
            $answer->quiz = $quiz['id'];
            $answer->answer = $ans;
            if ($key == $correctAnswerIndex) { $answer->correct = 1; $is_correct = true; }
            else $answer->correct = 0;
            R::store($answer);
        }
        $answers = R::getAll('SELECT * FROM `quizanswers` WHERE `quiz` = ? ORDER BY `id` ASC', [$quiz['id']]);
        if (!$answers[3]['answer'] or !$is_correct) {
            R::trash($quiz);
            $keyboard[0][0]['text'] = '🔜 Далі 🔜';
            $keyboard[0][0]['callback_data'] = 'quiz_next_'.$question;
            $chat->editMessageText('<b>💢 Схоже, що в нас виникла проблема. Давай домовимося так: Я зарахую це питання, але ти спробуєш ще раз:</b>

<code>[ERR] Failed to parse ChatGPT response</code>
<code>'.$text.'</code>', ['inline_keyboard' => $keyboard], $user->LocalStorageGet('msg_id')); die();
        }
    }
}
$answers_text = '';
var_dump($answers);
foreach ($answers as $key => $answer) {
    $answers_text .= '<b>'.$letters[$key].')</b> '.$answer['answer'].'
';
    if ($answer['correct']) {
        $correct_answer = $letters[$key].') '.$answer['answer'];
    }
    $keyboard[0][$key]['text'] = $letters[$key];
    $keyboard[0][$key]['callback_data'] = 'quiz_answer_'.$quiz['id'].'_'.$key.'_'.($question+1);
}
$chat->editMessageText('<b>❓ '.$question.'/10 ></b> '.mb_convert_case($themes[$theme], MB_CASE_TITLE, "UTF-8").' [⏰ 45 сек.]

'.$quiz['question'].'
<em>= Обери 1 відповідь: =</em>
'.$answers_text, ['inline_keyboard' => $keyboard], $user->LocalStorageGet('msg_id'));
sleep(45);
$keyboard = null;
$keyboard[0][0]['text'] = '🔜 Далі 🔜';
$keyboard[0][0]['callback_data'] = 'quiz_answer_'.$quiz['id'].'_-1_'.($question+1);
$chat->editMessageText('<b>❓ '.$question.'/10 ></b> '.mb_convert_case($themes[$theme], MB_CASE_TITLE, "UTF-8").'

>> <b>ЧАС ВИЧЕРПАНО!</b>

Правильна відповідь:
<em>'.$correct_answer.'</em>', ['inline_keyboard' => $keyboard], $user->LocalStorageGet('msg_id'));