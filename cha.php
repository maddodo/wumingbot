<?php
if (empty($arg1)) die('not here');
if (is_numeric($arg1) && strlen($arg1) == 11 && $arg1 > 13000000000) $type = 'mobile';
elseif (is_numeric($arg1) && strlen($arg1) > 4 && strlen($arg1) < 11) $type = 'qq';
elseif (filter_var($arg1, FILTER_VALIDATE_EMAIL)) $type = 'mail';
elseif (preg_match_all("/^([\x80-\xff]{2,5})/", $arg1) > 0) $type = 'name';
else $type = 'other';
$send = "查询结果：\n";
switch ($type) {
    case 'mobile':
        $qb = $DB->get_row("SELECT username as qq, mobile FROM `bind`.`8eqq` WHERE `mobile` = '{$arg1}'");
        $wb = $DB->get_row("SELECT * FROM `bind`.`weibo` WHERE `mobile` = '{$arg1}'");
        $send .= empty($qb) ? "Q绑为空\n" : arr2txt($qb);
        $send .= empty($wb) ? "微博为空\n" : arr2txt($wb);
        include(__DIR__ . '/rk.php');

        $telegram->sendMessage([
            'chat_id' => $chat_id, 'text' => $send,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ]);
        break;

    case 'qq':
        # qq号或者微博UID
        $qb = $DB->get_row("SELECT username as qq, mobile FROM `bind`.`8eqq` WHERE `username` = '{$arg1}'");
        $wb = $DB->get_row("SELECT * FROM `bind`.`weibo` WHERE `uid` = '{$arg1}'");
        $send .= empty($qb) ? "Q绑为空\n" : arr2txt($qb);
        $send .= empty($wb) ? "微博为空\n" : arr2txt($wb);
        if ($qb['mobile']) $arg1 = $qb['mobile'];
        elseif ($wb['mobile']) $arg1 = $wb['mobile'];

        if (isset($arg1)) {
            $type = 'mobile';
            include('rk.php');
        }

        $telegram->sendMessage([
            'chat_id' => $chat_id, 'text' => $send, 'reply_markup' => $keyboard,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ]);
        break;

    case 'name':
        # 那就是姓名或者LOL
        $lol = $DB->get_row("SELECT * FROM `bind`.`lol` WHERE `name` = '{$arg1}'");
        $send .= empty($lol) ? "LOL为空\n" : arr2txt($lol);
        if ($lol['qq']) {
            $qb = $DB->get_row("SELECT username as qq, mobile FROM `bind`.`8eqq` WHERE `username` = '{$lol['qq']}'");
            $send .= empty($qb) ? "Q绑为空\n" : arr2txt($qb);
            if ($qb['mobile']) $mobile = $qb['mobile'];

            if (isset($mobile)) {
                $type = 'mobile';
                include('rk.php');
            }
        }
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id, 'text' => "正在查询中，稍安勿躁。。。",
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ]);

        include('rk.php');
        if (strlen("总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n" . $send) > 4096) {
            $telegram->sendChatAction($chat_id, 'upload_document');
            $savedir = __DIR__ . '/lemon_tmp/' . date('Y-m-d') . '/';
            $filename = $arg1 . '_' . $truenum . '_' . $user_id . '.txt';
            if (!file_exists($savedir)) {
                mkdir($savedir, 0744, true);
            }
            file_put_contents($savedir . $filename, $send);
            $file = curl_file_create($savedir . $filename, 'text/plain');
            $telegram->deleteMessage($chat_id, $response[0]['result']['message_id']);
            $telegram->sendChatAction($chat_id, 'upload_document');
            $telegram->sendDocument([
                'chat_id' => $chat_id, 'document' => $file, 'reply_markup' => $keyboard,
                'caption' => "总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n[想查询更多结果吗？](https://t.me/wmsgkc/315)",
                'parse_mode' => 'MarkdownV2',
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ]);
        } else{
            $content = [
                'chat_id' => $chat_id, 'message_id' => $response[0]['result']['message_id'],
                'text' => "总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n" . $send,
                'reply_markup' => $keyboard
            ];
            $telegram->editMessageText($content);
        }
        break;

    default:
        $lol = $DB->get_row("SELECT * FROM `bind`.`lol` WHERE `name` = '{$arg1}'");
        $send .= empty($lol) ? "LOL为空\n" : arr2txt($lol);
        if ($lol['qq']) {
            $qb = $DB->get_row("SELECT username as qq, mobile FROM `bind`.`8eqq` WHERE `username` = '{$lol['qq']}'");
            $send .= empty($qb) ? "Q绑为空\n" : arr2txt($qb);
            if ($qb['mobile']) $mobile = $qb['mobile'];

            if (isset($mobile)) {
                $type = 'mobile';
                include('rk.php');
            }
        }

        $telegram->sendMessage([
            'chat_id' => $chat_id, 'text' => $send, 'reply_markup' => $keyboard,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ]);
        break;
}
