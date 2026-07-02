# Postal provider

Webhook handler for [postal](https://docs.postalserver.io/) emailing provider.

## Usage

```php
use WHEP\Exception\IpException;  
use WHEP\Exception\ProviderException;  
use WHEP\Factory;

try {
    $provider = Factory::provider('postal', [
        'allowed_ip' => ['my.postal.server.ipv4', 'my:postal:server::ipv6'],
        'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? null, // Use method from your framework to get the ServerRequest client ip.
        'callbacks' => [
            ProviderInterface::EVENT_BLOCKED => [$this, 'callbackInvalidate'],
            ProviderInterface::EVENT_BOUNCE_QUOTA => [$this, 'callbackUnsub'],
        ],
    ]);

    // process the data.
    $provider->process($webhookData);
    
    // Data available from provider getters.
    $recipient = $provider->getRecipient();
    
    // Launch callbacks
    $provider->callback();
} catch (IpException $e) {
    // log ?
} catch (ProviderException $e) {
    // log ?
}
```

## IP Validation

Add your self-hosted postal IP to `allowed_ip`.

```php
Factory::provider('postal', [
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'allowed_ip' => [
        '10.0.0.1',
        'fe80::0023:1',
        '192.168.0.1/24',
    ],
]);
```

You can bypass IP check with `check_ip` sets to `false`.
