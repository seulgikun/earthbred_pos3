<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class BrevoTransport extends Transport
{
    /**
     * The Brevo API Key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function __construct($apiKey, Client $client = null)
    {
        $this->apiKey = $apiKey;
        $this->client = $client ?: new Client([
            'timeout' => 15,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        // Format recipient list for Brevo API
        $toList = [];
        foreach ((array) $message->getTo() as $email => $name) {
            $item = ['email' => $email];
            if ($name) {
                $item['name'] = $name;
            }
            $toList[] = $item;
        }

        // Format sender
        $fromList = (array) $message->getFrom();
        $fromEmail = array_key_first($fromList);
        $fromName = $fromList[$fromEmail] ?? null;

        if (!$fromEmail) {
            $fromEmail = config('mail.from.address', 'christopherlim1995@gmail.com');
            $fromName = config('mail.from.name', 'Earthbred POS');
        }

        $sender = ['email' => $fromEmail];
        if ($fromName) {
            $sender['name'] = $fromName;
        }

        $payload = [
            'sender' => $sender,
            'to' => $toList,
            'subject' => $message->getSubject() ?: 'Notification',
            'htmlContent' => $message->getBody() ?: '',
        ];

        // Add CC if present
        $ccList = [];
        foreach ((array) $message->getCc() as $email => $name) {
            $item = ['email' => $email];
            if ($name) $item['name'] = $name;
            $ccList[] = $item;
        }
        if (!empty($ccList)) {
            $payload['cc'] = $ccList;
        }

        // Add BCC if present
        $bccList = [];
        foreach ((array) $message->getBcc() as $email => $name) {
            $item = ['email' => $email];
            if ($name) $item['name'] = $name;
            $bccList[] = $item;
        }
        if (!empty($bccList)) {
            $payload['bcc'] = $bccList;
        }

        // Add Reply-To if present
        $replyToList = (array) $message->getReplyTo();
        if (!empty($replyToList)) {
            $replyEmail = array_key_first($replyToList);
            $payload['replyTo'] = ['email' => $replyEmail];
        }

        try {
            $response = $this->client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $this->sendPerformed($message);
            return $this->numberOfRecipients($message);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            \Log::error('Brevo API send error response: ' . $errBody);
            throw new \Exception('Brevo API Error: ' . $errBody, $e->getCode(), $e);
        } catch (\Throwable $e) {
            \Log::error('Brevo API send error: ' . $e->getMessage());
            throw $e;
        }
    }
}
