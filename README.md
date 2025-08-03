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

| Provider                                | Package             |
|-----------------------------------------|---------------------|
| [Brevo](https://www.brevo.com/)         | erwane/whep-brevo   |
| [Mailjet](https://www.mailjet.com/)     | erwane/whep-mailjet |
| [Postal](https://docs.postalserver.io/) | erwane/whep-postal  |

## Usage

```shell
composer require erwane/whep-<provider>
```

```php
use WHEP\Client;

$provider = Client::getProvider('<provider>', [
    'callbacks' => [
        ProviderInterface::EVENT_BLOCKED => [$this, 'callbackInvalidate'],
        ProviderInterface::EVENT_BOUNCE_HARD => [$this, 'callbackInvalidate'],
        ProviderInterface::EVENT_BOUNCE_QUOTA => [$this, 'callbackUnsub'],
    ],
]);

try {
    // process the data.
    $provider->process($webhookData);
    
    // Data available from provider getters.
    $email = $provider->getRecipient();
    
    // Launch callbacks
    $provider->callback();
} catch (WebhookProviderException $e) {
    // log ?
}
```

## Available callbacks

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
