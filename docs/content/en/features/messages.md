---
title: 'Messages'
menuTitle: 'Messages'
description: ''
category: 'Features'
fullscreen: false 
position: 30
---

Messages can be sent to a Telegram chat using a `TelegraphChat` model

```php
use DefStudio\Telegraph\Models\TelegraphChat;

$chat = TelegraphChat::find(44);

// this will use the default parsing method set in config/telegraph.php
$chat->message('hello')->send();

$chat->html("<b>hello</b>\n\nI'm a bot!")->send();

$chat->markdown('*hello*')->send();
```

## Options

Telegraph allows sending complex messages by setting some options:

### edit

Updates an existing message instead of sending a new one


```php
$chat->edit(123456)->message("new text")->send();
```

### reply

The message can be sent as a reply by setting the original message ID

```php
$chat->message("ok!")->reply(123456)->send();
```

### forceReply

Forces the user to reply to the message. For more information see [the official api documentation](https://core.telegram.org/bots/api#forcereply)

```php
$chat->message("ok!")->forceReply(placeholder: 'Enter your reply...')->send();
```

### protected

Protects message contents from forwarding and saving

```php
$chat->message("please don't share this")->protected()->send();
```

### silent

Sends the message [silently](https://telegram.org/blog/channels-2-0#silent-messages). Users will receive a notification with no sound.

```php
$chat->message("late night message")->silent()->send();
```

### withoutPreview

Disables link previews for links in this message

```php
$chat->message("http://my-blog.dev")->withoutPreview()->send();
```

## Delete a message

The [`->deleteMessage()`](features/telegram-api-calls/delete-message) Telegraph method allows to remove a message from a chat/group/channel

<alert type="alert">A message can be deleted if it was sent less than 48h ago and if it **was sent** by the bot or if the bot **has permission** to delete other users' messages</alert>
