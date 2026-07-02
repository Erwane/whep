# Mailgun provider

Webhook handler for [Mailgun](https://www.mailgun.com/) (Sinch) emailing provider.

## Warning  
Mailgun provider require additional vendor.

```
composer require dflydev/dot-access-data:"^3.0"
```

## Usage

```php
use WHEP\Exception\SecurityException;  
use WHEP\Exception\WHEPException;  
use WHEP\Factory;  

try {
    $provider = Factory::provider('mailgun', [
        'signing_key' => 'my-private-signing-key',
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
} catch (SecurityException $e) {
    // log ?
} catch (WHEPException $e) {
    // log ?
}
```

## Signing key security

You should pass your mailgun private key with `signing_key` option.

```php
Factory::provider('mailgun', ['signing_key' => 'my-private-signing-key']);
```

The validation is done during `ProviderInterface::process()`
