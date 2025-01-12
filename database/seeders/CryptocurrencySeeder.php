<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use Illuminate\Database\Seeder;

class CryptocurrencySeeder extends Seeder
{
    public function run(): void
    {
        $cryptocurrencies = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'icon_url' => 'https://cryptologos.cc/logos/bitcoin-btc-logo.png',
                'investment_duration_days' => 30,
                'minimum_deposit' => 100.00,
                'maximum_deposit' => 10000.00,
                'profit_percentage' => 0.5,
                'wallet_address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
                'description' => 'Bitcoin is the first and most well-known cryptocurrency.',
                'is_active' => true
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'icon_url' => 'https://cryptologos.cc/logos/ethereum-eth-logo.png',
                'investment_duration_days' => 60,
                'minimum_deposit' => 50.00,
                'maximum_deposit' => 5000.00,
                'profit_percentage' => 0.8,
                'wallet_address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
                'description' => 'Ethereum is a decentralized platform that runs smart contracts.',
                'is_active' => true
            ],
            [
                'name' => 'Binance Coin',
                'symbol' => 'BNB',
                'icon_url' => 'https://cryptologos.cc/logos/bnb-bnb-logo.png',
                'investment_duration_days' => 90,
                'minimum_deposit' => 25.00,
                'maximum_deposit' => 3000.00,
                'profit_percentage' => 1.0,
                'wallet_address' => 'bnb1grpf0955h0ykzq3ar5nmum7y6gdfl6lxfn46h2',
                'description' => 'Binance Coin is the cryptocurrency used to pay for fees on the Binance exchange.',
                'is_active' => true
            ],
            [
                'name' => 'Solana',
                'symbol' => 'SOL',
                'icon_url' => 'https://cryptologos.cc/logos/solana-sol-logo.png',
                'investment_duration_days' => 45,
                'minimum_deposit' => 30.00,
                'maximum_deposit' => 2000.00,
                'profit_percentage' => 1.2,
                'wallet_address' => '5D5PNxoGjkZNxGBqz5qLhSwB6BKHEhZQxpBxjXBcH9Me',
                'description' => 'Solana is a high-performance blockchain platform.',
                'is_active' => true
            ],
            [
                'name' => 'Cardano',
                'symbol' => 'ADA',
                'icon_url' => 'https://cryptologos.cc/logos/cardano-ada-logo.png',
                'investment_duration_days' => 75,
                'minimum_deposit' => 20.00,
                'maximum_deposit' => 1500.00,
                'profit_percentage' => 0.9,
                'wallet_address' => 'addr1qxck8j0cuepn97grdc6m5n83yk0fqh5pg3d6hz7p89zenx',
                'description' => 'Cardano is a proof-of-stake blockchain platform.',
                'is_active' => true
            ]
        ];

        foreach ($cryptocurrencies as $crypto) {
            Cryptocurrency::create($crypto);
        }
    }
}