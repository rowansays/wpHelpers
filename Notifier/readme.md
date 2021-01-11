# Notifier

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

These factory functions all share the same signature. They accept a `string` as
their first parameter. This represents the user-facing text of the notice.
Zero or more parameters may follow the first and represent values which will be
passed, along with the first, to `sprintf()` allowing for the use of formatted
strings in the first parameter.

#### Create a Notice

```PHP
$notice = Info("There's a crack in everything. That's how the light gets in.");
```

#### Create a Formatted Notice

```PHP
$notice = Info("There's a crack in %1$s. That's how the %2$s gets in.", 'everything', 'light');
```

https://www.php.net/manual/en/function.printf.php

### Registering Notices

Two methods are available to register notices to be rendered during the
[admin_notices](https://developer.wordpress.org/reference/hooks/admin_notices/)
action. These are:

  1. `Notifier::notifyAdmin()` - For single site screens.
  2. `Notifier::notifyNetwork()` - For network admin screens.

Each of these methods accept a single parameter which must be an instance of
`Notice`. Various examples follow illustrating the use of these methods.

#### Display a notice on all admin screens:

```PHP
  Notifier()->notifyAdmin(Error('Something bad happened.'));
  Notifier()->notifyAdmin(Info('You should know about this.'));
  Notifier()->notifyAdmin(Success('Something good happened.'));
  Notifier()->notifyAdmin(Warning('Be afraid. Be very afraid.'));
```

#### Display a notice on all network admin screens:

```PHP
  Notifier()->notifyNetwork(Error('Something bad happened.'));
  Notifier()->notifyNetwork(Info('You should know about this.'));
  Notifier()->notifyNetwork(Success('Something good happened.'));
  Notifier()->notifyNetwork(Warning('Be afraid. Be very afraid.'));
```
