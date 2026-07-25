<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('instructor')) {
            return $course->instructors()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}