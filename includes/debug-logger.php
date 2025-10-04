<?php
/**
 * Comprehensive Debug Logger for WordPress Plugin
 * Logs all plugin activities, WordPress hooks, errors, and user interactions
 * Perfect for GitHub Copilot analysis and debugging
 */

if (!defined('ABSPATH')) {
    exit;
}

class DebugLogger {
    private static $instance = null;
    private $log_file;
    private $enabled = false;
    
    private function __construct() {
        $this->log_file = dirname(__DIR__) . '/debug-complete.log';
        
        // Enable logging if WP_DEBUG or custom constant is set
        $this->enabled = (defined('WP_DEBUG') && WP_DEBUG) || 
                        (defined('PLUGIN_DEBUG') && PLUGIN_DEBUG) ||
                        (defined('COPILOT_DEBUG') && COPILOT_DEBUG);
        
        if ($this->enabled) {
            $this->init_logging();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function init_logging() {
        // Clear old log on new session
        if (file_exists($this->log_file) && filesize($this->log_file) > 5 * 1024 * 1024) { // 5MB limit
            $this->rotate_log();
        }
        
        $this->log("=== DEBUG SESSION STARTED ===", 'SYSTEM');
        $this->log("WordPress Version: " . get_bloginfo('version'), 'SYSTEM');
        $this->log("Plugin Directory: " . plugin_dir_path(__FILE__), 'SYSTEM');
        $this->log("Current User: " . (is_user_logged_in() ? wp_get_current_user()->user_login : 'Not logged in'), 'SYSTEM');
        $this->log("Request URI: " . $_SERVER['REQUEST_URI'] ?? 'Unknown', 'SYSTEM');
        $this->log("Request Method: " . $_SERVER['REQUEST_METHOD'] ?? 'Unknown', 'SYSTEM');
        
        // Hook into WordPress core events
        $this->setup_wordpress_hooks();
        
        // Hook into plugin-specific events
        $this->setup_plugin_hooks();
        
        // Hook into error handling
        $this->setup_error_handling();
    }
    
    public function log($message, $category = 'INFO', $context = []) {
        if (!$this->enabled) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $memory = $this->format_bytes(memory_get_usage());
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';
        
        $log_entry = sprintf(
            "[%s] [%s] [%s] [MEM:%s] %s",
            $timestamp,
            str_pad($category, 8),
            $caller,
            $memory,
            $message
        );
        
        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $log_entry .= PHP_EOL;
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    private function setup_wordpress_hooks() {
        // Admin hooks
        add_action('admin_init', function() {
            $this->log("Admin initialized", 'WP_ADMIN');
        });
        
        add_action('admin_menu', function() {
            $this->log("Admin menu building", 'WP_ADMIN');
        });
        
        add_action('admin_enqueue_scripts', function($hook) {
            $this->log("Admin scripts enqueued for hook: $hook", 'WP_ADMIN');
        });
        
        // Form submissions
        add_action('admin_post_nopriv_*', function() {
            $this->log("Admin post action (no priv): " . ($_POST['action'] ?? 'unknown'), 'WP_FORM', $_POST);
        });
        
        add_action('admin_post_*', function() {
            $this->log("Admin post action: " . ($_POST['action'] ?? 'unknown'), 'WP_FORM', $_POST);
        });
        
        // Settings API
        add_action('update_option', function($option_name, $old_value, $value) {
            if (strpos($option_name, 'dcb_') === 0 || strpos($option_name, 'dual_chatbot') === 0) {
                $this->log("Option updated: $option_name", 'WP_OPTION', [
                    'old_value' => $old_value,
                    'new_value' => $value
                ]);
            }
        }, 10, 3);
        
        // Database queries
        add_filter('query', function($query) {
            if (strpos($query, 'dual_chatbot') !== false || strpos($query, 'dcb_') !== false) {
                $this->log("Database query: " . substr($query, 0, 200) . (strlen($query) > 200 ? '...' : ''), 'DB_QUERY');
            }
            return $query;
        });
        
        // REST API
        add_action('rest_api_init', function() {
            $this->log("REST API initialized", 'WP_REST');
        });
        
        // AJAX
        add_action('wp_ajax_*', function() {
            $action = $_POST['action'] ?? $_GET['action'] ?? 'unknown';
            $this->log("AJAX action: $action", 'WP_AJAX', $_POST);
        });
        
        add_action('wp_ajax_nopriv_*', function() {
            $action = $_POST['action'] ?? $_GET['action'] ?? 'unknown';
            $this->log("AJAX action (no priv): $action", 'WP_AJAX', $_POST);
        });
    }
    
    private function setup_plugin_hooks() {
        // Plugin activation/deactivation
        add_action('activated_plugin', function($plugin) {
            if (strpos($plugin, 'dual-chatbot') !== false || strpos($plugin, 'ki-kraft') !== false) {
                $this->log("Plugin activated: $plugin", 'PLUGIN');
            }
        });
        
        add_action('deactivated_plugin', function($plugin) {
            if (strpos($plugin, 'dual-chatbot') !== false || strpos($plugin, 'ki-kraft') !== false) {
                $this->log("Plugin deactivated: $plugin", 'PLUGIN');
            }
        });
        
        // Hook into all plugin-specific actions
        $plugin_actions = [
            'dcb_save_privacy_settings',
            'dcb_dsar_export',
            'dcb_dsar_delete',
            'dual_chatbot_reset_colors',
            'dual_chatbot_chat',
            'dual_chatbot_upload_knowledge'
        ];
        
        foreach ($plugin_actions as $action) {
            add_action("admin_post_$action", function() use ($action) {
                $this->log("Plugin action triggered: $action", 'PLUGIN_ACTION', [
                    'POST' => $_POST,
                    'GET' => $_GET,
                    'user' => wp_get_current_user()->user_login ?? 'unknown'
                ]);
            }, 1);
        }
        
        // Settings registration
        add_action('admin_init', function() {
            global $wp_settings_fields;
            if (isset($wp_settings_fields['dual-chatbot-settings-privacy'])) {
                $this->log("Privacy settings fields registered", 'PLUGIN_SETTINGS', 
                    array_keys($wp_settings_fields['dual-chatbot-settings-privacy']));
            }
        }, 999);
    }
    
    private function setup_error_handling() {
        // PHP errors
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (strpos($errfile, 'dual-chatbot-plugin') !== false || strpos($errfile, 'ki-kraft') !== false) {
                $this->log("PHP Error: $errstr in $errfile:$errline", 'PHP_ERROR');
            }
            return false; // Don't prevent default error handling
        });
        
        // WordPress errors
        add_action('wp_die_handler', function($message) {
            $this->log("WordPress die: $message", 'WP_ERROR');
            return $message;
        });
        
        // Database errors
        add_action('wp_db_error', function($error) {
            $this->log("Database error: $error", 'DB_ERROR');
        });
    }
    
    public function log_form_submission($form_data, $form_type = 'UNKNOWN') {
        $sanitized_data = [];
        foreach ($form_data as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                $sanitized_data[$key] = substr($value, 0, 100) . '...[truncated]';
            } else {
                $sanitized_data[$key] = $value;
            }
        }
        
        $this->log("Form submitted: $form_type", 'FORM_SUBMIT', $sanitized_data);
    }
    
