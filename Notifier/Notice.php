<?php
/**
 * Notice.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/NoticeInterface.php';

class Notice implements NoticeInterface {
  private array $classes = ['notice'];
  private string $type = 'info';
  private string $text = '';
  private array $exclude = [];
  private array $include = [];
  /**
   * Create a new notice.
   *
   * @param string $type The following values are recognized: 'error', 'info',
   *   'success', and 'warning'. A type of 'info' will be used in cases where
   *   an unrecognized type is given.
   * @param string $text User-facing message. Required.
   * @param string $values Zero or more values to use when $text is formatted. Optional
   *
   * @return Notice
   */
  public function __construct (string $type, string $text, ...$values) {
    $types = ['error', 'info', 'success', 'warning'];
    $this->type = in_array($type, $types) ? $type : 'info';
    $cleanText = $this->cleanHtml($text);
    if ($cleanText === '') {
      throw new \Exception('Parameter 2 `$text` must not be empty.');
    } else {
      $this->text = sprintf($cleanText, ...$values);
    }
    $this->classes[] = 'notice-' . $this->type;
  }
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  public function getClassList() : string {
    return implode(' ', $this->classes);
  }
  public function getHash () : string {
    return md5(implode($this->classes) . $this->type . $this->text);
  }
  public function getText() : string {
    return $this->text;
  }
  /**
   * Exclude this notice from one or more admin screens.
   *
   * @param string $screenIds one or more screen ids to exclude.
   * @return Notice Updated instance
   */
  public function hideOnScreen (string ...$screenIds) : NoticeInterface {
    $this->exclude = $this->arrayMerge($this->exclude, $screenIds);
    return $this;
  }
  /**
   * Include this notice only on one or more admin screens.
   *
   * @return Notice Updated instance
   */
  public function showOnScreen (string ...$screenIds) : NoticeInterface {
    $this->include = $this->arrayMerge($this->include, $screenIds);
    return $this;
  }
  /**
   * Should this notice be rendered on a given screen?
   *
   * @return bool
   */
  public function existsOnScreen (string $screenId) : bool {
    if (count($this->include) === 0 && count($this->exclude) === 0) {
      return true;
    }
    if (count($this->include) > 0) {
      return in_array($screenId, $this->include);
    }
    return ! in_array($screenId, $this->exclude);
  }
  private function arrayMerge (array $a, array $b) {
    return array_unique(array_filter(array_merge($a, $b)));
  }
  private function cleanHtml (string $dirty) {
    return trim(wp_kses($dirty, [
      'abbr' => [],
      'b' => [],
      'em' => [],
      'i' => [],
      'strong' => [],
    ]));
  }
}
