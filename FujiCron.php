<?php
require_once __DIR__.'/custom_fields.php';
require_once __DIR__.'/Property.php';
require_once __DIR__.'/PropertyImage.php';
require_once __DIR__.'/FujiSettings.php';
require_once __DIR__.'/FujiLogger.php';
require_once __DIR__.'/FujiSync.php';
require_once __DIR__.'/FujiFTP.php';

require_once __DIR__.'/../../../wp-load.php';

class FujiCron {
    private $setting;

    public function __construct() {
        $this->setting = new FujiSettings();
        date_default_timezone_set(get_option('timezone_string'));
        if (is_admin()) {
            register_activation_hook(__DIR__.'/fujilondon-cfp-sync.php', [$this, 'activation']);
            register_deactivation_hook(__DIR__.'/fujilondon-cfp-sync.php' ,[$this, 'deactivation']);
        }
        add_action('fujilondon_cfp_sync_schedule', [$this, 'cfp_sync']);
    }

    public function cfp_sync() {
        // do job
        $sync = new FujiSync();
        $sync->sync();
    }

    public function activation() {
        $today = strtotime('10:00 GMT');
        $tomorrow = strtotime('tomorrow 10:00 GMT');
        
        if($today > strtotime('now')) {
            $schedule = $today;
        } else {
            $schedule = $tomorrow;
        }
        wp_schedule_event($schedule, 'daily', 'fujilondon_cfp_sync_schedule');
    }

    public function deactivation() {
        wp_clear_scheduled_hook('fujilondon_cfp_sync_schedule');
    }
}

require_once 'oldcode.php';
$p = new FujiCron();
$p->cfp_sync();