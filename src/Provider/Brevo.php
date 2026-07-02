<?php
/**
 * Brevo provider for WHEP library.
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
 * Brevo provider.
 *
 * @link https://www.brevo.com/
 */
class Brevo extends AbstractProvider
{
    protected array $_typesMap = [
        'request' => ProviderInterface::EVENT_REQUEST,
        'delivered' => ProviderInterface::EVENT_SENT,
        'unique_opened' => ProviderInterface::EVENT_OPENED,
        'opened' => ProviderInterface::EVENT_OPENED,
        'proxy_open' => ProviderInterface::EVENT_OPENED,
        'unique_proxy_open' => ProviderInterface::EVENT_OPENED,
        'click' => ProviderInterface::EVENT_CLICK,
        'soft_bounce' => ProviderInterface::EVENT_BOUNCE_SOFT,
        'hard_bounce' => ProviderInterface::EVENT_BOUNCE_HARD,
        'invalid_email' => ProviderInterface::EVENT_ERROR,
        'error' => ProviderInterface::EVENT_ERROR,
        'deferred' => ProviderInterface::EVENT_DEFERRED,
        'spam' => ProviderInterface::EVENT_ABUSE,
        'unsubscribed' => ProviderInterface::EVENT_UNSUB,
        'blocked' => ProviderInterface::EVENT_BLOCKED,
    ];

    protected array $_allowedIpAndNetwork = [
        '1.179.112.0/20',
        '172.246.240.0/20',
    ];

    /**
     * @inheritDoc
     */
    public function checkSecurity(array $data): ProviderInterface
    {
        $this->_checkClientIp($this->_config['client_ip']);

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _load(array $data): void
    {
        parent::_load($data);

        $event = $data['event'] ?? null;

        // Type
        $this->_type = $this->_typesMap[$event] ?? ProviderInterface::EVENT_ERROR;

        $this->_recipient = $data['email'] ?? null;

        $hasSmtpResponse = [
            ProviderInterface::EVENT_BOUNCE_HARD,
            ProviderInterface::EVENT_BOUNCE_SOFT,
        ];

        if (in_array($this->_type, $hasSmtpResponse)) {
            $this->_smtp = $data['reason'] ?? null;
        } else {
            $this->_details = $data['reason'] ?? null;
        }

        if ($this->_type === ProviderInterface::EVENT_CLICK) {
            $this->_url = $data['link'] ?? null;
        }

        $this->_raw = $data;
    }
}
