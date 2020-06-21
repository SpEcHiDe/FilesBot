<?php
$GLOBALS["TG_BOT_TOKEN"] = getenv("TG_BOT_TOKEN");
$GLOBALS["TG_DUMP_CHANNEL_ID"] = getenv("TG_DUMP_CHANNEL_ID");
$GLOBALS["START_MESSAGE"] = <<<EOM
Thank you for using me 😬

you can forward me any media message, and I might help you to create a PUBlic link.

Subscribe ℹ️ @SpEcHlDe if you ❤️ using this bot!
EOM;
$GLOBALS["CHECKING_MESSAGE"] = "🤔";
require_once __DIR__ . "/../vendor/autoload.php";