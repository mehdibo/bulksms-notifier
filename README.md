# bulksms-notifier
[Symfony Notifier bridge](https://symfony.com/doc/current/notifier.html) to use [Bulksms.ma](https://bulksms.ma)

## Requirements
[PHP 7.4](https://www.php.net/releases/7_4_0.php) or higher

## Installation

Use composer to require the library:

```bash
composer require mehdibo/bulksms-notifier
```

Add the token to your `.env` file

```
BULKSMS_DSN=bulksms://token_here@default?shortcode=a_shortcode
```

Add `bulksms` to `config/packages/notifier.yaml`:
```yaml
framework:
    notifier:
        texter_transports:
            bulksms: '%env(BULKSMS_DSN)%' # Add this line
```

Add this to `config/services.yaml`:
```yaml
notifier.transport_factory.bulksms:
        class: Mehdibo\Symfony\Notifier\Bridge\Bulksms\BulksmsTransportFactory
        tags: ['texter.transport_factory']
```