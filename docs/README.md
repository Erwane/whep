# Webhooks Handler for Emailing Providers documentation

## Examples

* [Brevo](https://www.brevo.com/): [WHEP documentation](Brevo.md)
* [Mailgun](https://www.mailgun.com/): [WHEP documentation](Mailgun.md)
* [Mailjet](https://www.mailjet.com/): [WHEP documentation](Mailjet.md)
* [Postal](https://docs.postalserver.io/): [WHEP documentation](Postal.md)

## Options

You can pass options to `Factory::provider('<provider>', $options)` method.  
All available options are:

* `remote_ip`: The remote IP who request your url. Default `null`
* `allowed_ip`: Array of IPv4/IPv6 network (range) and allowed IP. Default depends on provider.
* `check_ip`: Set to false to bypass security IP check. Default is `false`.
* `signing_key`: Your provider private key to validate request came from trusted provider. Default `null`
* `callbacks`: You `callable` you want to be called, depends on event type.

### Security

Except if your webhook url has a security token, you can't ensure the webhook really came from trusted provider.  
Some providers use a signing key to validate data or provide an IP addresses list.

#### IP validation

When provider publish his IP addresses, you should pass the webhook remote IP to the provider.

```php
Factory::provider('mailjet', ['remote_ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
```

When provider is self-hosted, like [Postal](Postal.md), you can pass your postal server IP.

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
See [Event type & Callbacks](#event-type--callbacks) section for details.

```php
Factory::provider('<provider>', ['callbacks' => [ProviderInterface::EVENT_UNSUB => [$this, 'callbackUnsub']]]);
```

#### Event type & Callbacks

You can configure one callback by event type. Available callbacks are:

| Event                                   | Why event was emitted                                      |
|-----------------------------------------|------------------------------------------------------------|
| `ProviderInterface::EVENT_REQUEST`      | You send an e-mail to your provider.                       |
| `ProviderInterface::EVENT_DEFERRED`     | The send was deferred by provider.                         |
| `ProviderInterface::EVENT_BLOCKED`      | The recipient e-mail is in provider blocklist.             |
| `ProviderInterface::EVENT_SENT`         | E-mail was sent.                                           |
| `ProviderInterface::EVENT_BOUNCE_SOFT`  | E-mail receive a soft-bounce (4xx) with reason.            |
| `ProviderInterface::EVENT_BOUNCE_QUOTA` | Like BOUNCE_SOFT but quota problem detected.               |
| `ProviderInterface::EVENT_BOUNCE_HARD`  | E-mail receive a hard-bounce (5xx) with reason.            |
| `ProviderInterface::EVENT_OPENED`       | E-mail was opened.                                         |
| `ProviderInterface::EVENT_CLICK`        | A link was clicked.                                        |
| `ProviderInterface::EVENT_ABUSE`        | Recipient report your e-mail as abuse.                     |
| `ProviderInterface::EVENT_UNSUB`        | Recipient want to unsubscribed from you list.              |
| `ProviderInterface::EVENT_BLOCKLIST`    | You provider IP is in MX recipient blocklist (spam/dnsbl). |
| `ProviderInterface::EVENT_ERROR`        | Provider error.                                            |

## Methods

ProviderInterface has the following methods:

- [getName()](#getname)
- [getTime()](#gettime)
- [getType()](#gettype)
- [getRecipient()](#getrecipient)
- [getDetails()](#getdetails)
- [getSmtpResponse()](#getsmtpresponse)
- [getUrl()](#geturl)
- [getRaw()](#getraw)
- [process()](#process)
- [callback()](#callback)
- [securityChecked()](#securitychecked)

### getName()

Return provider name.

```php
echo $provider->getName();
```

### getTime()

Get event time as `\DateTimeInterface`. This represents when hook was received, not event time.

```php
$time = $provider->getTime();
```

### getType()

Return event type. See [Event type & Callbacks](#event-type--callbacks) for all types.

```php
if ($provider->getType() === \WHEP\ProviderInterface::EVENT_UNSUB) {
    // Do something
}
```

### getRecipient()

Return event related e-mail recipient.

```php
echo $provider->getRecipient();
```

### getDetails()

Return provider event details (or reason).

```php
echo $provider->getDetails();
```

### getSmtpResponse()

Return recipient MX SMTP response.

```php
echo $provider->getSmtpResponse();
```

### getUrl()

Return url of clicked link. Available for `\WHEP\ProviderInterface::EVENT_CLICK` only.  
Some providers (mailgun) do not return this information.

```php
echo $provider->getUrl();
```

### getRaw()

Return event raw data as array by default. Return as json if `$asJson` is `true`.

```php
$raw = $provider->getRaw();

// raw data in JSON format.
echo $provider->getRaw(true);
```

### process()

Process the webhook data. This method is chainable.

```php
$provider = \WHEP\Factory::provider('mailgun')
    ->process($webhookData);
```

### callback()

Run you related event type callable if configured.

```php
// This will process data and call self::callbackUnsub($provider) if event is unsub.
$provider = \WHEP\Factory::provider('mailgun', [
    'callbacks' => [
        \WHEP\ProviderInterface::EVENT_UNSUB => [$this, 'callbackUnsub'],
    ],
])
    ->process($webhookData)
    ->callback();
```

### securityChecked()

Return true if security was checked. Default to `false`.

```php
if (!$provider->securityChecked()) {
    // Your webhook url deserve security.
}
```
