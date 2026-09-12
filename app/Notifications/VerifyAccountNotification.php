<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class VerifyAccountNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $isCashier = strtolower($notifiable->role) === 'cashier';

        $mail = (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($isCashier) {
            $mail->subject('Welcome to Earthbred - Verify & Activate Your Cashier Account')
                 ->line('An account has been created for you at Earthbred Coffee Studio POS as a Cashier.')
                 ->line('Please click the button below to verify your email address and activate your account:')
                 ->action('Verify & Activate Cashier Account', $verificationUrl)
                 ->line('Once your account is activated, you can log in to the POS terminal using your 6-digit PIN.');
        } else {
            $mail->subject('Welcome to Earthbred - Verify Your Account')
                 ->line('An account has been created for you at Earthbred Coffee Studio POS as a ' . ucfirst($notifiable->role) . '.')
                 ->line('Please click the button below to verify your email address and activate your account.')
                 ->action('Verify & Activate Account', $verificationUrl);
        }

        $mail->line('If you did not request this account, please contact the store manager.');

        return $mail;
    }
}
