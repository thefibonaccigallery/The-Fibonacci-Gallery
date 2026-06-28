<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class CoursePolicy
{
    public function access(User $user, Course $course): bool
    {
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();
    }
}
