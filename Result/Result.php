<?php
/**
 * Result Interface
 *
 * Objects that implement the ResultInterface are mutable objects intended to
 * represent the result of a specific action.
 *
 * @see https://github.com/rowansays/wpHelpers/blob/master/Result/readme.md
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace Please\Change\Me;

interface ResultInterface extends \Countable {
  /**
   * Set the result's state to "fail" with an optional message.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders
   *
   * @return ResultInterface
   */
  public function fail (string $message = '', ...$values) : ResultInterface;
  /**
   * Is the result negative?
   *
   * @return bool
   */
  public function failed () : bool;
  public function getPayload ();
  /**
   * Append a message to the log.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders.
   *
   * @return ResultInterface
   * @throws \Exception when $message is empty.
   */
  public function log (string $template, ...$values) : ResultInterface;
  /**
   * Merge a result into the current result.
   *
   * @param ResultInterface $result
   * @return ResultInterface
   * @throws \Exception when $result is undefined.
   */
  public function merge (ResultInterface $result) : ResultInterface;
  /**
   * Set the result's state to "pass" with an optional message.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders
   *
   * @return ResultInterface
   */
  public function pass (string $template = '', ...$values) : ResultInterface;
  /**
   * Is the result positive?
   *
   * @return bool
   */
  public function passed () : bool;
  public function payload () : ResultInterface;
  public function renderText () : string;
}

abstract class AbstractResult {
  private string $action = 'Anonymous action';
  private $log = [];
  private $payload;
  private string $state = '';
  /**
   * Construct a new object that implements ResultInterface.
   *
   * @param string $action The name of the action that this result represents.
   * @param mixed ...$values Zero or more values to be inserted into $action in
   *   cases where $action contains printf() style placeholders.
   *
   * @return ResultInterface
   */
  public function __construct (string $action = '', ...$values) {
    if ($action !== '') {
      $this->action = strip_tags(sprintf($action, ...$values));
    }
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
   * Set the result's state to "fail" with an optional message.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders.
   *
   * @return ResultInterface
   */
  public function fail (string $message = '', ...$values) : ResultInterface {
    if ($message !== '') {
      $this->log($message, ...$values);
    }
    $this->state = 'failed';
    return $this;
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
   * Append a message to the log.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders.
   *
   * @return ResultInterface
   * @throws \Exception when $message is empty.
   */
  public function log (string $message, ...$values) : ResultInterface {
    $entry = strip_tags(sprintf($message, ...$values));
    if ($entry === '') {
      throw new \Exception('Message must not be empty');
    }
    $this->log[] = new static($entry);
    return $this;
  }
  /**
   * Merge a result into the current result.
   *
   * @param ResultInterface $result
   * @return ResultInterface
   * @throws \Exception when $result is undefined.
   */
  public function merge (ResultInterface $result) : ResultInterface {
    // Undefined results may not be merged.
    if (!$result->passed() && !$result->failed()) {
      throw new \Exception('Unable to merge in an instance with an undefined state.');
    }

    // Inherit state from given result.
    if ($result->passed()) {
      $this->pass();
    } else {
      $this->fail();
    }

    // Convert given result to a log item and append to log.
    $this->log[] = clone $result;

    return $this;
  }
  /**
   * Set the result's state to "pass" with an optional message.
   *
   * @param string $message A message to append to the log.
   * @param mixed ...$values One or more values to be inserted into $message in
   *   cases where $message contains printf() style placeholders
   *
   * @return ResultInterface
   */
  public function pass (string $message = '', ...$values) : ResultInterface {
    if ($message !== '') {
      $this->log($message, ...$values);
    }
    $this->state = 'passed';
    return $this;
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
  public function renderText (int $level = 1) : string {
    $template = $this->state === '' ? '%s' : '%s (%s)';
    $output = sprintf($template, $this->action, $this->state);
    foreach($this->log as $index => $item) {
      $indent = str_repeat(' ', $level * 4);
      $output .=  sprintf("\n" . '%1$s%2$d. %3$s',
        $indent,
        $index + 1,
        $item->renderText($level + 1)
      );
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
  public function getPayload () {
    return $this->payload;
  }
  /**
   * Define a payload value for this result.
   *
   * @param mixed $payload Any value
   * @return Result
   */
  public function payload ($payload = null) : Result {
    $this->payload = $payload;
    return $this;
  }
}
