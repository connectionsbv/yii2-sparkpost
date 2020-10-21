<?php

namespace connectionsbv\sparkpost;

use Exception;
use yii\base\NotSupportedException;
use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    protected $from;
    protected $to = [];
    protected $replyTo;
    protected $cc = [];
    protected $bcc = [];
    protected $returnPath;
    protected $subject;
    protected $textBody;
    protected $htmlBody;
    protected $attachments = [];
    protected $tag;
    protected $trackOpens = false;
    protected $transactional = true;
    protected $showToInHeader = true;
    protected $headers = [];
    protected $charset = 'utf-8';

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        throw new NotSupportedException();
    }

    public function getFrom()
    {
        $from = [];
        foreach ($this->from as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $from = ['name' => $name, 'email' => $email];
        }

        return $from;
    }

    public function setFrom($from)
    {
        if (is_string($from) === true) {
            $from = [$from];
        }
        $this->from = $from;
        return $this;
    }

    public function getTo()
    {
        $emailsString = $this->showToInHeader ? self::stringifyEmails($this->to) : null;

        $to = [];
        foreach (array_merge($this->to, $this->cc, $this->bcc) as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $to[] = ['address' => ['name' => $name, 'email' => $email, 'header_to' => $emailsString]];
        }

        if (!empty($this->cc)) {
            $this->headers['CC'] = self::stringifyEmails($this->cc);
        }

        return $to;
    }

    public function setTo($to)
    {
        if (is_string($to) === true) {
            $to = [$to];
        }
        $this->to = $to;
        return $this;
    }

    /**
     * @param array|string $emailsData email can be defined as string. In this case no transformation is done
     *                                 or as an array ['email@test.com', 'email2@test.com' => 'Email 2']
     * @return string|null
     * @since XXX
     */
    public static function stringifyEmails($emailsData)
    {
        $emails = null;
        if (empty($emailsData) === false) {
            if (is_array($emailsData) === true) {
                foreach ($emailsData as $key => $email) {
                    if (is_int($key) === true) {
                        $emails[] = $email;
                    } else {
                        if (preg_match('/[.,:]/', $email) > 0) {
                            $email = '"' . $email . '"';
                        }
                        $emails[] = $email . ' ' . '<' . $key . '>';
                    }
                }
                $emails = implode(', ', $emails);
            } elseif (is_string($emailsData) === true) {
                $emails = $emailsData;
            }
        }
        return $emails;
    }

    public function getReplyTo()
    {
        return self::stringifyEmails($this->replyTo);
    }

    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function getCc()
    {
        $cc = [];
        foreach ($this->cc as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $cc[] = ['address' => ['name' => $name, 'email' => $email]];
        }
        return $cc;
    }

    public function setCc($cc)
    {
        if (is_string($cc) === true) {
            $cc = [$cc];
        }
        $this->cc = $cc;
        return $this;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function setBcc($bcc)
    {
        if (is_string($bcc) === true) {
            $bcc = [$bcc];
        }
        $this->bcc = $bcc;
        return $this;
    }

    public function getReturnPath()
    {
        return $this->returnPath;
    }

    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null text body of the message
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    public function setTextBody($text)
    {
        $this->textBody = $text;
        return $this;
    }

    /**
     * @return string|null html body of the message
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;
        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function getTrackOpens()
    {
        return $this->trackOpens;
    }

    public function setTrackOpens($trackOpens)
    {
        $this->trackOpens = $trackOpens;
        return $this;
    }

    public function getTransactional()
    {
        return $this->transactional;
    }

    public function setTransactional($transactional)
    {
        $this->transactional = $transactional;
        return $this;
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    public function getHeaders()
    {
        return empty($this->headers) ? null : $this->headers;
    }

    public function getAttachments()
    {
        if (empty($this->attachments)) {
            return null;
        }
        $attachments = [];
        foreach ($this->attachments as $attachment) {
            $attachments[] = [
              'name' => $attachment['Name'],
              'type' => $attachment['ContentType'],
              'data' => $attachment['Content'],
            ];
        }
        return $attachments;
    }

    public function attach($fileName, array $options = [])
    {
        $attachment = [
          'Content' => base64_encode(file_get_contents($fileName))
        ];
        if (!empty($options['fileName'])) {
            $attachment['Name'] = $options['fileName'];
        } else {
            $attachment['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
        }
        if (!empty($options['contentType'])) {
            $attachment['ContentType'] = $options['contentType'];
        } else {
            $attachment['ContentType'] = 'application/octet-stream';
        }
        $this->attachments[] = $attachment;
        return $this;
    }

    public function attachContent($content, array $options = [])
    {
        $attachment = [
          'Content' => base64_encode($content)
        ];
        if (!empty($options['fileName'])) {
            $attachment['Name'] = $options['fileName'];
        } else {
            throw new Exception('Filename is missing');
        }
        if (!empty($options['contentType'])) {
            $attachment['ContentType'] = $options['contentType'];
        } else {
            $attachment['ContentType'] = 'application/octet-stream';
        }
        $this->attachments[] = $attachment;
        return $this;
    }

    public function embed($fileName, array $options = [])
    {
        $embed = [
          'Content' => base64_encode(file_get_contents($fileName))
        ];
        if (!empty($options['fileName'])) {
            $embed['Name'] = $options['fileName'];
        } else {
            $embed['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
        }
        if (!empty($options['contentType'])) {
            $embed['ContentType'] = $options['contentType'];
        } else {
            $embed['ContentType'] = 'application/octet-stream';
        }
        $embed['ContentID'] = 'cid:' . uniqid();
        $this->attachments[] = $embed;
        return $embed['ContentID'];
    }

    public function embedContent($content, array $options = [])
    {
        $embed = [
          'Content' => base64_encode($content)
        ];
        if (!empty($options['fileName'])) {
            $embed['Name'] = $options['fileName'];
        } else {
            throw new Exception('Filename is missing');
        }
        if (!empty($options['contentType'])) {
            $embed['ContentType'] = $options['contentType'];
        } else {
            $embed['ContentType'] = 'application/octet-stream';
        }
        $embed['ContentID'] = 'cid:' . uniqid();
        $this->attachments[] = $embed;
        return $embed['ContentID'];
    }

    public function toString()
    {
        return serialize($this);
    }
}