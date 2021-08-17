<?php
/**
 * INFO
 * +-----------------------------------------------------------------------+
 * | PHP Version 5 - 7                                                     |
 * +-----------------------------------------------------------------------+
 * | PHP Mail sender script                                                |
 * | (русская версия)                                                      |
 * | Copyright (c) 2003-2019 Yuri Frantsevich                              |
 * +-----------------------------------------------------------------------+
 * |                                                                       |
 * | This library is free software; you can redistribute it and/or         |
 * | modify it under the terms of the GNU Lesser General Public            |
 * | License as published by the Free Software Foundation; either          |
 * | version 3 of the License, or (at your option) any later version.      |
 * | See http://www.gnu.org/copyleft/lesser.html                           |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 * | Author: Yuri Frantsevich <fyn@tut.by>                                 |
 * +-----------------------------------------------------------------------+
 *
 * $Id: FMail.php, v 6.0.2 2021/08/17 12:54:18
 */

/**
 * PHP Mail sender script
 *
 * @name    /FYN/FMail
 * @access  public
 * @version 6.0.2 (ru)
 * @author  Yuri Frantsevich <frantsevich@gmail.com>
 * @charset UTF-8
 *
 * Date 09/08/2003
 * @copyright 2003-2021
 */

namespace FYN;

use FYN\Base;

class FMail {

//      +---------------------------------------------+
//      |    Переменные настройки скрипта (класса)    |
//      +---------------------------------------------+

    /**
     * Использовать или нет функцию mail
     * Если не используется, то подключаемся через сокет
     * @var bool
     */
    private $usemail = true;

    /**
     * Включить отладочные функции скрипта
     * @var bool
     */
    private $debug = false;

    /**
     * Адрес сервера при подключении через сокет
     * @var string
     */
    private $server = 'localhost';

    /**
     * Порт SMTP сервера при работе через сокет (отправка сообщений)
     * @var int
     */
    private $port = 25;

    /**
     * Порт IMAP или POP3 сервера при работе через сокет (получение сообщений)
     * @var int
     */
    private $imap_port = 143;

    /**
     * Тип используемого подключения для чтения почты (pop3, imap)
     * @var string
     */
    private $imap_type = 'imap';

    /**
     * Использовать или нет SSL для шифрования сессии
     * @var bool
     */
    private $imap_ssl = false;

    /**
     * Дополнительные флаги подключения (см. документацию к функции imap_open)
     * @var string
     */
    private $imap_flags = '';

    /**
     * Папка из которой читаем поступившую почту
     * @var string
     */
    private $imap_folder = 'INBOX';

    /**
     * Время ожидания подключения к сокету (сек.)
     * @var int
     */
    private $timeout = 10;

    /**
     * Домен отправителя
     * @var string
     */
    private $mydomain = '';

    /**
     * Логин пользователя при авторизации
     * @var string
     */
    private $login = '';

    /**
     * Пароль пользователя при авторизации
     * @var string
     */
    private $password = '';

    /**
     * Адрес отправителя по умолчанию
     * @var string
     */
    private $FROM = '';

    /**
     * Адрес получателя по умолчанию
     * @var string
     */
    private $TO = '';

    /**
     * Текст письма по умолчанию
     * @var string
     */
    private $MESSAGE = '';

    /**
     * Тема письма по умолчанию
     * @var string
     */
    private $SUBJECT = '';

    /**
     * Максимальное количество получателей в одном письме
     * @var int
     */
    private $maxrecipients = 1;

    /**
     * Имя файла в который сохраняется лог
     * @var string
     */
    private $log_file = 'email.log';

//      +------------------------------------------+
//      |            Переменные письма             |
//      +------------------------------------------+

    /**
     * Кодирование письма
     * @var array
     */
    private $Encoding       = array (
        '8bit',
        'base64',
        '7bit',
        'quoted-printable'
    );

    /**
     * Формат письма
     * @var array
     */
    private $ContentType    = array(
        'text/plain',
        'multipart/mixed',
        'application/octet-stream',
        'text/html',
        'multipart/alternative',
        'message/rfc822'
    );

    /**
     * Кодировка письма
     * @var array
     */
    private $Charset        = array(
        'iso-8859-1',
        'utf-8',
        'windows-1251',
        'koi8-r'
    );

    /**
     * Версия скрипта
     * @var string
     */
    private $MimeVersion    = '4.0.2';

    /**
     * Приоретет отправки
     * @var string
     */
    private $XPriority      = '3 (Normal)';

    /**
     * Имя скрипта
     * @var string
     */
    private $XMailer        = 'FMail v4.0.2 (ru)';

    /**
     * Типы авторизации на серверах SMTP
     * @var array
     */
    private $Authentications = array('LOGIN', 'PLAIN');

//      +-----------------------------------------+
//      |           Служебные переменные          |
//      +-----------------------------------------+

    /**
     * Кодировка UTF8
     * @var array
     */
    private $chars = array();

    /**
     * Данные сокета при подключении на прямую без функции mail.
     * @var resource
     */
    private $socket = '';

    /**
     * Данные подключения на чтение писем (IMAP).
     * @var resource
     */
    private $imap = '';

    /**
     * Адрес получателя
     * @var array
     */
    private $to = array();

    /**
     * Адреса получателей текущего письма
     * @var array
     */
    private $to_now = array();

    /**
     * Адреса получателей без имени пользователя
     * @var array
     */
    private $to_sock = array();

    /**
     * Адрес отправителя
     * @var string
     */
    private $from = '';

    /**
     * Адрес скрытого получателя
     * @var array
     */
    private $bcc = array();

    /**
     * Адреса скрытых получателей без имени
     * @var array
     */
    private $bcc_sock = array();

    /**
     * Текст письма
     * @var string
     */
    private $message = '';

    /**
     * Тема письма
     * @var string
     */
    private $subject = '';

    /**
     * Метод авторизации на сервере при подключении через сокет
     * Поддерживаются методы перечисленные в массиве $Authentications
     * Если любой отличный от указанных, то подключается без авторизации
     * @var string
     */
    private $auth = '';

    /**
     * Кодировка письма
     * @var string
     */
    private $encoding = 'base64';

    /**
     * @var string
     */
    private $imap_base64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+,';

    /**
     * Кодировка текста
     * @var string
     */
    private $charset = 'utf-8';

    /**
     * Формат письма
     * @var string
     */
    private $ctype = 'text/plain';

    /**
     * Массив прикрепляемых файлов
     * @var array
     */
    private $files = array();

    /**
     * Перевод строки
     * @var string
     */
    //var $rn = "\015\012"; // v 2.4.0
    //var $rn = "\r\n"; // v 0.0.1
    private $rn = PHP_EOL; // v 4.0.0

    /**
     * Лог событий
     * @var array
     */
    private $log = array();

    /**
     * Инициализация класса
     * @return boolean
     */
    public function __construct() {

        $this->encoding = $this->Encoding[1];
        $this->charset = $this->Charset[1];
        $this->ctype = $this->ContentType[0];

        if (defined("MAIL_USE_MAIL")) $this->setMailUse(MAIL_USE_MAIL);
        if (defined("MAIL_SERVER"))$this->setServer(MAIL_SERVER);
        if (defined("MAIL_PORT")) $this->setPort(MAIL_PORT);
        if (defined("MAIL_AUTH")) $this->setAuth(MAIL_AUTH);
        if (defined("MAIL_LOGIN")) $this->setLogin(MAIL_LOGIN);
        if (defined("MAIL_PASSWD")) $this->setPassword(MAIL_PASSWD);
        if (defined("MAIL_DOMAIN")) $this->setDomain(MAIL_DOMAIN);
        if (defined("MAIL_FROM")) $this->setFrom(MAIL_FROM);
        if (defined("MAIL_ADMIN_EMAIL")) $this->setTo(MAIL_ADMIN_EMAIL);
        elseif (defined("MAIL_TO")) $this->setTo(MAIL_TO);
        if (defined("MAIL_MESSAGE")) $this->setMessage(MAIL_MESSAGE);
        if (defined("MAIL_SUBJ")) $this->setSubject(MAIL_SUBJ);
        if (defined("MAIL_TIMEOUT")) $this->setTimeout(MAIL_TIMEOUT);
        if (defined("MAIL_MAXREP")) $this->setMaxRecipient(MAIL_MAXREP);
        if (defined("MAIL_DEBUG")) $this->setDebug(MAIL_DEBUG);
        if (defined("MAIL_LOG_NAME")) $this->log_file = MAIL_LOG_NAME;

        $this->log['data'] = array();
        $this->log['error'] = array();
        $this->log['info'] = array();
        $this->log['action'] = array();

        $this->setLog('Init - OK');
        return true;
    }

    /**
     * Запись в лог
     * Деструктор класса.
     */
    public function __destruct() {

    }

