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

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ReflectionClass;
use const DATE_ATOM;

/**
 * Webhooks handler abstract emailing provider class.
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected $_defaultConfig = [
        'callbacks' => [
            ProviderInterface::EVENT_DEFERRED => null,
            ProviderInterface::EVENT_BOUNCE_SOFT => null,
            ProviderInterface::EVENT_BOUNCE_HARD => null,
            ProviderInterface::EVENT_BOUNCE_QUOTA => null,
            ProviderInterface::EVENT_BLOCKED => null,
            ProviderInterface::EVENT_REQUEST => null,
            ProviderInterface::EVENT_SENT => null,
            ProviderInterface::EVENT_OPENED => null,
            ProviderInterface::EVENT_CLICK => null,
            ProviderInterface::EVENT_ABUSE => null,
            ProviderInterface::EVENT_UNSUB => null,
            ProviderInterface::EVENT_ERROR => null,
        ],
    ];

    /**
     * Provider configuration.
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Event time.
     *
     * @var \DateTimeInterface|null
     */
    protected $_time = null;

    /**
     * Event type.
     *
     * @var string|null
     */
    protected $_type = null;

    /**
     * Recipient e-mail.
     *
     * @var string|null
     */
    protected $_recipient = null;

    /**
     * Provider details about event.
     *
     * @var string|null
     */
    protected $_details = null;

    /**
     * SMTP response.
     *
     * @var string|null
     */
    protected $_smtp = null;

    /**
     * Clicked url when event type is TYPE_CLICK
     *
     * @var string|null
     */
    protected $_url = null;

    /**
     * Raw event data
     *
     * @var array|null
     */
    protected $_raw = null;

    /**
     * Provider constructor.
     *
     * @param array $config Provider config
     */
    public function __construct(array $config = [])
    {
        $config += [
            'callbacks' => [],
        ];

        $callbacks = array_merge($this->_defaultConfig['callbacks'], $config['callbacks']);

        $this->_config = array_merge($this->_defaultConfig, $config);
        $this->_config['callbacks'] = $callbacks;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        $reflection = new ReflectionClass($this);

        return strtolower($reflection->getShortName());
    }

    /**
     * @inheritDoc
     */
    public function getTime(): ?DateTimeInterface
    {
        return $this->_time;
    }

    /**
     * Get event type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->_type;
    }

    /**
     * @inheritDoc
     */
    public function getRecipient(): ?string
    {
        if ($this->_recipient) {
            return trim(strtolower($this->_recipient));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getDetails(): ?string
    {
        return $this->_details;
    }

    /**
     * @inheritDoc
     */
    public function getSmtpResponse(): ?string
    {
        return $this->_smtp;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): ?string
    {
        return $this->_url;
    }

    /**
     * @inheritDoc
     */
    public function getRaw(bool $asJson = false)
    {
        if ($asJson) {
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS;

            return json_encode((array)$this->_raw, $jsonOptions);
        } else {
            return $this->_raw;
        }
    }

    /**
     * Process webhook data.
     *
     * @param array $data Emailing provider webhook data
     * @return $this
     */
    public function process(array $data)
    {
        $this->_load($data);
        $this->_typeFromResponse();

        return $this;
    }

    /**
     * Load webhook data.
     *
     * @param array $data Emailing provider webhook data
     * @return void
     */
    protected function _load(array $data): void
    {
        $this->_time = DateTimeImmutable::createFromFormat('U.u e', microtime(true) . ' UTC', new DateTimeZone('UTC'));
        $this->_raw = $data;
    }

    /**
     * Override event type from mx SMTP response.
     *
     * @return void
     */
    protected function _typeFromResponse(): void
    {
        if ($this->_smtp) {
            $responseType = MxDetector::getType($this->_smtp);
            if ($responseType) {
                $this->_type = $responseType;
            }
        }
    }

    /**
     * Call configured callback type.
     *
     * @return void
     */
    public function callback(): void
    {
        if ($this->_type) {
            foreach ($this->_config['callbacks'] as $type => $callable) {
                if ($this->_type === $type && is_callable($callable)) {
                    call_user_func_array($callable, [$this]);
                }
            }
        }
    }

    /**
     * Object dump representation.
     *
     * @return array
     */
    public function __debugInfo()
    {
        $debug = [
            'type' => $this->getType(),
            'time' => null,
            'email' => $this->getRecipient(),
            'details' => $this->getDetails(),
            'smtp' => $this->getSmtpResponse(),
            'url' => $this->getUrl(),
            'raw' => $this->getRaw(),
        ];

        if ($this->getTime()) {
            $debug['time'] = $this->getTime()->format(DATE_ATOM);
        }

        return $debug;
    }
}
