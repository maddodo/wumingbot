<?php
if (empty($arg1)) die('not here');
if (is_numeric($arg1) && strlen($arg1) == 11 && $arg1 > 13000000000) $type = 'mobile';
elseif (filter_var($arg1, FILTER_VALIDATE_EMAIL)) $type = 'mail';
elseif (preg_match_all("/^([\x80-\xff]{2,5})/", $arg1) > 0) $type = 'name';
else $type = 'other';
$send = "查询结果：\n";
switch ($type) {
    case 'mobile':
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`order` WHERE `mobile` = '{$arg1}' LIMIT 1000");
        if(empty($rs)){
            $telegram->sendMessage(['chat_id' => $chat_id, 'text' => '快递没有数据', 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
            die();
        }
        $send .= arr2txt($rs);

        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $send, 'reply_to_message_id' => $data['message']['message_id']]);
        break;

    case 'mail':
        # qq号或者微博UID
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`order` WHERE `mail` = '{$arg1}' LIMIT 1000");
        if(empty($rs)){
            $telegram->sendMessage(['chat_id' => $chat_id, 'text' => '快递没有数据', 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
            die();
        }
        $send .= arr2txt($rs);

        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $send, 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
        break;

    case 'name':
        # 那就是姓名或者LOL
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`order` WHERE `name` = '{$arg1}' LIMIT 1000");
        if(empty($rs)){
            $telegram->sendMessage(['chat_id' => $chat_id, 'text' => '快递没有数据', 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
            die();
        }
        $send .= arr2txt($rs);

        if (strlen($send) > 4096) {
            $telegram->sendChatAction($chat_id, 'upload_document');
            $savedir = __DIR__ . '/order_tmp/' . date('Y-m-d') . '/';
            $filename = $arg1 . '_' . $user_id . '.txt';
            if (!file_exists($savedir)) {
                mkdir($savedir, 0744, true);
            }
            file_put_contents($savedir . $filename, $send);
            $file = curl_file_create($savedir . $filename, 'text/plain');
            $telegram->sendDocument(['chat_id' => $chat_id, 'document' => $file, 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
        } else $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $send, 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
        break;

    default:
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => '没识别出来到底是什么类型', 'reply_markup' => $keyboard, 'reply_to_message_id' => $data['message']['message_id']]);
        break;
}
