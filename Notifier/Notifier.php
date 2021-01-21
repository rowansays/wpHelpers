<?php
/**
 * Admin Notifier.
 *
 * Easy registration and rendering of user-facing notices.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

declare(strict_types = 1);

namespace Please\Change\Me;

###############################################################################
# Interfaces                                                                  #
###############################################################################

interface NoticeInterface {
  public function getClasses() : array;
  public function isRenderable() : bool;
  public function getText() : string;
  public function getType() : string;
  public function getUserIds() : array;

}
interface NotifierInterface {
  public function hook() : NotifierInterface;
  /**
   * Register a notice.
   *
   * @param $notice Notice
   * @return NotifierInterface
   */
  public function addNotice (NoticeInterface $notice) : NotifierInterface;
  public function addQueryNotice (string $key, NoticeInterface $notice) : NotifierInterface;
  /**
   * Redirect with with a notice.
   *
   * @param string $url The url to redirect to.
   * @param string $noticeKey The key of notice to display on the url.
   *
   * @return void Redirects to a new URL and terminates script execution.
   */
  public function redirect(string $url, string $noticeKey) : void;
  /**
   * Store notices sent via GET request.
   *
   * @return void
   */
  public function withQuery (iterable $query) : NotifierInterface;
}

###############################################################################
# Abstracts                                                                   #
###############################################################################

