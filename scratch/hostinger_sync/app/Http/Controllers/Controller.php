<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Redirects to a specified route while perfectly preserving all state filters
     * (page, search, status, sort, filter, view, dates, tabs, etc.) from the request or Referer.
     *
     * @param string $route
     * @param array $flashData e.g. ['success' => 'Archived successfully!']
     * @param array $additionalParams
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function preserveStateAndRedirect($route, array $flashData = [], array $additionalParams = [])
    {
        $keys = [
            'page', 'search', 'status', 'sort', 'filter', 'view',
            'month', 'year', 'date', 'category', 'date_from', 'date_to',
            'tab', 'severity', 'type', 'date_filter', 'status_filter'
        ];

        // 1. Try to get parameters from the current request query string
        $params = request()->only($keys);
        $params = array_filter($params, function ($val) {
            return $val !== null && $val !== '';
        });

        // 2. If empty, fall back to parsing them from the Referer header
        if (empty($params)) {
            $referer = request()->headers->get('referer');
            if ($referer) {
                $queryStr = parse_url($referer, PHP_URL_QUERY);
                if ($queryStr) {
                    parse_str($queryStr, $refererParams);
                    $params = array_intersect_key($refererParams, array_flip($keys));
                    $params = array_filter($params, function ($val) {
                        return $val !== null && $val !== '';
                    });
                }
            }
        }

        // Merge with any additional manually passed parameters
        $params = array_merge($params, $additionalParams);

        $redirect = redirect()->to(route($route, $params));

        foreach ($flashData as $key => $message) {
            $redirect = $redirect->with($key, $message);
        }

        return $redirect;
    }
}
