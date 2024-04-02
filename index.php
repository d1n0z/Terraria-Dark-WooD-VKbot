<?php
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/php_errors.log');

define("VK_TOKEN_GROUP", json_decode(file_get_contents('config.json'), TRUE)['vk_token_group']);
define("VK_ACCESS_KEY", json_decode(file_get_contents('config.json'), TRUE)['vk_access_key']);
define("GROUP_ID", json_decode(file_get_contents('config.json'), TRUE)['group_id']);
define("QNA_LIST", json_decode(file_get_contents('config.json'), TRUE)['qna_list']);
define("UNKNOWN_MESSAGE", json_decode(file_get_contents('config.json'), TRUE)['unknown_msg']);

$data = json_decode(file_get_contents('php://input'));
if (!isset($data)) {
    http_response_code(422);
    echo "none";
    return "none";
}

function send_msg($msg, $id)
{
    $request_params = array(
        'message' => $msg,
        'peer_id' => $id,
        'access_token' => VK_TOKEN_GROUP,
        'v' => '5.131',
        'random_id' => '0'
    );

    $params = http_build_query($request_params);

    return json_decode(file_get_contents('https://api.vk.com/method/messages.send?' . $params));
}

switch ($data->type) {
    case 'confirmation':
        echo VK_ACCESS_KEY;
        break;
    case 'message_new':
        echo "ok";

        $text = $data->object->message->text;

        if (mb_substr($text, 0, 10) == "#рассылка&") {
            $mailing_text = mb_substr($text, 11);
            file_put_contents('mailingtext.txt', $mailing_text);
            exec('php sender.php > /dev/null 2>/dev/null &');
            return;
        } else $text = mb_strtolower($data->object->message->text);
        $i = 0;
        $keys = array_keys(QNA_LIST);
        foreach (QNA_LIST as $l) {
            foreach ($l as $item) {
                if ($text == mb_strtolower($item)) {
                    send_msg($keys[$i], $data->object->message->from_id);
                    return;
                }
            }
            $i++;
        }
        send_msg(UNKNOWN_MESSAGE, $data->object->message->from_id);
        return;
}
