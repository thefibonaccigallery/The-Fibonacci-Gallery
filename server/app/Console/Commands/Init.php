<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Init extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $phpArtisan = 'php artisan ';
    protected $signature = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the project with default setup';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
        $this->artisan('passport:keys --force');
        $this->registerPassportPersonalClients();
        $this->info('INITIATED');
    }

    /**
     * Runs an artisan command
     *
     * @param string $command
     * @return void
     */
    protected function artisan($command): void {
        passthru($this->phpArtisan . $command);
    }


    protected function registerPassportPersonalClients(): void {
        $authProviders = array_keys(config('auth')['providers']);
        foreach ($authProviders as $provider) {
            $this->artisan('passport:client --personal --name=' . $provider . ' --provider=' . $provider);
        }
    }
}
