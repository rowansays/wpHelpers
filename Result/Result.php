<?php
/**
 * Result Library
 *
 * Objects that implement the ResultInterface are mutable objects intended to
 * represent the result of a specific action.
 *
 * @see https://github.com/rowansays/wpHelpers/blob/master/Result/readme.md
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 * @package RowanSays\Wp\Helpers
 * @version 2.0.0
 */

declare(strict_types = 1);

namespace Please\Change\Me;

interface ResultInterface extends \Countable {
  /**
   * Did the result fail?
   *
   * @return bool
   */
  public function failed () : bool;
  /**
   * Did the result pass?
   *
   * @return bool
   */
  public function passed () : bool;
  public function toText () : string;
  public function toMarkdown () : string;
  public function toValue ();
}

abstract class AbstractResult {
  private string $action = 'Anonymous action';
  private $log = [];
  protected $payload;
  private string $state = '';
  /**
   * Construct a new object that implements ResultInterface.
   *
   * @param string $action The name of the action that this result represents.
   * @param string $state
   * @param mixed $value
   * @param iterable<Result> $log
   *
   * @return ResultInterface
   */
  public function __construct (
    string $action,
    ?string $state = null,
    $value = null,
    iterable $log = []
  ) {
    if ($action === '') {
      throw new \InvalidArgumentException('Parameter one $action must not be empty.');
    }

    $states = [null, 'failed', 'passed'];
    if (!in_array($state, $states)) {
      throw new \InvalidArgumentException(sprintf(
        'Parameter one $action has an unrecognized value of "%s". It must be ' .
        'one of the following values: %s.',
        $state,
        implode(', ', array_map(function ($s) { return '"' . $s . '"'; }, $states))
      ));
    }

    // Throw if $log contains a non-result.
    foreach ($log as $aught) {
      $interface = __NAMESPACE__ . '\\ResultInterface';
      if (!$aught instanceof $interface) {
        $valueString = is_object($aught)
          ? sprintf('An object of class "%s" was passed.', get_class($aught))
          : sprintf('A value with a type of "%s" was passed.', gettype($aught))
        ;
        throw new \InvalidArgumentException(sprintf(
          'Parameter three $log must be an array that contains only ' .
          'instances of %1$s. %2$s', $interface, $valueString
        ));
      }
    }

    $this->action = $action;
    $this->state = $state === null ? 'undefined' : $state;
    $this->payload = $value;
    $this->log = $log;
  }
  /**
   * Disallow the creation of new properties on an instance.
   *
   * @throws \Exception
   */
  public function __set ($name, $value) {
    throw new \Exception('Direct property mutation is not permitted.');
  }
  /**
   * Return the number of messages in the log.
   *
   * The number returned represents only top-level messages. Nested log
   * messages are not factored into this value.
   *
   * @return int The size of the log.
   */
  public function count () : int {
    return count($this->log);
  }
  /**
   * Is the result negative?
   *
   * @return bool
   */
  public function failed () : bool {
    return $this->state === 'failed';
  }
  /**
   * Is the result positive?
   *
   * @return bool
   */
  public function passed () : bool {
    return $this->state === 'passed';
  }
  /**
   * Return a plain-text represention of this result
   *
   * @param int $level The amount that the log items should be indented when
   *   rendered.
   * @return string
   */
  public function toText (int $level = 1) : string {
    $template = $this->state === 'undefined' ? '%s' : '%s (%s)';
    $output = sprintf($template, $this->action, $this->state);
    foreach($this->log as $index => $item) {
      $indent = str_repeat(' ', $level * 4);
      $output .=  sprintf("\n" . '%1$s%2$d. %3$s',
        $indent,
        $index + 1,
        $item->toText($level + 1)
      );
    }
    return $output;
  }
  /**
   * Return a represention of this result in markdown
   *
   * @param int $level The amount that the log items should be indented when
   *   rendered.
   * @return string
   */
  public function toMarkdown (int $level = 1) : string {
    $template = $this->state === 'undefined' ? '%s' : '%s (%s)';
    $output = sprintf($template, $this->action, $this->state);
    foreach($this->log as $index => $item) {
      $indent = str_repeat(' ', $level * 2);
      $content = $item->toMarkdown($level + 1);
      $output .=  sprintf("\n" . '%s* %s', $indent, $content);
    }
    return $output;
  }
}



/**
 * General result
 *
 * The payload may be any value.
 */
final class Result extends AbstractResult implements ResultInterface {
  /**
   * Get the payload value for this result.
   *
   * @return mixed Any value
   */
  public function toValue () {
    return $this->payload;
  }
}
