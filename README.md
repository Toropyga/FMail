# Fmail
PHP Mail sender script

![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)
![Version](https://img.shields.io/badge/version-v6.0.6-blue.svg)
![PHP](https://img.shields.io/badge/php-v5.5_--_v8-blueviolet.svg)

Description and examples of using PHP class for sending and receiving mail FMail

# Содержание

- [General concepts](#General-concepts)
- [Class capabilities](#Class-capabilities)
- [Description](#Description)
- [Пример использования](#пример-использования)

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
Очистить список получателей, так как функция setTo накопительная
```php
$ml->clearTo();
```
Указание, если надо, скрытых получателей письма. (Подробнее - смотри описание функции)
```php
$ml->setBcc('bcc1@mail.com');
$ml->setBcc('bcc2@mail.com', 'Alex Merphy');
$ml->setBcc('bcc3@mail.com', $ml->getUtf2Win('Michael Marshal'));
$ml->setBcc(array(array('mail'=>'bcc4@mail.com', 'username'=>'Alex Merphy')));
```
Очистить скрытых список получателей, так как функция setBcc накопительная
```php
$ml->clearBcc();
```
Указание отправителя письма. (Подробнее - смотри описание функции)
```php
$ml->setFrom('this@server.com');
```
или
```php
$ml->setFrom('this@server.com', 'Имя Отправителя');
```
Указание темы сообщения
```php
$ml->setSubject('Тема сообщения');
```
Задание текста письма.

Для задания текста письма можно использовать 4 различных функции. При создании письма
необходимо выбрать одну, так как каждая функция не добавляет к ранее заданному тексту
письма новый текст, а заменяет старый новым!!!
1. Создание простого текстового сообщения (text/plain)
```php
$ml->setMessage("Здесь текст письма!");
```
2. Создание текстового сообщения в формате HTML
```php
$ml->setHTMLMessage("Здесь текст письма");
```
или
```php
$ml->setHTMLMessage("<html><body>Здесь<br>текст<b>письма!!!</b></body></html>");
```
3. Создание простого текстового сообщения (text/plain) из HTML файла
```php
$ml->setMessageFromHTML("file.html"); //Указываем путь к файлу HTML
```
4. Создание сообщения из HTML файла (возвращает true или false)
```php
$ml->setHTMLfile("file.html"); //Указываем путь к файлу HTML
```
Добавление файлов к письму (возвращает true или false)
```php
$ml->setFile("file1.txt"); //Указываем путь к файлу
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
```
Очистить список файлов, так как функция setFile накопительная
```php
$ml->clearFiles();
```
Отправка письма (возвращает true или false)
```php
$ml->send();
```

**Основные функции получения сообщений**

Подключение файла класса
```php
require_once("FMail.php");
```
Инициализация класса
```php
$ml = new /FYN/FMail();
```
При подключении по умолчанию используется в качестве сервера localhost, для
изменения пользуемся функцией:
```php
$ml->setServer('your_mailserver.com'); //можно указать IP адрес или доменное имя сервера
```
При подключении IMAP по умолчанию используется 143 порт, для изменения пользуемся функцией:
```php
$ml->setImapPort(993); //Указывает номер порта
```
По умолчанию используется подключение по протоколу IMAP,
можно использовать, но не рекомендуется, протокол POP3.
Для изменения пользуемся функцией:
```php
$ml->setImapType('pop3');
```
Настройка флагов подключения для функции imap_open
```php
$ml->setImapFlags('/ssl/debug/user=Administrator', true);
```
Для авторизации необходимо указать логин и пароль пользователя. Пользуемся функциями:
```php
$ml->setLogin('login'); //Указываем логин пользователя
$ml->setPassword('password'); //Указываем пароль пользователя
```
Считываем список папок.
```php
$folders = $ml->getImapFolders();
```
Задание папки для чтения её содержимого
```php
$ml->setImapFolder('INBOX/Работа');
```
Возвращаем список писем в папке
Все параметры необязательные. Указаваем имя папки, номер сообщения с которого начинаем чтение папки
и количество сообщений для возврата (см. описание функции)
```php
$ml->read_folder('INBOX', 124, 10);
```
Возврат списка писем в почтовом ящике по заданным параметрам
```php
$mails = $ml->receive('UNSEEN'); // список параметров см. в описании функции
```
Чтение письма по номеру письма
```php
$ml->read_mail(124);
```
Чтение письма по UID
```php
$ml->read_mail_UID(24);
```

**Дополнительные функции**

Проверка правильности написания адреса электронной почты (возвращает true или false)
```php
$ml->getCheck('test@mail.com');
```
Конвертация текста из кодировки Windows-1251 в кодировку UTF-8
```php
$text = $ml->getWin2Utf($text); //передаём текст который надо конвертировать
```
Конвертация текста из кодировки UTF-8 в кодировку Windows-1251
```php
$text = $ml->getUtf2Win($text); //передаём текст который надо конвертировать
```
Включение отладочных функций скрипта (при ошибке выводит сообщение на экран)
```php
$ml->setDebug(true);
```
Просмотр логов класса. Передаём параметры необходимых логов.
0 - все логи, 1 - отработавшие функции, 2 - переданные и полученные данные, 3 - ошибки
```php
$logs = $ml->getLogs(3); //При включенных отладочных функциях выводит логи на экран
```
Декодирование строк вида =?utf-8?B?0KHQv9GA0LDQstC+0YfQvdC40Log0JHQmNCa?=
```php
$text = $ml->getSubjectDecode($text);
```

# Пример использования

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
$ml->setTo('test2@mail.com', 'Иван Иванов');
$ml->setBcc('bcc1@mail.com');
$ml->setFrom('this@server.com', $ml->getWin2Utf('Имя Отправителя'));
$ml->setSubject('Тема сообщения');
$ml->setHTMLMessage("Здесь текст письма");
$ml->setFile("file1.txt");
$ml->setFile("file2.gif");
$ml->setFile("file3.zip");
if (!$ml->send()) {
    $ml->setDebug(true);
    $ml->getLogs();
}
else echo "Mail sending - OK";
```
