<?php

namespace App\Services\Audit;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Mapping of event prefixes to the category they belong to.
     *
     * @var array<string, string>
     */
    private const CATEGORY_BY_PREFIX = [
        'auth' => AuditLog::CATEGORY_AUTHENTICATION,
        'transaction' => AuditLog::CATEGORY_TRANSACTION,
        'payout' => AuditLog::CATEGORY_TRANSACTION,
        'appointment' => AuditLog::CATEGORY_SCHEDULING,
        'property' => AuditLog::CATEGORY_MODERATION,
        'user' => AuditLog::CATEGORY_ADMINISTRATION,
        'role' => AuditLog::CATEGORY_ADMINISTRATION,
    ];

    /**
     * Attribute keys that must never be persisted inside audit metadata.
     *
     * @var list<string>
     */
    private const REDACTED_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'secret',
        'api_key',
        'credentials',
        'raw_payload',
    ];

    /**
     * Persist an immutable audit trail entry for the given event.
     *
     * @param  array<string, mixed>  $properties
     */
    public function record(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $actor = null,
        ?string $category = null,
    ): AuditLog {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            $actor = null;
        }

        return AuditLog::query()->create([
            'actor_id' => $actor?->getKey(),
            'actor_name' => $actor?->name,
            'actor_email' => $actor?->email,
            'actor_role' => $actor?->getRoleNames()->first(),
            'category' => $category ?? $this->categoryFor($event),
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $this->subjectLabel($subject),
            'properties' => $this->sanitize($properties) ?: null,
            'ip_address' => $this->request()?->ip(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    private function categoryFor(string $event): string
    {
        $prefix = Str::before($event, '.');

        return self::CATEGORY_BY_PREFIX[$prefix] ?? AuditLog::CATEGORY_SYSTEM;
    }

    private function subjectLabel(?Model $subject): ?string
    {
        if ($subject === null) {
            return null;
        }

        if ($subject instanceof Appointment
            && $subject->relationLoaded('property')
            && $subject->property !== null) {
            return $subject->property->title;
        }

        foreach (['title', 'name', 'reference_id', 'uuid'] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    /**
     * Recursively remove sensitive values from the audit metadata.
     *
     * @param  array<array-key, mixed>  $properties
     * @return array<array-key, mixed>
     */
    private function sanitize(array $properties): array
    {
        $sanitized = [];

        foreach ($properties as $key => $value) {
            if (is_string($key) && in_array($key, self::REDACTED_KEYS, true)) {
                continue;
            }

            $sanitized[$key] = match (true) {
                is_array($value) => $this->sanitize($value),
                $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
                $value instanceof Model => $value->getKey(),
                is_scalar($value), $value === null => $value,
                default => null,
            };
        }

        return $sanitized;
    }

    private function request(): ?Request
    {
        if (app()->runningInConsole() || ! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }

    private function userAgent(): ?string
    {
        $userAgent = $this->request()?->userAgent();

        return is_string($userAgent) && $userAgent !== ''
            ? Str::limit($userAgent, 500, '')
            : null;
    }
}
