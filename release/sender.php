<?php

ini_set('display_errors', 1);
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/php_errors.log');
var_dump($_GET);

define("VK_TOKEN_GROUP", json_decode(file_get_contents(realpath(dirname(__FILE__)).'/config.json'),
    TRUE)['vk_token_group']);
define("VK_GROUP_ID", json_decode(file_get_contents(realpath(dirname(__FILE__)).'/config.json'),
    TRUE)['group_id']);
define("mailing_text", $_GET['text']);
echo 'Рассылка началась<br>';
$ids = [];
$err_counter = 0;
for ($i = 0; $i < PHP_INT_MAX; $i++) {
    $request_params = array(
        'offset' => ($i * 200),
        'count' => 200,
        'access_token' => VK_TOKEN_GROUP,
        'group_id' => VK_GROUP_ID,
        'v' => '5.131'
    );
    $cons = file_get_contents('https://api.vk.com/method/messages.getConversations?' . http_build_query($request_params));
    $cons = json_decode($cons, true);
    foreach ($cons['response']['items'] as $c) {
        $ids[] = $c['conversation']['peer']['id'];
    }
    if (count($cons['response']['items']) < 200) break;
}
$ids = array_unique($ids);
echo 'Рассылка: ' . count($ids) . ' осталось.<br>';
for ($is = 0; $is < count($ids); $is++) {
    $request_params = array(
        'message' => mailing_text,
        'peer_id' => $ids[$is],
        'access_token' => VK_TOKEN_GROUP,
        'v' => '5.131',
        'random_id' => '0'
    );
    $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params)));
    if (property_exists($answer, 'error')) $err_counter++;
    else echo $is . '<br>';
    usleep(50000);
}
echo 'Отправлено ' . (count($ids) - $err_counter) . '/' . count($ids);
