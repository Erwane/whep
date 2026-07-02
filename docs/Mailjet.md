# Mailjet provider

Webhook handler for [Mailjet](https://www.mailjet.com/) (Sinch) emailing provider.

## Usage

```php
use WHEP\Exception\SecurityException;  
use WHEP\Exception\WHEPException;  
use WHEP\Factory;  

try {
    $provider = Factory::provider('mailjet', [
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
} catch (SecurityException $e) {
    // log ?
} catch (WHEPException $e) {
    // log ?
}
```

## IP Validation

You should pass the mailjet webhook remote IP to the provider.

```php
Factory::provider('mailjet', ['remote_ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
```

You can bypass IP check with `check_ip` sets to `false`.

```php
Factory::provider('mailjet', ['check_ip' => false]);
```