    /**
     * Отправка письма
     * @return bool
     */
    public function send () {
        if (!defined("SEND_STOP")) {
            $this->setLog("send");
            if (!sizeof($this->to) && !$this->TO && !sizeof($this->bcc)) {
                $this->getError('send', __LINE__, "No recipient!");
                return false;
            }
            elseif (!sizeof($this->to) && $this->TO) {
                if ($this->getCheck($this->TO)) $this->to[] = $this->TO;
                else {
                    $this->getError('send', __LINE__, "No recipient!");
                    return false;
                }
            }
            if (!$this->from && !$this->FROM) {
                $this->getError('send', __LINE__, "No sender address!");
                return false;
            }
            elseif (!$this->from && $this->FROM) {
                if ($this->getCheck($this->FROM)) $this->from = $this->FROM;
                else {
                    $this->getError('send', __LINE__, "No sender address!");
                    return false;
                }
            }
            $match = array();
            if (preg_match("/<(.+)>/", $this->from, $match)) {
                $from = $this->from;
                $this->from = $match[1];
            }
            else $from = $this->from;
            if (!$this->subject) $this->subject = $this->SUBJECT;
            if (!$this->message) $this->message = $this->MESSAGE;
            //подготовка письма
            $un = strtoupper(uniqid(time()));
            $head = "From: " . $from . "$this->rn";
            $addbcc = 0;
            if (sizeof($this->bcc)) {
                if ((sizeof($this->bcc) + sizeof($this->to)) > $this->maxrecipients && !$this->usemail) $addbcc = 1;
                else {
                    $bcc = join(';', $this->bcc);
                    $head .= "Bcc: " . $bcc . "$this->rn";
                }
            }
            $head .= "X-Priority: " . $this->XPriority . "$this->rn";
            $head .= "X-Mailer: " . $this->XMailer . "$this->rn";
            $head .= "Mime-Version: " . $this->MimeVersion . "$this->rn";
            $head .= "Mime-Version: 1.0".$this->rn;
            $head .= "Reply-To: " . $this->from . "$this->rn";

            if (!$this->usemail) $head .= "Subject: " . $this->subject . "$this->rn";
            if ($this->files) {
                $head .= "Content-Type: " . $this->ContentType[1] . ";\n\tboundary=\"----=_NextPart_000_0001_FYN$un\"$this->rn";
                $head .= "Importance: Normal$this->rn";
                $head .= "This is a multi-part message in MIME format.$this->rn";
                $message = "------=_NextPart_000_0001_FYN$un" . "$this->rn";
                $message .= "Content-Type: " . $this->ctype . "; charset=" . $this->charset . "$this->rn";
                $message .= "Content-Transfer-Encoding: " . $this->encoding . "$this->rn";
                $message .= "$this->rn";
                $message .= $this->message . "$this->rn";
                foreach ($this->files as $num => $file) {
                    $message .= "$this->rn";
                    $message .= "------=_NextPart_000_0001_FYN$un" . "$this->rn";
                    $message .= "Content-Type: " . $file['ctype'] . "; name=\"" . $file['name'] . "\"$this->rn";
                    $message .= "Content-Transfer-Encoding: " . $this->Encoding[1] . "\n";
                    if ($file['cid']) $message .= "Content-ID: <" . $file['cid'] . ">$this->rn";
                    else $message .= "Content-Disposition: attachment;\n\tfilename=\"" . $file['name'] . "\"$this->rn";
                    $message .= "$this->rn.";
                    $message .= ".";

                    $message .= $file['body'];
                }
            }
            else {
                $head .= "Content-Type: " . $this->ctype . "; charset=" . $this->charset . "$this->rn";
                $head .= "Content-Transfer-Encoding: " . $this->encoding . "$this->rn";
                $message = $this->message . "$this->rn";
            }
            //отправка письма
            $sz = sizeof($this->to);
            if ($sz > $this->maxrecipients || $addbcc) {
                $rcpt = $this->to_sock;
                if ($this->usemail) {
                    $offset = 0;
                    while (sizeof($this->to_now = array_slice($rcpt, $offset, $this->maxrecipients)) > 0) {
                        $to_sock = array_slice($this->to, $offset, $this->maxrecipients);
                        $offset += $this->maxrecipients;
                        $to = join(', ', $to_sock);
                        $tto = array_diff($this->to, $to_sock);
                        $ton = join(', ', $tto);
                        $heads = "To: " . $ton . "$this->rn" . $head;
                        if (@mail($to, $this->subject, $message, $heads)) $this->setLog('Sending mail to: ' . $to . " - OK.");
                        else {
                            $this->getError('send', __LINE__, "Sending to: $to - ERROR!");
                            define("SEND_STOP", true);
                            return false;
                        }
                    }
                }
                else {
                    if (!$this->getSocketConnect()) return false;
                    $rcpt = array_merge($rcpt, $this->bcc_sock);
                    $offset = 0;
                    $maxround = sizeof($rcpt) / $this->maxrecipients;
                    $maxround = ceil($maxround);
                    $n = 0;
                    $setmail = true;
                    while (sizeof($this->to_now = array_slice($rcpt, $offset, $this->maxrecipients)) > 0) {
                        $to = join(', ', array_slice($this->to, $offset, $this->maxrecipients));
                        $heads = "To: " . $to . "$this->rn" . $head;
                        $offset += $this->maxrecipients;
                        if (!$this->getSocket($setmail)) {
                            $n++;
                            if ($n == $maxround) return false;
                            $setmail = false;
                            continue;
                        }
                        $setmail = true;
                        $this->setLog(">>> $heads", 1);
                        if (is_resource($this->socket)) {
                            fputs($this->socket, $heads . "$this->rn");
                            if ($message) {
                                $this->setLog(">>> $message", 1);
                                fputs($this->socket, $message . "$this->rn");
                            }
                            $this->setLog(">>> .", 1);
                            fputs($this->socket, ".$this->rn");
                            if (!$this->getCode(250)) {
                                $this->getError('send', __LINE__, "No answer");
                                define("SEND_STOP", true);
                                return false;
                            }
                        }
                        else {
                            $this->getError('send', __LINE__, "No resource");
                            define("SEND_STOP", true);
                            return false;
                        }
                    }
                    $this->closeSocket();
                }
            }
            else {
                $this->to_now = array_merge($this->to_sock, $this->bcc_sock);
                if (!$this->usemail) {
                    $to = join(', ', $this->to);
                    $head = "To: " . $to . "$this->rn" . $head;
                    if (!$this->getSocketConnect()) return false;
                    if (!$this->getSocket()) return false;
                    $this->setLog(">>> $head", 1);
                    if (is_resource($this->socket)) {
                        fputs($this->socket, $head . "$this->rn");
                        if ($message) {
                            $this->setLog(">>> $message", 1);
                            fputs($this->socket, $message . "$this->rn");
                        }
                        $this->setLog(">>> .", 1);
                        fputs($this->socket, ".$this->rn");
                        if (!$this->getCode(250)) {
                            $this->getError('send', __LINE__, "No answer");
                            define("SEND_STOP", true);
                            return false;
                        }
                        $this->closeSocket();
                    }
                    else {
                        $this->getError('send', __LINE__, "No resource");
                        define("SEND_STOP", true);
                        return false;
                    }
                }
                else {
                    $to = join(', ', $this->to);
                    if (@mail($to, $this->subject, $message, $head)) $this->setLog('Sending mail to: ' . $to . " - OK.");
                    else {
                        $errorMessage = error_get_last()['message'];
                        $this->getError('send', __LINE__, "Sending ERROR! " . $errorMessage);
                        define("SEND_STOP", true);
                        return false;
                    }
                }
            }
            $this->clearTo();
            $this->clearBcc();
            $this->clearFiles();
        }
        return true;
    }

    /**
     * Возвращаем список писем в папке IMAP
     * @param string $folder имя папки
     * @param int $start - Номер сообщения с которого начинаем читать папку
     * @param int $count - количество возвращаемых записей
     * Возвращает массив с ключами:
     *      'sum'           - общее количество писем в папке
     *      'count'         - запрошенное количество возвращаемых писем
     *      'real_count'    - реальное количество возвращаемых писем
     *      'start'         - Номер сообщения с которого начинаем читать папку
     *      'end'           - Номер сообщения последнего возвращаемого письма
     *      'error'         - false
     * Если произошла ошибка, то возвращает массив с ключами:
     *      'error'         - true
     *      'error_info'    - информация об ошибке
     * @return array
     */
    public function read_folder ($folder = '', $start = 0, $count = 0) {
        $result = array();
        if ($folder && !$this->setImapFolder($folder)) {
            $result['error'] = true;
            $result['error_info'] = 'Error folder name: '.$folder;
            return $result;
        }
        if ($this->getIMAPConnect()) {
            $num = imap_num_msg($this->imap);
            if (!$start || $start > $num) $start = $num;
            if ($start < 1) $start = 1;
            if (!$count || $count < 1) $end = 1;
            elseif ($count == 1) $end = $start;
            else $end = $start - $count + 1;
            if ($end < 1) $end = 1;
            if ($end == $start) $param = $start;
            else $param = "{$end}:{$start}";
            $mails = imap_fetch_overview($this->imap, $param, 0);
            $last_uid = 0;
            $first_uid = 0;
            $result['messages'] = array();
            foreach ($mails as $overview) {
                if (!$first_uid) $first_uid = $overview->msgno;
                $decode = $this->getSubjectDecode($overview->from);
                $overview->from = $decode['decoded-line'];
                $decode = $this->getSubjectDecode($overview->to);
                $overview->to = $decode['decoded-line'];
                $decode = $this->getSubjectDecode($overview->subject);
                $overview->subject = $decode['decoded-line'];
                $result['messages'][$overview->msgno]['date'] = $overview->date;
                $result['messages'][$overview->msgno]['subject'] = $overview->subject;
                $result['messages'][$overview->msgno]['from'] = $overview->from;
                $result['messages'][$overview->msgno]['to'] = $overview->to;
                $result['messages'][$overview->msgno]['size'] = $overview->size;
                $result['messages'][$overview->msgno]['seen'] = $overview->seen;
                $result['messages'][$overview->msgno]['flagged'] = $overview->flagged;
                if (isset($overview->in_reply_to) && $overview->in_reply_to) $result['messages'][$overview->msgno]['in_reply_to'] = $overview->in_reply_to;
                $result['messages'][$overview->msgno]['message_id'] = $overview->message_id;
                $result['messages'][$overview->msgno]['msgno'] = $overview->msgno;
                $result['messages'][$overview->msgno]['udate'] = $overview->udate;
                $result['messages'][$overview->msgno]['draft'] = $overview->draft;
                $last_uid = $overview->msgno;
            }
            $result['sum'] = $num;
            $result['count'] = $count;
            $result['real_count'] = count($result['messages']);
            $result['start'] = $first_uid;
            $result['end'] = $last_uid;
            krsort($result['messages']);
            imap_close($this->imap);
        }
        else {
            $result['error'] = true;
            $result['error_info'] = 'Error connection to Mail Server';
        }
        return $result;
    }

