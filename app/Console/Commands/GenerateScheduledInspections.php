<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionSchedule;
use Carbon\Carbon;

class GenerateScheduledInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspections:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate inspection results from active schedules that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inspection generation...');

        // Get all schedules that are due for generation
        $dueSchedules = InspectionSchedule::active()
            ->where('next_generation_at', '<=', now())
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['form', 'unit'])
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No schedules due for generation.');
            return 0;
        }

        $this->info("Found {$dueSchedules->count()} schedule(s) due for generation.");

        $generated = 0;
        $failed = 0;

        foreach ($dueSchedules as $schedule) {
            try {
                // Check if form is still published
                if ($schedule->form->status !== 'PUBLISHED') {
                    $this->warn("Skipping schedule #{$schedule->id}: Form is not published");
                    continue;
                }

                // Generate inspection result
                $result = $schedule->generateNextInspection();

                $this->info("✓ Generated inspection {$result->result_code} for {$schedule->unit->code} - {$schedule->form->form_title}");
                $generated++;

            } catch (\Exception $e) {
                $this->error("✗ Failed to generate inspection for schedule #{$schedule->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("Generated: {$generated}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }
        $this->info("===============\n");

        return 0;
    }
}
