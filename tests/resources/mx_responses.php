<?php
/**
 * Copyright (c) Erwane BRETON
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);

use WHEP\ProviderInterface;

return [
    ProviderInterface::EVENT_BOUNCE_QUOTA => [
        'free_1' => '552 5.2.2 user quota exceeded (UserSearch)',
        'gmail_1' => "452-4.2.2 The recipient's inbox is out of storage space. Please direct the\n452-4.2.2 recipient to\n452 4.2.2  https://support.google.com/mail/?p=OverQuotaTemp ffacd0b85a97d-3b76fcff3f8si2004203f8f.527 - gsmtp",
        'icloud_1' => '552 5.2.2 <recipient@example.com>: user is over quota',
        'ionos_1' => '552-Requested mail action aborted: exceeded storage allocation',
        'laposte_1' => '552 5.2.2 \u003postmaster@laposte.net\u003e: Over quota',
        'orange_1' => '552 5.1.1 hibJumn68I371 Boite du destinataire pleine. Recipient overquota. OFR_417 [417]',
    ],
    ProviderInterface::EVENT_BOUNCE_SOFT => [
    ],
    ProviderInterface::EVENT_BOUNCE_HARD => [
        'gmail_1' => "552-5.2.2 The recipient's inbox is out of storage space and inactive. Please\n552-5.2.2 direct the recipient to\n552 5.2.2  https://support.google.com/mail/?p=OverQuotaPerm 5b1f17b1804b1-45871daec68si13206435e9.59 - gsmtp",
        'orange_1' => '550 5.1.1 hhtzumeItI371 Adresse d au moins un destinataire invalide. Invalid recipient. OFR_416 [416]',
    ],
    ProviderInterface::EVENT_ERROR => [
        't-online_1' => '554 IP=1.2.3.4 - None/bad reputation. Ask your postmaster for help or to contact tobr@rx.t-online.de for reset. (NOWL)',
    ],
];