    /**
     * Возврат списка писем в почтовом ящике по заданным параметрам
     * @param string $type - параметры поиска сообщений. Могут принимать следующий вид:
     *      ALL                 - возвращать все сообщения, соответствующие остальным критериям
     *      ANSWERED            - сообщения с выставленным флагом \\ANSWERED
     *      BCC "string"        - сообщения в поле Bcc: которых присутствует "string"
     *      BEFORE "date"       - сообщения с Date: до "date"
     *      BODY "string"       - сообщения содержащие "string" в теле
     *      CC "string"         - сообщения в поле Cc: которых присутствует "string"
     *      DELETED             - удаленные сообщения
     *      FLAGGED             - сообщения с установленным флагом \\FLAGGED (иногда называют "Срочное" или "Важное")
     *      FROM "string"       - сообщения в поле From: которых присутствует "string"
     *      KEYWORD "string"    - сообщения с ключевым словом "string"
     *      NEW                 - новые сообщения
     *      OLD                 - старые сообщения
     *      ON "date"           - сообщения с Date: равным "date"
     *      RECENT              - означает сообщения с выставленным флагом \\RECENT
     *      SEEN                - прочтенные сообщения (установлен флаг \\SEEN)
     *      SINCE "date"        - сообщения с Date: после "date"
     *      SUBJECT "string"    - сообщения в поле Subject: которых присутствует "string"
     *      TEXT "string"       - сообщения с текстом "string"
     *      TO "string"         - сообщения в поле To: которых присутствует "string"
     *      UNANSWERED          - неотвеченные сообщения
     *      UNDELETED           - не удаленные сообщения
     *      UNFLAGGED           - сообщения без установленных флагов
     *      UNKEYWORD "string"  - сообщения, не имеющие ключевого слова "string"
     *      UNSEEN              - непрочтенные сообщения
     * При запросе в параметрах даты, она должна соответствовать формату: date("j F Y");
     * @param bool $get_uid - вернуть UID сообщений вместо номеров
     * Возвращает массив с ключами:
     *      'mail_sum'      - общее количество писем в папке
     *      'count'         - количество писем, соответствующих заданным параметрам
     *      'mails'         - массив номеров сообщений или UID, соответствующих заданным параметрам
     *      'error'         - false
     * Если произошла ошибка, то возвращает массив с ключами:
     *      'error'         - true
     *      'error_info'    - информация об ошибке
     * @return array
     */
    public function receive ($type = 'all', $get_uid = false) {
        $result = array();
        $param = ($get_uid)?SE_UID:'';
        if ($this->getIMAPConnect()) {
            $this->setLog("IMAP Search messages Type: ".$type, 1);
            $result['mail_sum'] = imap_num_msg($this->imap);
            if ($mails = imap_search($this->imap, strtoupper($type),  $param)) {
                $result['count'] = count($mails);
                $result['mails'] = $mails;
            }
            else $result['count'] = 0;
            $result['error'] = false;
            $this->setLog("IMAP Result Count: ".$result['count'], 1);
            imap_close($this->imap);
        }
        else {
            $result['error'] = true;
            $result['error_info'] = 'Error connection to Mail Server';
        }
        return $result;
    }

    /**
     * Получение данных о письме по номеру письма
     * @param $mid - номер письма в папке
     * @param bool $mark_as_seen - пометить письмо как прочтённое (true - да, false - нет)
     * @param bool $show_inline - показать изображения в теле письма
     * Возвращается массив с ключами:
     *      'mid'               - номер сообщения
     *      'header'            - заголовки сообщения (объект)
     *      'content-encoding'  - кодировка сообщения
     *      'encoding-type'     - тип кодировки сообщения
     *      'subject'           - тема сообщения
     *      'message'           - массив полей сообщения
     *      'error'             - false
     * Массив полей сообщения имеет следующие ключи:
     *      'header'            - заголовок сообщения (строка)
     *      'body'              - текст (тело) сообщения
     *      'mime'              - mime-type сообщения (если есть)
     *      'attach'            - массив вложений соостоящий из массивов данных о вложениях
     * Массив данных о вложении имеет следующие ключи:
     *      'mime'              - mime-type вложения
     *      'name'              - имя вложения (имя файла)
     *      'content-type'      - тип содержимого
     *      'content-encoding'  - кодировка содержимого
     *      'content-disposition' - расположение вложения в письме (inline - в теле письма, attach - вложено отдельным файлом)
     *      'size'              - размер вложения
     *      'body'              - тело вложения
     * Если произошла ошибка, то возвращает массив с ключами:
     *      'error'         - true
     *      'error_info'    - информация об ошибке
     * @return array
     */
    public function read_mail ($mid, $mark_as_seen = true, $show_inline = false) {
        if ($mark_as_seen) $options = NULL;
        else $options = FT_PEEK;
        $result = array();
        if (preg_match("/^\d+$/", $mid)) {
            if ($this->getIMAPConnect()) {
                $this->setLog("IMAP Read message ID: " . $mid, 1);
                $result['mid'] = $mid;
                if ($result['header'] = imap_headerinfo($this->imap, $mid)) {
                    $decode = $this->getSubjectDecode($result['header']->Subject);
                    $result['content-encoding'] = $decode['content-encoding'];
                    $result['encoding-type'] = $decode['encoding-type'];
                    $result['subject'] = $decode['decoded-line'];
                    $result['message']['header'] = imap_fetchbody($this->imap, $mid, 0, $options);
                    $result['message']['body'] = imap_base64(imap_fetchbody($this->imap, $mid, 1, $options));
                    $result['message']['mime'] = imap_fetchmime($this->imap, $mid, 0, $options);
                    $i = 2;
                    while ($attach = imap_fetchbody($this->imap, $mid, $i, $options)) {
                        $n = $i - 2;
                        $mime = imap_fetchmime($this->imap, $mid, $i, $options);
                        $mime_array = preg_split("/\n/", $mime);
                        $name = '';
                        $content_type = '';
                        $content_encoding = '';
                        $disposition = '';
                        $size = 0;
                        foreach ($mime_array as $line) {
                            if (preg_match("/\s(name)\s?=\s?\"([^\"]+)\"/i", $line, $match)) $name = $match[2];
                            if (preg_match("/Content-Type:\s?([^\;]+)/i", $line, $match)) $content_type = $match[1];
                            if (preg_match("/Content-Transfer-Encoding:\s?([^\;]+)/i", $line, $match)) $content_encoding = $match[1];
                            if (preg_match("/Content-Disposition:\s?([^\;]+)/i", $line, $match)) $disposition = $match[1];
                            if (preg_match("/\s(size)\s?=\s?([^\;]+)/i", $line, $match)) $size = $match[2];
                        }
                        $decode = $this->getSubjectDecode($name);
                        $name = $decode['decoded-line'];
                        $result['message']['attach'][$n]['mime'] = $mime;
                        $result['message']['attach'][$n]['name'] = $name;
                        $result['message']['attach'][$n]['content-type'] = $content_type;
                        $result['message']['attach'][$n]['content-encoding'] = $content_encoding;
                        $result['message']['attach'][$n]['content-disposition'] = $disposition;
                        $result['message']['attach'][$n]['size'] = $size;
                        $result['message']['attach'][$n]['body'] = $attach;
                        if (strtolower($disposition) == 'inline' && $show_inline && in_array($content_type, array('image/png', 'image/jpeg', 'image/gif', 'image/bmp', 'image/vnd.microsoft.icon', 'image/tiff', 'image/svg+xml'))) $result['message']['body'] = preg_replace("/src=\"cid:" . $name . "@[^\"]+\"/", "src=\"data:" . $content_type . ";" . $content_encoding . "," . $attach . "\"", $result['message']['body']);
                        $i++;
                    }
                    $result['error'] = false;
                }
                else {
                    $result['error'] = true;
                    $result['error_info'] = 'Mail not found on Server';
                    $this->setLog("IMAP Mail number " . $mid . " not found on Server. " . imap_last_error(), 2);
                }
                imap_close($this->imap);
            }
            else {
                $result['error'] = true;
                $result['error_info'] = 'Error connection to Mail Server';
            }
        }
        else {
            $result['error'] = true;
            $result['error_info'] = 'Wrong mail number: '.$mid;
            $this->setLog("IMAP Mail number " . $mid . " is wrong.", 2);
        }
        return $result;
    }

    /**
     * Получение данных о письме по UID письма
     * Функция аналогична функции read_mail()
     * @param $uid - номер письма в папке
     * @param bool $mark_as_seen - пометить письмо как прочтённое (true - да, false - нет)
     * @return array
     */
    public function read_mail_UID ($uid, $mark_as_seen = true) {
        $result = array();
        if (preg_match("/^\d+$/", $uid)) {
            if ($this->getIMAPConnect()) {
                $mails = imap_fetch_overview($this->imap, "{$uid}", FT_UID);
                if (count($mails)) {
                    $mid = $mails[0]->msgno;
                    $result = $this->read_mail($mid, $mark_as_seen);
                } else {
                    $result['error'] = true;
                    $result['error_info'] = 'Mail not found on Server';
                    $this->setLog("IMAP Mail ID " . $uid . " not found on Server. " . imap_last_error(), 2);
                    imap_close($this->imap);
                }
            }
            else {
                $result['error'] = true;
                $result['error_info'] = 'Error connection to Mail Server';
            }
        }
        else {
            $result['error'] = true;
            $result['error_info'] = 'Wrong UID: '.$uid;
            $this->setLog("IMAP Mail ID " . $uid . " is wrong.", 2);
        }
        return $result;
    }

    ////////////////////////////////////////////////////////////////
    //                         Set block                          //
    ////////////////////////////////////////////////////////////////

    /**
     * Создаём массив получателей письма
     * Функцию можно вызывать несколько раз с разными параметрами
     * При каждом вызове функции переданные адреса накапливаются в массиве $this->to
     * Для очистки массива получателей пользуйтесь функцией clearTo()
     *
     * @param mixed $to - адрес получателя письма, может быть задан как строками, так и массивами
     *
     * 1. Использование параметра $to как строки
     * 1.1. в строке передаётся один адрес получателя (можно использовать дополнительный параметр функции $username)
     * 1.2. в строке передаётся несколько адресов, адреса должны быть разделены запятой (,) или точкой с запятой (;)
     *
     * 2. Использование параметра $to как массива
     * 2.1. передаётся одномерный массив адресов получателей письма
     * 2.2. передаётся массив ассоциативных массивов адресов ('mail') и имён получателей ('username')
     *      $to = array(array('mail'=>'адрес получателя 1', 'username'=>'имя получателя 1'), array('mail'=>'адрес получателя 2', 'username'=>'имя получателя 2'));
     * 2.3. можно передавать смешанный массив данных как п.2.1. и п.2.2.
     *
     * @param mixed $username - имя пользователя (используется только при передаче параметров согласно п.1.1.)
     *
     */
    public function setTo ($to, $username = false) {
        if (is_array($to)) {
            foreach ($to as $key=>$tm) {
                if (is_array($tm) && isset($tm['mail']) && isset($tm['username'])) {
                    if ($this->getCheck($tm['mail'])) {
                        $ml = "=?$this->charset?B?".base64_encode(trim($tm['username']))."?=".' <'.$tm['mail'].'>';
                        if (!in_array($tm['mail'], $this->to_sock)) {
                            if (in_array($tm['mail'], $this->bcc_sock)) {
                                $key = array_search($tm['mail'], $this->bcc_sock);
                                array_splice($this->bcc_sock, $key, 0);
                                $sch = $tm['mail'];
                                foreach ($this->bcc as $k=>$mail) {
                                    if (preg_match("/$sch/is", $mail)) {
                                        $key = $k;
                                        break;
                                    }
                                }
                                array_splice($this->bcc, $key, 0);
                            }
                            $this->to[] = $ml;
                            $this->to_sock[] = $tm['mail'];
                        }
                    }
                }
                else {
                    if ($this->getCheck($tm)) {
                        if (!in_array($tm, $this->to_sock)) {
                            if (in_array($tm, $this->bcc_sock)) {
                                $key = array_search($tm, $this->bcc_sock);
                                array_splice($this->bcc_sock, $key, 0);
                                $sch = $tm;
                                foreach ($this->bcc as $k=>$mail) {
                                    if (preg_match("/$sch/is", $mail)) {
                                        $key = $k;
                                        break;
                                    }
                                }
                                array_splice($this->bcc, $key, 0);
                            }
                            $this->to[] = $tm;
                            $this->to_sock[] = $tm;
                        }
                    }
                }
            }
        }
        else {
            if (preg_match("/(;|,)/", $to)) {
                $to = preg_replace("/,/", ';', $to);
                $to = preg_split('/;/', $to);
                foreach ($to as $num => $tm) {
                    if ($this->getCheck($tm)) {
                        if (!in_array($tm, $this->to_sock)) {
                            if (in_array($tm, $this->bcc_sock)) {
                                $key = array_search($tm, $this->bcc_sock);
                                array_splice($this->bcc_sock, $key, 0);
                                $sch = $tm;
                                foreach ($this->bcc as $k=>$mail) {
                                    if (preg_match("/$sch/is", $mail)) {
                                        $key = $k;
                                        break;
                                    }
                                }
                                array_splice($this->bcc, $key, 0);
                            }
                            $this->to[] = $tm;
                            $this->to_sock[] = $tm;
                        }
                    }
                }
            }
            else {
                if ($this->getCheck($to)) {
                    if (!in_array($to, $this->to_sock)) {
                        if (in_array($to, $this->bcc_sock)) {
                            $key = array_search($to, $this->bcc_sock);
                            array_splice($this->bcc_sock, $key, 0);
                            $sch = $to;
                            foreach ($this->bcc as $k=>$mail) {
                                if (preg_match("/$sch/is", $mail)) {
                                    $key = $k;
                                    break;
                                }
                            }
                            array_splice($this->bcc, $key, 0);
                        }
                        $this->to_sock[] = $to;
                        if ($username) $to = "=?$this->charset?B?".base64_encode(trim($username))."?="." <$to>";
                        $this->to[] = $to;
                    }
                }
            }
        }
    }

