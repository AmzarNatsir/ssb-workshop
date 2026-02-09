<?php

namespace App\Notifications;

use App\Models\LoanTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnReminderNotification extends Notification
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
                    ->subject('Tool Return Reminder - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('This is a friendly reminder that you have a tool that needs to be returned soon.')
                    ->line('**Loan Details:**')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Tool: ' . $this->loan->tool->name . ' (' . $this->loan->tool->code . ')')
                    ->line('Expected Return Date: ' . $this->loan->expected_return_date->format('d M Y H:i'))
                    ->line('Time Remaining: ' . $timeRemaining['duration'])
                    ->action('View Loan Details', url('/tool-lending/loans/' . $this->loan->loan_number))
                    ->line('Please return the tool on time to avoid overdue charges.')
                    ->line('Thank you for using the Tool Lending System!');
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
        ];
    }
}
