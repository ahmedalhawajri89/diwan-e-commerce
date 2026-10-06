<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * What every admin list shares: a search term, a sort from a fixed list, and
 * a row of status chips each carrying its real count.
 *
 * Nothing the address bar says reaches a query unchecked: a sort or a status
 * that is not on the list is the default, never an error and never SQL.
 */
final class AdminList
{
    /** The search term, trimmed, or null when there is none worth searching for. */
    public static function term(Request $request): ?string
    {
        $q = trim((string) $request->query('q', ''));

        return $q === '' ? null : mb_substr($q, 0, 100);
    }

    /** One of $allowed, or the default. */
    public static function choice(Request $request, string $key, array $allowed, ?string $default = null): ?string
    {
        $value = $request->query($key);

        return is_string($value) && in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * Match the term against plain columns and against columns of relations
     * ("user.name"), all OR'd inside one group so it never loosens a filter
     * applied beside it.
     */
    public static function search(Builder $query, ?string $term, array $columns): Builder
    {
        if ($term === null) {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn (Builder $r) => $r->where($field, 'like', $like));
                } else {
                    $q->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * The chips for one column: "All" first, then each value with how many
     * rows hold it under the current search. Counted, never estimated; a value
     * with none is still offered, at zero.
     *
     * @param  Builder  $base  the list's query with the search applied, before the chip's own filter
     * @param  list<string>  $values
     * @return list<array{value: ?string, label: string, count: int, url: string, active: bool}>
     */
    public static function chips(Builder $base, string $column, array $values, ?string $current, string $labels, string $param = 'status'): array
    {
        $counts = (clone $base)->reorder()->selectRaw("{$column} as v, COUNT(*) as n")->groupBy($column)->pluck('n', 'v');

        $chips = [[
            'value' => null,
            'label' => __('admin.list.all'),
            'count' => (int) $counts->sum(),
            'url' => request()->fullUrlWithQuery([$param => null, 'page' => null]),
            'active' => $current === null,
        ]];

        foreach ($values as $value) {
            $chips[] = [
                'value' => $value,
                'label' => __($labels.'.'.$value),
                'count' => (int) ($counts[$value] ?? 0),
                'url' => request()->fullUrlWithQuery([$param => $value, 'page' => null]),
                'active' => $current === $value,
            ];
        }

        return $chips;
    }

    /**
     * The sort menu's entries, each a link that keeps everything else.
     *
     * @param  list<string>  $keys
     * @return list<array{label: string, url: string, active: bool}>
     */
    public static function sorts(array $keys, string $current): array
    {
        return array_map(fn ($key) => [
            'label' => __('admin.list.sort.'.$key),
            'url' => request()->fullUrlWithQuery(['sort' => $key, 'page' => null]),
            'active' => $key === $current,
        ], $keys);
    }
}
