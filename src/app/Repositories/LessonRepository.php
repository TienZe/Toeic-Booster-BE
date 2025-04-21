<?php

namespace App\Repositories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

class LessonRepository
{
    /**
     * Get all lessons
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Lesson::all();
    }

    /**
     * Create a new lesson
     *
     * @param array $data
     * @return Lesson
     */
    public function create(array $data): Lesson
    {
        return Lesson::create($data);
    }

    /**
     * Update an existing lesson
     *
     * @param int|string $id
     * @param array $data
     * @return Lesson|null
     */
    public function update(int|string $id, array $data): ?Lesson
    {
        $lesson = Lesson::findOrFail($id);

        $lesson->update($data);

        return $lesson;
    }

    /**
     * Delete a lesson
     *
     * @param int|string $id
     * @return int
     */
    public function delete(int|string $id): int
    {
        return Lesson::destroy($id);
    }
}
