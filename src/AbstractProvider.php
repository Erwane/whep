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
use IPLib\Address\AddressInterface;
use IPLib\Factory as IpFactory;
use IPLib\Range\RangeInterface;
use ReflectionClass;
use WHEP\Exception\IpException;
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
    protected array $_config = [];

    /**
     * Maps provider events to WHEP event.
     * Type could be adjusted in self::_load() or
     *
     * @var array
     */
    protected array $_typesMap = [];

    /**
     * Event time.
     *
     * @var \DateTimeInterface|null
     */
    protected ?DateTimeInterface $_time = null;

    /**
     * Event type.
     *
     * @var string|null
     */
    protected ?string $_type = null;

    /**
     * Recipient e-mail.
     *
     * @var string|null
     */
    protected ?string $_recipient = null;

    /**
     * Provider details about event.
     *
     * @var string|null
     */
    protected ?string $_details = null;

    /**
     * SMTP response.
     *
     * @var string|null
     */
    protected ?string $_smtp = null;

    /**
     * Clicked url when event type is TYPE_CLICK
     *
     * @var string|null
     */
    protected ?string $_url = null;

    /**
     * Raw event data
     *
     * @var array|null
     */
    protected ?array $_raw = null;

    /**
     * @var array<string>  Provider allowed ip and network
     */
    protected array $_allowedIpAndNetwork = [];

    /**
     * @var array
     */
    private array $_ipAndNetwork = [];

    private $_clientIpChecked = false;

    /**
     * Provider constructor.
     *
     * @param array $config Provider config
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(array $config = [])
    {
        $config += [
            'client_ip' => null,
            'allowed_ip' => [],
            'callbacks' => [],
            'check_ip' => true, // Check client ip against allowed_ip list.
        ];

        $callbacks = array_merge($this->_defaultConfig['callbacks'], $config['callbacks']);

        $this->_config = array_merge($this->_defaultConfig, $config);
        $this->_config['callbacks'] = $callbacks;
        $this->_config['client_ip'] = IpFactory::parseAddressString($config['client_ip']);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->addAllowedIpOrNetwork(array_merge($this->_allowedIpAndNetwork, $config['allowed_ip']));
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
    public function getRaw(bool $asJson = false): array|string|null
    {
        if ($asJson) {
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS;

            return json_encode((array)$this->_raw, $jsonOptions);
        } else {
            return $this->_raw;
        }
    }

    /**
     * Add network or IP to allowed list.
     *
     * @param array<string>|string $input Ip or CIDR network
     * @return $this
     * @throws \WHEP\Exception\IpException
     */
    public function addAllowedIpOrNetwork(array|string $input)
    {
        if (is_array($input)) {
            foreach ($input as $item) {
                $this->addAllowedIpOrNetwork($item);
            }
        } else {
            $item = IpFactory::parseAddressString($input);
            if ($item === null) {
                $item = IpFactory::parseRangeString($input);
                if ($item === null) {
                    throw new IpException('Invalid ip or network.');
                }
            }

            if (!in_array($item, $this->_ipAndNetwork)) {
                $this->_ipAndNetwork[] = $item;
            }
        }

        return $this;
    }

    /**
     * Reset and set allowed ip or network, ignoring default provider list.
     *
     * @param array<string> $input Ip or CIDR network
     * @return $this
     * @throws \WHEP\Exception\IpException
     */
    public function setAllowedIpOrNetwork(array $input)
    {
        $this->_ipAndNetwork = [];

        return $this->addAllowedIpOrNetwork($input);
    }

    /**
     * Process webhook data.
     *
     * @param array $data Emailing provider webhook data
     * @return $this
     * @throws \WHEP\Exception\IpException
     */
    public function process(array $data)
    {
        $this->_checkClientIp();
        $this->_load($data);
        $this->_typeFromResponse();

        return $this;
    }

    /**
     * Check client_ip is in IP or network allowed list.
     *
     * @return void
     * @throws \WHEP\Exception\IpException
     */
    protected function _checkClientIp(): void
    {
        if ($this->_config['check_ip']) {
            if (!$this->_config['client_ip']) {
                throw new IpException('Client IP not set. Pass `client_ip` to `Factory::provider()`.');
            }

            $success = false;
            $clientIp = $this->_config['client_ip'];
            $clientComparableString = $clientIp->getComparableString();
            foreach ($this->_ipAndNetwork as $item) {
                if (
                    ($item instanceof RangeInterface && $item->contains($clientIp))
                    || ($item instanceof AddressInterface && $item->getComparableString() === $clientComparableString)
                ) {
                    $success = true;
                    break;
                }
            }

            if (!$success) {
                throw new IpException(sprintf('Client IP "%s" is not in allowed list.', $clientIp->toString()));
            }

            $this->_clientIpChecked = true;
        }
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

        $event = $data['event'] ?? null;
        $this->_type = $this->_typesMap[$event] ?? ProviderInterface::EVENT_ERROR;
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
    public function __debugInfo(): array
    {
        $debug = [
            'name' => $this->getName(),
            'client_ip' => $this->_config['client_ip']?->toString(),
            'client_ip_checked' => $this->_clientIpChecked,
            'type' => $this->getType(),
            'time' => null,
            'recipient' => $this->getRecipient(),
            'details' => $this->getDetails(),
            'smtp' => $this->getSmtpResponse(),
            'url' => $this->getUrl(),
            'raw' => $this->getRaw(),
            'allowed_ip' => [],
        ];

        if ($this->getTime()) {
            $debug['time'] = $this->getTime()->format(DATE_ATOM);
        }

        /** @var \IPLib\Address\AddressInterface|\IPLib\Range\RangeInterface $item */
        foreach ($this->_ipAndNetwork as $item) {
            $debug['allowed_ip'][] = $item->toString();
        }

        return $debug;
    }
}
