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

        $secondCourse = Course::create([
            'title'            => 'The Big Five Strategy Forex Blueprint: Mastering S.R.S.F.T.R from Beginner to Advanced',
            'slug'             => Str::slug('Forex Trading: The Big Five Strategy'),
            'description'      => 'Designed for traders at every level, this blueprint provides a structured path from basic market concepts to advanced backtesting. Learn to manage risk, conquer trading psychology, and execute trades using a powerful 5-point confluence framework tailored for consistent profitability..',
            'level'            => 'intermediate',
            'price'            => 20.00,
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

        $secondCourseChapters = [
            [
                'title' => 'Introduction to Trading',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Basics of Forex Trading', 'youtube_video_id' => '7R6nej27Lrk'],
                    ['title' => 'Financial Instruments and Participants', 'youtube_video_id' => 'cDJWvQr-pak'],
                ],
            ],
            [
                'title' => 'Platforms & Brokers',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Platforms and Brokers', 'youtube_video_id' => '3jTGcgpdoJs'],
                    ['title' => 'Trading Platforms and Cost of Forex Trading', 'youtube_video_id' => 'bYYH1hSrR2s'],
                ],
            ],
            [
                'title' => 'Forex Market Mechanics',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Pairs basics', 'youtube_video_id' => '5rW73pTS3sQ'],
                ],
            ],
            [
                'title' => 'Account Metrics & Execution Orders',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'Trading Account Metrics Part 1', 'youtube_video_id' => 'w7r-EeIvE4I'],
                    ['title' => 'Trading Account Metrics Part 2', 'youtube_video_id' => 'ZY5hHSrbvJc'],
                    ['title' => 'What is leverage', 'youtube_video_id' => 'FymcHw6vSFA'],
                    ['title' => 'Bid, Ask Prices, Spreads, Profit and loss Calculations and Market Orders', 'youtube_video_id' => 'vMB8ChW39SU'],
                ],
            ],
            [
                'title' => 'Trading Outcomes',
                'level' => 'beginner',
                'videos' => [
                    ['title' => 'The Secret of Compounding and Risk To Reward Ratio', 'youtube_video_id' => 'NuMMaIzNHxM'],
                    ['title' => 'Drawdown VS Recovery', 'youtube_video_id' => '_JjTVVQj7d8'],
                ],
            ],
            [
                'title' => 'Risk Management',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Risk management', 'youtube_video_id' => 'fEg59j3dVCs'],
                ],
            ],
            [
                'title' => 'Trading Psychology',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Trading Psychology', 'youtube_video_id' => 'Za2zVs9lzvE'],
                ],
            ],
            [
                'title' => 'Market Analysis',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Types of Market Analysis', 'youtube_video_id' => '9Ed2Ark0FxA'],
                ],
            ],
            [
                'title' => 'Candlesticks and Chart Patterns',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Candlesticks and Chart Patterns', 'youtube_video_id' => 'S1O8zxEka8c'],
                ],
            ],
            [
                'title' => 'Technical Environment & Market Structure',
                'level' => 'intermediate',
                'videos' => [
                    ['title' => 'Trading Tools and Indicators', 'youtube_video_id' => 'wgGdRwuTkzw'],
                    ['title' => 'Market Structure and Price Action Basics', 'youtube_video_id' => 'Ar6BlgPtRuA'],
                    ['title' => 'Market Structure Advanced', 'youtube_video_id' => '2HGVTdxJKYk'],
                    ['title' => 'Trading View Dashboard', 'youtube_video_id' => '_LRLx-Nqf68'],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Support & Resistance (SR)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Support and Resistance Part 1', 'youtube_video_id' => 'hXfgu2lmIuk'],
                    ['title' => 'The Big Five Support and Resistance Part 2', 'youtube_video_id' => 'UE-83mBcTxY'],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Swing Points (S)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Swing Points or Pivots Part 1', 'youtube_video_id' => 'wtvnq3Sz-1Q'],
                    ['title' => 'The Big Five Swing Points or Pivots Part 2', 'youtube_video_id' => 'Q1YRk4DuEZ8'],
                    ['title' => 'The Big Five Swing Points or Pivots Part 3', 'youtube_video_id' => 'G9eYYOFkpgQ'],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Fibonacci (F)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Fibonacci Part 1', 'youtube_video_id' => 'yOB1JHpvsnA'],
                    ['title' => 'The Big Five Fibonacci Part 2', 'youtube_video_id' => 'RlLh30VdtdE'],
                    ['title' => 'The Big Five Fibonacci Part 3', 'youtube_video_id' => 'tIdgRKvbucs'],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Trendline (T)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five Trendline Part 1', 'youtube_video_id' => 'vMXmLU2mSVQ'],
                    ['title' => 'The Big Five Trendline Part 2', 'youtube_video_id' => 'BrTHvied-W8'],
                    ['title' => 'The Big Five Trendline Part 3', 'youtube_video_id' => 'gjjukBHP7CU'],
                ],
            ],
            [
                'title' => 'The Big Five Strategy — Relative Strength Index (RSI)',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'The Big Five RSI Relative Strength Index Part 1', 'youtube_video_id' => 'lyrzb7O2JI4'],
                    ['title' => 'The Big Five RSI Relative Strength Index Part 2', 'youtube_video_id' => 'mVwiB6KX-RU'],
                ],
            ],
            [
                'title' => 'Backtesting — Ranging Markets',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'Types of Market Movements', 'youtube_video_id' => 'ZIKXtw5q9hA'],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 1', 'youtube_video_id' => 'tkD4SNqtQDg'],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 2', 'youtube_video_id' => 'R84rjNTsERc'],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 3', 'youtube_video_id' => 'wW2LftpU3hs'],
                    ['title' => 'Backtesting 3 month Data of Ranging Markets Part 4', 'youtube_video_id' => 'ZFHoDqmAnHg'],
                ],
            ],
            [
                'title' => 'Backtesting — Trending Markets',
                'level' => 'advanced',
                'videos' => [
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 1', 'youtube_video_id' => 'AGdwfchfX0g'],
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 2', 'youtube_video_id' => 'xJ9uchVFFwA'],
                    ['title' => 'Backtesting 3 month Data of the Trending Markets Part 3', 'youtube_video_id' => 'PqMxd9SmiBY'],
                ],
            ],
        ];

        foreach ($secondCourseChapters as $order => $chapterData) {
            $chapter = $secondCourse->chapters()->create([
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
