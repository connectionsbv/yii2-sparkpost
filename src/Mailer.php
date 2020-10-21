<?php

namespace connectionsbv\sparkpost;

use GuzzleHttp\Client;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use SparkPost\SparkPost;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    public $token;
    public $apiUri = 'api.eu.sparkpost.com';
    public $messageClass = 'connectionsbv\sparkpost\Message';

    /**
     * @param \yii\mail\MessageInterface $message
     * @return messageId on success, null on failure
     * @throws InvalidConfigException
     */
    public function sendMessage($message)
    {
        if ($this->token === null) {
            throw new InvalidConfigException('Token is missing');
        }

        $httpClient = new GuzzleAdapter(new Client());
        $sparky = new SparkPost(
          $httpClient,
          [
            'key' => $this->token,
            'host' => $this->apiUri,
            'async' => false,
          ]
        );

        // build message
        $messageData = [
          'content' => [
            'from' => $message->getFrom(),
            'headers' => $message->getHeaders(),
            'reply_to' => $message->getReplyTo(),
            'subject' => $message->getSubject(),
            'html' => $message->getHtmlBody(),
            'text' => $message->getTextBody(),
            'attachments' => $message->getAttachments(),
          ],
          'return_path' => $message->getReturnPath(),
          'recipients' => $message->getTo(),
          'options' => [
            'transactional' => $message->getTransactional(),
            'open_tracking' => $message->getTrackOpens(),
          ],
        ];

        // send message
        $response = $sparky->transmissions->post($messageData);

        if ($response->getStatusCode() == 200) { // message accepted
            $responseBody = $response->getBody();
            return $responseBody['results']['id'] ?? null;
        }

        return null;
    }
}