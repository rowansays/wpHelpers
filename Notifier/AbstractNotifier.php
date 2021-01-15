<?php
/**
 * AbstractNotifier.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/Notice.php';
require_once __DIR__ . '/NoticeInterface.php';
require_once __DIR__ . '/NotifierInterface.php';

abstract class AbstractNotifier implements NotifierInterface {
  /**
   * Unique.
   *
   * A unique prefix or namespace this notifier. This value is used to as a
   * prefix for notices sent via query string.
   *
   * @access protected
   * @var string
   */
  private string $unique = '';
  /**
   * Notices.
   *
   * An array of Notice instances representing the notices which will be
   * rendered to the screen.
   *
   * @access protected
   * @var NoticeInterface[]
   */
  protected array $notices = [];
  /**
   * Recognized query arguments.
   *
   * An array of strings representing the names of GET arguments recognized by
   * this notifier. Each argument name is prefxed with a unique value which
   * allows us to esily distinguish our notices from other arguments which may
   * have similar names.
   *
   * @access protected
   * @var array
   */
  protected array $recognizedQueryArgs = [];
  public function __construct (string $unique) {
    $match = boolval(preg_match('/^[a-zA-Z0-9_\-\x80-\xff]*$/', $unique));
    if ($match === false) {
      $template =
        'Value must be a non-empty string containing only letters, numbers, ' .
        'dashes, and/or underscores. A value of (%1$s) was provided.'
      ;
      throw new \Exception(sprintf($template, esc_html($unique)));
    }
    $this->unique = $unique;
    $this->recognizedQueryArgs = [
      $unique . '-error',
      $unique . '-info',
      $unique . '-success',
      $unique . '-warning',
    ];
  }
  /**
   * Disallow the creation of new properties on an instance
   * @throws \Exception
   */
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  public function notify (NoticeInterface $notice) : NotifierInterface {
    $this->notices[$notice->getHash()] = $notice;
    return $this;
  }
  public function redirect (string $url, NoticeInterface $notice) : void {
    $key = $this->unique . '-' . $notice->type;
    $url = add_query_arg([$key => rawurlencode($notice->getText())], $url);
		wp_safe_redirect($url);
		exit;
  }
  public function withQuery (iterable $query) : NotifierInterface {
    $this->parseQuery($query);
    return $this;
  }
  private function parseQuery (iterable $dirty) : void {
    $defaults = array_fill_keys($this->recognizedQueryArgs, '');
		$get = array_filter(array_intersect_key(array_merge($defaults, $dirty), $defaults));

    $notices = [];
		foreach($get as $key => $value) {
      try {
        $type = str_replace($this->getUnique() . '-', '', $key); // This is iffy at best. FIX PLEASE
        $notice = new Notice($type, $value);
        $this->notify($notice);
      } catch (\Exception $e) {
        continue;
      }
    }
  }
  protected function getUnique () : string {
    return $this->unique;
  }
}
