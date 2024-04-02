<?php
define("VK_TOKEN_GROUP", json_decode(file_get_contents(realpath(dirname(__FILE__)).'\config.json'), TRUE)['vk_token_group']);
define("mailing_text", file_get_contents(realpath(dirname(__FILE__)).'\mailingtext.txt'));
function send($msg): void
{
    $request_params = array(
        'message' => $msg,
        'peer_id' => 39743129,
        'access_token' => VK_TOKEN_GROUP,
        'v' => '5.131',
        'random_id' => '0'
    );
    file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
}
send('Рассылка началась');
$ids = [];
$err_counter = 0;
for ($i = 0; $i < PHP_INT_MAX; $i++) {
    $request_params = array(
        'offset' => ($i * 200),
        'count' => 200,
        'access_token' => VK_TOKEN_GROUP,
        'v' => '5.131'
    );
    $cons = file_get_contents('https://api.vk.com/method/messages.getConversations?' . http_build_query($request_params));
    $cons = json_decode($cons, true);
    foreach ($cons['response']['items'] as $c) {
        $ids[] = $c['conversation']['peer']['id'];
    }
    if (count($cons['response']['items']) < 200) break;
}
send('Рассылка: ' . count($ids) . ' осталось.');
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
    usleep(50000);
}
send('Отправлено ' . (count($ids) - $err_counter) . '/' . count($ids));