<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-admin-tools"></span> Auto Delete Post Settings</h1>
    
    <div class="adp-admin-container">
        <div class="adp-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#settings" class="nav-tab nav-tab-active">Settings</a>
                <a href="#status" class="nav-tab">Status & Statistics</a>
                <a href="#logs" class="nav-tab">Logs</a>
            </nav>
        </div>
        
        <div id="settings" class="adp-tab-content adp-tab-active">
            <form method="post" action="">
                <?php settings_fields('auto_delete_post_settings'); ?>
                <?php wp_nonce_field('auto_delete_post_settings-options'); ?>
                
                <div class="adp-card">
                    <h2><span class="dashicons dashicons-admin-generic"></span> Global Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Deletion Process</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[enabled]" value="1" <?php checked($settings['enabled']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description">Master switch to enable or disable the entire deletion process.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Cron Interval</th>
                            <td>
                                <select name="auto_delete_post_settings[cron_interval]">
                                    <?php foreach ($this->getCronIntervals() as $value => $label): ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['cron_interval'], $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">How often the deletion process should run.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="adp-card">
                    <h2><span class="dashicons dashicons-admin-post"></span> Posts Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Post Deletion</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[posts][enabled]" value="1" <?php checked($settings['posts']['enabled']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Posts per Execution</th>
                            <td>
                                <input type="number" name="auto_delete_post_settings[posts][limit]" value="<?php echo esc_attr($settings['posts']['limit']); ?>" min="1" max="1000" class="small-text">
                                <p class="description">Maximum number of posts to delete in each execution.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Delete Attachments</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[posts][delete_attachments]" value="1" <?php checked($settings['posts']['delete_attachments']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description"><strong>Warning:</strong> This will permanently delete all images and files attached to posts.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><span class="dashicons dashicons-filter"></span> Date Filter Settings</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Date Filter</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[posts][date_filter_enabled]" value="1" <?php checked(isset($settings['posts']['date_filter_enabled']) ? $settings['posts']['date_filter_enabled'] : false); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description">Enable filtering posts by date range.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Date Filter Mode</th>
                            <td>
                                <label>
                                    <input type="radio" name="auto_delete_post_settings[posts][date_filter_mode]" value="include" <?php checked(isset($settings['posts']['date_filter_mode']) ? $settings['posts']['date_filter_mode'] : 'include', 'include'); ?>>
                                    Include posts from this period
                                </label><br>
                                <label>
                                    <input type="radio" name="auto_delete_post_settings[posts][date_filter_mode]" value="exclude" <?php checked(isset($settings['posts']['date_filter_mode']) ? $settings['posts']['date_filter_mode'] : 'include', 'exclude'); ?>>
                                    Exclude posts from this period
                                </label>
                                <p class="description">Choose whether to include or exclude posts from the specified date range.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Start Date</th>
                            <td>
                                <input type="date" name="auto_delete_post_settings[posts][date_start]" value="<?php echo esc_attr(isset($settings['posts']['date_start']) ? $settings['posts']['date_start'] : ''); ?>" class="regular-text">
                                <p class="description">Start date for the filter range.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">End Date</th>
                            <td>
                                <input type="date" name="auto_delete_post_settings[posts][date_end]" value="<?php echo esc_attr(isset($settings['posts']['date_end']) ? $settings['posts']['date_end'] : ''); ?>" class="regular-text">
                                <p class="description">End date for the filter range.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><span class="dashicons dashicons-admin-users"></span> User Filter Settings</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable User Filter</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[posts][user_filter_enabled]" value="1" <?php checked(isset($settings['posts']['user_filter_enabled']) ? $settings['posts']['user_filter_enabled'] : false); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description">Enable filtering posts by specific users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">User Filter Mode</th>
                            <td>
                                <label>
                                    <input type="radio" name="auto_delete_post_settings[posts][user_filter_mode]" value="include" <?php checked(isset($settings['posts']['user_filter_mode']) ? $settings['posts']['user_filter_mode'] : 'include', 'include'); ?>>
                                    Include posts from selected users
                                </label><br>
                                <label>
                                    <input type="radio" name="auto_delete_post_settings[posts][user_filter_mode]" value="exclude" <?php checked(isset($settings['posts']['user_filter_mode']) ? $settings['posts']['user_filter_mode'] : 'include', 'exclude'); ?>>
                                    Exclude posts from selected users
                                </label>
                                <p class="description">Choose whether to include or exclude posts from the selected users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Select Users</th>
                            <td>
                                <?php
                                $users = get_users(array('fields' => array('ID', 'display_name')));
                                $selectedUsers = isset($settings['posts']['selected_users']) ? $settings['posts']['selected_users'] : array();
                                ?>
                                <div class="adp-user-selection">
                                    <?php foreach ($users as $user): ?>
                                        <label class="adp-user-checkbox">
                                            <input type="checkbox" name="auto_delete_post_settings[posts][selected_users][]" value="<?php echo esc_attr($user->ID); ?>" <?php checked(in_array($user->ID, $selectedUsers)); ?>>
                                            <?php echo esc_html($user->display_name); ?> (ID: <?php echo esc_html($user->ID); ?>)
                                        </label><br>
                                    <?php endforeach; ?>
                                </div>
                                <p class="description">Select specific users to include or exclude from deletion.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="adp-card">
                    <h2><span class="dashicons dashicons-admin-comments"></span> Comments Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Comment Deletion</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[comments][enabled]" value="1" <?php checked($settings['comments']['enabled']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Comments per Execution</th>
                            <td>
                                <input type="number" name="auto_delete_post_settings[comments][limit]" value="<?php echo esc_attr($settings['comments']['limit']); ?>" min="1" max="500" class="small-text">
                                <p class="description">Maximum number of comments to delete in each execution.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="adp-card">
                    <h2><span class="dashicons dashicons-category"></span> Categories Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Category Deletion</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[categories][enabled]" value="1" <?php checked($settings['categories']['enabled']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Categories per Execution</th>
                            <td>
                                <input type="number" name="auto_delete_post_settings[categories][limit]" value="<?php echo esc_attr($settings['categories']['limit']); ?>" min="1" max="100" class="small-text">
                                <p class="description">Maximum number of categories to delete in each execution.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Delete Only Empty Categories</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[categories][delete_empty_only]" value="1" <?php checked($settings['categories']['delete_empty_only']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description">When enabled, only categories without any posts will be deleted.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="adp-card">
                    <h2><span class="dashicons dashicons-tag"></span> Tags Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Tag Deletion</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[tags][enabled]" value="1" <?php checked($settings['tags']['enabled']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Tags per Execution</th>
                            <td>
                                <input type="number" name="auto_delete_post_settings[tags][limit]" value="<?php echo esc_attr($settings['tags']['limit']); ?>" min="1" max="100" class="small-text">
                                <p class="description">Maximum number of tags to delete in each execution.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Delete Only Empty Tags</th>
                            <td>
                                <label class="adp-toggle">
                                    <input type="checkbox" name="auto_delete_post_settings[tags][delete_empty_only]" value="1" <?php checked($settings['tags']['delete_empty_only']); ?>>
                                    <span class="adp-toggle-slider"></span>
                                </label>
                                <p class="description">When enabled, only tags without any posts will be deleted.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button('Save Settings', 'primary', 'submit'); ?>
            </form>
        </div>
        
        <div id="status" class="adp-tab-content">
            <div class="adp-card">
                <h2><span class="dashicons dashicons-dashboard"></span> Current Status</h2>
                <div class="adp-status-grid" id="status-container">
                    <div class="adp-status-item">
                        <h3>Global Status</h3>
                        <?php echo $this->getStatusBadge($settings['enabled']); ?>
                    </div>
                    <div class="adp-status-item">
                        <h3>Next Execution</h3>
                        <span class="adp-status-value">
                            <?php echo $nextScheduled ? date('Y-m-d H:i:s', $nextScheduled) : 'Not scheduled'; ?>
                        </span>
                    </div>
                    <div class="adp-status-item">
                        <h3>Posts Deletion</h3>
                        <?php echo $this->getStatusBadge($settings['posts']['enabled']); ?>
                    </div>
                    <div class="adp-status-item">
                        <h3>Comments Deletion</h3>
                        <?php echo $this->getStatusBadge($settings['comments']['enabled']); ?>
                    </div>
                    <div class="adp-status-item">
                        <h3>Categories Deletion</h3>
                        <?php echo $this->getStatusBadge($settings['categories']['enabled']); ?>
                    </div>
                    <div class="adp-status-item">
                        <h3>Tags Deletion</h3>
                        <?php echo $this->getStatusBadge($settings['tags']['enabled']); ?>
                    </div>
                </div>
            </div>
            
            <div class="adp-card">
                <h2><span class="dashicons dashicons-chart-bar"></span> Statistics</h2>
                <div class="adp-stats-actions">
                    <button type="button" class="button button-secondary" id="refresh-statistics">
                        <span class="dashicons dashicons-update"></span> Refresh
                    </button>
                    <button type="button" class="button button-secondary" id="reset-statistics">Reset Statistics</button>
                </div>
                <div class="adp-stats-grid" id="statistics-container">
                    <div class="adp-stat-item">
                        <h3>Posts Deleted</h3>
                        <span class="adp-stat-number"><?php echo number_format($statistics['posts_deleted']); ?></span>
                    </div>
                    <div class="adp-stat-item">
                        <h3>Comments Deleted</h3>
                        <span class="adp-stat-number"><?php echo number_format($statistics['comments_deleted']); ?></span>
                    </div>
                    <div class="adp-stat-item">
                        <h3>Categories Deleted</h3>
                        <span class="adp-stat-number"><?php echo number_format($statistics['categories_deleted']); ?></span>
                    </div>
                    <div class="adp-stat-item">
                        <h3>Tags Deleted</h3>
                        <span class="adp-stat-number"><?php echo number_format($statistics['tags_deleted']); ?></span>
                    </div>
                    <div class="adp-stat-item">
                        <h3>Total Executions</h3>
                        <span class="adp-stat-number"><?php echo number_format($statistics['total_executions']); ?></span>
                    </div>
                    <div class="adp-stat-item">
                        <h3>Last Execution</h3>
                        <span class="adp-stat-value"><?php echo $this->formatTimestamp($statistics['last_execution_time']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="logs" class="adp-tab-content">
            <div class="adp-card">
                <h2><span class="dashicons dashicons-media-text"></span> Recent Logs</h2>
                <div class="adp-logs-actions">
                    <button type="button" class="button button-secondary" id="download-logs">Download Logs</button>
                    <button type="button" class="button button-secondary" id="clear-logs">Clear Logs</button>
                    <button type="button" class="button button-secondary" id="refresh-logs">Refresh</button>
                </div>
                
                <div class="adp-logs-container" id="logs-container">
                    <?php if (empty($logs)): ?>
                        <p class="adp-no-logs">No logs available.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Level</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                    if (preg_match('/\[(.*?)\] \[(.*?)\] (.*)/', $log, $matches)) {
                                        $timestamp = $matches[1];
                                        $level = $matches[2];
                                        $message = $matches[3];
                                        $levelClass = 'adp-log-' . strtolower($level);
                                    } else {
                                        $timestamp = '';
                                        $level = 'INFO';
                                        $message = $log;
                                        $levelClass = 'adp-log-info';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($timestamp); ?></td>
                                        <td><span class="adp-log-level <?php echo esc_attr($levelClass); ?>"><?php echo esc_html($level); ?></span></td>
                                        <td><?php echo esc_html($message); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>