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
        $endpoint = sprintf("https://%s//developer/sms/send", $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'body' => $body,
        ]);
        if ($response->getStatusCode() !== 200)
        {
            $error = $response->toArray(false);
            throw new TransportException('Unable to send the SMS: '.$error['error'].'', $response);
        }
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }
}