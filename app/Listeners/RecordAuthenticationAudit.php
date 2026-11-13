<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class RecordAuthenticationAudit
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Login|Logout|Failed $event): void
    {
        $actor = $event->user instanceof User ? $event->user : null;

        if ($event instanceof Login) {
            $this->auditLogger->record(
                AuditLog::EVENT_AUTH_LOGIN,
                'Pengguna masuk ke platform melalui guard '.$event->guard.'.',
                $actor,
                ['guard' => $event->guard, 'remember' => (bool) $event->remember],
                $actor,
            );

            return;
        }

        if ($event instanceof Logout) {
            $this->auditLogger->record(
                AuditLog::EVENT_AUTH_LOGOUT,
                'Pengguna keluar dari platform melalui guard '.$event->guard.'.',
                $actor,
                ['guard' => $event->guard],
                $actor,
            );

            return;
        }

        $identifier = $event->credentials['phone'] ?? $event->credentials['email'] ?? null;

        $this->auditLogger->record(
            AuditLog::EVENT_AUTH_FAILED,
            'Percobaan masuk gagal'
                .(filled($identifier) ? ' untuk '.$identifier : '').'.',
            null,
            ['guard' => $event->guard, 'identifier' => $identifier],
        );
    }
}
