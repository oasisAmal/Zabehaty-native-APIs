<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StoreSectionsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requiredData:store-sections 
                            {--force : Recreate items even if they exist}
                            {--sections=50 : Number of sections to create per type}
                            {--items-per-section=100 : Number of items to add per section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample HomePage / DynamicCategories / DynamicShops sections with items (idempotent)';

    public function handle(): int
    {
        $sectionsCount = (int) $this->option('sections');
        $itemsPerSection = (int) $this->option('items-per-section');
        $force = $this->option('force') ? '--force' : '';

        $this->info("Storing sections data... (Sections: {$sectionsCount}, Items per section: {$itemsPerSection})");
        $this->warn('This command is deprecated. Please use module-specific commands:');
        $this->warn('  - homepage:store-sections');
        $this->warn('  - dynamiccategories:store-sections');
        $this->warn('  - dynamicshops:store-sections');

        $exitCode = 0;

        // Call HomePage command
        $this->info("\n=== Running HomePage command ===");
        $homePageExitCode = $this->call('homepage:store-sections', [
            '--sections' => $sectionsCount,
            '--items-per-section' => $itemsPerSection,
            '--force' => $this->option('force'),
        ]);
        if ($homePageExitCode !== 0) {
            $exitCode = 1;
        }

        // Call DynamicCategories command
        $this->info("\n=== Running DynamicCategories command ===");
        $dynamicCategoriesExitCode = $this->call('dynamiccategories:store-sections', [
            '--sections' => $sectionsCount,
            '--items-per-section' => $itemsPerSection,
            '--categories' => 10,
            '--force' => $this->option('force'),
        ]);
        if ($dynamicCategoriesExitCode !== 0) {
            $exitCode = 1;
        }

        // Call DynamicShops command
        $this->info("\n=== Running DynamicShops command ===");
        $dynamicShopsExitCode = $this->call('dynamicshops:store-sections', [
            '--sections' => $sectionsCount,
            '--items-per-section' => $itemsPerSection,
            '--shops' => 10,
            '--force' => $this->option('force'),
        ]);
        if ($dynamicShopsExitCode !== 0) {
            $exitCode = 1;
        }

        if ($exitCode === 0) {
            $this->info("\nAll sections data stored successfully.");
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
