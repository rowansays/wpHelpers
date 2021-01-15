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

interface NoticeInterface {
  public function existsOnScreen (string $screenId) : bool;
  public function getClassList() : string;
  public function getHash () : string;
  public function getText() : string;
  public function hideOnScreen (string ...$screenIds) : NoticeInterface;
  public function showOnScreen (string ...$screenIds) : NoticeInterface;
}