    /**
     * Создаём массив скрытых получателей письма
     * Функцию можно вызывать несколько раз с разными параметрами
     * При каждом вызове функции переданные адреса накапливаются в массиве $this->bcc
     * Для очистки массива получателей пользуйтесь функцией clearBcc()
     *
     * @param mixed $bcc - адрес получателя письма, может быть задан как строками, так и массивами
     *
     * 1. Использование параметра $bcc как строки
     * 1.1. в строке передаётся один адрес получателя (можно использовать дополнительный параметр функции $username)
     * 1.2. в строке передаётся несколько адресов, адреса должны быть разделены запятой (,) или точкой с запятой (;)
     *
     * 2. Использование параметра $bcc как массива
     * 2.1. передаётся одномерный массив адресов получателей письма
     * 2.2. передаётся массив ассоциативных массивов адресов ('mail') и имён получателей ('username')
     *      $bcc = array(array('mail'=>'адрес получателя 1', 'username'=>'имя получателя 1'), array('mail'=>'адрес получателя 2', 'username'=>'имя получателя 2'));
     * 2.3. можно передавать смешанный массив данных как п.2.1. и п.2.2.
     *
     * @param mixed $username - имя пользователя (используется только при передаче параметров согласно п.1.1.)
     */
    public function setBcc ($bcc, $username = false) {
        if (is_array($bcc)) {
            foreach ($bcc as $key=>$tm) {
                if (is_array($tm) && isset($tm['mail']) && isset($tm['username'])) {
                    if ($this->getCheck($tm['mail'])) {
                        if (!in_array($tm['mail'], $this->to_sock) && !in_array($tm['mail'], $this->bcc_sock)) {
                            $ml = "=?$this->charset?B?".base64_encode(trim($tm['username']))."?=".' <'.$tm['mail'].'>';
                            $this->bcc_sock[] = $tm['mail'];
                            $this->bcc[] = $ml;
                        }
                    }
                }
                else {
                    if ($this->getCheck($tm)) {
                        if (!in_array($tm, $this->to_sock) && !in_array($tm, $this->bcc_sock)) {
                            $this->bcc[] = $tm;
                            $this->bcc_sock[] = $tm;
                        }
                    }
                }
            }
        }
        else {
            if (preg_match("/(;|,)/", $bcc)) {
                $bcc = preg_replace("/,/", ';', $bcc);
                $bcc = preg_split('/;/', $bcc);
                foreach ($bcc as $num => $tm) {
                    if ($this->getCheck($tm)) {
                        if (!in_array($tm, $this->to_sock) && !in_array($tm, $this->bcc_sock)) {
                            $this->bcc_sock[] = $tm;
                            $this->bcc[] = $tm;
                        }
                    }
                }
            }
            else {
                if ($this->getCheck($bcc)) {
                    if (!in_array($bcc, $this->to_sock) && !in_array($bcc, $this->bcc_sock)) {
                        $this->bcc_sock[] = $bcc;
                        if ($username) $bcc = "=?$this->charset?B?".base64_encode(trim($username))."?="." <$bcc>";
                        $this->bcc[] = $bcc;
                    }
                }
            }
        }
    }

    /**
     * Задание адреса отправителя
     * @param string $from - адрес отправителя письма
     * @param string $username - имя пользователя
     * @return bool
     */
    public function setFrom ($from, $username = '') {
        if (!is_array($from)) {
            if ($this->getCheck($from)) {
                if ($username) $from = "=?$this->charset?B?".base64_encode(trim($username))."?="." <$from>";
                $this->from = $from;
                return true;
            }
            else return false;
        }
        else {
            $this->getError('setFrom', __LINE__, 'Wrong from address: '.print_r($from, true));
            return false;
        }
    }

    /**
     * Залание темы сообщения
     * @param string $subject
     * @return bool
     */
    public function setSubject ($subject = '') {
        if (!is_array($subject)) {
            $this->subject = "=?$this->charset?B?".base64_encode($subject)."?=";
            return true;
        }
        else {
            $this->getError('setSubject', __LINE__, 'Wrong subject: '.print_r($subject, true));
            return false;
        }
    }

    /**
     * Задание простого текстового сообщения
     * @param string $message
     * @return bool
     */
    public function setMessage ($message = '') {
        $this->encoding = $this->Encoding[0];
        $this->ctype = $this->ContentType[0];
        $this->message = $message;
        return true;
    }

    /**
     * Задание сообщения HTML вида
     * @param string $message
     * @return bool
     */
    public function setHTMLMessage ($message = '') {
        $message = trim($message);
        if (!preg_match("/^<html/i", $message)) $message = "<html".">\n".$message;
        if (!preg_match("/<\/html>$/i", $message)) $message = $message."\n</html>";
        $this->ctype = $this->ContentType[3];
        $this->encoding = $this->Encoding[1];
        $this->message = chunk_split(base64_encode($message));
        return true;
    }

    /**
     * Создание текстового сообщения из HTML файла
     * @param string $filepath - путь к файлу HTML
     * @return bool
     */
    public function setMessageFromHTML ($filepath) {
        $filepath = trim($filepath);
        if (preg_match('/^(http|ftp|www)/i', $filepath) || (file_exists($filepath) && is_file($filepath))) {
            if (!$message = @file_get_contents($filepath)) {
                $this->getError('setHTMLfile', __LINE__, 'File not exists: '.print_r($filepath, true));
                return false;
            }
            ini_set('max_execution_time', 720);
            if (preg_match("/<meta\s+http-equiv\s?=\s?\\\?\"?\'?Content-Type\\\?\"\'? content=\\\?\"\'?text\/html; charset=\s?([^\s>\"\']+)/is", $message, $match)) {
                $this->charset = $match[1];
            }
            $message = strip_tags($message);
            $message = $this->getHTMLdecode($message);
            $this->message = $message;
            $this->encoding = $this->Encoding[0];
            $this->ctype = $this->ContentType[0];
            return true;
        }
        else {
            $this->getError('setMessageFromHTML', __LINE__, 'File not exists: '.print_r($filepath, true));
            return false;
        }
    }

