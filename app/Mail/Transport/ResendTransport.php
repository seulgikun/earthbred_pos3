<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class ResendTransport extends Transport
{
    /**
     * The Resend API Key.
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

        $to = array_keys((array) $message->getTo());
        $cc = array_keys((array) $message->getCc());
        $bcc = array_keys((array) $message->getBcc());

        $fromList = (array) $message->getFrom();
        $fromEmail = array_key_first($fromList);
        $fromName = $fromList[$fromEmail] ?? null;

        if (!$fromEmail) {
            $fromEmail = config('mail.from.address', 'onboarding@resend.dev');
            $fromName = config('mail.from.name', 'Earthbred POS');
        }

        $fromFormatted = $fromName ? "{$fromName} <{$fromEmail}>" : $fromEmail;

        $payload = [
            'from' => $fromFormatted,
            'to' => $to,
            'subject' => $message->getSubject() ?: 'Notification',
            'html' => $message->getBody() ?: '',
        ];

        if (!empty($cc)) {
            $payload['cc'] = $cc;
        }

        if (!empty($bcc)) {
            $payload['bcc'] = $bcc;
        }

        $replyTo = array_keys((array) $message->getReplyTo());
        if (!empty($replyTo)) {
            $payload['reply_to'] = $replyTo;
        }

        try {
            $response = $this->client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $this->sendPerformed($message);
            return $this->numberOfRecipients($message);
        } catch (\Throwable $e) {
            \Log::error('Resend API send error: ' . $e->getMessage());
            throw $e;
        }
    }
}
