<?php

/**
 * Uninstall handler.
 *
 * WordPress calls this file when the user clicks "Delete" on the plugin
 * from the plugins page — NOT on simple deactivation. That distinction is
 * intentional: deactivating shouldn't wipe data, only uninstalling should.
 *
 * @package Apiki\Favorites
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

$table = $wpdb->prefix . 'apiki_favorites';

// We can't load the plugin autoloader from here reliably (uninstall.php
// runs in a minimal bootstrap context). The literal table name is
// duplicated here on purpose; if Plugin::TABLE_NAME ever changes, this
// constant must change alongside it.
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- DROP TABLE is exactly what uninstall should do; caching/preparing does not apply.
$wpdb->query("DROP TABLE IF EXISTS {$table}");
