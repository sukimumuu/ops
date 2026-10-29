<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

#[Signature('webpush:generate-vapid-keys')]
#[Description('Generate VAPID keys for browser push notifications')]
class GenerateVapidKeys extends Command
{
    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->line('WEBPUSH_VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('WEBPUSH_VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('Set WEBPUSH_VAPID_SUBJECT to a mailto: address or HTTPS URL.');
        $this->warn('Keep the private key secret and do not commit it.');

        return self::SUCCESS;
    }
}
