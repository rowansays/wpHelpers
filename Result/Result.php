<?php

declare(strict_types = 1);

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
 * @version 3.0.0
 */

namespace RowanSaysWpHelpers\Result;

/**
 * Defines signatures for all public methods of objects that behave as results.
 *
 * @since v1.1.0
 */
interface ResultInterface extends \Countable, \IteratorAggregate {
  /**
   * Did the result fail?
   *
   * @return bool
   */
  public function failed (?string $code) : bool;
  /**
   * Did the result pass?
   *
   * @return bool
   */
  public function passed () : bool;
  public function toMarkdown () : string;
  public function toValue ();
}
/**
 * An abstract class which  can be used to quickly craft a final concrete class
 * that implements ResultInterface.
 *
 * This abstract class implements all of the functionality required by
 * `ResultInterface` except for the {@see ResultInterface::toValue()} method.
 *
 * @since v1.1.0
 */
abstract class AbstractResult {
  protected string $action = 'Anonymous action';
  protected $log = [];
  protected $value;
  protected string $state = '';
  /**
   * Construct a new object that implements ResultInterface.
   *
   * @param string $action The name of the action that this result represents.
   * @param string|null $state Was the action successful, unsuccessful, or
   *   undefined? Defaults to `null` which represents undefined results. Use
   *   "passed" when this result succeeded. All other non-empty strings
   *   indicate that the result has failed and their value be used as the error
   *   code.
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

    if ($state === null) {
      $state = 'undefined';
    } else if ($state === 'passed') {
      $state = 'passed';
    } else {
      $trimmed = trim($state);
      if (strlen($trimmed) === 0) {
        throw new \InvalidArgumentException(
          'The string passed for parameter two $state must not be empty'
        );
      } else {
        $state = $trimmed;
      }
    }

    $this->validateLogParameter($log, 'four');

    $this->action = $action;
    $this->state = $state === null ? 'undefined' : $state;
    $this->value = $value;
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
   * Retrieve an external iterator.
   *
   * This method single-handedly fulfills the requirements of PHP's built-in
   * IteratorAggregate interface which allows instances of TextList to
   * be used and recognized as iterables.
   *
   * @see https://www.php.net/manual/en/iteratoraggregate.getiterator.php
   * @return ArrayIterator
   */
  public function getIterator() : \ArrayIterator {
    return new \ArrayIterator($this->log);
  }
  /**
   * Get the value of this result.
   *
   * @return mixed Any value
   */
  public function toValue () {
    return $this->value;
  }
  /**
   * Is the result negative?
   *
   * @param string|null $code Optional error code
   * @return bool
   */
  public function failed (?string $code = null) : bool {
    $nonFails = ['passed', 'undefined'];
    if ($code === null) {
      return !in_array($this->state, $nonFails);
    } else if (in_array($code, $nonFails)) {
      return false;
    } else {
      return $code === $this->state;
    }
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
   * Return a represention of this result in markdown
   *
   * @param int $level The amount that the log items should be indented when
   *   rendered.
   * @return string
   */
  public function toMarkdown (int $level = 1, array $options = []) : string {
    $template = $this->state === 'undefined' ? '%s' : '%s (%s)';
    $output = sprintf($template, $this->action, $this->state);
    foreach($this->log as $index => $item) {
      $indent = str_repeat(' ', $level * 2);
      $content = $item->toMarkdown($level + 1);
      $output .=  sprintf("\n" . '%s* %s', $indent, $content);
    }
    return $output;
  }
  /**
   * Ensure that all entries in a log array are allowed.
   *
   * @throws \InvalidArgumentException
   */
  protected function validateLogParameter (array $values, string $position) : void {
    foreach ($values as $aught) {
      $interface = __NAMESPACE__ . '\\ResultInterface';
      if (!$aught instanceof $interface) {
        $valueString = is_object($aught)
          ? sprintf('An object of class "%s" was passed.', get_class($aught))
          : sprintf('A value with a type of "%s" was passed.', gettype($aught))
        ;
        throw new \InvalidArgumentException(sprintf(
          'Parameter %1$s `$log` must be an array that contains only ' .
          'instances of %2$s. %3$s', $position, $interface, $valueString
        ));
      }
    }
  }
}
/**
 * General result
 *
 * The value may be any value.
 *
 * @since v1.1.0
 */
final class Result extends AbstractResult implements ResultInterface {}
/**
 * Convert a WP_Error instance to a result.
 *
 * It's important to note that {@link https://developer.wordpress.org/reference/classes/wp_error/ WP_Error}
 * uses an array indexed by string to store errors. This means that the order in
 * which the errors happened may not be perserved.
 *
 * @since v3.0.0
 */
final class ResultFromWpError extends AbstractResult implements ResultInterface {
  /**
   * Construct a new result from an instance of WP_Error.
   *
   * @param string $action (Required) The name of the action that this result
   *   represents.
   * @param \WP_Error $error (Optional) The instance of `WP_Error` to convert to
   *   a `Result` instance.
   * @param mixed $value (Optional) Any value
   */
  public function __construct (string $action, ?\WP_Error $error = null, $value = null) {
    if ($action === '') {
      throw new \InvalidArgumentException('Parameter one $action must not be empty.');
    }

    $log = [];
    if ($error !== null) {
      foreach ($error->errors as $code => $messages) {
        foreach ($messages as $message ) {
          $log[] = new static(sprintf('%s - %s', $code, $message));
        }
      }
    }

    $this->action = $action;
    $this->state = $error === null ? 'undefined' : 'failed';
    $this->value = $value;
    $this->log = $log;
  }
}
/**
 * Create a result that contains contains only information.
 *
 * The `InfoResult` constructor is an abbreviated form of
 * `Result::__construct()`. It provides only parameters for `$action` and
 * `$log`. It should be understood that instances of this class will always
 * have `null` values and thier state will always be undefined.
 *
 * @since v3.0.0
 */
final class InfoResult extends AbstractResult implements ResultInterface {
  public function __construct (string $action, iterable $log = []) {
    if ($action === '') {
      throw new \InvalidArgumentException('Parameter one $action must not be empty.');
    }

    $this->validateLogParameter($log, 'two');

    $this->action = $action;
    $this->state = 'undefined';
    $this->value = null;
    $this->log = $log;
  }
}
/**
 * Create a result that contains an array as its value.
 *
 * Instances of the `ResultArray` class function similarly to those of the
 * `Result` class with one exception: their values are always arrays.
 *
 * @since v3.0.0
 */
final class ResultArray extends AbstractResult implements ResultInterface {
  /**
   * Create a new instance
   *
   * @throws \TypeError when parameters are of an unrecognized type
   */
  public function __construct (
    string $action,
    ?string $state,
    array $value,
    iterable $log = []
  ) {
    parent::__construct($action, $state, $value, $log);
  }
  /**
   * @return array
   */
  public function toValue () : array {
    return $this->value;
  }
}
/**
 * Create a result that contains a string as its value.
 *
 * Instances of the `ResultString` class function similarly to those of the
 * `Result` class with one exception: their values are always strings.
 *
 * @since v3.0.0
 */
final class ResultString extends AbstractResult implements ResultInterface {
  /**
   * Create a new instance
   *
   * @throws \TypeError when parameters are of an unrecognized type
   */
  public function __construct (
    string $action,
    ?string $state,
    string $value,
    iterable $log = []
  ) {
    parent::__construct($action, $state, $value, $log);
  }
  /**
   * @return string
   */
  public function toValue () : string {
    return $this->value;
  }
}
