<?php

declare (strict_types = 1);

namespace RowanSaysWpHelpers\File;

/**
 * Creates a random file name for user uploads.
 *
 * Name consists of 32 hexidecimal characters.
 * Extension consists of lowercase latin letters and integers.
 *
 * @since 3.0.0
 */
class RandomFilename {
  private string $name;
  private string $extension;
  /**
   * @param string $extension Required. Allowed characters include a-z and 0-9.
   *   Case sensitive.
   */
  public function __construct (string $extension) {
    if (strlen($extension) === 0) {
      throw new \Exception(
        'Parameter one `$extension` must contain at least one character.'
      );
    }
    if (!self::isValidExtension($extension)) {
      throw new \Exception(
        'Parameter one `$extension` must contain only lowercase basic latin ' .
        'letters and integers 0-9.'
      );
    }
    $this->name = strtolower(bin2hex(random_bytes(16)));
    $this->extension = $extension;
  }
  /**
   * Return this filename as a string.
   */
  public function __toString () : string {
    return $this->name . '.' . $this->extension;
  }
  /**
   * Create a new instance from a given filename.
   */
  public static function fromString (string $filename) {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    if (!self::isValidName($name)) {
      throw new \Exception(sprintf(
        'Parameter one `$filename` must have a name consisting of 32 ' .
        'hexidecimal characters. A value of [%s] was provided', $name
      ));
    }

    $extension  = pathinfo($filename, PATHINFO_EXTENSION);
    if (!self::isValidExtension($extension)) {
      throw new \Exception(sprintf(
        'Parameter one `$filename` must have an extension that consists ' .
        'only of lowercase basic latin letters a-z and integers 0-9. A value ' .
        'of [%s] was provided', $extension
      ));
    }

    $output = new static($extension);
    $output->name = $name;
    return $output;
  }
  /**
   * Does a file with this name exist in a given directory?
   *
   * @param string $dir Absolute path to a directory to inspect.
   *
   * @throws \Exception when parameter one is empty.
   * @throws \Exception when parameter one is not a directory.
   *
   * @return bool True if file exists; false otherwise.
   */
  public function exists (string $dir) : bool {
    if (empty($dir)) {
      throw new \Exception('Parameter one `$dir` must not be empty.');
    }
    if (!is_dir($dir)) {
      throw new \Exception('Parameter one `$dir` must be a valid directory.');
    }
    return is_file($dir . DIRECTORY_SEPARATOR . strval($this));
  }
  /**
   * Return a unique random filename for a given directory.
   *
   * @param string $dir Absolute path to a directory.
   *
   * @throws \Exception when parameter one is empty.
   * @throws \Exception when parameter one is not a directory.
   *
   * @return RandomFilename A random filename that does not exist in the
   *   provided directory.
   */
  public function unique (string $dir) : RandomFilename {
    if (empty($dir)) {
      throw new \Exception('Parameter one `$dir` must not be empty.');
    }
    if (!is_dir($dir)) {
      throw new \Exception('Parameter one `$dir` must be a valid directory.');
    }

    $filename = $this;
    while ($filename->exists($dir)) {
      $filename = new RandomFilename($this->extension);
    }

    return $filename;
  }
  /**
   *
   */
  public function withName (string $name) : RandomFilename {
    if (!self::isValidName($name)) {
      throw new \Exception(
        'Parameter one `$name` must be a 32 character hexidecimal string.'
      );
    }
    $output = new static($this->extension);
    $output->name = $name;
    return $output;
  }
  private static function isValidName (string $aught) {
    return preg_match('/^[a-f0-9]{32}+$/', $aught) === 1;
  }
  private static function isValidExtension (string $aught) {
    return preg_match('/^[a-z0-9]+$/', $aught) === 1;
  }
}

function isRandomFilename ($aught) : bool {
  if (!is_string($aught)) {
    return false;
  }
  return preg_match('/^[a-f0-9]{' . PRECISION * 2 . '}\.[a-z]+$/', $aught) === 1;
}
