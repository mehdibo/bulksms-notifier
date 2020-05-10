<?php

namespace Mehdibo\Symfony\Notifier\Bridge\Bulksms;

use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

class BulksmsTransportFactory extends AbstractTransportFactory
{

    protected function getSupportedSchemes(): array
    {
        return ['bulksms'];
    }

    public function create(Dsn $dsn): TransportInterface
    {
        // bulksms://token@default?shortcode=xxx
        $scheme = $dsn->getScheme();
        $token = $this->getUser($dsn);
        $shortcode = $dsn->getOption('shortcode');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        if ($scheme === 'bulksms') {
            return (new BulksmsTransport($token, $shortcode, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        throw new UnsupportedSchemeException($dsn, 'bulksms', $this->getSupportedSchemes());
    }
}