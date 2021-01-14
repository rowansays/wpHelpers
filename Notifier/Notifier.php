<?php
/**
 * Admin Notifier.
 *
 * Easy registration and rendering of user-facing notices.
 *
 * @package RowanSays\Wp\Helpers
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 * @version 0.3.1
 */

declare(strict_types = 1);

namespace RowanSays\Wp\Helpers;

class Notifier {
  /**
   * Unique.
   *
   * A unique prefix or namespace this notifier. This value is used to as a
   * prefix for notices sent via query string.
   *
   * @access private
   * @var string
   */
  private string $unique = '';
  /**
   * Admin Notices.
   *
   * An array of Notice instances representing the notices which will be
   * rendered to the screen.
   *
   * @access private
   * @var Notice[]
   */
  private array $adminNotices = [];
  /**
   * Network Notices.
   *
   * An array of Notice instances representing the notices which will be
   * rendered to the screen.
   *
   * @access private
   * @var Notice[]
   */
  private array $networkNotices = [];
  /**
   * Query String Notices.
   *
   * Notices that have been registered via the query string of the requesting
   * URL. These notices may be rendered on either admin or network admin
   * screens.
   *
   * @access private
   * @var Notice[]
   */
  private array $queryNotices = [];

  /**
   * Recognized query arguments.
   *
   * An array of strings representing the names of GET arguments recognized by
   * this notifier. Each argument name is prefxed with a unique value which
   * allows us to esily distinguish our notices from other arguments which may
   * have similar names.
   *
   * @access private
   * @var array
   */
  private array $recognizedQueryArgs = [];
  /**
   * Create a new Notifier instance.
   *
   * @param $unique {string} A unique identifier for this notifier.
   * @return Notifier
   */
  public function __construct (string $unique) {
    $this->unique = $unique;
    $this->recognizedQueryArgs = [
      $unique . '-error',
      $unique . '-info',
      $unique . '-success',
      $unique . '-warning',
    ];
  }
  /**
   * Allow read-only access to all private properties.
   *
   * @return mixed
   */
  public function __get ($name) {
    return property_exists($this, $name) ? $this->$name : null;
  }
  /**
   * Disallow the creation of new properties on an instance
   * @throws Exception
   */
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  /**
   * Is at least one notice registered for a given screen?
   *
   * @return bool
   */
  public function hasNoticesFor (string $screenId) : bool {
    $all = array_merge(
      $this->adminNotices,
      $this->networkNotices,
      $this->queryNotices
    );

    foreach($all as $notice) {
      if ($notice->existsOn($screenId)) {
        return true;
      }
    }

    return false;
  }
  /**
	 * Hook into WordPress.
   *
   * This method is not called automatically by the constructor
   *
   * @return Notifier
	 */
	public function hook () : Notifier {
    add_action('admin_notices', function () : void {
      $this->parseQuery();
      $this->renderNotices($this->adminNotices);
      $this->renderNotices($this->queryNotices);
    });

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
  public function notifyAdmin (Notice $notice) : void {
    $this->adminNotices[$notice->md5()] = $notice;
  }
  public function notifyNetwork (Notice $notice) : void {
    $this->networkNotices[$notice->md5()] = $notice;
  }
	/**
	 * Redirect with with a notice.
	 *
	 * @param string $url The url to redirect to.
	 * @param Notice $notice Notice to display to the user.
   *
   * @return void Redirects to a new URL and terminates script execution.
	 */
	public function redirect (string $url, Notice $notice) : void {
    $key = $this->unique . '-' . $notice->type;
    $url = add_query_arg([$key => $notice->getUrlString()], $url);
		wp_safe_redirect($url);
		exit;
  }
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
	private function filterQueryArgs ($args) : array {
    return is_array($args)
      ? array_merge($args, array_values($this->recognizedQueryArgs))
      : $this->recognizedQueryArgs
    ;
  }
  /**
   * Store notices sent via GET request.
   *
   * @return void
   */
  private function parseQuery () : void {
    $dirty = $_GET;
    $defaults = array_fill_keys($this->recognizedQueryArgs, '');
		$get = array_filter(array_intersect_key(array_merge($defaults, $dirty), $defaults));

		foreach($get as $key => $value) {
      try {
        $type = str_replace($this->unique . '-', '', $key); // This is iffy at best. FIX PLEASE
        $notice = new Notice($type, $value);
        $this->queryNotices[$notice->md5()] = $notice;
      } catch (\Exception $e) {
        // Intentional noop.
      }
    }
  }
  /**
   * Register a notice.
   *
   * @param $scope string
   * @param $notice Notice
   *
   * @return void Echoes HTML markup.
   */
  private function register (string $scope, Notice $notice) : void {
    $this->notices[$scope][$notice->md5()] = $notice;
  }
  private function renderNotice (Notice $notice) {
    return vsprintf('<div class="%1$s"><p>%2$s</p></div>', [
      '1: classList' => esc_html($notice->getClassList()),
      '2: innerText' => $notice->text
    ]);
  }
  /**
	 * Render registered notices.
   *
   * @param $notices Notice[] The notices to render.
   * @return void Echoes HTML markup.
	 */
	private function renderNotices (array $notices) : void {
    $screen = get_current_screen();
    foreach($notices as $notice) {
      if ($notice->existsOn($screen->id)) {
        echo $this->renderNotice($notice);
      }
    }
  }
}

class Notice {
  private array $classes = ['notice'];
  private string $type = 'info';
  private string $text = '';
  private array $exclude = [];
  private array $include = [];
  /**
   * Create a new notice.
   *
   * @param string $type The following values are recognized: 'error', 'info',
   *   'success', and 'warning'. A type of 'info' will be used in cases where
   *   an unrecognized type is given.
   * @param string $text User-facing message. Required.
   * @param string $values Zero or more values to use when $text is formatted. Optional
   *
   * @return Notice
   */
  public function __construct (string $type, string $text, ...$values) {
    $types = ['error', 'info', 'success', 'warning'];
    $this->type = in_array($type, $types) ? $type : 'info';
    $cleanText = trim($text);
    if ($cleanText === '') {
      throw new \Exception('Parameter 2 `$text` must not be empty.');
    } else {
      $this->text = sprintf($cleanText, ...$values);
    }
    $this->classes[] = 'notice-' . $this->type;
  }
  public function __get ($name) {
    return property_exists($this, $name) ? $this->$name : null;
  }
  public function __set ($name, $value) {
    throw new \Exception('Mutation of a read-only instance is not permitted.');
  }
  public function md5 () : string {
    return md5(implode($this->classes) . $this->type . $this->text);
  }
  public function getUrlString() {
    return rawurlencode($this->text);
  }
  public function getClassList() {
    return implode(' ', $this->classes);
  }
  /**
   * Exclude this notice from one or more screens.
   *
   * @param string $screenIds one or more screen ids to exclude.
   * @return Notice Updated instance
   */
  public function hideOn (string ...$screenIds) {
    $this->exclude = $this->arrayMerge($this->exclude, $screenIds);
    return $this;
  }
  /**
   * Include this notice on one or more screens.
   *
   * @return Notice Updated instance
   */
  public function showOn (string ...$screenIds) {
    $this->include = $this->arrayMerge($this->include, $screenIds);
    return $this;
  }
  /**
   * Should this notice be rendered on a given screen?
   *
   * @return bool
   */
  public function existsOn (string $screenId) : bool {
    if (count($this->include) === 0 && count($this->exclude) === 0) {
      return true;
    }
    if (count($this->include) > 0) {
      return in_array($screenId, $this->include);
    }
    return ! in_array($screenId, $this->exclude);
  }
  private function arrayMerge (array $a, array $b) {
    return array_unique(array_filter(array_merge($a, $b)));
  }
}

/**
 * Error Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Error ($text, ...$values) {
  return new Notice('error', $text, ...$values);
}

/**
 * Information Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Info ($text, ...$values) {
  return new Notice('info', $text, ...$values);
}

/**
 * Success Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Success ($text, ...$values) {
  return new Notice('success', $text, ...$values);
}

/**
 * Warning Notice.
 *
 * @param string $text User-facing message. Required.
 * @param string $values Zero or more values to use when $text is formatted. Optional
 *
 * @return Notice
 */
function Warning ($text, ...$values) {
  return new Notice('warning', $text, ...$values);
}
