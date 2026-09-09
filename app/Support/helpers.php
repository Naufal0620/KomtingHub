<?php

use Illuminate\Http\Request;

if (! function_exists('resolvePerPage')) {
    /**
     * Resolve the per-page limit from the request, supporting an "all" value.
     *
     * Valid values are clamped to $allowed; any other/invalid value falls back
     * to $default. "all" is treated as a large number so that paginate()
     * renders every row on a single page while keeping the paginator API intact.
     */
    function resolvePerPage(Request $request, int $default = 10, array $allowed = [5, 10, 15, 25, 50, 100]): int
    {
        $raw = $request->query('per_page');

        if ($raw !== null && is_string($raw) && strtolower($raw) === 'all') {
            return 1000000;
        }

        $per = (int) $raw;

        return in_array($per, $allowed, true) ? $per : $default;
    }
}