    /**
     * Сохдание письма из HTML файла
     * @param string $filepath - путь к файлу HTML
     * @return bool
     */
    public function setHTMLfile ($filepath) {
        $filepath = trim($filepath);
        if (preg_match('/^(http|ftp|www)/i', $filepath) || (file_exists($filepath) && is_file($filepath))) {
            if (preg_match("/^www/i", $filepath)) $filepath = "http://".$filepath;
            if (!$file_content = @file_get_contents($filepath)) {
                $this->getError('setHTMLfile', __LINE__, 'File not exists: '.print_r($filepath, true));
                return false;
            }
            ini_set('max_execution_time', 720);
            //$scr = array();
            $img = array();
            $style = array();
            $replase = array();
            $domain = '';
            if (preg_match("/^((http|https|ftp):\/\/)(.+)/i", $filepath, $match)) {
                $apath = preg_split('/\//', $match[3]);
                $domain = $match[1].$apath[0];
                $sz = sizeof($apath);
                $sz = $sz-1;
                if ($sz > 0) {
                    if (preg_match("/(\.|\?|\&)/s", $apath[$sz])) {
                        array_pop($apath);
                        $path = $match[1].join('/', $apath);
                    }
                    else $path = $match[1].join('/', $apath);
                }
                else {
                    $path = $filepath;
                    $path = preg_replace("/\/$/", '', $path);
                }
            }
            else {
                $path = dirname($filepath);
            }
            //$punct = rand(100, 999);
            if (preg_match_all("/<link[^>]+href\s?=\s?\\\?\"?\'?([^\>\"\'\s]+\.css)\s?\\\?\"?\'?[^>]*>/is", $file_content, $styles)) {
                $style = array_unique($styles[1]);
                foreach ($styles[0] as $num=>$link) {
                    $file_content = strtr($file_content, array($link=>''));//
                }
            }
            if (preg_match_all("/\@import\s?url\(\\\?\"?\'?([^\.\"\'\s]+\.css)\\\?\"?\'?\)\s?;+/isU", $file_content, $styles)) {
                $style = array_merge($style, array_unique($styles[1]));
                foreach ($styles[0] as $num=>$link) {
                    $file_content = strtr($file_content, array($link=>''));//
                }
            }
            $style = array_unique($style);
            $cssstyle = '';
            if (sizeof($style)) {
                foreach ($style as $num => $css) {
                    if (preg_match("/^(http|ftp)/i", $css)) $spath = $css;
                    elseif ($domain && preg_match("/^\//", $css)) $spath = $domain.$css;
                    elseif (preg_match("/^\.\//", $css)) {
                        $css = preg_replace("/\.\//", '/', $css);
                        $spath = $path.$css;
                    }
                    elseif (preg_match("/^\.\.\//", $css)) {
                        $spath = $path.'/'.$css;
                    }
                    elseif (preg_match("/^\//", $css)) $spath = $path.$css;
                    else {
                        $spath = $path.'/'.$css;
                    }
                    $cssload = @file_get_contents("$spath");
                    if (preg_match_all("/(background-image:\s*url\()(.+)(\))/isU", $cssload, $images)) {
                        $img = array_unique($images[2]);
                    }
                    if (preg_match_all("/(background:\s*[^\(]*url\()(.+)(\))/isU", $cssload, $images)) {
                        $img = array_merge($img, array_unique($images[2]));
                    }
                    $img = array_unique($img);
                    $replase = array();
                    $dir_css = dirname($css);
                    foreach ($img as $cssimg) {
                        if (!preg_match("/^\//", $cssimg)) {
                            $replase[$cssimg] = $dir_css.'/'.$cssimg;
                        }
                    }
                    $cssstyle .= $cssload;
                }
            }
            if ($cssstyle) {
                $cssstyle = strtr($cssstyle, $replase);
                $img = array();
                if (preg_match("/<\\\?\/head>/i", $file_content)) {
                    $file_content = preg_replace("/(<\\\?\/head>)/i", "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\r\n<style type=\"text/css\">$cssstyle</style>\\1", $file_content);
                }
                elseif (preg_match("/<body/i", $file_content)) {
                    $file_content = preg_replace("/(<body)/i", "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\r\n<style type=\"text/css\">$cssstyle</style>\\1", $file_content);
                }
            }
            if (preg_match_all("/(background-image:\s*url\()(.+)(\))/isU", $file_content, $images)) {
                $img = array_unique($images[2]);
            }
            if (preg_match_all("/(background:\s*[^\(]*url\()(.+)(\))/isU", $file_content, $images)) {
                $img = array_merge($img, array_unique($images[2]));
            }
            // Подключение скриптов отдельным файлом
            if (preg_match_all("/<script[^.]+src\s?=\s?\\\?\"?\'?([^>\"\'\s]+)\s?\\\?\"?\'?[^>]*>/is", $file_content, $images)) {
                $img = array_merge($img, array_unique($images[1]));
            }

            $img = array_unique($img);
            if (preg_match_all("/<img[^.]+src\s?=\s?\\\?\"?\'?([^\>\"\'\s]+)\s?\\\?\"?\'?[^>]*>/is", $file_content, $images)) {
                $img = array_merge($img, array_unique($images[1]));
                $img = array_unique($img);
            }
            if (preg_match_all("/background\s?=\s?\\\?\"?\'?([^>\"\']+)\\\?\"?\'?/is", $file_content, $images)) {
                $img = array_merge($img, array_unique($images[1]));
                $img = array_unique($img);
            }
            if (preg_match_all("/<param[^>]+value\s?=\s?\\\?\"?\'?([^>\"\']+\.swf)\\\?\"?\'?/isU", $file_content, $images)) {
                $img = array_merge($img, array_unique($images[1]));
                $img = array_unique($img);
            }
            $files = array_unique($img);
            sort($files);
            $i = sizeof($this->files);
            $replase = array();
            $loadet = array();
            foreach ($files as $num => $filep) {
                if (preg_match("/^(http|ftp)/i", $filep)) $spath = $filep;
                elseif ($domain && preg_match("/^\//", $filep)) $spath = $domain.$filep;
                elseif (preg_match("/^\.\//", $filep)) {
                    $filep = preg_replace("/\.\//", '/', $filep);
                    $spath = $path.$filep;
                }
                elseif (preg_match("/^\.\.\//", $filep)) {
                    $spath = $path.'/'.$filep;
                }
                elseif (preg_match("/^\//", $filep)) $spath = $path.$filep;
                else {
                    $spath = $path.'/'.$filep;
                }
                $file = @file_get_contents($spath);
                if (!$file) {
                    $this->getError('setHTMLFile', __LINE__, 'File not exists: '.print_r($spath, true));
                    $replase[$filep] = "";
                    continue;
                }
                $nowsize = md5($file);
                $un = strtoupper(uniqid(time()));//.'.FMail';
                $type = getimagesize($spath);
                $name = basename($spath);
                if (preg_match("/\?/", $name)) {
                    $nm = preg_split('/\?/', $name);
                    $name = $nm[0];
                }
                if ($loadet[$name]) {
                    $new = 1;
                    foreach ($loadet[$name]['size'] as $nm => $oldsize) {
                        if ($oldsize == $nowsize) {
                            $replase[$filep] = $loadet[$name]['cid'][$nm];
                            $new = 0;
                        }
                    }
                    if ($new) {
                        $this->files[$i]['cid'] = $un;
                        $loadet[$name]['size'][] = $nowsize;
                        $replase[$filep] = "cid:$un";
                        $this->files[$i]['name'] = $name;
                    }
                    else continue;
                }
                else {
                    $loadet[$name]['cid'][] = "cid:$un";
                    $loadet[$name]['size'][] = $nowsize;
                    $this->files[$i]['cid'] = $un;
                    $replase[$filep] = "cid:$un";
                    $this->files[$i]['name'] = $name;
                }

                if ($type[2] >= 1 && $type[2] <= 4) {
                    switch ($type[2]) {
                        case 1:
                            $this->files[$i]['ctype'] = 'image/gif';
                            break;
                        case 2:
                            $this->files[$i]['ctype'] = 'image/jpeg';
                            break;
                        case 3:
                            $this->files[$i]['ctype'] = 'image/png';
                            break;
                        default:
                            $this->files[$i]['ctype'] = $this->ContentType[2];
                    }
                }
                if (!$this->files[$i]['ctype']) $this->files[$i]['ctype'] = $this->ContentType[2];
                $this->files[$i]['body'] = chunk_split(base64_encode($file));
                $i++;
            }

            $file_content = strtr($file_content, $replase);
            if (preg_match("/<meta\s+http-equiv\s?=\s?\\\?\"?\'?Content-Type\\\?\"\'? content=\\\?\"\'?text\/html; charset=\s?([^\s>\"\']+)/is", $file_content, $match)) {
                $this->charset = $match[1];
            }
            $nl = '';
            if (preg_match_all("/<pre([^>]*)>(.+)<\/pre>/isU", $file_content, $match)) {
                $nl = 'FYNPUNKTFMailPHP'.rand(100,999);
                //$replase = array();
                foreach ($match[0] as $num=>$line) {
                    $pre = preg_replace("/\r/", "", $line);
                    $pre = preg_replace("/\n/", $nl, $pre);
                    $file_content = str_replace($line, $pre, $file_content);
                }
            }
            $file_content = preg_replace("/\r/", '', $file_content);
            $file_content = preg_replace("/\n/", '', $file_content);
            if ($nl) $file_content = strtr($file_content, array($nl=>"\n"));
            $file_content = $this->getQuotedPrint($file_content);
            $this->ctype = $this->ContentType[3];
            $this->encoding = $this->Encoding[3];
            $this->message = $file_content;
            return true;
        }
        else {
            $this->getError('setHTMLfile', __LINE__, 'File not exists: '.print_r($filepath, true));
            return false;
        }
    }

    /**
     * Добавление файлов к письму
     * @param string $filepath - путь к файлу
     * @return bool
     */
    public function setFile ($filepath) {
        $i = sizeof($this->files);
        if (file_exists($filepath) && is_file($filepath)) {
            $file = file_get_contents($filepath);
            $this->files[$i]['name'] = basename($filepath);
            if (is_callable("mime_content_type")) {
                $this->files[$i]['ctype'] = mime_content_type($filepath);
            }
            if ($this->files[$i]['ctype'] = 'text/plain') {
                $type = getimagesize($filepath);
                if ($type[2] >= 1 && $type[2] <= 4) {
                    switch ($type[2]) {
                        case 1:
                            $this->files[$i]['ctype'] = 'image/gif';
                            break;
                        case 2:
                            $this->files[$i]['ctype'] = 'image/jpeg';
                            break;
                        case 3:
                            $this->files[$i]['ctype'] = 'image/png';
                            break;
                        default:
                            $this->files[$i]['ctype'] = $this->ContentType[2];
                    }
                }
                elseif (preg_match("/\.doc$/i", $this->files[$i]['name'])) {
                    $this->files[$i]['ctype'] = 'application/msword';
                }
                elseif (preg_match("/\.xls$/i", $this->files[$i]['name'])) {
                    $this->files[$i]['ctype'] = 'application/vnd.ms-excel';
                }
                elseif (preg_match("/\.zip$/i", $this->files[$i]['name'])) {
                    $this->files[$i]['ctype'] = 'application/zip';
                }
                elseif (preg_match("/\.pdf$/i", $this->files[$i]['name'])) {
                    $this->files[$i]['ctype'] = 'application/pdf';
                }
                elseif (!preg_match("/\.txt$/i", $this->files[$i]['name'])) {
                    $this->files[$i]['ctype'] = $this->ContentType[2];
                }
            }
            if (!$this->files[$i]['ctype']) $this->files[$i]['ctype'] = $this->ContentType[2];
            $this->files[$i]['body'] = chunk_split(base64_encode($file), 68, "\n");
            return true;
        }
        else {
            $this->getError('setFile', __LINE__, 'File not exists: '.print_r($filepath, true));
            return false;
        }
    }

    /**
     * Установка логина пользователя
     * @param string $login
     */
    public function setLogin ($login = '') {
        $this->login = $login;
    }

    /**
     * Установка пароля пользователя
     * @param string $password
     */
    public function setPassword ($password = '') {
        $this->password = $password;
    }

    /**
     * Установка адреса сервера при работе через сокет
     * @param string $server
     */
    public function setServer ($server = 'localhost') {
        if ($server) $this->server = $server;
    }

    /**
     * Установка порта для подключения через сокет
     * @param int $port
     */
    public function setPort ($port = 25) {
        if (is_numeric($port)) $this->port = $port;
        else $this->getError('setPort', __LINE__, 'Wrong port number: '.$port);
    }

    /**
     * Установка порта для подключения IMAP
     * @param int $port
     */
    public function setImapPort ($port = 143) {
        if (is_numeric($port)) $this->imap_port = $port;
        else $this->getError('setImapPort', __LINE__, 'Wrong IMAP port number: '.$port);
    }

