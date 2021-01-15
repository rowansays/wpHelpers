<?php
/**
 * Public Notifier.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

require_once __DIR__ . '/NotifierInterface.php';
require_once __DIR__ . '/AbstractNotifier.php';

final class PublicNotifier extends AbstractNotifier {
  public function hook () : NotifierInterface {
    add_action('wp_footer', function () {
      $template = '
        <script>
          void (function() {
            if (URL) {
              var params = %1$s;
              var url = new URL(window.location);
              for (var i = 0; i < params.length; i++) {
                url.searchParams.delete(params[i]);
              }
              window.history.replaceState({}, document.title, url);
            }
          })();
        </script>
      ';

      printf($template, json_encode($this->recognizedQueryArgs));
    });
    return $this;
  }
  /**
	 * Render registered notices.
   *
   * @return void Echoes HTML markup.
	 */
	public function renderNotices () : void {
    foreach($this->notices as $notice) {
      echo vsprintf('<div class="%1$s"><p>%2$s</p></div>', [
        '1: classList' => esc_html($notice->getClassList()),
        '2: innerText' => $notice->getText(),
      ]);;
    }
  }
}
