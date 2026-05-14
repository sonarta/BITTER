<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleRequest;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function store(ModuleRequest $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $maxOrder = $course->modules()->max('sort_order') ?? -1;

        $course->modules()->create([
            ...$request->validated(),
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Module added successfully.');
    }

    public function update(ModuleRequest $request, Course $course, Module $module): RedirectResponse
    {
        Gate::authorize('update', $course);

        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $module->update($request->validated());

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Course $course, Module $module): RedirectResponse
    {
        Gate::authorize('update', $course);

        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }

    /**
     * Reorder modules within a course.
     */
    public function reorder(Course $course): JsonResponse
    {
        Gate::authorize('update', $course);

        $validated = request()->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string'],
        ]);

        foreach ($validated['order'] as $index => $moduleId) {
            Module::where('id', $moduleId)
                ->where('course_id', $course->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
