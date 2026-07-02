<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace WHEP\Provider;

use WHEP\AbstractProvider;
use WHEP\ProviderInterface;

/**
 * Postal server provider.
 *
 * @link https://docs.postalserver.io/
 */
class Postal extends AbstractProvider
{
    protected array $_typesMap = [
        'SoftFail' => ProviderInterface::EVENT_BOUNCE_SOFT,
        'HardFail' => ProviderInterface::EVENT_BOUNCE_HARD,
        'MessageBounced' => ProviderInterface::EVENT_BOUNCE_HARD,
        'Held' => ProviderInterface::EVENT_BLOCKED,
        'Sent' => ProviderInterface::EVENT_SENT,
        'MessageLoaded' => ProviderInterface::EVENT_OPENED,
        'MessageLinkClicked' => ProviderInterface::EVENT_CLICK,
        'DomainDNSError' => ProviderInterface::EVENT_ERROR,
    ];

    /**
     * @inheritDoc
     */
    public function checkSecurity(array $data): ProviderInterface
    {
        $this->_checkClientIp($this->_config['remote_ip']);

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _load(array $data): void
    {
        parent::_load($data);

        $payload = $data['payload'] ?? $data;

        $event = $payload['status'] ?? $data['event'] ?? null;
        $message = $payload['message'] ?? $payload['original_message'] ?? [];
        $bounce = $payload['bounce'] ?? null;

        // Type
        $this->_type = $this->_typesMap[$event] ?? ProviderInterface::EVENT_ERROR;

        // Details
        $this->_details = $payload['details'] ?? $bounce['subject'] ?? $payload['dkim_error'] ?? null;

        // SMTP output
        $this->_smtp = $payload['output'] ?? null;

        // Raw
        $this->_raw = $payload;

        // E-mail
        $this->_recipient = $message['to'] ?? null;

        if ($this->_type === ProviderInterface::EVENT_CLICK) {
            $this->_url = $payload['url'] ?? null;
        }
    }
}
