<?php

namespace App\Http\Controllers;

use App\Models\Platform\FutureModule;
use Inertia\Inertia;

class FutureModuleController extends Controller
{
    public function show(string $key)
    {
        $module = FutureModule::where('key', $key)->firstOrFail();

        // Planned modules stay hidden (404) until activated in the admin — this
        // is intentional; the routes aren't linked anywhere in the UI.
        abort_unless($module->isActive(), 404);

        return Inertia::render('Public/FutureModules/Show', [
            'module' => [
                'key' => $module->key,
                'name' => $module->name,
                'summary' => $module->summary,
                'moduleGroup' => $module->module_group,
                'readinessChecks' => $module->readiness_checks ?? [],
            ],
        ]);
    }
}
