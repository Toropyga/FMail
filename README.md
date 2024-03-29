# Fmail
PHP Mail sender script

![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)
![Version](https://img.shields.io/badge/version-v6.0.4-blue.svg)
![PHP](https://img.shields.io/badge/php-v5.5_--_v8-blueviolet.svg)

Описание и примеры использования PHP класса отправки и получения почты FMail

# Содержание

- [Общие понятия](#общие-понятия)
- [Возможности класса FMail](#возможности-класса-FMail)
- [Описание работы](#описание-работы)
- [Пример использования](#пример-использования)

# Общие понятия
Класс FMail предназначен для отправки и получения электронных почтовых сообщений средствами PHP.
Данный класс не является полноценной почтовой программой для работы с электронной почтой!
Для работы необходимо наличие PHP версии 4 и выше.

# Возможности класса FMail
Позволяет отправлять письма используя стандартную функцию PHP mail(), а также не используя данную функции напрямую подключаться к указанному почтовому серверу через сокет.

Позволяет получать письма используя библиотеку PHP IMAP

Поддерживает авторизацию на почтовых серверах методом PLAIN и LOGIN.

Поддерживаются текстовые кодировки (charset) ISO-8859-1, UTF-8, WINDOWS-1251 и KOI8-R.

Поддерживаются отправка сообщений в текстовом и HTML формате.

Отправка письма нескольким получателям.

Поддержка ограничения на количество получателей одного письма, т.е. если задано несколько получателей и стоит ограничение на количество получателей - 1, то каждому получателю будет создано и отправлено своё письмо.

Поддержка отправки писем скрытым получателям, имя и адрес получателя не отображается в поле
"Кому" (To).

Позволяет прикрепить к письму файлы любого формата.

Создание сообщения из любого HTML файла с подгрузкой картинок, стилевых таблиц и скриптов.

Поддерживается кодирование текста из кодировки Windows-1251 в кодировку UTF-8 и обратно, не требуя наличия модуля iconv.

Проверка электронного адреса на правильность написания.

Протоколирование всех действий.

# Описание работы
**Основные функции отправки сообщений**

Подключение файла класса
```php
require_once("FMail.php");
```
или с использованием composer
```php
require_once("vendor/autoload.php");
```
Инициализация класса
```php
$ml = new FYN\FMail();
```

Внимание!!! В классе есть значения используемые по умолчанию. Изменение всех параметров по умолчанию можно произвести в блоке переменных "Переменные настройки скрипта (класса)". Или через специальные функции класса которые будут описаны ниже.

По умолчанию скрипт использует функцию PHP mail(). Для подключения через сокет указываем:
```php
$ml->setMailUse(false);
```
При подключении через сокет по умолчанию используется в качестве сервера localhost, для
изменения пользуемся функцией:
```php
$ml->setServer('your_mailserver.com'); //можно указать IP адрес или доменное имя сервера
```
При подключении через сокет по умолчанию используется 25 порт, для изменения пользуемся функцией:
```php
$ml->setPort(2525); //Указывает номер порта
```
При подключении через сокет по умолчанию используется 10 секундное ожидание ответа сокета, для
изменения пользуемся функцией:
```php
$ml->setTimeout(30); //Указываем время в секундах
```
При подключении через сокет по умолчанию не требуется авторизация пользователя на сервере, для
изменения пользуемся функцией:
```php
$ml->setAuth('PLAIN'); //Указываем метод авторизации LOGIN или PLAIN
```
Для авторизации необходимо указать логин и пароль пользователя. Пользуемся функциями:
```php
$ml->setLogin('login'); //Указываем логин пользователя
$ml->setPassword('password'); //Указываем пароль пользователя
```
По умолчанию стоит ограничение на количество одновременных получателей письма - 1 (один).
Для изменения пользуемся функцией:
```php
$ml->setMaxRecipient(2); //Количество одновременных получателей письма
```
По умолчанию используется кодировка текста UTF-8. Для изменения пользуемся функцией:
```php
$ml->setCharset('WIN'); //Указываем код кодировки
                        //(WIN=>windows-1251, UTF=>utf-8, ISO=>iso-8859-1, KOI=>koi8-r)
```
Указание получателей письма. (Подробнее - смотри описание функции)
```php
$ml->setTo('test1@mail.com');
$ml->setTo('test2@mail.com', 'Иван Иванов');
$ml->setTo('test3@mail.com', $ml->getWin2Utf('Вася Пупкин'));
$ml->setTo(array(array('mail'=>'test4@mail.com', 'username'=>'Иван Иванов')));
```
Очистить список получателей, так как функция setTo накопительная
```php
$ml->clearTo();
```
Указание, если надо, скрытых получателей письма. (Подробнее - смотри описание функции)
```php
$ml->setBcc('bcc1@mail.com');
$ml->setBcc('bcc2@mail.com', 'Иван Иванов');
$ml->setBcc('bcc3@mail.com', $ml->getUtf2Win('Вася Пупкин'));
$ml->setBcc(array(array('mail'=>'bcc4@mail.com', 'username'=>'Иван Иванов')));
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
