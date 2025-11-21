<?php
class Sprawdzanie_plikow {
    
    public function bezpiecznie_usun_media($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        
        if (!$this->czy_zawarty_gdzieindziej($attachment_id, $file_path)) {
            wp_delete_attachment($attachment_id, true);
            return [
                'status' => 'deleted',
                'file' => basename($file_path),
                'message' => 'Plik bezpiecznie usunięty'
            ];
        } else {
            return [
                'status' => 'skipped',
                'file' => basename($file_path),
                'message' => 'Plik używany w innych miejscach - pominięto'
            ];
        }
    }
    
    public function czy_zawarty_gdzieindziej($attachment_id, $file_path) {
        global $wpdb;
        
        $filename = basename($file_path);
        
        // Sprawdź w treści postów
        $in_content = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_content LIKE %s 
            AND post_status = 'publish'
        ", '%' . $wpdb->esc_like($filename) . '%'));
        
        // Sprawdź w meta postów
        $in_meta = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->postmeta} 
            WHERE meta_value LIKE %s
        ", '%' . $wpdb->esc_like($filename) . '%'));
        
        // Sprawdź w galeriach
        $in_galleries = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_content LIKE %s 
            AND post_status = 'publish'
        ", '%gallery%' . $wpdb->esc_like($attachment_id) . '%'));
        
        return ($in_content > 0 || $in_meta > 0 || $in_galleries > 0);
    }
}