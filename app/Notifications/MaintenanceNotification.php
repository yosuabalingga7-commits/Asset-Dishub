<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MaintenanceNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     * Menerima array $data berisi title, message, url, dan type.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Tentukan channel pengiriman.
     * Kita gunakan 'database' agar muncul di tabel notifications.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Struktur data yang akan disimpan dalam kolom 'data' di tabel database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->data['title'] ?? 'Notifikasi Baru',
            'message' => $this->data['message'] ?? '',
            'url'     => $this->data['url'] ?? '#',
            'type'    => $this->data['type'] ?? 'info', // misal: 'urgent', 'success', 'warning'
        ];
    }
}