<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->limit(5)->get();

        if ($users->count() < 5) {
            $this->command->warn(
                'Please create at least 5 users before running this seeder.'
            );

            return;
        }

        $testimonials = [
            [
                'ratings' => 5,
                'reviews' => 'Excellent diffuser with a beautiful design. It makes my living room feel relaxing.',
                'is_verified' => true,
            ],
            [
                'ratings' => 5,
                'reviews' => 'Amazing quality and elegant appearance. I really love this product.',
                'is_verified' => true,
            ],
            [
                'ratings' => 4,
                'reviews' => 'The diffuser works well and looks premium. Very happy with my purchase.',
                'is_verified' => true,
            ],
            [
                'ratings' => 5,
                'reviews' => 'Beautiful product with a calming aroma. Highly recommended.',
                'is_verified' => false,
            ],
            [
                'ratings' => 4,
                'reviews' => 'Good quality and stylish design. It is a lovely addition to my bedroom.',
                'is_verified' => false,
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            Testimonial::updateOrCreate(
                ['user_id' => $users[$index]->id],
                $testimonial
            );
        }

        $this->command->info('5 testimonials seeded successfully.');
    }
}

