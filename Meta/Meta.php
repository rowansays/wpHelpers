<?php

declare (strict_types = 1);

namespace RowanSaysWpHelpers\Meta;

use RowanSaysWpHelpers\Result\Result;
use RowanSaysWpHelpers\Result\ResultString;

require_once dirname(__DIR__) . '/Result/Result.php';

/**
 * Retrieve a meta value from WordPress.
 *
 * This function's result will pass when the requested meta key exists and has
 * a value that is a non-empty string.
 *
 * @param string $type The type of object for which to retrieve meta. The
 *   following types are supported in a default installation of WordPress:
 *   post, comment, term, and user.
 * @param int $id The unique numeric identifier of the object for which to
 *   retrieve meta.
 * @param int $key The meta key.
 *
 * @return ResultString The result will fail when the meta key does not exist,
 *   is not a string, or has an empty value.
 */
function getMetaString (string $type, int $id, string $key = '') : ResultString {
  $action = sprintf(
    'Requesting string for meta key [%1$s] from [%2$s] object having id [%3$s]',
    $key, $type, $id,
  );
  $state = 'failed';
  $value = '';
  $results = [];

  $meta = get_metadata($type, $id, $key, true);

  if (is_string($meta)) {
    if (strlen($meta) > 0) {
      $state = 'passed';
      $value = $meta;
    } else {
      $results[] = new Result(
        'Either the requested key does not exist or has been saved with a ' .
        'value of an empty string or null'
      );
    }
  } else {
    $results[] = new Result(sprintf(
      'The value stored for this key has a type of [%s]', gettype($meta)
    ));
  }

  return new ResultString($action, $state, $value, $results);
}
