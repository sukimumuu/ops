<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuditLogService
{
    /**
     * Paginate the audit trail using the given validated filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, bool $includeRestricted = false): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('actor:id,name,email')
            ->when(
                ! $includeRestricted,
                fn (Builder $query): Builder => $query->where('category', '!=', AuditLog::CATEGORY_SYSTEM),
            )
            ->category($filters['category'] ?? null)
            ->event($filters['event'] ?? null)
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['from'] ?? null),
                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $filters['from']),
            )
            ->when(
                filled($filters['to'] ?? null),
                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $filters['to']),
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Summarise today's audited activity per category.
     *
     * @return array<string, int>
     */
    public function summary(bool $includeRestricted = false): array
    {
        $counts = AuditLog::query()
            ->whereDate('created_at', today())
            ->when(
                ! $includeRestricted,
                fn (Builder $query): Builder => $query->where('category', '!=', AuditLog::CATEGORY_SYSTEM),
            )
            ->selectRaw('category, COUNT(*) as aggregate')
            ->groupBy('category')
            ->pluck('aggregate', 'category')
            ->all();

        return [
            'total' => array_sum($counts),
            'authentication' => (int) ($counts[AuditLog::CATEGORY_AUTHENTICATION] ?? 0),
            'transaction' => (int) ($counts[AuditLog::CATEGORY_TRANSACTION] ?? 0),
            'scheduling' => (int) ($counts[AuditLog::CATEGORY_SCHEDULING] ?? 0),
            'moderation' => (int) ($counts[AuditLog::CATEGORY_MODERATION] ?? 0),
        ];
    }

    /**
     * Available category filter options.
     *
     * @return array<string, string>
     */
    public function categoryOptions(bool $includeRestricted = false): array
    {
        if ($includeRestricted) {
            return AuditLog::CATEGORY_LABELS;
        }

        return array_diff_key(AuditLog::CATEGORY_LABELS, [AuditLog::CATEGORY_SYSTEM => true]);
    }

    /**
     * Recorded event names available as filter options.
     *
     * @return list<string>
     */
    public function eventOptions(bool $includeRestricted = false): array
    {
        return AuditLog::query()
            ->when(
                ! $includeRestricted,
                fn (Builder $query): Builder => $query->where('category', '!=', AuditLog::CATEGORY_SYSTEM),
            )
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->values()
            ->all();
    }
}