    /**
     * Установка типа подключения к серверу для получения почты
     * По умолчанию и рекомендуется imap, можно использовать pop3, но сразу сокращается функциональность
     * @param string $type
     */
    public function setImapType ($type = 'imap') {
        if (is_string($type) && in_array(strtolower(trim($type)), array('imap', 'pop3'))) $this->imap_type = strtolower(trim($type));
        else $this->getError('setImapType', __LINE__, 'Wrong IMAP type: '.$type);
    }

    /**
     * Установка параметра подключения IMAP с использованием SSL
     * @param bool $ssl - использовать или нет (true|false)
     */
    public function setImapSSL ($ssl = false) {
        if ($ssl === true) {
            $this->imap_ssl = true;
            $this->setLog('setImapSSL to TRUE', 1);
        }
        else {
            $this->imap_ssl = false;
            $this->setLog('setImapSSL to FALSE', 1);
        }
    }

    /**
     * Установка домена с которогоо отправляем письма
     * @param string $domain
     */
    public function setDomain ($domain = '') {
        if (is_string($domain)) $this->mydomain = $domain;
        else $this->getError('setDomain', __LINE__, 'Wrong domain name: '.$domain);
    }

    /**
     * Установка времени ожидания подключения к сокету
     * @param int $timeout
     */
    public function setTimeout ($timeout = 30) {
        if (is_int($timeout)) $this->timeout = $timeout;
    }

    /**
     * Установка параметра использования стандартной функции mail
     * @param bool $usemail
     */
    public function setMailUse ($usemail = true) {
        $this->usemail = $usemail;
    }

    /**
     * Установка метода авторизации на сервере при подключении через сокет
     * @param string $auth
     */
    public function setAuth ($auth = 'LOGIN') {
        $this->setLog("setAuth: $auth");
        $auth = strtoupper($auth);
        if (in_array($auth, $this->Authentications)) {
            $this->auth = $auth;
        }
        else {
            $this->auth = 0;
        }
    }

    /**
     * Установка максимального количества получателей в одном письме
     * @param int $num
     */
    public function setMaxRecipient ($num = 1) {
        if (!is_numeric($num)) $this->maxrecipients = 1;
        else $this->maxrecipients = $num;
    }

    /**
     * Логирование действий
     * @param string $message - сообщение
     * @param integer $type - тип лога (0 - функции, 1 - данные, 2 - ошибки)
     */
    private function setLog ($message = '', $type = 0) {
        $tm = date('d.m.Y (H:i:s)') . ': '.$message;
        switch ($type) {
            case 1:
                $this->log['data'][] = $tm;
                break;
            case 2:
                $this->log['error'][] = $tm;
                break;
            case 3:
                $this->log['info'][] = $tm;
                break;
            default:
                $this->log['action'][] = $tm;
        }
    }

    /**
     * Установка кодировки письма
     * Могут передаваться параметры:
     * WIN - windows-1251
     * KOI - koi8-r
     * ISO - iso-8859-1
     * UTF8 - utf-8
     * По умолчанию используется UTF8
     * @param string $enc
     */
    public function setCharset ($enc = 'UTF8') {
        switch (strtoupper($enc)) {
            case 'WIN':
                $this->charset = $this->Charset[2];
                break;
            case 'ISO':
                $this->charset = $this->Charset[0];
                break;
            case 'KOI':
                $this->charset = $this->Charset[3];
                break;
            default:
                $this->charset = $this->Charset[1];
        }
    }

    /**
     * Влючение отладочных функций класса
     * @param bool $debug
     */
    public function setDebug ($debug = false) {
        $this->debug = $debug;
    }

    /**
     * Установка папки для работы с почтой IMAP
     * @param $folder
     * @return bool
     */
    public function setImapFolder ($folder = 'INBOX') {
        $folders = $this->getImapFolders();
        if (in_array($folder, $folders['boxes'])) {
            $folder = $this->imap_encode($folder);
            $this->imap_folder = $folder;
            if (is_resource($this->imap) && imap_ping($this->imap)) {
                imap_close($this->imap);
                if (!$this->getIMAPConnect()) return false;
            }
            return true;
        }
        $this->setLog("Wrong Folder name: " . $folder, 2);
        return false;
    }

    /**
     * Установка флагов подключения
     * /imap
     * /pop3
     * /ssl             использовать SSL для шифрования сессии
     * /user=user       имя пользователя для входа на сервер
     * /authuser=user   удаленный пользователь для аутентификации; если указано, то это будет тот пользователь, чей пароль используется (например administrator)
     * /anonymous       удаленный доступ под анонимным пользователем
     * /debug           записывать телеметрию протокола в специальный лог-файл приложения
     * /secure          не передавать пароль по сети в виде нешифрованного текста
     * /norsh           не использовать rsh или ssh для установки преавторизованной сессии IMAP
     * /validate-cert   проверять сертификаты серверов TLS/SSL (поведение по умолчанию)
     * /novalidate-cert не проверять сертификаты от серверов TLS/SSL. полезно для серверов с самоподписанным сертификатом
     * /tls             принудительно использовать start-TLS для шифрования сессии и отвергать соединения с серверами его не поддерживающими
     * /notls           не применять start-TLS для шифрования сессии, даже если сервер его поддерживает
     * /readonly        подключение только для чтения
     * @param string $flag - флаг из перечня выше (см. документацию к функции imap_open)
     * @param bool $clear  - очистить старые флаги (true|false)
     */
    public function setImapFlags ($flag = '', $clear = false) {
        $flags_array = array('anonymous', 'debug', 'secure', 'norsh', 'validate-cert', 'novalidate-cert', 'tls', 'notls', 'readonly');
        if (!$flag) $this->imap_flags = '';
        else {
            $flag = strtr($flag, array('\\'=>'\/'));
            $flag = preg_replace("/^\s?\//", '', $flag);
            $flags = preg_split("/\//", $flag);
            $flags_old = preg_replace("/^\s?\//", '', $this->imap_flags);
            $flags_old_array = preg_split("/\//", $flags_old);
            $line = '';
            foreach ($flags as $ln) {
                if (!$clear && in_array(strtolower(trim($ln)), $flags_old_array)) continue;
                if (in_array(strtolower(trim($ln)), $flags_array)) $line .= '/'.strtolower(trim($ln));
                elseif (in_array(strtolower(trim($ln)), array('imap', 'pop3'))) $this->setImapType(strtolower(trim($ln)));
                elseif (strtolower(trim($ln)) == 'ssl') $this->setImapSSL(true);
                elseif (preg_match("/^user\=(.+)$/i", trim($ln), $match)) {
                    $user = $match[1];
                    if (!$clear && preg_match("/\/user\=([^\/]+)/i", trim($this->imap_flags))) $this->imap_flags = preg_replace("/\/user\=([^\/]+)/i", '\/user='.$user, trim($this->imap_flags));
                    else $line .= '/user='.$user;
                }
                elseif (preg_match("/^authuser\=(.+)$/i", trim($ln), $match)) {
                    $user = $match[1];
                    if (!$clear && preg_match("/\/authuser\=([^\/]+)/i", trim($this->imap_flags))) $this->imap_flags = preg_replace("/\/authuser\=([^\/]+)/i", '\/authuser='.$user, trim($this->imap_flags));
                    else $line .= '/authuser='.$user;
                }
            }
            if ($clear) $this->imap_flags = $line;
            else $this->imap_flags .= $line;
        }
    }

    ////////////////////////////////////////////////////////////////
    //                           Get block                        //
    ////////////////////////////////////////////////////////////////

