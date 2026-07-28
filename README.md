# Fmail
PHP Mail sender script

![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)
![Version](https://img.shields.io/badge/version-v6.0.6-blue.svg)
![PHP](https://img.shields.io/badge/php-v5.5_--_v8-blueviolet.svg)

Description and examples of using PHP class for sending and receiving mail FMail

# Contents

- [General concepts](#General-concepts)
- [Class capabilities](#Class-capabilities)
- [Description](#Description)
- [Example of use](#пример-использования)

# General concepts
The FMail class is designed to send and receive email messages using PHP.
This class is not a full-fledged email program for working with email!
PHP version 4 or higher is required for operation.

# Class capabilities
Allows sending emails using the standard PHP mail() function, and also without using this function directly connecting to the specified mail server via a socket.

Allows receiving emails using the PHP IMAP library

Supports authorization on mail servers using the PLAIN and LOGIN methods.

Supported text encodings (charset) ISO-8859-1, UTF-8, WINDOWS-1251 and KOI8-R.

Supports sending messages in text and HTML format.

Sending a letter to multiple recipients.

Support for limiting the number of recipients of one letter, i.e. if several recipients are specified and the limit for the number of recipients is 1, then each recipient will have their own letter created and sent.

Support for sending letters to hidden recipients, the name and address of the recipient are not displayed in the "To" field.

Allows you to attach files of any format to the letter.

Creating a message from any HTML file with loading pictures, style sheets and scripts.

Supports text encoding from Windows-1251 to UTF-8 and back, without requiring the iconv module.

Checking email addresses for spelling.

Logging all actions.

# Description
**Basic functions for sending messages**

Including a class file
```php
require_once("FMail.php");
```
or using composer
```php
require_once("vendor/autoload.php");
```
Initializing a class
```php
$ml = new FYN\FMail();
```

Attention!!! The class has default values. Changing all default parameters can be done in the variable block "Script (class) settings variables". Or through special class functions that will be described below.

By default, the script uses the PHP mail() function. To connect via a socket, specify:
```php
$ml->setMailUse(false);
```
When connecting via a socket, 'localhost' is used as the server by default. To change it, use the function:
```php
$ml->setServer('your_mailserver.com'); //You can specify the IP address or domain name of the server
```
When connecting via a socket, port 25 is used by default. To change this, use the function:
```php
$ml->setPort(2525); //Specify the port number.
```
When connecting via a socket, the default is to wait 10 seconds for a socket response. To change this, use the function:
```php
$ml->setTimeout(30); //Specify the time in seconds
```
When connecting via a socket, by default, user authorization on the server is not required. To change this, use the function:
```php
$ml->setAuth('PLAIN'); //Specify the authorization method LOGIN or PLAIN
```
To authorize, you must specify the username and password. We use the functions:
```php
$ml->setLogin('login'); //Specify the user login
$ml->setPassword('password'); //Specify the user password
```
By default, there is a limit on the number of simultaneous recipients of the letter - 1 (one). 
To change this, use the function:
```php
$ml->setMaxRecipient(2); //The number of simultaneous recipients of the letter
```
By default, UTF-8 text encoding is used. To change it, use the function:
```php
$ml->setCharset('WIN'); //Specify the encoding code
                        //(WIN=>windows-1251, UTF=>utf-8, ISO=>iso-8859-1, KOI=>koi8-r)
```
Specifying recipients of the letter. (For more details, see the function description)
```php
$ml->setTo('test1@mail.com');
$ml->setTo('test2@mail.com', 'Alex Merphy');
$ml->setTo('test3@mail.com', $ml->getWin2Utf('Michael Marshal'));
$ml->setTo(array(array('mail'=>'test4@mail.com', 'username'=>'Alex Merphy')));
```
Clear the recipient list, since setTo accumulates recipients
```php
$ml->clearTo();
```
Specify BCC recipients if needed (see the function description for details).
```php
$ml->setBcc('bcc1@mail.com');
$ml->setBcc('bcc2@mail.com', 'Alex Merphy');
$ml->setBcc('bcc3@mail.com', $ml->getUtf2Win('Michael Marshal'));
$ml->setBcc(array(array('mail'=>'bcc4@mail.com', 'username'=>'Alex Merphy')));
```
Clear the BCC recipient list, since setBcc accumulates recipients
```php
$ml->clearBcc();
```
Specify the sender (see the function description for details).
```php
$ml->setFrom('this@server.com');
```
или
```php
$ml->setFrom('this@server.com', 'Sender Name');
```
Setting the message subject
```php
$ml->setSubject('Message Subject');
```
Setting the message body.

You can use four different functions to define the message body. When composing a message,
choose only one, because each function replaces the previously defined message body
instead of appending to it.
1. Создание простого текстового сообщения (text/plain)
```php
$ml->setMessage("Message text goes here!");
```
2. Создание текстового сообщения в формате HTML
```php
$ml->setHTMLMessage("Message text goes here");
```
или
```php
$ml->setHTMLMessage("<html><body>Message<br><b>body!!!</b></body></html>");
```
3. Создание простого текстового сообщения (text/plain) из HTML файла
```php
$ml->setMessageFromHTML("file.html"); //Specify the path to the HTML file
```
4. Создание сообщения из HTML файла (возвращает true или false)
```php
$ml->setHTMLfile("file.html"); //Specify the path to the HTML file
```
Attach files to the message (returns true or false)
```php
$ml->setFile("file1.txt"); //Specify the file path
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
```
Clear the attachment list, since setFile accumulates files
```php
$ml->clearFiles();
```
Send the message (returns true or false)
```php
$ml->send();
```

**Basic functions for receiving messages**

Including the class file
```php
require_once("FMail.php");
```
Initializing the class
```php
$ml = new /FYN/FMail();
```
By default, localhost is used as the server. To change it,
use:
```php
$ml->setServer('your_mailserver.com'); //You can specify an IP address or domain name
```
При подключении IMAP по умолчанию используется 143 порт, для use:
```php
$ml->setImapPort(993); //Specify the port number
```
IMAP is used by default.
POP3 can also be used, but it is not recommended.
Для use:
```php
$ml->setImapType('pop3');
```
Configure connection flags for imap_open
```php
$ml->setImapFlags('/ssl/debug/user=Administrator', true);
```
Specify the user login and password for authentication:
```php
$ml->setLogin('login'); //Specify the user login
$ml->setPassword('password'); //Specify the user password
```
Read the list of folders.
```php
$folders = $ml->getImapFolders();
```
Select the folder to read
```php
$ml->setImapFolder('INBOX/Работа');
```
Return the list of messages in the folder
All parameters are optional. Specify the folder name, the first message number
and the number of messages to return.
```php
$ml->read_folder('INBOX', 124, 10);
```
Возврат списка писем в почтовом ящике по заданным параметрам
```php
$mails = $ml->receive('UNSEEN'); // See the function description for available parameters
```
Read a message by its sequence number
```php
$ml->read_mail(124);
```
Read a message by UID
```php
$ml->read_mail_UID(24);
```

**Additional functions**

Validate an email address (returns true or false)
```php
$ml->getCheck('test@mail.com');
```
Convert text from Windows-1251 to UTF-8
```php
$text = $ml->getWin2Utf($text); //Pass the text to convert
```
Convert text from UTF-8 to Windows-1251
```php
$text = $ml->getUtf2Win($text); //Pass the text to convert
```
Enable debug mode (prints errors to the screen)
```php
$ml->setDebug(true);
```
View class logs. Pass the required log type.
0 - all logs, 1 - executed functions, 2 - transmitted/received data, 3 - errors
```php
$logs = $ml->getLogs(3); //With debug mode enabled, logs are printed to the screen
```
Decode strings such as =?utf-8?B?0KHQv9GA0LDQstC+0YfQvdC40Log0JHQmNCa?=
```php
$text = $ml->getSubjectDecode($text);
```

# Example of use

```php
require_once("FMail.php");
$ml = new FYN\FMail();
$ml->setMailUse(false);
$ml->setServer('your_mail_server.com');
$ml->setAuth('LOGIN');
$ml->setLogin('login');
$ml->setPassword('password');
$ml->setMaxRecipient(2);
$ml->setCharset('UTF');
$ml->setTo('test1@mail.com');
$ml->setTo('test2@mail.com', 'John Doe');
$ml->setBcc('bcc1@mail.com');
$ml->setFrom('this@server.com', $ml->getWin2Utf('Sender Name'));
$ml->setSubject('Message Subject');
$ml->setHTMLMessage("Message text goes here");
$ml->setFile("file1.txt");
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
if (!$ml->send()) {
    $ml->setDebug(true);
    $ml->getLogs();
}
else echo "Mail sending - OK";
```
