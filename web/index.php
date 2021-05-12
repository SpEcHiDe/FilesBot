<?php
require_once __DIR__ . "/config.php";

use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\{Message, Exception as TelegramException};

$Bot = new Bot($GLOBALS["TG_BOT_TOKEN"], [
    "disable_ip_check" => true,
    "parse_mode" => "HTML",
    "disable_notification" => true,
    "disable_web_page_preview" => true,
    "debug" => (int) $GLOBALS["TG_DUMP_CHANNEL_ID"],
]);

$Bot->onCommand('start', function(Message $message, array $args) use ($Bot) {
    $chat = $message->chat;

    if(empty($args) || $args[0] === ""){
        $message->reply($GLOBALS["START_MESSAGE"], true);
    }
    else{
        if (strpos($args[0], "_") !== FALSE) {
            $msg_param_s = explode("_", $args[0]);
            $req_message_id = $msg_param_s[1];
            try {
                $chat->copyMessage([
                    "from_chat_id" => $GLOBALS["TG_DUMP_CHANNEL_ID"],
                    "message_id" => $req_message_id
                ], true);
            }
            catch (TelegramException $e) {
                /**
                 * sometimes, forwarding FAILS 😉
                 */
            }
        }
        else {
            $message->delete(null, true);
        }
    }
});

$Bot->onMessage(function (Message $message){
    if(!isset($message->text)){
        if ($GLOBALS["IS_PUBLIC"] !== FALSE) {
            $message->getLink();
        }
        else if (in_array($chat_id, $GLOBALS["TG_AUTH_USERS"])) {
            $message->getLink();
        }
        else {
            $message->delete();
        }
    }
});


Message::addMethod("getLink", function (){
    $status_message = $this->reply($GLOBALS["CHECKING_MESSAGE"]);
    $req_message = $this->forward($GLOBALS["TG_DUMP_CHANNEL_ID"]);
    $required_url = "https://t.me/" . $GLOBALS["TG_BOT_USERNAME"] . "?start=view_{$req_message->message_id}_tg";
    $status_message->editText($required_url);
});
