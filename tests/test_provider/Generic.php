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

class Generic extends AbstractProvider
{
    protected array $_allowedIpAndNetwork = ['192.168.0.1/24'];

    /**
     * @inheritDoc
     */
    public function checkSecurity(array $data): self
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

        $this->_smtp = $data['smtp'] ?? null;
        $this->_recipient = $data['email'] ?? null;
    }

    public function customCallback()
    {
    }
}
