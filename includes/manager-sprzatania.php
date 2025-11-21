<?php
class Sprzatacz
{
    private $sprawdzanie_plikow;
    private $manager_kwarantanny;

    public function __construct()
    {
        $this->sprawdzanie_plikow = new Sprawdzanie_plikow();
        $this->manager_kwarantanny = new Manager_Kwarantanny();
        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('auto_cleanup_daily_event', [$this, 'process_expired_posts']);
        add_action('init', [$this, 'schedule_daily_cleanup']);
    }

    public function schedule_daily_cleanup()
    {
        if (!wp_next_scheduled('auto_cleanup_daily_event')) {
            wp_schedule_event(time(), 'daily', 'auto_cleanup_daily_event');
        }
    }

    public function process_expired_posts()
    {
        $settings = get_option('przedawniacz_settings', []);
        $logs = [];

        foreach ($settings as $rule) {
            $posts = $this->get_expired_posts($rule);

            foreach ($posts as $post) {
                $result = $this->process_single_post($post, $rule);
                $logs[] = $result;
            }
        }

        $this->save_logs($logs);
    }

    private function get_expired_posts($rule)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$rule['days']} days"));

        return get_posts([
            'post_type' => $rule['post_type'],
            'post_status' => 'publish',
            'date_query' => [
                ['before' => $date]
            ],
            'numberposts' => -1
        ]);
    }

    private function process_single_post($post, $rule)
    {
        $attachments = get_attached_media('', $post->ID);
        $file_logs = [];

        // Sprawdź i usuń załączniki
        foreach ($attachments as $attachment) {
            $file_result = $this->sprawdzanie_plikow->bezpiecznie_usun_media($attachment->ID);
            $file_logs[] = $file_result;
        }

        // Przenieś do kwarantanny lub usuń
        if ($rule['action'] === 'quarantine') {
            $result = $this->manager_kwarantanny->do_kwarantanny($post, $rule['quarantine_days']);
            $message = "Post {$post->ID} przeniesiony do kwarantanny";
        } else {
            wp_delete_post($post->ID, true);
            $message = "Post {$post->ID} usunięty";
        }

        return [
            'timestamp' => current_time('mysql'),
            'post_id' => $post->ID,
            'action' => $rule['action'],
            'message' => $message,
            'file_logs' => $file_logs
        ];
    }

    private function save_logs($logs)
    {
        $existing_logs = get_option('auto_cleanup_logs', []);
        $new_logs = array_merge($existing_logs, $logs);

        // Zachowaj tylko ostatnie 1000 wpisów
        if (count($new_logs) > 1000) {
            $new_logs = array_slice($new_logs, -1000);
        }

        update_option('auto_cleanup_logs', $new_logs);
    }
}