abstract class AbstractNotice implements NoticeInterface {
  protected array $classes = ['notice'];
  protected string $text = '';
  protected string $type = 'info';
  protected array $userIds = [];
  protected array $capabilities = [];
  /**
   * Create a new notice.
   *
   * @param string $type The following values are recognized: 'error', 'info',
   *   'success', and 'warning'. A type of 'info' will be used in cases where
   *   an unrecognized type is given.
   * @param string $text User-facing message. Required.
   * @param string $values Zero or more values to use when $text is formatted. Optional
   *
   * @return NoticeInterface
   */
  public function __construct (
    string $type = 'info',
    string $text = '',
    array $classes = [],
    array $userIds = [],
    array $capabilities = []
  ) {
    $allowedMarkup = [
      'a' => ['href' => []],
      'abbr' => [],
      'b' => [],
      'em' => [],
      'i' => [],
      'strong' => [],
    ];
    $cleanType = in_array($type, ['error', 'info', 'success', 'warning']) ? $type : 'info';
    $this->capabilities = $this->formatArray($capabilities);
    $this->classes = $this->formatArray(
      array_merge(['notice', 'notice-' . $cleanType], array_values($classes))
    );
    $this->text = trim(wp_kses($text, $allowedMarkup));
    $this->type = $cleanType;
    $this->userIds = $this->formatArray($userIds);
  }
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  /**
   * Only display this notice to given users.
   *
   * @param \WP_User|int|string ...$userId One or more user ids.
   * @return NoticeInterface
   * @throws \Exception when no parameters are given.
   */
  public function forUser (...$users) : NoticeInterface {
    $userIds = [];
    foreach($users as $user) {
      if ($user instanceof \WP_User) {
        $userIds[] = $user->ID;
      } else if (is_int($user)) {
        $userIds[] = $user;
      } else if (is_email($user)) {
        $obj = get_user_by('email', $user);
        if ($obj instanceof \WP_User) {
          $userIds[] = $obj->ID;
        }
      } else if (is_string($user)) {
        $obj = get_user_by('login', $user);
        if ($obj instanceof \WP_User) {
          $userIds[] = $obj->ID;
        }
      }
    }

    $params = array_filter(array_unique($userIds));
    if (count($params) === 0) {
      $template = 'At least one user id must be passed to %1$s::%2$s().';
      throw new \Exception(sprintf($template, static::class, __FUNCTION__));
    }
    return $this->cloneWith([
      'userIds' => array_unique(array_merge($this->userIds, $params))
    ]);
  }
  /**
   * Only display this notice to those who have a given capability.
   *
   * @param string ...$capabilities One or more capabilites.
   * @return NoticeInterface
   * @throws \Exception when no parameters are given.
   */
  public function forUsersWhoCan (string ...$capabilities) : NoticeInterface {
    $params = array_filter(array_unique(array_values($capabilities)));
    if (count($params) === 0) {
      $template = 'At least one capability must be passed to %1$s::%2$s().';
      throw new \Exception(sprintf($template, static::class, __FUNCTION__));
    }
    return $this->cloneWith([
      'capabilities' => array_unique(array_merge($this->capabilities, $params))
    ]);
  }
  public function getCapabilities() : array {
    return $this->capabilities;
  }
  public function getClasses() : array {
    return $this->classes;
  }
  public function getClassList() : string {
    return implode(' ', $this->classes);
  }
  public function getText() : string {
    return $this->text;
  }
  public function getType() : string {
    return $this->type;
  }
  public function getUserIds() : array {
    return $this->userIds;
  }
  public function isRenderable() : bool {
    if (count($this->userIds) > 0) {
      return in_array(get_current_user_id(), $this->userIds);
    }
    if (count($this->capabilities) === 0) {
      return true;
    }
    foreach ($this->capabilities as $capability) {
      if (current_user_can($capability)) {
        return true;
      }
    }
    return false;
  }
  public function withClass (string ...$classes) : NoticeInterface {
    $params = array_filter(array_unique(array_values($classes)));
    if (count($params) === 0) {
      $template = 'At least one class must be passed to %1$s::%2$s().';
      throw new \Exception(sprintf($template, static::class, __FUNCTION__));
    }
    return $this->cloneWith([
      'classes' => array_unique(array_merge($this->classes, $params))
    ]);
  }
  protected function cloneWith(array $props) : NoticeInterface {
    return new static(...$this->getParams($props));
  }
  protected function formatArray (array $a) {
    return array_filter(array_unique(array_values($a)));
  }
  protected function getParams (array $props = []) : array {
    $capabilities = $props['capabilities'] ?? null;
    $classes = $props['classes'] ?? null;
    $text = $props['text'] ?? null;
    $type = $props['type'] ?? null;
    $userIds = $props['userIds'] ?? null;

    return [
      is_string($type) ? $type : $this->type,
      is_string($text) ? $text : $this->text,
      is_array($classes) ? $classes : $this->classes,
      is_array($userIds) ? $userIds : $this->userIds,
      is_array($capabilities) ? $capabilities : $this->capabilities
    ];
  }
}
abstract class AbstractNotifier implements NotifierInterface {
  /**
   * Unique.
   *
   * A unique prefix or namespace this notifier. This value is used to as a
   * prefix for notices sent via query string.
   *
   * @access protected
   * @var string
   */
  private string $unique = '';
  /**
   * Notices.
   *
   * An array of Notice instances representing the notices which will be
   * rendered to the screen.
   *
   * @access protected
   * @var NoticeInterface[]
   */
  protected array $notices = [];
  protected array $queryNotices = [];
  /**
   * Create a new instance.
   *
   * @param $unique {string} A unique identifier for this notifier.
   * @return Notifier
   */
  public function __construct (string $unique) {
    $match = boolval(preg_match('/^[a-zA-Z0-9_\-\x80-\xff]*$/', $unique));
    if ($match === false) {
      $template =
        'Value must be a non-empty string containing only letters, numbers, ' .
        'dashes, and/or underscores. A value of (%1$s) was provided.'
      ;
      throw new \Exception(sprintf($template, esc_html($unique)));
    }
    $this->unique = $unique;
  }
  /**
   * Disallow the creation of new properties on an instance
   * @throws \Exception
   */
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  public function getUnique () : string {
    return $this->unique;
  }
  public function addNotice (NoticeInterface $notice) : NotifierInterface {
    $this->notices[$this->hashNotice($notice)] = $notice;
    return $this;
  }
  public function addQueryNotice (string $key, NoticeInterface $notice) : NotifierInterface {
    if (isset($this->queryNotices[$key])) {
      throw new \Exception(
        'Parameter one ($key) has already been used. Please choose another.'
      );
    }

    $this->queryNotices[$key] = $notice;
    return $this;
  }
  public function redirect(string $url, string $noticeKey) : void {
    $key = $this->unique . '-' . $notice->type;
    $url = add_query_arg([$key => rawurlencode($notice->getText())], $url);
		wp_safe_redirect($url);
		exit;
  }
  /**
	 * Render registered notices.
   *
   * @param $notices Notice[] The notices to render.
   * @return void Echoes HTML markup.
	 */
	public function renderNotices () : void {
    foreach($this->notices as $notice) {
      if ($notice->isRenderable()) {
        echo vsprintf('<div class="%1$s"><p>%2$s</p></div>', [
          '1: classList' => esc_html($notice->getClassList()),
          '2: innerText' => $notice->getText()
        ]);
      }
    }
  }
  public function withQuery (iterable $query) : NotifierInterface {
    $keys = $query[$this->unique] ?? [];
    $keys = is_string($keys) ? [$keys] : $keys;
    $keys = is_array($keys) ? $keys : [];
		foreach($keys as $key) {
      if (isset($this->queryNotices[$key])) {
        $this->addNotice($this->queryNotices[$key]);
      }
    }
    return $this;
  }
  private function appendNotice (NoticeInterface $notice) : void {
    $this->notices[$this->hashNotice($notice)] = $notice;
  }
  private function hashNotice(NoticeInterface $notice) : string {
    return md5(
      implode(' ', $notice->getClassList()) .
      $notice->getType() .
      $notice->getText()
    );
  }
}
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
    $customArgs = [$this->getUnique()];
    return is_array($args) ? array_merge($args, $customArgs) : $customArgs;
  }
  /**
   * Is at least one notice registered for a given admin screen?
   *
   * @return bool
   */
  public function hasNoticesForScreen (string $screenId) : bool {
    foreach($this->notices as $notice) {
      if ($notice->existsOnScreen($screenId)) {
        return true;
      }
    }
    return false;
  }
}