    public function log_user_action($action, $details = []) {
        $user = wp_get_current_user();
        $context = array_merge([
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'user_roles' => $user->roles,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $details);
        
        $this->log("User action: $action", 'USER_ACTION', $context);
    }
    
    public function log_api_call($endpoint, $method, $data = [], $response = null) {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'request_data' => $data,
            'response_code' => is_wp_error($response) ? 'ERROR' : wp_remote_retrieve_response_code($response),
            'response_message' => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_response_message($response)
        ];
        
        $this->log("API call to $endpoint", 'API_CALL', $context);
    }
    
    private function format_bytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . $units[$i];
    }
    
    private function rotate_log() {
        if (file_exists($this->log_file)) {
            $backup_file = str_replace('.log', '_' . date('Y-m-d_H-i-s') . '.log', $this->log_file);
            rename($this->log_file, $backup_file);
            
            // Keep only last 5 backup files
            $backup_files = glob(dirname($this->log_file) . '/debug-complete_*.log');
            if (count($backup_files) > 5) {
                usort($backup_files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                foreach (array_slice($backup_files, 0, -5) as $old_file) {
                    unlink($old_file);
                }
            }
        }
    }
    
    public function __destruct() {
        if ($this->enabled) {
            $this->log("=== DEBUG SESSION ENDED ===", 'SYSTEM');
        }
    }
}

// Helper functions for easy logging throughout the plugin
function debug_log($message, $category = 'INFO', $context = []) {
    DebugLogger::getInstance()->log($message, $category, $context);
}

function debug_log_form($form_data, $form_type = 'UNKNOWN') {
    DebugLogger::getInstance()->log_form_submission($form_data, $form_type);
}

function debug_log_user($action, $details = []) {
    DebugLogger::getInstance()->log_user_action($action, $details);
}

function debug_log_api($endpoint, $method, $data = [], $response = null) {
    DebugLogger::getInstance()->log_api_call($endpoint, $method, $data, $response);
}

// Initialize the logger
DebugLogger::getInstance();