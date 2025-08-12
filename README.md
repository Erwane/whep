# Webhooks Handler for Emailing providers

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![codecov](https://codecov.io/gh/Erwane/whep-client/branch/2.x/graph/badge.svg?token=L98IZZFBY2)](https://codecov.io/gh/Erwane/whep-client)
[![Build Status](https://github.com/Erwane/whep-client/actions/workflows/ci.yml/badge.svg?branch=2.x)](https://github.com/Erwane/whep-client/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Erwane/whep-client)](https://packagist.org/packages/Erwane/whep-client)
[![Packagist Version](https://img.shields.io/packagist/v/Erwane/whep-client)](https://packagist.org/packages/Erwane/whep-client)

This is the base project to easily handle webhooks sent by different emailing providers and uniformizing in
a comprehensive object.

This project is not made to be used alone, you need to pick your providers handlers corresponding to your project.

## Available providers handlers

| Provider                                | Package                                                       |
|-----------------------------------------|---------------------------------------------------------------|
| [Brevo](https://www.brevo.com/)         | [erwane/whep-brevo](https://github.com/Erwane/whep-brevo)     |
| [Mailjet](https://www.mailjet.com/)     | [erwane/whep-mailjet](https://github.com/Erwane/whep-mailjet) |
| [Postal](https://docs.postalserver.io/) | [erwane/whep-postal](https://github.com/Erwane/whep-postal)   |

## Usage

```shell
composer require erwane/whep-<provider>
```

```php
use WHEP\Factory;  
use WHEP\Exception\SecurityException;  
use WHEP\Exception\WHEPException;  

try {
    $provider = Factory::provider('<provider>', [
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null, // Use your framework correct method to get the client ip.
        'callbacks' => [
            ProviderInterface::EVENT_BLOCKED => [$this, 'callbackInvalidate'],
            ProviderInterface::EVENT_BOUNCE_HARD => [$this, 'callbackInvalidate'],
            ProviderInterface::EVENT_BOUNCE_QUOTA => [$this, 'callbackUnsub'],
        ],
    ]);

    // process the data.
    $provider->process($webhookData);
    
    // Data available from provider getters.
    $recipient = $provider->getRecipient();
    
    // Launch callback
    $provider->callback();
} catch (SecurityException $e) {
    // log ?
} catch (WHEPException $e) {
    // log ?
}
```

## Provider options

You can pass options to `Factory::provider('<provider>', $options)` method.  
All available options are:
* `client_ip`: The client IP who request your url. Default `null`
* `allowed_ip`: Array of IPv4/IPv6 network (range) and allowed IP. Default depends on provider.
* `check_ip`: Set to false to bypass security IP check. Default is `false`.
* `signing_key`: Your provider private key to validate request came from trusted provider. Default `null`
* `callbacks`: You `callable` you want to be called, depends on event type.

### Security
Except if your webhook url has a security token, you can't ensure the webhook really came from trusted provider.  
Some providers use a signing key to validate data or provide an IP addresses list.

#### IP validation
When provider publish his IP addresses, you should pass the webhook client IP to the provider.
```php
Factory::provider('mailjet', ['client_ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
```

When provider is self-hosted, like [Postal](https://docs.postalserver.io/), you can pass your postal server IP.
```php
Factory::provider('postal', [
    'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'allowed_ip' => [
        '10.0.0.1',
        'fe80::0023:1',
        '192.168.0.1/24',
    ],
]);
```

You can bypass IP check with `check_ip` sets to `false`.
```php
Factory::provider('mailjet', ['check_ip' => false]);
```

#### Signing key

When provider support signing key, you can pass your private key with `signing_key` option.

```php
Factory::provider('mailgun', ['signing_key' => 'my-private-signing-key']);
```
The validation is done during `ProviderInterface::process()`

### Callbacks
Your callback method are cast when `$provider->callback()` is called (you decide when).
See [Available callbacks](#available-callbacks) section for details.

```php
Factory::provider('<provider>', ['callbacks' => [ProviderInterface::EVENT_UNSUB => [$this, 'callbackUnsub']]]);
```

#### Available callbacks

You can configure one callback by event type. Available callbacks are:

| Event                                   | Why event was emitted                           |
|-----------------------------------------|-------------------------------------------------|
| `ProviderInterface::EVENT_REQUEST`      | You send an e-mail to your provider.            |
| `ProviderInterface::EVENT_DEFERRED`     | The send was deferred by provider.              |
| `ProviderInterface::EVENT_BLOCKED`      | The recipient e-mail is in provider blocklist.  |
| `ProviderInterface::EVENT_SENT`         | E-mail was sent.                                |
| `ProviderInterface::EVENT_BOUNCE_SOFT`  | E-mail receive a soft-bounce (4xx) with reason. |
| `ProviderInterface::EVENT_BOUNCE_QUOTA` | Like BOUNCE_SOFT but quota problem detected.    |
| `ProviderInterface::EVENT_BOUNCE_HARD`  | E-mail receive a hard-bounce (5xx) with reason. |
| `ProviderInterface::EVENT_OPENED`       | E-mail was opened.                              |
| `ProviderInterface::EVENT_CLICK`        | A link was clicked.                             |
| `ProviderInterface::EVENT_ABUSE`        | Recipient report your e-mail as abuse.          |
| `ProviderInterface::EVENT_UNSUB`        | Recipient want to unsubscribed from you list.   |
| `ProviderInterface::EVENT_ERROR`        | Provider error.                                 |

## Provider getters

### getName(): string

Return provider name.

### getTime(): \DateTimeInterface

Get when event time was received by your webhook listener. This is not the emit time.

### getType(): ?string

Event type. Match it with `\WHEP\ProviderInterface::EVENT_xyz` constants.

### getRecipient(): ?string

The event related e-mail recipient.

### getDetails(): ?string

Event detail provided by event emitter. The content depends on the provider.

### getSmtpResponse(): ?string

Recipient MX SMTP response.

### getUrl(): ?string

The target click url. For `\WHEP\ProviderInterface::EVENT_CLICK` only.

### getRaw(bool $asJson = false)

Event raw data as array. Is `$asJson` is `true` return json string.
