<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::create([
            'title'            => 'The Complete Forex Trading Course',
            'slug'             => Str::slug('The Complete Forex Trading Course'),
            'description'      => 'A comprehensive course covering everything you need to know about Forex trading from the ground up.',
            'level'            => 'beginner',
            'price'            => 20.00,
            'length_seconds'   => 96960,
            'what_is_included' => [
                'Lifetime access',
                'Progress tracking',
                'Certificate of completion',
                'Downloadable resources',
            ],
            'is_published' => true,
            'published_at' => now(),
            'uuid'         => Str::uuid(),
        ]);

        $chapters = [
            [
                'title' => 'Introduction to Forex Trading',
                'level' => 'beginner',
                'videos' => [
                    'What is Forex Trading',
                    'History and Evolution of Forex Markets',
                ],
            ],
            [
                'title' => 'Forex Market Fundamentals',
                'level' => 'beginner',
                'videos' => [
                    'Currency Pairs Explained',
                    'Bid Price, Ask Price and Spread',
                ],
            ],
            [
                'title' => 'Understanding Market Participants',
                'level' => 'beginner',
                'videos' => [
                    'Banks, Brokers and Retail Traders',
                    'How Market Makers Operate',
                ],
            ],
            [
                'title' => 'Reading Forex Charts',
                'level' => 'beginner',
                'videos' => [
                    'Candlestick Charts vs Line Charts',
                    'Timeframes and When to Use Them',
                ],
            ],
            [
                'title' => 'Technical Analysis Basics',
                'level' => 'intermediate',
                'videos' => [
                    'Support and Resistance Levels',
                    'Trend Lines and Channels',
                ],
            ],
            [
                'title' => 'Popular Trading Indicators',
                'level' => 'intermediate',
                'videos' => [
                    'Moving Averages — SMA and EMA',
                    'RSI, MACD and Bollinger Bands',
                ],
            ],
            [
                'title' => 'Fundamental Analysis',
                'level' => 'intermediate',
                'videos' => [
                    'Economic Indicators That Move Markets',
                    'How to Trade News Events',
                ],
            ],
            [
                'title' => 'Risk Management',
                'level' => 'intermediate',
                'videos' => [
                    'Position Sizing and Lot Sizes',
                    'Setting Stop Loss and Take Profit',
                ],
            ],
            [
                'title' => 'Trading Psychology',
                'level' => 'advanced',
                'videos' => [
                    'Managing Emotions While Trading',
                    'Building a Consistent Trading Mindset',
                ],
            ],
            [
                'title' => 'Building Your Trading Plan',
                'level' => 'advanced',
                'videos' => [
                    'Defining Your Strategy and Rules',
                    'Backtesting and Journaling Your Trades',
                ],
            ],
        ];

        foreach ($chapters as $order => $chapterData) {
            $chapter = $course->chapters()->create([
                'title'          => $chapterData['title'],
                'level'          => $chapterData['level'],
                'order'          => $order + 1,
                'length_seconds' => 0,
                'uuid'           => Str::uuid(),
            ]);

            foreach ($chapterData['videos'] as $videoOrder => $videoTitle) {
                $chapter->videos()->create([
                    'title'            => $videoTitle,
                    'youtube_video_id' => 'dQw4w9WgXcQ',
                    'order'            => $videoOrder + 1,
                    'level'            => $chapterData['level'],
                    'duration_seconds' => 300,
                    'is_published'     => true,
                    'uuid'             => Str::uuid(),
                    'is_preview'       => $videoOrder === 0,
                    'is_big_five'      => false,
                ]);
            }
        }
    }
}
