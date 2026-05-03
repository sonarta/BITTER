<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function store(LessonRequest $request, Course $course, Module $module): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        $maxOrder = $module->lessons()->max('sort_order') ?? -1;
        $data['sort_order'] = $maxOrder + 1;

        $lesson = $module->lessons()->create($data);

        $this->syncResources($lesson, $data['resources'] ?? []);

        return back()->with('success', 'Lesson added successfully.');
    }

    public function update(LessonRequest $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        $lesson->update($data);

        $this->syncResources($lesson, $data['resources'] ?? []);

        return back()->with('success', 'Lesson updated successfully.');
    }

    /**
     * Sync lesson resources.
     *
     * @param  array<int, array<string, string>>  $resources
     */
    private function syncResources(Lesson $lesson, array $resources): void
    {
        $existingIds = [];

        foreach ($resources as $index => $resource) {
            $model = $lesson->resources()->updateOrCreate(
                ['id' => $resource['id'] ?? null],
                [
                    'title' => $resource['title'],
                    'url' => $resource['url'],
                    'type' => $resource['type'] ?? 'Link',
                    'sort_order' => $index,
                ],
            );

            $existingIds[] = $model->id;
        }

        $lesson->resources()->whereNotIn('id', $existingIds)->delete();
    }

    public function destroy(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('update', $course);

        $lesson->delete();

        return back()->with('success', 'Lesson deleted successfully.');
    }

    /**
     * Reorder lessons within a module.
     */
    public function reorder(Course $course, Module $module): JsonResponse
    {
        Gate::authorize('update', $course);

        $validated = request()->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string'],
        ]);

        foreach ($validated['order'] as $index => $lessonId) {
            Lesson::where('id', $lessonId)
                ->where('module_id', $module->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
