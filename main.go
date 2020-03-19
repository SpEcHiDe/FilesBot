package main

import (
	// "encoding/json"
	"log"
	"os"
	"strconv"
	"strings"
	// "time"

	"github.com/sirupsen/logrus"

	"github.com/PaulSonOfLars/gotgbot"
	"github.com/PaulSonOfLars/gotgbot/ext"
	"github.com/PaulSonOfLars/gotgbot/handlers"
	"github.com/PaulSonOfLars/gotgbot/handlers/Filters"
)

func main() {
	log.Println("Starting gotgbot...")
	token := os.Getenv("TG_BOT_TOKEN")
	log.Println("token:", token)
	updater, err := gotgbot.NewUpdater(token)
	if err != nil {
		log.Fatal(err)
	}
	// reply to /start messages
	updater.Dispatcher.AddHandler(handlers.NewCommand("start", start))

	// updater.Dispatcher.AddHandler(handlers.NewCommand("why", why))
	// reply to media messages
	updater.Dispatcher.AddHandler(handlers.NewMessage(Filters.All, incoming_tg_media))

	if os.Getenv("USE_WEBHOOKS") == "t" {
		webhook_port, err := strconv.Atoi(os.Getenv("PORT"))
		if err != nil {
			log.Fatal("PORT is not a valid integer: ", err)
		}
		// start getting updates
		webhook := gotgbot.Webhook{
			Serve:          "0.0.0.0",
			ServePort:      webhook_port,
			ServePath:      updater.Bot.Token,
			URL:            os.Getenv("WEBHOOK_URL"),
			MaxConnections: 30,
		}
		updater.StartWebhook(webhook)
		ok, err := updater.SetWebhook(updater.Bot.Token, webhook)
		if err != nil {
			logrus.WithError(err).Fatal("Failed to start bot due to: ", err)
		}
		if !ok {
			logrus.Fatal("Failed to set webhook")
		}
	} else {
		err := updater.StartPolling()
		if err != nil {
			logrus.WithError(err).Fatal("failed to start polling")
		}
	}

	// wait
	updater.Idle()
}


func start(b ext.Bot, u *gotgbot.Update) error {
	// log.Println(u.Message.Text)
	MessageId := get_message_id(u.Message.Text)
	if MessageId == -1 {
		const start_message = `Thank you for using me 😬

I am inspired from the <a href="https://t.me/SpEcHlDe/943">TamTam RoBot</a>

Subscribe ℹ️ @SpEcHlDe if you ❤️ using this bot!`
		u.EffectiveMessage.ReplyHTML(start_message)
	} else {
		tg_dump_channel_id, err := strconv.Atoi(os.Getenv("TG_DUMP_CHANNEL_ID"))
		if err != nil {
			log.Fatal("TG_DUMP_CHANNEL_ID is not a valid integer: ", err)
		}
		// log.Println(tg_dump_channel_id)
		b.ForwardMessage(u.Message.Chat.Id, tg_dump_channel_id, MessageId)
	}
	return nil
}

func why(b ext.Bot, u *gotgbot.Update) error {
	const why_message = `https://t.me/BotListCollections/229

Subscribe ℹ️ @SpEcHlDe if you ❤️ using this bot!`
	u.EffectiveMessage.ReplyHTML(why_message)
	return nil
}


func incoming_tg_media(b ext.Bot, u *gotgbot.Update) error {
	tg_dump_channel_id, err := strconv.Atoi(os.Getenv("TG_DUMP_CHANNEL_ID"))
	if err != nil {
		log.Fatal("TG_DUMP_CHANNEL_ID is not a valid integer: ", err)
	}
	fwded_mesg, err := u.EffectiveMessage.Forward(tg_dump_channel_id)
	if err != nil {
		reply_str := "<u>" +
			"errors occurred" +
			"</u>" +
			"\n\n" +
			"<code>" + "#ERROR" + "</code>"
		u.EffectiveMessage.ReplyHTML(reply_str)
	} else {
		// log.Info(fwded_mesg.MessageId)
		generated_url := "https://t.me" +
			"/GoFiIesBot" +
			"?start=" +
			"view" +
			"_" +
			strconv.Itoa(fwded_mesg.MessageId) +
			"_" +
			"tg"
		reply_str := "<a href='" +
			generated_url +
			"'>" +
			"click here to open file " +
			"</a>" +
			"\n\n" +
			"Subscribe ℹ️ @SpEcHlDe if you ❤️ using this bot!"
		u.EffectiveMessage.ReplyHTML(reply_str)
	}
	return nil
}


func get_message_id(incoming_text string) int {
	s := strings.Split(incoming_text, " ")
	p := -1
	if len(s) > 1 {
		// log.Println(s[1])
		t := strings.Split(s[1], "_")
		// log.Println(t)
		p, _ = strconv.Atoi(t[1])
		// log.Println(p)
	}
	return p
}
