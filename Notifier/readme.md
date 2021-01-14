# Notifier

Renders notices in the admin section of WordPress.

## Usage

### Setup

First, you'll want to download the Notifier.php file and save it to your plugin
or theme. Next you'll want to change the namespace of the file to something
unique. After these steps have been completed you should include the file into
your extension and create a memoized factory function which returns an
instance of `Notifier`. For example:

```PHP
declare(strict_types = 1);

namespace myName\Wp\myProject;

require_once __DIR__ . '/wpHelpers/Notifier/Notifier.php';

use RowanSays\Wp\Admin\Notifier;
use function RowanSays\Wp\Admin\Error;
use function RowanSays\Wp\Admin\Info;
use function RowanSays\Wp\Admin\Success;
use function RowanSays\Wp\Admin\Warning;

function Notifier (...$args) {
  static $instance = null;
  if ($instance === null) {
    $instance = new Notifier('RowansSaysTestNotifier');
  }
  return $instance;
}
```

### Hook

Once your project is has been set up you'll need to hook into WordPress. This
can be done by calling the `Notifier::hook()` method directly from our factory
function during the [admin_init action](https://developer.wordpress.org/reference/hooks/admin_init/).

```PHP
add_action('admin_init', function() {
  Notifier()->hook();
});
```

### Creating Notices

Four factory functions are available to create notices.

  1. `Error()` - renders with a red side border.
  1. `Info()` - renders with a blue side border.
  1. `Success()` - renders with a green side border.
  1. `Warning()` - renders with an orange side border.

These functions all share the same signature. They accept a `string` as their
first parameter. This represents the user-facing text of the notice. Zero or
more parameters may follow the first and represent values which will be passed,
along with the first, to `sprintf()` allowing for the use of
[formatted strings](https://www.php.net/manual/en/function.printf.php) in the
first parameter.

#### Create a Notice

```PHP
$notice = Info("There's a crack in everything. That's how the light gets in.");
```

#### Create a Formatted Notice

```PHP
$notice = Info("There's a crack in %1$s. That's how the %2$s gets in.", 'everything', 'light');
```

### Registering Notices

Two methods are available to register notices to be rendered during the
[admin_notices](https://developer.wordpress.org/reference/hooks/admin_notices/)
action. These are:

  1. `Notifier::notifyAdmin()` - For single site screens.
  2. `Notifier::notifyNetwork()` - For network admin screens.

Each of these methods accept a single parameter which must be an instance of
`Notice`. Various examples follow illustrating the use of these methods.

#### All Admin Screens:

```PHP
  Notifier()->notifyAdmin(Info('You should know about this.'));
```

#### All Network Admin Screens:

```PHP
  Notifier()->notifyNetwork(Error('Something bad happened.'));
```

#### Only on the Dashboard:

```PHP
  Notifier()->notifyAdmin(Success('Something good happened.')->showOn('dashboard'));
```

#### All Admin Screens Except the Dashboard:

```PHP
  Notifier()->notifyAdmin(Warning('Your attention is needed!')->hideOn('dashboard'));
```

### Redirecting

When validating a POST request, it is often helpful to redirect with a
specific error message after the request has been procecessed and when the
request fails . The `Notifier::redirect()` method can be used for both
instances. This method has a simple signature accepting a URL and a Notice as
parameters.

The following code creates a simple admin screen with a form that has a button
for each notice type. When each button is pressed, the form is submitted and the
user is redirected back to the custom screen with the custom notice appended to
the URL. The notice is, almost immediately, removed from the URL using the
[removable_query_args](https://developer.wordpress.org/reference/functions/wp_removable_query_args/)
feature of WordPress.

```PHP
add_action('admin_menu', function () {
  add_management_page(
    'Notifier Test',
    'Notifier Test',
    'edit_plugins',
    'RowanSaysNotifierTest',
    function () {
      echo '' .
        '<div class="wrap">' .
          '<h1>Notifier Test</h1>' .
          '<form method="post" action="admin-post.php">' .
            '<input type="hidden" name="action" value="RowanSaysTestNotifier">' .
            '<input type="submit" class="button-primary" name="type" value="error"> ' .
            '<input type="submit" class="button-primary" name="type" value="success"> ' .
            '<input type="submit" class="button-primary" name="type" value="info"> ' .
            '<input type="submit" class="button-primary" name="type" value="warning"> ' .
          '</form>' .
        '</div>'
      ;
    }
  );
});

add_action('admin_post_RowanSaysTestNotifier', function () {
  $url = add_query_arg(['page' => 'RowanSaysNotifierTest'], admin_url('tools.php'));
  switch($_POST['type']) {
    case 'error' : Notifier()->redirect($url, Error('Error notice from URL'));
    case 'info' : Notifier()->redirect($url, Info('Info notice from URL'));
    case 'success' : Notifier()->redirect($url, Success('Success notice from URL'));
    case 'warning' : Notifier()->redirect($url, Warning('Warning notice from URL'));
  }
});
```
