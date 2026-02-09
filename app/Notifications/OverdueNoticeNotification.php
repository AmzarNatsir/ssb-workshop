<?php

namespace App\Notifications;

use App\Models\LoanTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueNoticeNotification extends Notification
{
    use Queueable;

    protected $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(LoanTransaction $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $timeRemaining = $this->loan->getTimeRemaining();
        
        return (new MailMessage)
                    ->error()
                    ->subject('OVERDUE: Tool Return Notice - ' . $this->loan->loan_number)
                    ->greeting('URGENT: ' . $notifiable->name)
                    ->line('Your borrowed tool is now **OVERDUE** and must be returned immediately.')
                    ->line('**Loan Details:**')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Tool: ' . $this->loan->tool->name . ' (' . $this->loan->tool->code . ')')
                    ->line('Expected Return Date: ' . $this->loan->expected_return_date->format('d M Y H:i'))
                    ->line('Overdue By: ' . $timeRemaining['duration'])
                    ->action('Return Tool Now', url('/tool-lending/loans/' . $this->loan->loan_number . '/return'))
                    ->line('**IMPORTANT:** Please return this tool immediately to avoid further penalties.')
                    ->line('If you have already returned the tool, please contact the Tool Room administrator.')
                    ->line('Thank you for your immediate attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_number' => $this->loan->loan_number,
            'tool_name' => $this->loan->tool->name,
            'expected_return_date' => $this->loan->expected_return_date,
            'overdue' => true,
        ];
    }
}
