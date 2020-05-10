<?php


namespace Mehdibo\Symfony\Notifier\Bridge\Bulksms;


use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BulksmsTransport extends AbstractTransport
{
    protected const HOST = 'bulksms.ma';

    private string $token;
    private ?string $shortcode;

    public function __construct(string $token, ?string $shortcode, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->token = $token;
        $this->shortcode = $shortcode;
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('bulksms://%s', $this->getEndpoint());
    }

    protected function doSend(MessageInterface $message): void
    {
        if (!$message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $body = [
            'token' => $this->token,
            'tel' => $message->getPhone(),
            'message' => $message->getSubject(),
        ];
        if ($this->shortcode !== NULL)
            $body['shortcode'] = $this->shortcode;
        $endpoint = sprintf("https://%s/developer/sms/send", $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'body' => $body,
        ]);
        // We dont use $response->toArray() because the API always sends an html content type (even with Json)
        $responseBody = trim($response->getContent());
        $responseBody = json_decode($responseBody);
        // We don't check for the status code because the API sucks and it always sends 200
        if (!isset($responseBody->success) || $responseBody->success !== 1)
        {
            $error = $responseBody->error;
            throw new TransportException('Unable to send the SMS: '.$error.'', $response);
        }
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }
}