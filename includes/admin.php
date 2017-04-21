<?php
/**
 * Admin UI Page template
 */

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Cron Master', 'cron-master' ); ?></h1>
    <p>
        <button type="button" class="button start-cron"><?php esc_html_e( 'Start', 'cron-master' ); ?></button>
        <button type="button" class="button get-status"><?php esc_html_e( 'Status', 'cron-master' ); ?></button>
        <button type="button" class="button stop-cron"><?php esc_html_e( 'Stop', 'cron-master' ); ?></button>
        <button type="button" class="button clear-logs"><?php esc_html_e( 'Clear Logs', 'cron-master' ); ?></button>
    </p>
    <p id="cron-status"><?php esc_html_e( 'Checking cron status', 'cron-master' ); ?></p>
</div>
