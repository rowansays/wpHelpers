<?php
/**
 * NotifierInterface.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

interface NotifierInterface {
  /**
   * Create a new Notifier instance.
   *
   * @param $unique {string} A unique identifier for this notifier.
   * @return Notifier
   */
  public function __construct (string $unique);
  public function hook() : NotifierInterface;
  /**
   * Register a notice.
   *
   * @param $notice Notice
   * @return NotifierInterface
   */
  public function notify (Notice $notice) : NotifierInterface;
  /**
	 * Redirect with with a notice.
	 *
	 * @param string $url The url to redirect to.
	 * @param Notice $notice Notice to display to the user.
   *
   * @return void Redirects to a new URL and terminates script execution.
	 */
  public function redirect(string $url, Notice $notice) : void;
  /**
   * Store notices sent via GET request.
   *
   * @return void
   */
  public function withQuery (iterable $query) : NotifierInterface;
}
