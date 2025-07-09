<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = "https://localhost:3000/reset-password?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Você solicitou a redefinição da sua senha.')
            ->action('Redefinir Senha', $url)
            ->line('Este link expira em 30 minutos.')
            ->line('Se você não solicitou isso, ignore este e-mail.')
            ->salutation('Atenciosamente, Sistema de Passagens de Barco');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