###############################################################################
# Notices                                                                     #
###############################################################################

final class Notice extends AbstractNotice {
}
final class AdminNotice extends AbstractNotice {
  private array $hideOn = [];
  private array $showOn = [];
  /**
   * Create a new notice.
   *
   * @param NoticeInterface $notice .
   * @param string[] $showOn List of admin screen ids on which this notice will
   *   be rendered.
   * @param string[] $hideOn List of admin screen ids on which this notice will
   *   not be rendered.
   */
  public function __construct (
    string $type,
    string $text,
    array $classes = [],
    array $userIds = [],
    array $capabilities = [],
    array $showOn = [],
    array $hideOn = []
  ) {
    parent::__construct(...func_get_args());
    $this->showOn = $this->formatArray($showOn);
    $this->hideOn = $this->formatArray($hideOn);
  }
  protected function getParams (array $props = []) : array {
    $showOn = $props['showOn'] ?? null;
    $hideOn = $props['hideOn'] ?? null;

    $params = parent::getParams($props);

    $params[] = is_array($showOn) ? $showOn : $this->showOn;
    $params[] = is_array($hideOn) ? $hideOn : $this->hideOn;
    return $params;
  }
  /**
   * Is this notice capable of being rendered in the context from which it
   * was called?
   *
   * @return bool
   * @throws \Exception
   */
  public function isRenderable() : bool {
    if (!is_admin()) {
      throw new \Exception(
        'AdminNotice::isRenderable() may only be called in the admin'
      );
    }
    if (0 === did_action('admin_init')) {
      throw new \Exception(
        'AdminNotice::isRenderable() must be called during or after the ' .
        '"admin_head" action.'
      );
    }
    if (false === parent::isRenderable()) {
      return false;
    }
    return $this->existsOnScreen(get_current_screen()->id ?? '');
  }
  /**
   * Exclude this notice from one or more admin screens.
   *
   * @param string $screenIds one or more screen ids to exclude.
   * @return Notice Updated instance
   */
  public function hideOnScreen (string ...$screenIds) : AdminNotice {
    return $this->cloneWith([
      'hideOn' => $this->arrayMerge($this->hideOn, $screenIds),
    ]);
  }
  /**
   * Include this notice only on one or more admin screens.
   *
   * @return Notice Updated instance
   */
  public function showOnScreen (string ...$screenIds) : AdminNotice {
    return $this->cloneWith([
      'showOn' => $this->arrayMerge($this->showOn, $screenIds),
    ]);
  }
  /**
   * Should this notice be rendered on a given screen?
   *
   * @return bool
   */
  public function existsOnScreen (string $screenId) : bool {
    if (count($this->showOn) === 0 && count($this->hideOn) === 0) {
      return true;
    }
    if (count($this->showOn) > 0) {
      return in_array($screenId, $this->showOn);
    }
    return ! in_array($screenId, $this->hideOn);
  }
  private function arrayMerge (array $a, array $b) : array {
    return array_unique(array_filter(array_merge($a, $b)));
  }
}

