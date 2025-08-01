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
    public const STATUS_SUCCESS = 200;
    public const STATUS_WARN = 400;
    public const STATUS_FAIL = 500;

    public const TYPE_DELAYED = 'delayed';
    public const TYPE_BOUNCED = 'bounced';
    public const TYPE_QUOTA = 'quota';
    public const TYPE_BLOCKED = 'blocked';
    public const TYPE_SOFT_FAIL = 'softfail';
    public const TYPE_HARD_FAIL = 'hardfail';
    public const TYPE_SENT = 'sent';
    public const TYPE_ABUSE = 'abuse';
    public const TYPE_UNSUB = 'unsubscribed';
    public const TYPE_OPENED = 'opened';
    public const TYPE_CLICK = 'click';
    public const TYPE_ERROR = 'error';

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
     * Event status. Could be 200|400|500.
     *
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * Get event type.
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Get related email.
     *
     * @return string|null
     */
    public function getEmail(): ?string;

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
     * Process webhook data.
     *
     * @param array $data Webhook data
     * @return $this
     */
    public function process(array $data);

    /**
     * Call the correct callbacks if configured.
     *
     * @return $this
     */
    public function callbacks();
}
