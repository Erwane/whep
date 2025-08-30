<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace WHEP;

use DateTimeInterface;

/**
 * Webhooks handler emailing provider interface.
 */
interface ProviderInterface
{
    public const EVENT_DEFERRED = 'deferred';
    public const EVENT_BOUNCE_SOFT = 'soft_bounce';
    public const EVENT_BOUNCE_HARD = 'hard_bounce';
    public const EVENT_BOUNCE_QUOTA = 'quota';
    public const EVENT_BLOCKED = 'blocked';
    public const EVENT_REQUEST = 'request';
    public const EVENT_SENT = 'sent';
    public const EVENT_OPENED = 'opened';
    public const EVENT_CLICK = 'click';
    public const EVENT_ABUSE = 'abuse';
    public const EVENT_UNSUB = 'unsubscribed';
    public const EVENT_ERROR = 'error';
    public const EVENT_BLOCKLIST = 'blocklist';

    /**
     * Get provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get event time.
     *
     * @return \DateTimeInterface|null
     */
    public function getTime(): ?DateTimeInterface;

    /**
     * Get event type.
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Get recipient e-mail.
     *
     * @return string|null
     */
    public function getRecipient(): ?string;

    /**
     * Get event provider detail.
     *
     * @return string|null
     */
    public function getDetails(): ?string;

    /**
     * Get MX SMTP response.
     *
     * @return string|null
     */
    public function getSmtpResponse(): ?string;

    /**
     * Get click event url.
     *
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * Get event data.
     *
     * @param bool $asJson Return as json string
     * @return array|string|null
     */
    public function getRaw(bool $asJson = false);

    /**
     * Webhook data security by checking client ip or signing key.
     *
     * @param array $data Webhook data.
     * @return $this
     * @throws \WHEP\Exception\SecurityException
     */
    public function checkSecurity(array $data): ProviderInterface;

    /**
     * Return the security check status.
     *
     * @return bool
     */
    public function securityChecked(): bool;

    /**
     * Process webhook data.
     *
     * @param array $data Webhook data
     * @return $this
     */
    public function process(array $data): ProviderInterface;

    /**
     * Call the correct callback if configured.
     *
     * @return $this
     */
    public function callback(): ProviderInterface;
}