###############################################################################
# Notifiers                                                                   #
###############################################################################

final class AdminNotifier extends AbstractAdminNotifier {
  /**
	 * Hook into WordPress.
   *
   * @return NotifierInterface
	 */
	public function hook () : AdminNotifier {
    add_action('admin_notices', function () : void {
      $this->renderNotices();
    });
    add_filter('removable_query_args',  function ($args) : array {
      return $this->filterQueryArgs($args);
    });
    return $this;
  }
}
final class NetworkNotifier extends AbstractAdminNotifier {
  /**
	 * Hook into WordPress.
   *
   * @return NotifierInterface
	 */
	public function hook () : NetworkNotifier {
		add_action('network_admin_notices', function () : void {
      $this->renderNotices();
    });
    add_filter('removable_query_args',  function ($args) : array {
      return $this->filterQueryArgs($args);
    });
    return $this;
  }
}
final class ThemeNotifier extends AbstractNotifier {
  public function hook () : ThemeNotifier {
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

      printf($template, json_encode([$this->getUnique()]));
    });
    return $this;
  }
}

###############################################################################
# Factories                                                                   #
###############################################################################

/**
 * Admin Notifier.
 *
 * Memoized, combination getter/setter intended to be used for working with
 * multiple instances of AdminNotifier over the course of a wordpress page
 * rendering.
 *
 * @return AdminNotifier
 */
function adminNotifier (string $unique = '') : AdminNotifier {
  static $memo = null;
  if ($memo === null) {
    $memo = new AdminNotifier($unique);
  }

  return $memo;
}
/**
 * Public Notifier.
 *
 * Memoized, combination getter/setter intended to be used for working with
 * multiple instances of AdminNotifier over the course of a wordpress page
 * rendering.
 *
 * @return AdminNotifier
 */
function themeNotifier (string $unique = '') : ThemeNotifier {
  static $memo = null;
  if ($memo === null) {
    $memo = new ThemeNotifier($unique);
  }

  return $memo;
}
/**
 * Admin Error Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function adminError ($text, ...$values) : NoticeInterface {
  return new AdminNotice('error', sprintf($text, ...$values));
}
/**
 * Admin Information Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function adminInfo ($text, ...$values) : NoticeInterface {
  return new AdminNotice('info', sprintf($text, ...$values));
}
/**
 * Admin Success Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function adminSuccess ($text, ...$values) : NoticeInterface {
  return new AdminNotice('success', sprintf($text, ...$values));
}
/**
 * Admin Warning Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function adminWarning ($text, ...$values) : NoticeInterface {
  return new AdminNotice('warning', sprintf($text, ...$values));
}
/**
 * Public Error Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function themeError ($text, ...$values) : NoticeInterface {
  return new Notice('error', sprintf($text, ...$values));
}
/**
 * Public Information Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function themeInfo ($text, ...$values) : NoticeInterface {
  return new Notice('info', sprintf($text, ...$values));
}
/**
 * Public Success Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function themeSuccess ($text, ...$values) : NoticeInterface {
  return new Notice('success', sprintf($text, ...$values));
}
/**
 * Public Warning Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function themeWarning ($text, ...$values) : NoticeInterface {
  return new Notice('warning', sprintf($text, ...$values));
}
/**
 * WooCommerce Error Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function wooError ($text, ...$values) : NoticeInterface {
  return (new Notice('error', sprintf($text, ...$values)))->withClass('woocommerce-error');
}
/**
 * WooCommerce Information Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function wooInfo ($text, ...$values) : NoticeInterface {
  return (new Notice('info', sprintf($text, ...$values)))->withClass('woocommerce-info');
}
/**
 * WooCommerce Success Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return NoticeInterface
 */
function wooSuccess ($text, ...$values) : NoticeInterface {
  return (new Notice('success', sprintf($text, ...$values)))->withClass('woocommerce-message');
}
