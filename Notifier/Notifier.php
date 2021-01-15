<?php
/**
 * Admin Notifier.
 *
 * Easy registration and rendering of user-facing notices.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;


require_once __DIR__ . '/AdminNotifier.php';
require_once __DIR__ . '/NetworkNotifier.php';
require_once __DIR__ . '/PublicNotifier.php';




/**
 * Error Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Error ($text, ...$values) {
  return new Notice('error', $text, ...$values);
}

/**
 * Information Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Info ($text, ...$values) {
  return new Notice('info', $text, ...$values);
}

/**
 * Success Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Success ($text, ...$values) {
  return new Notice('success', $text, ...$values);
}

/**
 * Warning Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Warning ($text, ...$values) {
  return new Notice('warning', $text, ...$values);
}
