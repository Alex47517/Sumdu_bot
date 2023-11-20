<?php
//
// Command: Вікторина #
// Text: !вікторина !викторина /quiz #
// Callback: quiz #
// Info: Запускає вікторину #
// Syntax: !вікторина #
// Args: 0 #
// Rank: USER #
//
require_once '/home/alex/websites/bot.sumdubot.pp.ua/test/vendor/autoload.php';
require __DIR__.'/../lib/Process.php';
use Orhanerday\OpenAi\OpenAi;
use api\{update as update, stats as stats};
$themes = [
    1 => 'програмування',
    2 => 'моделювання',
    3 => 'телеком',
    4 => 'математика',
    5 => 'електроніка',
];
$letters = ['А', 'Б', 'В', 'Г'];
if ($msg == '!вікторина' or update::$callback_data == 'quiz') {
    if ($user->user['quiz'] > date('U')) {
        $d_time = Time::sec2time_txt(($user->user['quiz'] - date('U')));
        $text = '<b>💢 Вікторина вже пройдена</b>

До наступної вікторини: '.$d_time;
        if (update::$callback_data) $chat->editMessageText($text, null, update::$btn_id);
        else $chat->sendMessage($text, update::$message_id);
    } else {
        if ($chat->chat_id != $user->user['tg_id']) {
            custom_error('Неможливо запустити вікторину у групі', 'Ви можете пройти вікторину у приватних повідомленнях з ботом');
        }
        $user->LocalStorageSet('quiz_success', 0);
        $keyboard[0][0]['text'] = '💻 Програмування';
        $keyboard[0][0]['callback_data'] = 'quiz_1';
        $keyboard[1][0]['text'] = '🗿 Моделювання';
        $keyboard[1][0]['callback_data'] = 'quiz_2';
        $keyboard[2][0]['text'] = '🌍 Телеком';
        $keyboard[2][0]['callback_data'] = 'quiz_3';
        $keyboard[3][0]['text'] = '🔢 Математика';
        $keyboard[3][0]['callback_data'] = 'quiz_4';
        $keyboard[4][0]['text'] = '💡 Електроніка';
        $keyboard[4][0]['callback_data'] = 'quiz_5';
        $text = '<b>📝 Вікторина</b>

Обери тему';
        if (update::$callback_data) $chat->editMessageText($text, ['inline_keyboard' => $keyboard], update::$btn_id);
        else $chat->sendMessage($text, null, ['inline_keyboard' => $keyboard], false, true);
    }
    die();
} elseif ($ex_callback[0] == 'quiz' && $ex_callback[1] != 'answer') {
    if ($user->user['quiz'] > date('U')) {
        $d_time = Time::sec2time_txt(($user->user['quiz'] - date('U')));
        $text = '<b>💢 Вікторина вже пройдена</b>

До наступної вікторини: '.$d_time;
        if (update::$callback_data) $chat->editMessageText($text, null, update::$btn_id);
        else $chat->sendMessage($text, update::$message_id);
    } else {
        if (!$user->LocalStorageGet('quiz_theme')) {
            $user->LocalStorageSet('quiz_theme', $ex_callback[1]);
        }
        if ($ex_callback[2]) $question = $ex_callback[2];
        else $question = 1;
        if ($question > 10) {
            $chat->editMessageText('<b>🧠 Вікторина завершена!</b>

= Результат: =
<em>Правильних відповідей: '.$user->LocalStorageGet('quiz_success').'
Нагорода: '.($user->LocalStorageGet('quiz_success')*300).'💰</em>', null, update::$btn_id);
            $user->addBal($user->LocalStorageGet('quiz_success')*300);
            $user->LocalStorageClear();
            if ($user->user['id'] != 1) {
                $user->update('quiz', (date('U')+86400));
            }
            die();
        }
        $result = $chat->editMessageText('<b>⏳ Завантаження...</b>

<em>Майте на увазі - час обмежено!</em>', null, update::$btn_id);
        $id = $result->result->message_id;
        $command = 'php -f ' . __DIR__ . '/../daemons/quiz.php '.$user->user['id'].' '.$user->LocalStorageGet('quiz_theme').' '.$question.' '.$id;
//        $chat->sendMessage($command);
        $process = new Process($command);
        $processId = $process->getPid();
        $user->LocalStorageSet('quiz_pid', $processId);
        $user->LocalStorageSet('msg_id', $id);
        die();
    }
} elseif ($ex_callback[1] == 'answer') {
    $quiz_id = $ex_callback[2];
    $user_answer = $ex_callback[3];
    $pid = $user->LocalStorageGet('quiz_pid');
    $process = new Process();
    $process->setPid($pid);
    $quiz = R::load('quiz', $quiz_id);
    $answers = R::getAll('SELECT * FROM `quizanswers` WHERE `quiz` = ? ORDER BY `id` ASC', [$quiz['id']]);
    $correct = false;
    foreach ($answers as $key => $answer) {
        if ($key == $user_answer && $answer['correct']) {
            $correct = true;
            $extra = '✅ ';
        } elseif ($key == $user_answer) {
            $extra = '❌ ';
        } elseif ($answer['correct']) {
            $extra = '✔ ';
        } else $extra = null;
        $answers_text .= '<b>'.$extra.''.$letters[$key].')</b> '.$answer['answer'].'
';
    }
    $answer = R::dispense('quizresults');
    $answer->user = $user->user['id'];
    $answer->question = $quiz['id'];
    if ($correct) {
        if (!$user->LocalStorageGet('quiz_success')) {
            $user->LocalStorageSet('quiz_success', 1);
        } else $user->LocalStorageSet('quiz_success', ($user->LocalStorageGet('quiz_success')+1));
        $answer->succes = 1;
        $text = '📝 <b>Правильна відповідь! (+300💰)</b>

'.$quiz['question'].'

'.$answers_text;
    } else {
        $answer->succes = 0;
        $text = '📝 <b>Помилка!</b>

'.$quiz['question'].'

'.$answers_text;
    }
    R::store($answer);
    $question = $ex_callback[4];
    $keyboard[0][0]['text'] = '🔜 Далі 🔜';
    $keyboard[0][0]['callback_data'] = 'quiz_next_'.$question;
    if ($process->stop()) {
        $chat->editMessageText($text, ['inline_keyboard' => $keyboard], update::$btn_id);
    } else {
        $chat->deleteMessage($user->LocalStorageGet('msg_id'));
        $result = $chat->sendMessage($text, null, ['inline_keyboard' => $keyboard], update::$btn_id);
        $id = $result->result->message_id;
        $user->LocalStorageSet('msg_id', $id);
    }
}