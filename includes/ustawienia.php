<?php
class Strona_Ustawienia {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Przedawniacz post-type ustawienia',
            'Przedawniacz post-type',
            'manage_options',
            'przedawnicz-post-type-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function init_settings() {
        register_setting('przedawniacz_settings', 'przedawniacz_settings');
        
        add_settings_section(
            'auto_cleanup_rules_section',
            'Reguły automatycznego czyszczenia',
            null,
            'auto-cleanup-settings'
        );
    }
    
    public function enqueue_assets($hook) {
        if ($hook !== 'settings_page_auto-cleanup-settings') return;
        
        wp_enqueue_script(
            'auto-cleanup-admin',
            AUTO_CLEANUP_PLUGIN_URL . 'assets/admin.js',
            ['jquery'],
            '1.0',
            true
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Ustawienia Przedawniacz post-type</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('przedawniacz_settings');
                $settings = get_option('przedawniacz_settings', []);
                ?>
                
                <div id="cleanup-rules">
                    <h3>Reguły czyszczenia</h3>
                    <?php $this->render_rules_table($settings); ?>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="cleanup-logs">
                <h3>Logi operacji</h3>
                <?php $this->render_logs_table(); ?>
            </div>
        </div>
        <?php
    }
    
    private function render_rules_table($settings) {
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Post Type</th>
                    <th>Ilość dni</th>
                    <th>Akcja</th>
                    <th>Dni kwarantanny</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody id="rules-container">
                <?php foreach ($settings as $index => $rule): ?>
                <tr class="rule-row">
                    <td>
                        <input type="text" 
                               name="przedawniacz_settings[<?php echo $index; ?>][post_type]" 
                               value="<?php echo esc_attr($rule['post_type']); ?>"
                               required>
                    </td>
                    <td>
                        <input type="number" 
                               name="przedawniacz_settings[<?php echo $index; ?>][days]" 
                               value="<?php echo esc_attr($rule['days']); ?>"
                               min="1" required>
                    </td>
                    <td>
                        <select name="przedawniacz_settings[<?php echo $index; ?>][action]">
                            <option value="delete" <?php selected($rule['action'], 'delete'); ?>>Usuń</option>
                            <option value="quarantine" <?php selected($rule['action'], 'quarantine'); ?>>Kwarantanna</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" 
                               name="przedawniacz_settings[<?php echo $index; ?>][quarantine_days]" 
                               value="<?php echo esc_attr($rule['quarantine_days'] ?? 30); ?>"
                               min="1" <?php echo ($rule['action'] === 'quarantine') ? 'required' : ''; ?>>
                    </td>
                    <td>
                        <button type="button" class="button remove-rule">Usuń</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" id="add-rule" class="button">Dodaj regułę</button>
        <?php
    }
    
    private function render_logs_table() {
        $logs = get_option('auto_cleanup_logs', []);
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Post ID</th>
                    <th>Akcja</th>
                    <th>Wiadomość</th>
                    <th>Pliki</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($logs) as $log): ?>
                <tr>
                    <td><?php echo $log['timestamp']; ?></td>
                    <td><?php echo $log['post_id']; ?></td>
                    <td><?php echo $log['action']; ?></td>
                    <td><?php echo $log['message']; ?></td>
                    <td>
                        <?php foreach ($log['file_logs'] as $file_log): ?>
                            <div><?php echo "{$file_log['file']}: {$file_log['message']}"; ?></div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}