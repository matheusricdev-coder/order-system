<?php

declare(strict_types=1);

namespace App\Mail;

use App\Application\Order\DTO\OrderDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderPaidMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly OrderDTO $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pagamento confirmado — sua compra foi aprovada!');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.paid',
            with: ['order' => $this->order->toArray()],
        );
    }
}
