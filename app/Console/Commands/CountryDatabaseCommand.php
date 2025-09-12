<?php

namespace App\Console\Commands;

use App\Services\Common\DatabaseConnectionService;
use App\Helpers\DatabaseHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CountryDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'country:db 
                            {action : The action to perform (migrate|seed|status|fresh)}
                            {--country= : Specific country code (AE, SA, OM, KW, BH)}
                            {--all : Apply to all countries}
                            {--seeder= : Seeder class name for seed action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage database operations for country-specific databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $country = $this->option('country');
        $all = $this->option('all');

        if (!$country && !$all) {
            $this->error('Please specify either --country or --all option');
            return 1;
        }

        $countries = $all ? DatabaseConnectionService::getAvailableConnections() : [strtolower($country)];

        foreach ($countries as $countryCode) {
            $this->info("Processing country: " . strtoupper($countryCode));
            
            try {
                switch ($action) {
                    case 'migrate':
                        $this->migrateCountry($countryCode);
                        break;
                    case 'seed':
                        $this->seedCountry($countryCode);
                        break;
                    case 'status':
                        $this->showStatus($countryCode);
                        break;
                    case 'fresh':
                        $this->freshCountry($countryCode);
                        break;
                    default:
                        $this->error("Unknown action: {$action}");
                        return 1;
                }
            } catch (\Exception $e) {
                $this->error("Error processing country {$countryCode}: " . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * Migrate database for a country
     */
    private function migrateCountry(string $countryCode): void
    {
        $this->info("Migrating database for country: " . strtoupper($countryCode));
        
        DatabaseConnectionService::withConnection($countryCode, function () {
            Artisan::call('migrate', ['--force' => true]);
        });
        
        $this->info("Migration completed for " . strtoupper($countryCode));
    }

    /**
     * Seed database for a country
     */
    private function seedCountry(string $countryCode): void
    {
        $seeder = $this->option('seeder');
        
        if (!$seeder) {
            $this->error('Please specify --seeder option for seed action');
            return;
        }

        $this->info("Seeding database for country: " . strtoupper($countryCode));
        
        DatabaseConnectionService::withConnection($countryCode, function () use ($seeder) {
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true
            ]);
        });
        
        $this->info("Seeding completed for " . strtoupper($countryCode));
    }

    /**
     * Show database status for a country
     */
    private function showStatus(string $countryCode): void
    {
        $status = DatabaseHelpers::getConnectionsStatus();
        
        if (isset($status[$countryCode])) {
            $info = $status[$countryCode];
            $this->info("Country: " . strtoupper($countryCode));
            $this->info("Status: " . $info['status']);
            
            if ($info['status'] === 'connected') {
                $this->info("Database: " . $info['database']);
                $this->info("Host: " . $info['host']);
                $this->info("Port: " . $info['port']);
            } else {
                $this->error("Error: " . $info['error']);
            }
        } else {
            $this->error("No connection found for country: " . strtoupper($countryCode));
        }
    }

    /**
     * Fresh migrate database for a country
     */
    private function freshCountry(string $countryCode): void
    {
        $this->info("Fresh migrating database for country: " . strtoupper($countryCode));
        
        DatabaseConnectionService::withConnection($countryCode, function () {
            Artisan::call('migrate:fresh', ['--force' => true]);
        });
        
        $this->info("Fresh migration completed for " . strtoupper($countryCode));
    }
}
