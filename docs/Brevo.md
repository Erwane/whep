# Brevo provider

Webhook handler for [Brevo](https://www.brevo.com/) (SendInBlue) emailing provider.

## Usage

```php
use WHEP\Exception\SecurityException;  
use WHEP\Exception\WHEPException;  
use WHEP\Factory;  

try {
    $provider = Factory::provider('brevo', [
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
