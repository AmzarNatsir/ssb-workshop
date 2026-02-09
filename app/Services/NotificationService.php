<?php

namespace App\Services;

use App\Models\LoanTransaction;
use App\Models\LoanReminder;
use App\Models\SystemSetting;
use App\Notifications\ReturnReminderNotification;
use App\Notifications\OverdueNoticeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send return reminder notification
     *
     * @param LoanTransaction $loan
     * @return bool
     */
    public function sendReturnReminder(LoanTransaction $loan)
    {
        try {
            $loan->load(['toolCard.employee', 'tool']);
            
            $employee = $loan->toolCard->employee;
            
            // For now, we'll just log it. You can implement email later
            Log::info("Return reminder sent for loan {$loan->loan_number} to {$employee->name}");
            
            // Create reminder record
            LoanReminder::create([
                'loan_transaction_id' => $loan->id,
                'reminder_type' => 'First Reminder',
                'sent_at' => now(),
                'sent_to' => $employee->name, // You can add email field to employees table
                'status' => 'Sent',
            ]);

            // TODO: Implement actual email notification
            // $employee->notify(new ReturnReminderNotification($loan));

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send return reminder: " . $e->getMessage());
            
            // Log failed reminder
            LoanReminder::create([
                'loan_transaction_id' => $loan->id,
                'reminder_type' => 'First Reminder',
                'sent_at' => now(),
                'sent_to' => $loan->toolCard->employee->name ?? 'Unknown',
                'status' => 'Failed',
            ]);

            return false;
        }
    }

    /**
     * Send overdue notice notification
     *
     * @param LoanTransaction $loan
     * @return bool
     */
    public function sendOverdueNotice(LoanTransaction $loan)
    {
        try {
            $loan->load(['toolCard.employee', 'tool']);
            
            $employee = $loan->toolCard->employee;
            
            // Log overdue notice
            Log::warning("Overdue notice sent for loan {$loan->loan_number} to {$employee->name}");
            
            // Create reminder record
            LoanReminder::create([
                'loan_transaction_id' => $loan->id,
                'reminder_type' => 'Overdue Notice',
                'sent_at' => now(),
                'sent_to' => $employee->name,
                'status' => 'Sent',
            ]);

            // Update loan status to Overdue
            $loan->update(['status' => 'Overdue']);

            // TODO: Implement actual email notification
            // $employee->notify(new OverdueNoticeNotification($loan));

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send overdue notice: " . $e->getMessage());
            
            LoanReminder::create([
                'loan_transaction_id' => $loan->id,
                'reminder_type' => 'Overdue Notice',
                'sent_at' => now(),
                'sent_to' => $loan->toolCard->employee->name ?? 'Unknown',
                'status' => 'Failed',
            ]);

            return false;
        }
    }

    /**
     * Check all active loans and send reminders as needed
     *
     * @return array
     */
    public function checkAndSendReminders()
    {
        $remindersSent = 0;
        $overdueNoticesSent = 0;

        // Get system settings
        $reminderBeforeHours = (int) SystemSetting::get('reminder_before_hours', 2);

        // Get active loans
        $activeLoans = LoanTransaction::with(['toolCard.employee', 'tool'])
            ->active()
            ->get();

        foreach ($activeLoans as $loan) {
            // Check if loan is overdue
            if ($loan->isOverdue()) {
                // Check if overdue notice already sent
                $overdueNoticeSent = $loan->reminders()
                                          ->where('reminder_type', 'Overdue Notice')
                                          ->where('status', 'Sent')
                                          ->exists();

                if (!$overdueNoticeSent) {
                    if ($this->sendOverdueNotice($loan)) {
                        $overdueNoticesSent++;
                    }
                }
            }
            // Check if reminder should be sent
            elseif ($hoursUntilDue <= $reminderBeforeHours && $hoursUntilDue >= 0) {
                // Check if first reminder already sent
                $firstReminderSent = $loan->reminders()
                                          ->where('reminder_type', 'First Reminder')
                                          ->where('status', 'Sent')
                                          ->exists();

                if (!$firstReminderSent) {
                    if ($this->sendReturnReminder($loan)) {
                        $remindersSent++;
                    }
                }
            }
        }

        return [
            'reminders_sent' => $remindersSent,
            'overdue_notices_sent' => $overdueNoticesSent,
            'total_active_loans' => $activeLoans->count(),
        ];
    }
}