    /**
     * Подключение через сокет для отправки сообщения
     * @return bool
     */
    private function getSocketConnect () {
        if (is_resource($this->socket)) return true;
        $this->socket = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            $pr_errno = print_r($errno,true);
            $pr_errstr = print_r($errstr, true);
            $pr_errno = Base::convertLine($pr_errno);
            $pr_errstr = Base::convertLine($pr_errstr);
            $this->getError('getSocket', __LINE__, 'Unable to connect to: '.$this->server.':'.$this->port."\n".$pr_errno."\n".$pr_errstr);
            return false;
        }
        else {
            $log = '';
            $this->setLog("getSocket: $this->server:$this->port - OK");
            if (!$res = $this->getCode(220)) return false;
            if (!$this->mydomain) list($user, $this->mydomain) = preg_split('/@/', $this->from);
            unset($user);

            if ($this->auth) {
                $this->setAuth($this->auth);
            }
            $this->setLog(">>> EHLO " . $this->mydomain, 1);
            fputs($this->socket, "EHLO " . $this->mydomain . "$this->rn");
            if (!$res = $this->getCode(250)) {
                $log .= $res;
                $this->setLog(">>> HELO " . $this->mydomain, 1);
                fputs($this->socket, "HELO ".$this->mydomain . "$this->rn");
                if (!$res = $this->getCode(250)) return false;
            }
            $log .= $res;
            $this->setLog($log, 3);
            if ($this->auth && $this->login) {
                # Авторизация
                if (!$this->getSocketLogin()) return false;
            }
            return true;
        }
    }

    /**
     * Подключение к почтовому ящику IMAP
     * @return bool
     */
    private function getIMAPConnect () {
        if (!function_exists("imap_open")) {
            $this->getError('getIMAPConnect', __LINE__, 'Function imap_open not exists.');
            return false;
        }
        if (is_resource($this->imap) && imap_ping($this->imap)) return true;
        $imap_path = $this->server.':'.$this->imap_port;
        if (strcasecmp($this->imap_type, 'imap') !== 0) $imap_path .= "/".$this->imap_type;
        if ($this->imap_ssl) $imap_path .= "/ssl";
        $this->imap_flags = preg_replace("/^\s?\//", '', $this->imap_flags);
        if ($this->imap_flags) $imap_path .= "/".$this->imap_flags;
        if ($this->imap_folder) $imap_path = "{".$imap_path."}".$this->imap_folder;
        if (!$this->imap = imap_open($imap_path, $this->login, $this->password)) {
            $this->getError('getIMAPConnect', __LINE__, 'Unable to connect to: '.$imap_path."\nUser: ".$this->login."\nPassword: ".$this->password."\n".imap_last_error());
            return false;
        }
        $this->setLog("getIMAPConnect: $imap_path - OK");
        return true;
    }

    /**
     * Передача заголовков сокету при отправке письма
     * @param bool $setmail - требуется ли отправка заголовка Mail from
     * @return bool
     */
    private function getSocket($setmail = true) {
        if (is_resource($this->socket)) {
            if ($setmail) {
                $this->setLog(">>> MAIL FROM: <".$this->from.">", 1);
                fputs($this->socket, "MAIL FROM: <".$this->from.">$this->rn");
                if (!$this->getCode(250)) return false;
            }
            $n = 0;
            foreach ($this->to_now as $num=>$mlto) {
                $this->setLog(">>> RCPT TO: <".$mlto.">", 1);
                fputs($this->socket, "RCPT TO: <".$mlto.">$this->rn");
                if (!$this->getCode(250, false)) {
                    $n++;

                }
            }
            if ($n == sizeof($this->to_now)) return false;
            $this->setLog(">>> DATA", 1);
            fputs($this->socket, "DATA$this->rn");
            if (!$this->getCode(354)) return false;

            return true;
        }
        else {
            $this->getError('setSocket', __LINE__, "No resource");
            return false;
        }
    }

    /**
     * Аторизация пользователя при подключении через сокет для отправки сообщения
     * @return bool
     */
    private function getSocketLogin () {
        $this->setLog("getSocketLogin");
        if (!is_resource($this->socket)) {
            $this->getError('setSocketLogin', __LINE__, "No resource");
            return false;
        }
        $log = '';
        if ($this->auth == 'LOGIN') {
            $this->setLog(">>> AUTH LOGIN", 1);
            fputs($this->socket, "AUTH LOGIN$this->rn");
            if(!$res = $this->getCode(334)) {
                if ($res = $this->getCode(503)) return true;
                else return false;
            }
            $log .= $res;
            $this->setLog(">>> ".base64_encode($this->login), 1);
            fputs($this->socket, base64_encode($this->login) . "$this->rn");
            if(!$res = $this->getCode(334)) return false;
            $log .= $res;
            $this->setLog(">>> ".base64_encode($this->password), 1);
            fputs($this->socket, base64_encode($this->password) . "$this->rn");
            if(!$res = $this->getCode(235)) return false;
            $log .= $res;
        }
        elseif ($this->auth == 'PLAIN') {
            $this->setLog(">>> AUTH PLAIN", 1);
            fputs($this->socket, "AUTH PLAIN$this->rn");
            if(!$res = $this->getCode(334)) {
                if ($res = $this->getCode(503)) return true;
                else return false;
            }
            $log .= $res;
            $auth_str = base64_encode(chr(0) . $this->login . chr(0) . $this->password);
            $this->setLog(">>> $auth_str", 1);
            fputs($this->socket, $auth_str . "$this->rn");
            if(!$res = $this->getCode(235)) return false;
            $log .= $res;
        }
        $this->setLog($log, 3);
        return true;
    }

    /**
     * Проверка ответа при соккетном соединении при отправке сообщения
     * @param int $code - сравниваемый код ответа
     * @param bool $exit - разорвать соединение при ошибке
     * @return mixed
     */
    private function getCode ($code = 200, $exit = true) {
        if (is_resource($this->socket)) {
            $file = @fread($this->socket, 1024);
            $file = rtrim($file);
            $file = preg_split("/\n/", $file);
            $res = '';
            foreach ($file as $num => $line) {
                $this->setLog("<<< ".$line, 1);
                $res .= $line."\n";
                if(substr($line,0,3) != $code) {
                    $this->getError('getCode "'.$code.'"', __LINE__, $res);
                    if ($exit) {
                        $this->closeSocket();
                        return false;
                    }
                    else {
                        $this->setLog("getCode $code - Error");
                        return false;
                    }
                }

            }
            if (!$res) {
                $this->setLog("getCode RESULT IS NULL");
                $res = true;
            }
            $this->setLog("getCode $code - OK");
            return $res;
        }
        else {
            $this->getError('getCode', __LINE__, "No resurce");
            return false;
        }
    }

    /**
     * Проверка строки адреса электронной почты
     * @param string $to - проверяемый адрес
     * @param bool $first - проверка первым регулярным выражением или вторым
     * @return bool
     */
    public function getCheck ($to, $first = true) {
        if ($to) {
            if (!is_array($to)) {
                if ($first) {
                    if (!(preg_match('/^[A-z0-9&\'\.\-_\+]+@[A-z0-9\-]+\.([A-z0-9\-]+\.)*?[A-z]+$/is', $to))) {
                        $this->getError('getCheck', __LINE__, "Incorrect mail adress: $to");
                        return false;
                    }
                    else return true;
                }
                else {
                    if (!(preg_match('/^[A-z0-9_\-]+(([^(\s\(\)<>@,;:\\<>\.\[\])]|\.)[A-z0-9_\-]+)*@[A-z0-9_]+((\-|\.)[A-z0-9_]+)*\.[A-z]{2,}$/is', $to))) {
                        $this->getError('getCheck', __LINE__, "Incorrect mail adress: $to");
                        return false;
                    }
                    else return true;
                }
            }
            else {
                $this->getError('getCheck', __LINE__, "Addresse is array: ".print_r($to, true));
                return false;
            }
        }
        else {
            $this->getError('getCheck', __LINE__, "Addresse is clear");
            return false;
        }
    }

    /**
     * Конвертация строки из кодировки WIN1251 в кодировку UTF8
     * @param string $str
     * @return string
     */
    public function getWin2Utf ($str = '') {
        if (is_callable('iconv')) {
            $str = @iconv('WINDOWS-1251', 'UTF-8', $str);
        }
        else {
            if (!sizeof($this->chars)) $this->getChars();
            $this->chars = array_flip($this->chars);
            $len = strlen($str);
            $temp = '';
            for($i = 0; $i < $len; $i++) {
                if(isset($this->chars[$str[$i]])) {
                    $key = (string) $this->chars[$str[$i]];
                    $chs = chr($key[0].$key[1].$key[2]).chr($key[3].$key[4].$key[5]);
                    $temp.=$chs;
                }
                else $temp.=$str[$i];
            }
            $this->chars = array_flip($this->chars);
            $str = $temp;
        }
        return $str;
    }

    /**
     * Конвертация строки из кодировки UTF8 в кодировку WIN1251
     * @param string $str
     * @return string
     */
    public function getUtf2Win ($str = '') {
        if (is_callable('iconv')) {
            $str = @iconv('UTF-8', 'WINDOWS-1251', $str);
        }
        else {
            if (!sizeof($this->chars)) $this->getChars();
            $len = strlen($str);
            $temp = '';
            for($i = 0; $i < $len; $i++) {
                $chcode = ord($str[$i]);
                while($i < ($len-1) && $chcode != 208 && $chcode != 209) { # skip not utf8 chars
                    $temp .= $str[$i];
                    $chcode = ord($str[++$i]);
                }
                if($i < ($len-1)) {
                    $key = (string) $chcode.ord($str[++$i]);
                    if(isset($this->chars[$key])) { # if after 208 or 209 correct char (exist as key in $chars)
                        $temp .= $this->chars[$key];
                    }
                    else $temp .= $str[$i];
                }
                else $temp .= $str[$i];
            }
            $str = $temp;
        }
        return $str;
    }

    /**
     * Инициализация массива кодировки UTF8
     */
    private function getChars () {
        $this->chars = array(
            # upper case letters
            '208144' => chr(192), '208145' => chr(193), '208146' => chr(194),
            '208147' => chr(195), '208148' => chr(196), '208149' => chr(197),
            '208129' => chr(168), '208150' => chr(198), '208151' => chr(199),
            '208152' => chr(200), '208153' => chr(201), '208154' => chr(202),
            '208155' => chr(203), '208156' => chr(204), '208157' => chr(205),
            '208158' => chr(206), '208159' => chr(207), '208160' => chr(208),
            '208161' => chr(209), '208162' => chr(210), '208163' => chr(211),
            '208164' => chr(212), '208165' => chr(213), '208166' => chr(214),
            '208167' => chr(215), '208168' => chr(216), '208169' => chr(217),
            '208170' => chr(218), '208171' => chr(219), '208172' => chr(220),
            '208173' => chr(221), '208174' => chr(222), '208175' => chr(223),
            # lower case letters
            '208176' => chr(224), '208177' => chr(225), '208178' => chr(226),
            '208179' => chr(227), '208180' => chr(228), '208181' => chr(229),
            '209145' => chr(184), '208182' => chr(230), '208183' => chr(231),
            '208184' => chr(232), '208185' => chr(233), '208186' => chr(234),
            '208187' => chr(235), '208188' => chr(236), '208189' => chr(237),
            '208190' => chr(238), '208191' => chr(239), '209128' => chr(240),
            '209129' => chr(241), '209130' => chr(242), '209131' => chr(243),
            '209132' => chr(244), '209133' => chr(245), '209134' => chr(246),
            '209135' => chr(247), '209136' => chr(248), '209137' => chr(249),
            '209138' => chr(250), '209139' => chr(251), '209140' => chr(252),
            '209141' => chr(253), '209142' => chr(254), '209143' => chr(255)
        );
    }

    /**
     * Конвертация HTML текста
     * @param string $html
     * @return string
     */
    private function getHTMLdecode ($html) {
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        $html = strtr($html, $trans);
        //supports the most used entity codes
        $html = str_replace("&nbsp;"," ",$html);
        $html = str_replace("&#380;","П",$html);
        $html = str_replace("&amp;","&",$html);
        $html = str_replace("&lt;","<",$html);
        $html = str_replace("&gt;",">",$html);
        $html = str_replace("&#728;","ў",$html);
        $html = str_replace("&#321;","Ј",$html);
        $html = str_replace("&euro;","Ђ",$html);
        $html = str_replace("&#260;","Ґ",$html);
        $html = str_replace("&trade;","™",$html);
        $html = str_replace("&copy;","©",$html);
        $html = str_replace("&reg;","®",$html);
        while(preg_match("/\s{3}/", $html)) {
            $html = preg_replace("/(\s)\s\s/", "\\1", $html);
        }
        return $html;
    }

    /**
     * Конвертация текста в формат Quoted-printable
     * @param string $line - текст
     * @return string
     */
    private function getQuotedPrint ($line) {
        if (is_array($line)) $line = implode($line);
        if (!function_exists("imap_8bit")) {
            $line = preg_replace( '/[^\x21-\x3C\x3E-\x7E\x09\x20]/e', 'sprintf( "=%02x", ord ( "$0" ) ) ;',  $line );
            preg_match_all( '/.{1,73}([^=]{0,3})?/', $line, $match );
            return implode( '=' . chr(13).chr(10), $match[0] );
        }
        else return imap_8bit($line);
    }

    /**
     * Обработка ошибок скрипта
     * @param string $function
     * @param int $line
     * @param string $message
     */
    private function getError ($function, $line, $message) {
        $this->setLog("getError");
        $this->setLog("Error in function $function. In line: $line. $message", 2);
        $error = '<span style="color:#FF0000">Error in function <b>'.$function."</b></span>\n";
        $error .= '<span style="color:#3300FF">In line: <b>'.$line."</b></span>\n";
        $error .= $message."\n";
        if ($this->debug) {
            if (defined("SITE_CHARSET")) $CODE = SITE_CHARSET;
            else $CODE = 'utf-8';
            header("Content-Type: text/html; charset=".$CODE);
            echo nl2br($error);
        }
    }

    /**
     * Возврат записей логов
     * @param int $param - параметры (0 - все логи, 1 - вызванные функции, 2 - переданные данные, 3 - ошибки, 4 - информационные сообщения)
     * @return array
     */
    public function getLogs ($param = 0) {
        switch ($param) {
            case 1:
                $log = $this->log['action'];
                break;
            case 2:
                $log = $this->log['data'];
                break;
            case 3:
                $log = $this->log['error'];
                break;
            case 4:
                $log = $this->log['info'];
                break;
            default:
                $log = $this->log;
        }
        if ($this->debug && count($log)) {
            echo "<pre>".print_r($log, true)."</pre>";
        }
        if (!$log) $log = array();
        return $log;
    }


    /**
     * Декодирование строк вида =?utf-8?B?0KHQv9GA0LDQstC+0YfQvdC40Log0JHQmNCa?= в заголовках сообщения (письма)
     * @param $string - строка для декодирования
     * Возвращает массив с ключами:
     *      'content-encoding'  - кодировка текста;
     *      'encoding-type'     - используемое кодирование в исходной строке ('B' - base64, 'Q' - Quoted-printable);
     *      'decoded-line'      - декодированная строка
     *      'old-line'          - исходная строка;
     * @return array
     */
    public function getSubjectDecode ($string) {
        $enc = 'utf-8';
        $result = array();
        if (is_string($string)) {
            $rows = preg_split("/\s/", $string);
            $type = '';
            $code = '';
            foreach ($rows as $i => $row) {
                if (preg_match("/^\=\?[^\?]+\?[^\?]+\?/", $row)) {
                    $code = preg_replace("/^\=\?([^\?]+)\?[^\?]+\?.+/", "$1", $row);
                    $type = preg_replace("/^\=\?[^\?]+\?([^\?]+)\?.+/", "$1", $row);
                    $row = preg_replace("/^(\=\?[^\?]+\?[^\?]+\?)/", "", $row);
                    if (strtoupper($type) == 'B') $rows[$i] = base64_decode($row);
                    elseif (strtoupper($type) == 'Q') {
                        if (function_exists("imap_qprint")) $rows[$i] = imap_qprint($row);
                        else {
                            $arr= array("A", "B", "C", "D", "E", "F");
                            foreach ($arr as $var) {
                                $k = 0;
                                while ($k <= 9){
                                    $row = str_replace("=".$var.$k,"%".$var.$k, $row);
                                    $k++;
                                }
                                foreach ($arr as $val) $row = str_replace("=".$var.$val,"%".$var.$val, $row);
                            }
                            $row = urldecode($row);
                            $rows[$i] = utf8_encode($row);
                        }
                    }
                }
            }
            $result['content-encoding'] = $code;
            $result['encoding-type'] = $type;
            $line = implode("", $rows);
            $cod = '';
            if (function_exists("mb_detect_encoding")) $cod = @mb_detect_encoding($line, array('utf-8', 'ascii', 'cp1251', 'KOI8-R', 'CP866', 'KOI8-U'), true);
            if (!$cod && file_exists('NetContent.php')) {
                $cod = Base::detect_encoding($line);
            }
            elseif (!$cod) $cod = @mb_detect_encoding($line, mb_detect_order(), true);
            if ($cod && strtoupper($cod) != strtoupper($enc)) $line = @mb_convert_encoding($line, strtoupper($enc), strtoupper($cod));
            $result['decoded-line'] = $line;
            $result['old-line'] = $string;
        }
        else $result = $string;
        return $result;
    }

    /**
     * Получение списка папок IMAP
     * @param string $folder - папка, относительно которой выбирается список, если не указано, то от корневой
     * @return array
     */
    public function getImapFolders ($folder = '') {
        $result = array();
        if ($this->getIMAPConnect()) {
            if ($folder) $param = $folder.'/*';
            else $param = '*';
            $list = imap_list($this->imap, "{".$this->server."}", $param);
            foreach ($list as $key=>$name) {
                $name = preg_replace("/{".$this->server."}/", '', $name);
                $list[$key] = $this->imap_decode($name);
            }
            $result['boxes'] = $list;
        }
        else {
            $result['error'] = true;
            $result['error_info'] = 'Error connection to Mail Server';
        }
        return $result;
    }

    /**
     * Декодирование строки IMAP при работе с почтовыми ящиками
     * Если имя ящика кирилицей или иной кодировке, отличной от ISO_8859-1, то возвращается строка вида '[Gmail]/&BBIEMAQ2BD0EPgQ1-'
     * Для декодирования используем эту функцию
     * @param string $line - строка для декодирования
     * @return string
     */
    public function imap_decode($line) {
        $res='';
        $n = strlen($line);
        $h = 0;
        while($h < $n) {
            $t = strpos($line,'&',$h);
            if ($t === false) $t=$n;
            $res .= substr($line, $h,$t-$h);
            $h = $t+1;
            if ($h >= $n) break;
            $t = strpos($line,'-',$h);
            if ($t === false) $t = $n;
            $k = $t - $h;
            if ($k == 0) $res .= '&';
            else $res .= $this->decode_b64imap(substr($line, $h, $k));
            $h = $t + 1;
        }
        return $res;
    }

    /**
     * Кодирование имени ящика кирилицей или в иной кодировке, отличной от ISO_8859-1 в формат вида '[Gmail]/&BBIEMAQ2BD0EPgQ1-'
     * @param $line - строка для кодировки
     * @return string
     */
    public function imap_encode($line) {
        $n = strlen($line);
        $err = 0;
        $buf ='';
        $res ='';
        for($i = 0; $i<$n;) {
            $x = ord($line[$i++]);
            if (($x & 0x80) == 0x00) { $r = $x; $w = 0; }
            else if (($x & 0xE0) == 0xC0) { $w = 1; $r = $x & 0x1F; }
            else if (($x & 0xF0) == 0xE0) { $w = 2; $r = $x & 0x0F; }
            else if (($x & 0xF8) == 0xF0) { $w = 3; $r = $x & 0x07; }
            else if (($x & 0xFC) == 0xF8) { $w = 4; $r = $x & 0x03; }
            else if (($x & 0xFE) == 0xFC) { $w = 5; $r = $x & 0x01; }
            else if (($x & 0xC0) == 0x80) { $w = 0; $r =- 1; $err++; }
            else {
                $w = 0;
                $r =- 2;
                $err++;
            }
            for($k = 0; $k < $w && $i < $n; $k++) {
                $x = ord($line[$i++]);
                if ($x & 0xE0 != 0x80) $err++;
                $r = ($r << 6)|($x & 0x3F);
            }
            if ($r<0x20 || $r>0x7E ) {
                $buf .= chr(($r >> 8) & 0xFF);
                $buf .= chr($r & 0xFF);
            }
            else {
                if (strlen($buf)) {
                    $res .= '&'.$this->encode_b64imap($buf).'-';
                    $buf = '';
                }
                if ($r == 0x26) $res .= '&-';
                else $res.=chr($r);
            }
        }
        if (strlen($buf)) $res.='&'.$this->encode_b64imap($buf).'-';
        return $res;
    }

    /**
     * Вспомогательная функция для декодирования строки функцией imap_decode()
     * @param $line
     * @return string
     */
    private function decode_b64imap($line) {
        $a = 0;
        $al = 0;
        $res = '';
        $n = strlen($line);
        for($i = 0; $i < $n; $i++) {
            $k = strpos($this->imap_base64, $line[$i]);
            if ($k === FALSE) continue;
            $a = ($a << 6) | $k;
            $al += 6;
            if ($al >= 8) {
                $res .= chr(($a >> ($al - 8)) & 255);
                $al -= 8;
            }
        }
        $result = '';
        $n = strlen($res);
        for($i = 0; $i < $n; $i++) {
            $c = ord($res[$i]);
            $i++;
            if ($i < $n) $c = ($c << 8) | ord($res[$i]);
            $result .= $this->encode_utf8_char($c);
        }
        return $result;
    }

    /**
     * Вспомогательная функция для кодирования строки функцией imap_encode()
     * @param $line - строка
     * @return string
     */
    private function encode_b64imap($line) {
        $a = 0;
        $al = 0;
        $result = '';
        $n = strlen($line);
        for($i = 0; $i < $n; $i++) {
            $a = ($a << 8) | ord($line[$i]);
            $al += 8;
            for(; $al >= 6; $al -= 6) $result .= $this->imap_base64[($a >> ($al-6)) & 0x3F];
        }
        if ($al > 0) $result .= $this->imap_base64[($a << (6 - $al)) & 0x3F];
        return $result;
    }

    /**
     * Определение кода символа для функций кодирования и декодирования строк IMAP
     * @param $w - символ
     * @return string
     */
    private function encode_utf8_char($w) {
        if ($w & 0x80000000) return '';
        if ($w & 0xFC000000) $n = 5;
        else if ($w & 0xFFE00000) $n = 4;
        else if ($w & 0xFFFF0000) $n = 3;
        else if ($w & 0xFFFFF800) $n = 2;
        else if ($w & 0xFFFFFF80) $n = 1;
        else return chr($w);
        $result = chr(( (255 << (7 - $n)) | ($w >> ($n * 6))) & 255);
        while(--$n >= 0) $result .= chr((($w >> ($n * 6)) & 0x3F) | 0x80);
        return $result;
    }


    ////////////////////////////////////////////////////////////////
    //                          Other block                       //
    ////////////////////////////////////////////////////////////////

    /**
     * Очистка списка получателей
     */
    public function clearTo () {
        $this->to = array();
    }

    /**
     * Очистка списка скрытых получателей
     */
    public function clearBcc () {
        $this->to = array();
    }

    /**
     * Очистка списка файлов
     */
    public function clearFiles () {
        $this->files = array();
    }

    /**
     * Закрытие подключения через сокет
     */
    private function closeSocket () {
        $this->setLog("closeSocket");
        if (is_resource($this->socket)) {
            $this->setLog(">>> QUIT", 1);
            fputs($this->socket, "QUIT\n");
            fclose($this->socket);
        }
    }
}