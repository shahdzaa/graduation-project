<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;

class MasarSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // ADMIN
        // ============================================================
        $admin = User::create([
            'name'              => 'Ahmed Khalid',
            'email'             => 'ahmed@masar.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // ============================================================
        // INSTRUCTORS
        // ============================================================
        $instructorsData = [
            [
                'user' => [
                    'name'              => 'Nasser Al-Subaie',
                    'email'             => 'nasser@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Senior software instructor specialized in modern web development, software architecture, and JavaScript technologies. Passionate about turning complex engineering concepts into practical learning experiences.',
                    'specialization'  => 'Computer Science',
                    'years_experience' => 8,
                    'average_rating'  => 4.9,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Amal Al-Harbi',
                    'email'             => 'amal@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Data science instructor with a passion for machine learning and statistical analysis.',
                    'specialization'  => 'Data Science',
                    'years_experience' => 6,
                    'average_rating'  => 4.7,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Abdullah Al-Qahtani',
                    'email'             => 'abdullah@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Backend systems engineer with deep expertise in distributed architectures and cloud infrastructure.',
                    'specialization'  => 'Backend Engineering',
                    'years_experience' => 10,
                    'average_rating'  => 4.8,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Fouzia Al-Shehri',
                    'email'             => 'fouzia@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'UI/UX and frontend development instructor focused on modern design systems and accessibility.',
                    'specialization'  => 'Frontend Development',
                    'years_experience' => 5,
                    'average_rating'  => 4.6,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Omar Al-Dosari',
                    'email'             => 'omar@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Cybersecurity expert and ethical hacking trainer with industry certifications.',
                    'specialization'  => 'Cybersecurity',
                    'years_experience' => 7,
                    'average_rating'  => 4.5,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Fatima Alzahra',
                    'email'             => 'fatima.ins@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Artificial intelligence researcher and educator with focus on NLP and deep learning.',
                    'specialization'  => 'Artificial Intelligence',
                    'years_experience' => 4,
                    'average_rating'  => 4.8,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Yousef Khalid',
                    'email'             => 'yousef.ins@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Mobile development instructor specializing in cross-platform solutions using Flutter and React Native.',
                    'specialization'  => 'Mobile Development',
                    'years_experience' => 5,
                    'average_rating'  => 4.7,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Aly Hariri',
                    'email'             => 'ali@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'DevOps engineer and cloud architect with extensive AWS and Kubernetes experience.',
                    'specialization'  => 'DevOps & Cloud',
                    'years_experience' => 9,
                    'average_rating'  => 4.6,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Abdullah Al-Khatibi',
                    'email'             => 'abdullah.k@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Database architect and SQL expert focused on performance optimization and data modeling.',
                    'specialization'  => 'Database Engineering',
                    'years_experience' => 11,
                    'average_rating'  => 4.9,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Fouad Al-Shahri',
                    'email'             => 'fouad@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'bio'             => 'Software architecture and design patterns instructor with enterprise development background.',
                    'specialization'  => 'Software Engineering',
                    'years_experience' => 12,
                    'average_rating'  => 4.8,
                    'linkedin_url'    => null,
                    'website_url'     => null,
                ],
            ],
        ];

        foreach ($instructorsData as $data) {
            $user = User::create($data['user']);
            $user->assignRole('instructor');
            InstructorProfile::create(array_merge(
                ['user_id' => $user->id],
                $data['profile']
            ));
        }

        // ============================================================
        // STUDENTS
        // ============================================================
        $studentsData = [
            [
                'user' => [
                    'name'              => 'Mohammed Ali',
                    'email'             => 'mohammed@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+966501234567',
                    'country'    => 'Saudi Arabia',
                    'birth_date' => '2000-03-15',
                    'github_url' => 'https://github.com/mohammed-ali',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Nourhan Mostafa',
                    'email'             => 'nourhah@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+20101234567',
                    'country'    => 'Egypt',
                    'birth_date' => '2001-07-22',
                    'github_url' => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Khalid Ali',
                    'email'             => 'khalid.a@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+96541234567',
                    'country'    => 'Kuwait',
                    'birth_date' => '1999-11-05',
                    'github_url' => 'https://github.com/khalid-ali',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Zainab Ahmed',
                    'email'             => 'zainab.a@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+9647712345678',
                    'country'    => 'Iraq',
                    'birth_date' => '2002-01-30',
                    'github_url' => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Omar Yousef',
                    'email'             => 'omar.y@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+97150123456',
                    'country'    => 'UAE',
                    'birth_date' => '2000-09-14',
                    'github_url' => 'https://github.com/omar-yousef',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Fatima Alzahra',
                    'email'             => 'fatima@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+212600123456',
                    'country'    => 'Morocco',
                    'birth_date' => '2001-05-18',
                    'github_url' => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Yousef Khalid',
                    'email'             => 'yousef@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+96171234567',
                    'country'    => 'Lebanon',
                    'birth_date' => '1998-12-03',
                    'github_url' => 'https://github.com/yousef-khalid',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Nasser Hamdan',
                    'email'             => 'nasser.h@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+97317123456',
                    'country'    => 'Bahrain',
                    'birth_date' => '2000-06-25',
                    'github_url' => null,
                ],
            ],
            [
                'user' => [
                    'name'              => 'Sara Al-Rashidi',
                    'email'             => 'sara.r@example.com',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'phone'      => '+96891234567',
                    'country'    => 'Oman',
                    'birth_date' => '2002-04-10',
                    'github_url' => 'https://github.com/sara-rashidi',
                ],
            ],
        ];

        foreach ($studentsData as $data) {
            $user = User::create($data['user']);
            $user->assignRole('student');
            StudentProfile::create(array_merge(
                ['user_id' => $user->id],
                $data['profile']
            ));
        }
    }
}
