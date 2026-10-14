<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::query()->delete();

        $course = Course::create([
            'title'            => 'The Big Five Strategy Forex Blueprint: Mastering S.R.S.F.T.R from Beginner to Advanced',
            'slug'             => Str::slug('Forex Trading: The Big Five Strategy'),
            'description'      => 'Designed for traders at every level, this blueprint provides a structured path from basic market concepts to advanced backtesting. Learn to manage risk, conquer trading psychology, and execute trades using a powerful 5-point confluence framework tailored for consistent profitability..',
            'level'            => 'intermediate',
            'price'            => 00.00,
            'length_seconds'   => 0,
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
                'title' => 'Introduction to Trading',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Basics of Forex Trading', 'youtube_video_id' => '7R6nej27Lrk', 'duration_seconds' => 350],
                    ['title' => 'Financial Instruments and Participants', 'youtube_video_id' => 'cDJWvQr-pak', 'duration_seconds' => 1009],
                ],
            ],
            [
                'title' => 'Platforms & Brokers',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Platforms and Brokers', 'youtube_video_id' => '3jTGcgpdoJs', 'duration_seconds' => 483],
                    ['title' => 'Trading Platforms and Cost of Forex Trading', 'youtube_video_id' => 'bYYH1hSrR2s', 'duration_seconds' => 583],
                ],
            ],
            [
                'title' => 'Forex Market Mechanics',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Pairs basics', 'youtube_video_id' => '5rW73pTS3sQ', 'duration_seconds' => 718],
                ],
            ],
            [
                'title' => 'Account Metrics & Execution Orders',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Account Metrics', 'youtube_video_id' => 'w7r-EeIvE4I', 'duration_seconds' => 597],
                    ['title' => 'What is leverage', 'youtube_video_id' => 'FymcHw6vSFA', 'duration_seconds' => 547],
                    ['title' => 'Bid, Ask Prices, Spreads, Profit and loss Calculations and Market Orders', 'youtube_video_id' => '2xFcbACh8Eg', 'duration_seconds' => 726],
                ],
            ],
            [
                'title' => 'Trading Outcomes',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'The Secret of Compounding and Risk To Reward Ratio', 'youtube_video_id' => 'NuMMaIzNHxM', 'duration_seconds' => 597],
                    ['title' => 'Drawdown VS Recovery', 'youtube_video_id' => '_JjTVVQj7d8', 'duration_seconds' => 557],
                ],
            ],
            [
                'title' => 'Risk Management',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Risk management', 'youtube_video_id' => 'fEg59j3dVCs', 'duration_seconds' => 835],
                ],
            ],
            [
                'title' => 'Trading Psychology',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Trading Psychology', 'youtube_video_id' => 'Za2zVs9lzvE', 'duration_seconds' => 745],
                ],
            ],
            [
                'title' => 'Market Analysis',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Types of Market Analysis', 'youtube_video_id' => '9Ed2Ark0FxA', 'duration_seconds' => 734],
                ],
            ],
            [
                'title' => 'Candlesticks and Chart Patterns',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Candlesticks and Chart Patterns', 'youtube_video_id' => 'S1O8zxEka8c', 'duration_seconds' => 571],
                ],
            ],
            [
                'title' => 'Technical Environment & Market Structure',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Trading Tools and Indicators', 'youtube_video_id' => 'wgGdRwuTkzw', 'duration_seconds' => 568],
                    ['title' => 'Trading View Dashboard', 'youtube_video_id' => '_LRLx-Nqf68', 'duration_seconds' => 853],
                    ['title' => 'Market Structure and Price Action Basics', 'youtube_video_id' => 'O2z3Jgvv31c', 'duration_seconds' => 845],
                    ['title' => 'Market Structure Advanced', 'youtube_video_id' => '2HGVTdxJKYk', 'duration_seconds' => 811],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Support & Resistance (SR)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Support and Resistance Part 1', 'youtube_video_id' => 'hXfgu2lmIuk', 'duration_seconds' => 1132],
                    ['title' => 'The Big Five Support and Resistance Part 2', 'youtube_video_id' => 'UE-83mBcTxY', 'duration_seconds' => 1169],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Swing Points (S)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Swing Points or Pivots Part 1', 'youtube_video_id' => 'wtvnq3Sz-1Q', 'duration_seconds' => 1026],
                    ['title' => 'The Big Five Swing Points or Pivots Part 2', 'youtube_video_id' => 'Q1YRk4DuEZ8', 'duration_seconds' => 694],
                    ['title' => 'The Big Five Swing Points or Pivots Part 3', 'youtube_video_id' => 'G9eYYOFkpgQ', 'duration_seconds' => 1080],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Fibonacci (F)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Fibonacci Part 1', 'youtube_video_id' => 'yOB1JHpvsnA', 'duration_seconds' => 1182],
                    ['title' => 'The Big Five Fibonacci Part 2', 'youtube_video_id' => 'RlLh30VdtdE', 'duration_seconds' => 579],
                    ['title' => 'The Big Five Fibonacci Part 3', 'youtube_video_id' => 'tIdgRKvbucs', 'duration_seconds' => 1908],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Trendline (T)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Trendline Part 1', 'youtube_video_id' => 'vMXmLU2mSVQ', 'duration_seconds' => 1156],
                    ['title' => 'The Big Five Trendline Part 2', 'youtube_video_id' => 'BrTHvied-W8', 'duration_seconds' => 1242],
                    ['title' => 'The Big Five Trendline Part 3', 'youtube_video_id' => 'gjjukBHP7CU', 'duration_seconds' => 708],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Relative Strength Index (RSI)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five RSI Relative Strength Index Part 1', 'youtube_video_id' => 'lyrzb7O2JI4', 'duration_seconds' => 999],
                    ['title' => 'The Big Five RSI Relative Strength Index Part 2', 'youtube_video_id' => 'mVwiB6KX-RU', 'duration_seconds' => 1492],
                ],
            ],
            [
                'title' => 'Backtesting — Ranging Markets',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'Types of Market Movements', 'youtube_video_id' => 'ZIKXtw5q9hA', 'duration_seconds' => 179],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 1', 'youtube_video_id' => 'tkD4SNqtQDg', 'duration_seconds' => 1204],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 2', 'youtube_video_id' => 'R84rjNTsERc', 'duration_seconds' => 1222],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 3', 'youtube_video_id' => 'wW2LftpU3hs', 'duration_seconds' => 1212],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 4', 'youtube_video_id' => 'ZFHoDqmAnHg', 'duration_seconds' => 1428],
                ],
            ],
            [
                'title' => 'Backtesting — Trending Markets',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 1', 'youtube_video_id' => 'AGdwfchfX0g', 'duration_seconds' => 1223],
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 2', 'youtube_video_id' => 'xJ9uchVFFwA', 'duration_seconds' => 1272],
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 3', 'youtube_video_id' => 'PqMxd9SmiBY', 'duration_seconds' => 1186],
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

            foreach ($chapterData['videos'] as $videoOrder => $videoData) {
                $chapter->videos()->create([
                    'title'            => $videoData['title'],
                    'youtube_video_id' => $videoData['youtube_video_id'],
                    'order'            => $videoOrder + 1,
                    'level'            => $chapterData['level'],
                    'duration_seconds' => $videoData['duration_seconds'] ?? 0,
                    'is_published'     => true,
                    'uuid'             => Str::uuid(),
                    'is_preview'       => $videoOrder === 0,
                    'is_big_five'      => false,
                ]);
            }

            $chapter->updateLengthSeconds();
        }

        $course->updateLengthSeconds();
    }
}
