<?php
/**
 * Abstract Admin Notifier.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/AbstractNotifier.php';

abstract class AbstractAdminNotifier extends AbstractNotifier {
  /**
   * Remove query string notices from the browser's location bar.
   *
	 * Intended to be hooked into the WordPress core `removable_query_args` filter.
   *
   * @see https://developer.wordpress.org/reference/functions/wp_removable_query_args/
   *
   * @param $args {array}
   * @return array
	 */
	protected function filterQueryArgs ($args) : array {
    return is_array($args)
      ? array_merge($args, array_values($this->recognizedQueryArgs))
      : $this->recognizedQueryArgs
    ;
  }
  /**
	 * Render registered notices.
   *
   * @param $notices Notice[] The notices to render.
   * @return void Echoes HTML markup.
	 */
	public function renderNotices () : void {
    $screen = get_current_screen();
    foreach($this->notices as $notice) {
      if ($notice->existsOnScreen($screen->id)) {
        echo vsprintf('<div class="%1$s"><p>%2$s</p></div>', [
          '1: classList' => esc_html($notice->getClassList()),
          '2: innerText' => $notice->getText()
        ]);;
      }
    }
  }
  /**
   * Is at least one notice registered for a given admin screen?
   *
   * @return bool
   */
  public function hasNoticesForScreen (string $screenId) : bool {
    $all = array_merge(
      $this->adminNotices,
      $this->networkNotices,
      $this->queryNotices
    );

    foreach($all as $notice) {
      if ($notice->existsOnScreen($screenId)) {
        return true;
      }
    }

    return false;
  }
}
