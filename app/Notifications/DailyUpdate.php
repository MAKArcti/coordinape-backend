<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Carbon\Carbon;
use SnoerenDevelopment\DiscordWebhook\DiscordMessage;
use SnoerenDevelopment\DiscordWebhook\DiscordWebhookChannel;

class DailyUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $name_str, $epoch, $total_gifts_sent, $total_tokens_sent,
              $opt_outs, $has_sent, $total_users, $epoch_num, $circle_name;
    public function __construct($epoch, $name_str, $total_gifts_sent, $total_tokens_sent, $opt_outs, $has_sent, $total_users, $epoch_num, $circle_name)
    {
        $this->epoch = $epoch;
        $this->name_str = $name_str;
        $this->total_gifts_sent = $total_gifts_sent;
        $this->total_tokens_sent = $total_tokens_sent;
        $this->opt_outs = $opt_outs;
        $this->has_sent = $has_sent;
        $this->total_users = $total_users;
        $this->epoch_num = $epoch_num;
        $this->circle_name = $circle_name;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];
        if(config('telegram.token'))
            $channels[] = TelegramChannel::class;
        if($notifiable->discord_webhook)
            $channels[] = DiscordWebhookChannel::class;

        return $channels;
    }

    private function getContent() {

        $alloc_str = $this->name_str;
        $name = '_'.$this->circle_name.'_';
        if($alloc_str) {
            $alloc_str = "Users that made new allocations today:\n" . $alloc_str;
        }
        $diff = $this->epoch->end_date->diffForHumans([
            'parts' => 3,
            'short' => true
        ]);
        $start_date = $this->epoch->start_date->format('Y/m/d');
        $end_date = $this->epoch->end_date->format('Y/m/d');

        $stats_content = "Total Allocations: *$this->total_gifts_sent*\nGIVES sent: *$this->total_tokens_sent*\nOpt Outs: *$this->opt_outs*\nUsers Allocated: *$this->has_sent/$this->total_users*";
        return "$name - *epoch $this->epoch_num*\n\n_{$start_date} to {$end_date}_\n\n$stats_content\nepoch ending *$diff* !\n\n$alloc_str";
    }

    public function toTelegram($notifiable=null)
    {

        return TelegramMessage::create()
            // Markdown supported.
            ->content($this->getContent());
    }

    public function toDiscord($notifiable=null)
    {
        return DiscordMessage::create()
            ->content($this->getContent());
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
