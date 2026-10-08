<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\AdminList;
use App\Support\CsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Who did what in the panel, newest first: by kind of thing, by person, or
 * by the name of what was touched. Grouped by day on the page; every line
 * opens to show each field before and after.
 */
class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        [$query, $filters, $chips] = $this->listing($request);

        $logs = $query->with('user')->paginate(30)->withQueryString();
        $days = $logs->getCollection()->groupBy(fn ($log) => $log->created_at->toDateString());

        // Everyone who has a line in the log, for the person filter.
        $people = User::whereIn('id', ActivityLog::select('user_id')->distinct())->orderBy('name')->get(['id', 'name']);

        return view('admin.activity.index', ['logs' => $logs, 'days' => $days, 'chips' => $chips, 'people' => $people] + $filters);
    }

    public function export(Request $request)
    {
        [$query] = $this->listing($request);

        return CsvExport::download('activity', $query->with('user'), [
            __('admin.activity.when') => fn ($l) => $l->created_at->format('Y-m-d H:i:s'),
            __('admin.activity.who') => fn ($l) => $l->user?->name,
            __('admin.activity.what') => fn ($l) => __('admin.activity.actions.'.$l->action),
            __('admin.activity.subject') => fn ($l) => __('admin.activity.subjects.'.$l->subject()),
            __('admin.activity.name') => fn ($l) => $l->entity_label,
            __('admin.activity.details') => fn ($l) => collect($l->changes ?? [])->except(['_product', 'agent'])
                ->map(fn ($pair, $field) => $field.': '.json_encode($pair[0] ?? null, JSON_UNESCAPED_UNICODE).' → '.json_encode($pair[1] ?? null, JSON_UNESCAPED_UNICODE))->implode('; '),
            __('admin.activity.ip') => fn ($l) => $l->ip_address,
        ]);
    }

    /** The log as the address asks for it; the export reads the same. */
    private function listing(Request $request): array
    {
        $kind = AdminList::choice($request, 'kind', array_keys(ActivityLog::KINDS));
        $person = $request->integer('person') ?: null;
        $q = AdminList::term($request);

        $base = ActivityLog::query()
            ->when($person, fn (Builder $w) => $w->where('user_id', $person))
            ->when($q, fn (Builder $w) => AdminList::search($w, $q, ['entity_label', 'user.name']));

        $chips = [[
            'value' => null, 'label' => __('admin.list.all'), 'count' => (clone $base)->count(),
            'url' => $request->fullUrlWithQuery(['kind' => null, 'page' => null]), 'active' => $kind === null,
        ]];
        foreach (ActivityLog::KINDS as $name => $types) {
            $chips[] = [
                'value' => $name, 'label' => __('admin.activity.kinds.'.$name),
                'count' => (clone $base)->whereIn('entity_type', $types)->count(),
                'url' => $request->fullUrlWithQuery(['kind' => $name, 'page' => null]), 'active' => $kind === $name,
            ];
        }

        $query = (clone $base)
            ->when($kind, fn (Builder $w) => $w->whereIn('entity_type', ActivityLog::KINDS[$kind]))
            ->latest()
            ->orderByDesc('id');

        return [$query, compact('kind', 'person', 'q'), $chips];
    }
}
