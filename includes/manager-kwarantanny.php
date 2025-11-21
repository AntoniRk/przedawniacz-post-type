<?php
class Manager_Kwarantanny
{

    public function do_kwarantanny($post, $quarantine_days)
    {
        // Utwórz post w kwarantannie
        $quarantine_id = wp_insert_post([
            'post_type' => 'quarantine',
            'post_title' => "[Kwarantanna] {$post->post_title}",
            'post_status' => 'publish',
            'post_content' => $post->post_content,
            'meta_input' => [
                'original_post_id' => $post->ID,
                'original_post_type' => $post->post_type,
                'quarantine_until' => date(
                    'Y-m-d H:i:s',
                    strtotime("+{$quarantine_days} days")
                ),
                'quarantine_start' => current_time('mysql')
            ]
        ]);

        // Przenieś załączniki
        $this->przenies_zalaczniki($post->ID, $quarantine_id);

        // Oryginalny post do kosza
        wp_trash_post($post->ID);

        return $quarantine_id;
    }

    private function przenies_zalaczniki($from_post, $to_post)
    {
        $attachments = get_attached_media('', $from_post);

        foreach ($attachments as $attachment) {
            wp_update_post([
                'ID' => $attachment->ID,
                'post_parent' => $to_post
            ]);
        }
    }

    public function kiedy_koniec_kwarantanny()
    {
        $expired_quarantines = get_posts([
            'post_type' => 'quarantine',
            'meta_query' => [
                [
                    'key' => 'quarantine_until',
                    'value' => current_time('mysql'),
                    'compare' => '<=',
                    'type' => 'DATETIME'
                ]
            ],
            'numberposts' => -1
        ]);

        foreach ($expired_quarantines as $quarantine) {
            $this->usun_perma($quarantine->ID);
        }
    }

    private function usun_perma($quarantine_id)
    {
        $attachments = get_attached_media('', $quarantine_id);

        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true);
        }

        wp_delete_post($quarantine_id, true);
    }
}
