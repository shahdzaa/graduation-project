<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * عدد المدرّسين يلي بدنا نولّدهم — عدّليه براحتك
     */
    protected int $count = 8;

    public function run(): void
    {
        User::factory()
            ->instructor() // بتحط role = 'instructor'
            ->count($this->count)
            ->create()
            ->each(function (User $user) {
                InstructorProfile::factory()->create([
                    'user_id' => $user->id,
                ]);
            });

        $this->command->info("تم إنشاء {$this->count} مدرّس مع بروفايلاتهم بنجاح ✅");
    }
}
