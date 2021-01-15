<?php
/**
 * Network Notifier.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/NotifierInterface.php';
require_once __DIR__ . '/AbstractAdminNotifier.php';

final class NetworkNotifier extends AbstractAdminNotifier {
  /**
	 * Hook into WordPress.
   *
   * @return NotifierInterface
	 */
	public function hook () : NotifierInterface {
		add_action('network_admin_notices', function () : void {
      $this->parseQuery();
      $this->renderNotices($this->networkNotices);
      $this->renderNotices($this->queryNotices);
    });

    add_filter('removable_query_args',  function ($args) : array {
      return $this->filterQueryArgs($args);
    });

    return $this;
  }
}
