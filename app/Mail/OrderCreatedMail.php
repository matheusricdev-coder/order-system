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

final class OrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly OrderDTO $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pedido recebido — aguardando pagamento');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.created',
            with: ['order' => $this->order->toArray()],
        );
    }
}
