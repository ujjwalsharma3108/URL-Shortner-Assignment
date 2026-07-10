<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsFilterRequest;
use App\Services\AnalyticsReport;
use Illuminate\Contracts\View\View;

class AnalyticsController extends Controller
{
    public function superAdmin(AnalyticsFilterRequest $request, AnalyticsReport $report): View
    {
        abort_unless($request->user('api')->isSuperAdmin(), 403);

        return $this->render($request, $report, true);
    }

    public function admin(AnalyticsFilterRequest $request, AnalyticsReport $report): View
    {
        abort_unless($request->user('api')->isAdmin(), 403);

        return $this->render($request, $report, false);
    }

    private function render(
        AnalyticsFilterRequest $request,
        AnalyticsReport $report,
        bool $isSuperAdminPage,
    ): View {
        $page = [
            'user' => $request->user('api'),
            'isSuperAdminPage' => $isSuperAdminPage,
            'pageTitle' => $isSuperAdminPage ? 'Super Admin Analytics' : 'Admin Analytics',
            'pageSubtitle' => $isSuperAdminPage
                ? 'Review every company, Admin, Member, short URL, and click from one place.'
                : 'Review your URLs and the performance of users created by you.',
            'routeName' => $isSuperAdminPage ? 'super-admin.analytics' : 'admin.analytics',
        ];

        return view('analytics.index', array_merge(
            $page,
            $report->build($request->user('api'), $isSuperAdminPage, $request->validated()),
        ));
    }
}
