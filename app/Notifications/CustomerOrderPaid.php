<?php

namespace App\Notifications;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderPaid extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $pdf = PDF::loadView('vendor.simple-commerce.receipt', $this->order->toAugmentedArray());

        return (new MailMessage)
            ->subject('Hvala za vašo prijavo na NM21')
            ->greeting('Pozdravljeni!')
            ->line(__('Hvala za vašo prijavo! Račun plačila je dodan kot priloga.'))
            ->line(__('Če imate kakršnakoli vprašanja, stopite v kontakt.'))
            ->salutation('Lep pozdrav')
            ->attachData(
                $pdf->output(),
                'racun.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
