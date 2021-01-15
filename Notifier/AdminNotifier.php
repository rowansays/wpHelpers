<?php
/**
 * Admin Notifier.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/NotifierInterface.php';
require_once __DIR__ . '/AbstractAdminNotifier.php';

final class AdminNotifier extends AbstractAdminNotifier {
  /**
	 * Hook into WordPress.
   *
   * @return NotifierInterface
	 */
	public function hook () : NotifierInterface {
    add_action('admin_notices', function () : void {
      $this->renderNotices($this->notices);
    });
    add_filter('removable_query_args',  function ($args) : array {
      return $this->filterQueryArgs($args);
    });
    return $this;
  }
}
