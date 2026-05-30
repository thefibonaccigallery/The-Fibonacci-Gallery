<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\CourseVideo;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::create([
            'title'        => 'The Complete Forex Trading Course',
            'slug'         => Str::slug('The Complete Forex Trading Course'),
            'description'  => 'A comprehensive course covering everything you need to know about Forex trading from the ground up.',
            'level'        => 'beginner',
            'price'        => 20.00,
            'length_seconds' => 96960,
            'is_published' => true,
            'published_at' => now(),
            'uuid' => Str::uuid(),
        ]);

        $chapters = [
            [
                'title'  => 'Introduction to Forex Trading',
                'level'  => 'beginner',
                'order'  => 1,
                'uuid' => Str::uuid(),
                'videos' => [
                    ['title' => 'What is Forex Trading',                          'order' => 1],
                    ['title' => 'Types of Market in Forex Trading',               'order' => 2],
                    ['title' => 'What are Financial Instruments',                 'order' => 3],
                    ['title' => 'Examples of Financial Instruments and Their Definitions', 'order' => 4],
                    ['title' => 'Types of Commodity Markets',                     'order' => 5],
                ],
            ],
            [
                'title'  => 'Forex Market Fundamentals',
                'level'  => 'beginner',
                'order'  => 2,
                'uuid' => Str::uuid(),
                'videos' => [
                    ['title' => 'Currency Pairs',          'order' => 1],
                    ['title' => 'Base Currency and Quote Currency', 'order' => 2],
                    ['title' => 'Bid Price and Ask Price',  'order' => 3],
                    ['title' => 'Pips, Points, and Lots',  'order' => 4],
                    ['title' => 'Leverage and Margin',     'order' => 5],
                ],
            ],
        ];

        foreach ($chapters as $chapterData) {
            $videos = $chapterData['videos'];
            unset($chapterData['videos']);

            $chapter = $course->chapters()->create([
                'title'          => $chapterData['title'],
                'level'          => $chapterData['level'],
                'order'          => $chapterData['order'],
                'length_seconds' => 0,
                'uuid' => Str::uuid(),
            ]);

            foreach ($videos as $videoData) {
                $chapter->videos()->create([
                    'title'            => $videoData['title'],
                    'youtube_video_id' => 'dQw4w9WgXcQ', // Sample YouTube video ID
                    'order'            => $videoData['order'],
                    'level'            => $chapterData['level'],
                    'duration_seconds' => 300, // 5 minutes default
                    'is_published'     => true,
                    'uuid' => Str::uuid(),
                    'is_preview'       => $videoData['order'] === 1, // First video is preview
                ]);
            }
        }
    }
}