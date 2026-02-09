<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckLoanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:check-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check active loans and send return reminders or overdue notices';

    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking loan reminders...');

        try {
            $result = $this->notificationService->checkAndSendReminders();

            $this->info("Total active loans: {$result['total_active_loans']}");
            $this->info("Return reminders sent: {$result['reminders_sent']}");
            $this->info("Overdue notices sent: {$result['overdue_notices_sent']}");

            if ($result['reminders_sent'] > 0 || $result['overdue_notices_sent'] > 0) {
                $this->info('✓ Notifications sent successfully');
            } else {
                $this->info('✓ No notifications needed at this time');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error checking loan reminders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
