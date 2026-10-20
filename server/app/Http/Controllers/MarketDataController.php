<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MarketDataController extends Controller
{
    /**
     * Symbol definitions: Yahoo Finance ticker => display metadata.
     */
    private array $symbols = [
        'indices' => [
            '%5EGSPC'  => ['name' => 'S&P 500',    'symbol' => 'SPX',  'prefix' => ''],
            '%5ENDX'   => ['name' => 'Nasdaq 100',  'symbol' => 'NDX',  'prefix' => ''],
            '%5EDJI'   => ['name' => 'Dow Jones',   'symbol' => 'DJI',  'prefix' => ''],
            '%5EFTSE'  => ['name' => 'FTSE 100',    'symbol' => 'FTSE', 'prefix' => ''],
        ],
        'forex' => [
            'EURUSD%3DX' => ['name' => 'EUR / USD', 'symbol' => 'EURUSD', 'prefix' => ''],
            'GBPUSD%3DX' => ['name' => 'GBP / USD', 'symbol' => 'GBPUSD', 'prefix' => ''],
            'JPY%3DX'    => ['name' => 'USD / JPY', 'symbol' => 'USDJPY', 'prefix' => ''],
            'AUDUSD%3DX' => ['name' => 'AUD / USD', 'symbol' => 'AUDUSD', 'prefix' => ''],
        ],
        'crypto' => [
            'BTC-USD' => ['name' => 'Bitcoin',  'symbol' => 'BTCUSD', 'prefix' => '$'],
            'ETH-USD' => ['name' => 'Ethereum', 'symbol' => 'ETHUSD', 'prefix' => '$'],
            'SOL-USD' => ['name' => 'Solana',   'symbol' => 'SOLUSD', 'prefix' => '$'],
            'ADA-USD' => ['name' => 'Cardano',  'symbol' => 'ADAUSD', 'prefix' => '$'],
        ],
        'commodities' => [
            'GC%3DF' => ['name' => 'Gold',        'symbol' => 'XAUUSD', 'prefix' => '$'],
            'SI%3DF' => ['name' => 'Silver',       'symbol' => 'XAGUSD', 'prefix' => '$'],
            'CL%3DF' => ['name' => 'Crude Oil',    'symbol' => 'USOIL',  'prefix' => '$'],
            'NG%3DF' => ['name' => 'Natural Gas',  'symbol' => 'NGAS',   'prefix' => '$'],
        ],
    ];

    /** Cache TTL in seconds */
    private int $cacheTtl = 60;

    public function index(): JsonResponse
    {
        $data = Cache::remember('market_data_v2', $this->cacheTtl, function () {
            return $this->fetchAll();
        });

        return response()->json($data);
    }

    private function fetchAll(): array
    {
        $result = [
            'indices'     => [],
            'forex'       => [],
            'crypto'      => [],
            'commodities' => [],
            'fetched_at'  => now()->toIso8601String(),
        ];

        // Build concurrent HTTP requests — one per symbol
        $requests = [];
        foreach ($this->symbols as $category => $items) {
            foreach ($items as $encodedTicker => $meta) {
                $requests[] = [
                    'category'       => $category,
                    'encodedTicker'  => $encodedTicker,
                    'meta'           => $meta,
                    'url'            => "https://query1.finance.yahoo.com/v8/finance/chart/{$encodedTicker}?interval=1d&range=1d",
                ];
            }
        }

        // Fire all requests concurrently using Laravel's pool
        $responses = Http::pool(function ($pool) use ($requests) {
            $poolRequests = [];
            foreach ($requests as $req) {
                $poolRequests[] = $pool
                    ->timeout(8)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept'     => 'application/json',
                    ])
                    ->get($req['url']);
            }
            return $poolRequests;
        });

        foreach ($responses as $idx => $response) {
            $req = $requests[$idx];

            try {
                if (!($response instanceof \Illuminate\Http\Client\Response) || !$response->successful()) {
                    continue;
                }

                $json   = $response->json();
                $result_set = $json['chart']['result'][0] ?? null;
                if (!$result_set) {
                    continue;
                }

                $meta  = $result_set['meta'] ?? [];
                $price = $meta['regularMarketPrice'] ?? null;
                $prev  = $meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null;

                if ($price === null || $prev === null || $prev == 0) {
                    continue;
                }

                $changePct = (($price - $prev) / $prev) * 100;
                $category  = $req['category'];

                // Smart decimal formatting
                $decimals = match (true) {
                    $category === 'forex'             => 4,
                    $price >= 100                     => 2,
                    $price >= 1                       => 2,
                    $category === 'commodities'       => 3,
                    $category === 'crypto'            => 4,
                    default                           => 4,
                };

                $result[$category][] = [
                    'name'   => $req['meta']['name'],
                    'symbol' => $req['meta']['symbol'],
                    'prefix' => $req['meta']['prefix'],
                    'value'  => number_format($price, $decimals),
                    'change' => ($changePct >= 0 ? '+' : '') . number_format($changePct, 2) . '%',
                    'up'     => $changePct >= 0,
                    'raw'    => $price,
                ];
            } catch (\Throwable $e) {
                Log::warning("Market fetch error for {$req['encodedTicker']}: " . $e->getMessage());
            }
        }

        return $result;
    }
}
