# Fmail
PHP Mail sender script

![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)
![Version](https://img.shields.io/badge/version-v7.0.0-blue.svg)
![PHP](https://img.shields.io/badge/php-v5.5_--_v8-blueviolet.svg)

Description and usage examples for the FMail PHP class for sending and receiving mail.

# Table of Contents

- [Overview](#overview)
- [Features of the FMail class](#features-of-the-fmail-class)
- [How it works](#how-it-works)
- [Usage example](#usage-example)

# Overview
The FMail class is designed for sending and receiving email messages using PHP.
This class is not a full-featured email client!
It requires PHP version 4 or higher.

# Features of the FMail class
Allows sending mail using the standard PHP `mail()` function, or, without using that function directly, connecting to a specified mail server via a socket.

Allows receiving mail using the PHP IMAP library.

Supports authorization on mail servers using the PLAIN and LOGIN methods.

Supports the ISO-8859-1, UTF-8, WINDOWS-1251, and KOI8-R text encodings (charsets).

Supports sending messages in both plain text and HTML format.

Sending a message to multiple recipients.

Supports limiting the number of recipients per message — i.e., if several recipients are specified and the recipient limit is set to 1, a separate message will be created and sent to each recipient individually.

Supports sending messages to hidden recipients, whose name and address are not shown in the "To" field.

Allows attaching files of any format to a message.

Creating a message from any HTML file, including loading images, stylesheets, and scripts.

Supports converting text from Windows-1251 encoding to UTF-8 and back, without requiring the iconv module.

Email address format validation.

Logging of all actions.

# How it works
**Core message-sending functions**

Include the class file
```php
require_once("FMail.php");
```
or using Composer
```php
require_once("vendor/autoload.php");
```
Initialize the class
```php
$ml = new Toropyga\FMail();
```

Warning!!! The class has default values. All default parameters can be changed in the "Script (class) settings variables" block, or via the special class functions described below.

By default, the script uses the PHP `mail()` function. To connect via a socket instead, specify:
```php
$ml->setMailUse(false);
```
When connecting via a socket, the default server is `localhost`. To change it, use:
```php
$ml->setServer('your_mailserver.com'); // you can specify an IP address or a domain name
```
When connecting via a socket, the default port is 25. To change it, use:
```php
$ml->setPort(2525); // specifies the port number
```
When connecting via a socket, the default socket response timeout is 10 seconds. To change it, use:
```php
$ml->setTimeout(30); // specify the time in seconds
```
When connecting via a socket, user authorization on the server is not required by default. To change this, use:
```php
$ml->setAuth('PLAIN'); // specify the authorization method: LOGIN or PLAIN
```
For authorization, you must specify the user's login and password. Use the functions:
```php
$ml->setLogin('login'); // specify the user's login
$ml->setPassword('password'); // specify the user's password
```
By default, the limit on the number of simultaneous message recipients is 1 (one).
To change it, use:
```php
$ml->setMaxRecipient(2); // number of simultaneous message recipients
```
By default, UTF-8 text encoding is used. To change it, use:
```php
$ml->setCharset('WIN'); // specify the encoding code
                        // (WIN=>windows-1251, UTF=>utf-8, ISO=>iso-8859-1, KOI=>koi8-r)
```
Specifying message recipients. (For more details, see the function description)
```php
$ml->setTo('test1@mail.com');
$ml->setTo('test2@mail.com', 'Ivan Ivanov');
$ml->setTo('test3@mail.com', $ml->getWin2Utf('Vasya Pupkin'));
$ml->setTo(array(array('mail'=>'test4@mail.com', 'username'=>'Ivan Ivanov')));
```
Clear the list of recipients, since the `setTo` function is cumulative
```php
$ml->clearTo();
```
Specifying hidden recipients (BCC) of the message, if needed. (For more details, see the function description)
```php
$ml->setBcc('bcc1@mail.com');
$ml->setBcc('bcc2@mail.com', 'Ivan Ivanov');
$ml->setBcc('bcc3@mail.com', $ml->getUtf2Win('Vasya Pupkin'));
$ml->setBcc(array(array('mail'=>'bcc4@mail.com', 'username'=>'Ivan Ivanov')));
```
Clear the list of hidden recipients, since the `setBcc` function is cumulative
```php
$ml->clearBcc();
```
Specifying the message sender. (For more details, see the function description)
```php
$ml->setFrom('this@server.com');
```
or
```php
$ml->setFrom('this@server.com', 'Sender Name');
```
Specifying the message subject
```php
$ml->setSubject('Message subject');
```
Setting the message body.

There are 4 different functions available for setting the message body. When creating a message,
choose only one, since each function does not append new text to the previously set message
body — it replaces the old text with the new one!!!
1. Creating a plain text message (text/plain)
```php
$ml->setMessage("Message text here!");
```
2. Creating a text message in HTML format
```php
$ml->setHTMLMessage("Message text here");
```
or
```php
$ml->setHTMLMessage("<html><body>Here<br>is the<b>message text!!!</b></body></html>");
```
3. Creating a plain text message (text/plain) from an HTML file
```php
$ml->setMessageFromHTML("file.html"); // specify the path to the HTML file
```
4. Creating a message from an HTML file (returns true or false)
```php
$ml->setHTMLfile("file.html"); // specify the path to the HTML file
```
Adding files to the message (returns true or false)
```php
$ml->setFile("file1.txt"); // specify the path to the file
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
```
Clear the list of files, since the `setFile` function is cumulative
```php
$ml->clearFiles();
```
Sending the message (returns true or false)
```php
$ml->send();
```

**Core message-receiving functions**

Include the class file
```php
require_once("FMail.php");
```
Initialize the class
```php
$ml = new Toropyga/FMail();
```
By default, the connection uses `localhost` as the server. To change it, use:
```php
$ml->setServer('your_mailserver.com'); // you can specify an IP address or a domain name
```
When connecting via IMAP, the default port is 143. To change it, use:
```php
$ml->setImapPort(993); // specifies the port number
```
The IMAP protocol is used by default for connections. POP3 can be used, but it is
not recommended. To change this, use:
```php
$ml->setImapType('pop3');
```
Setting connection flags for the `imap_open` function
```php
$ml->setImapFlags('/ssl/debug/user=Administrator', true);
```
For authorization, you must specify the user's login and password. Use the functions:
```php
$ml->setLogin('login'); // specify the user's login
$ml->setPassword('password'); // specify the user's password
```
Read the list of folders.
```php
$folders = $ml->getImapFolders();
```
Setting the folder to read its contents
```php
$ml->setImapFolder('INBOX/Work');
```
Returns the list of messages in a folder.
All parameters are optional. Specify the folder name, the message number to start reading from,
and the number of messages to return (see the function description)
```php
$ml->read_folder('INBOX', 124, 10);
```
Returns the list of messages in the mailbox based on the specified parameters
```php
$mails = $ml->receive('UNSEEN'); // see the function description for the list of parameters
```
Reading a message by message number
```php
$ml->read_mail(124);
```
Reading a message by UID
```php
$ml->read_mail_UID(24);
```

**Additional functions**

Validating the format of an email address (returns true or false)
```php
$ml->getCheck('test@mail.com');
```
Converting text from Windows-1251 encoding to UTF-8
```php
$text = $ml->getWin2Utf($text); // pass the text to be converted
```
Converting text from UTF-8 encoding to Windows-1251
```php
$text = $ml->getUtf2Win($text); // pass the text to be converted
```
Enabling the script's debug functions (displays an error message on screen if one occurs)
```php
$ml->setDebug(true);
```
Viewing the class logs. Pass the parameters for the logs you need.
0 - all logs, 1 - executed functions, 2 - data sent and received, 3 - errors
```php
$logs = $ml->getLogs(3); // when debug functions are enabled, displays the logs on screen
```
Decoding strings of the form =?utf-8?B?0KHQv9GA0LDQstC+0YfQvdC40Log0JHQmNCa?=
```php
$text = $ml->getSubjectDecode($text);
```

# Usage example

```php
require_once("FMail.php");
$ml = new Toropyga\FMail();
$ml->setMailUse(false);
$ml->setServer('your_mail_server.com');
$ml->setAuth('LOGIN');
$ml->setLogin('login');
$ml->setPassword('password');
$ml->setMaxRecipient(2);
$ml->setCharset('UTF');
$ml->setTo('test1@mail.com');
$ml->setTo('test2@mail.com', 'Ivan Ivanov');
$ml->setBcc('bcc1@mail.com');
$ml->setFrom('this@server.com', $ml->getWin2Utf('Sender Name'));
$ml->setSubject('Message subject');
$ml->setHTMLMessage("Message text here");
$ml->setFile("file1.txt");
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
if (!$ml->send()) {
    $ml->setDebug(true);
    $ml->getLogs();
}
else echo "Mail sending - OK";
```
