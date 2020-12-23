<?php
require_once __DIR__ . "/config.php";


use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\{Message, Exception as TelegramException};

// Set the bot TOKEN
$bot_id = $GLOBALS["TG_BOT_TOKEN"];
#$bot = new PHPBot($bot_id);
$Bot = new Bot($bot_id, [
    "disable_ip_check" => true,
    "parse_mode" => "HTML",
    "disable_notification" => true,
    "disable_web_page_preview" => true,
]);

$Bot->onMessage(function (Message $message){
    if(!isset($message->text)){
        if ($GLOBALS["IS_PUBLIC"] !== FALSE) {
            get_link($bot, $chat_id, $message_id);
        }
        else if (in_array($chat_id, $GLOBALS["TG_AUTH_USERS"])) {
            get_link($bot, $chat_id, $message_id);
        }
        else {
            $message->delete();
        }
    }
});

$Bot->onCommand('start', function(Message $message, array $args) use ($Bot) {
    $chat = $message->chat;

    if(empty($args)){
        $message->reply($GLOBALS["START_MESSAGE"]);
    }
    else{
        $message_params = $args;
        if (strpos($message_params[1], "_") !== FALSE) {
            $msg_param_s = explode("_", $message_params[1]);
            $req_message_id = $msg_param_s[1];
            try {
                $Bot->copyMessage([
                    "chat_id" => $chat->id,
                    "from_chat_id" => $GLOBALS["TG_DUMP_CHANNEL_ID"],
                    "message_id" => $req_message_id
                ]);
            }
            catch (TelegramException $e) {
                /**
                 * sometimes, forwarding FAILS 😉
                 */
            }
        }
        else {
            $message->delete();
        }
    }
});


Message::addMethod("getLink", function (){
    $status_message = $this->reply($GLOBALS["CHECKING_MESSAGE"]);
    $req_message = $message->forward($GLOBALS["TG_DUMP_CHANNEL_ID"]);
    $required_url = "https://t.me/" . $GLOBALS["TG_BOT_USERNAME"] . "?start=" . "view" . "_" . $req_message->message_id . "_" . "tg";
    $status_message->editText($required_url);
});

function get_link($bot, $chat_id, $message_id) {

    $req_message = $bot->api->forwardMessage(array(
        "chat_id" => $GLOBALS["TG_DUMP_CHANNEL_ID"],
        "from_chat_id" => $chat_id,
        "disable_notification" => True,
        "message_id" => $message_id
    ));

    $required_url = "https://t.me/" . $GLOBALS["TG_BOT_USERNAME"] . "?start=" . "view" . "_" . $req_message->message_id . "_" . "tg";

    $bot->api->editMessageText(array(
        "chat_id" => $chat_id,
        "message_id" => $status_message->message_id,
        "text" => $required_url,
        "disable_web_page_preview" => True
    ));
}
