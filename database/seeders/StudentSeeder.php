<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * عدد الطلاب يلي بدنا نولّدهم — عدّليه براحتك
     */
    protected int $count = 20;

    public function run(): void
    {
        User::factory()
            ->count($this->count)
            ->create()
            ->each(function (User $user) {
                StudentProfile::factory()->create([
                    'user_id' => $user->id,
                ]);
            });

        $this->command->info("تم إنشاء {$this->count} طالب مع بروفايلاتهم بنجاح ✅");
    }
}
