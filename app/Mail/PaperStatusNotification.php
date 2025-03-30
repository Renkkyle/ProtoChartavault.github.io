<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaperStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $paper;
    public $statusMessage;
    public $remarks; // Add remarks as a public property

    
    /**
     * Create a new message instance.
     *
     * @param $paper
     * @param $statusMessage
     * @param $remarks
     */
    public function __construct($paper, $statusMessage,$remarks = null)
    {
        $this->paper = $paper;
        $this->statusMessage = $statusMessage;
        $this->remarks = $remarks; // Set the remarks

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Paper Status Update: {$this->paper->status}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'components.paper_status_notification', // Update blade file path
            with: [
                'paperTitle' => $this->paper->title,
                'status' => $this->paper->status,
                'statusMessage' => $this->statusMessage,
                'remarks' => $this->remarks, // Pass remarks to the blade view
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
