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

use RowanSays\Wp\Admin\Notice;
use RowanSays\Wp\Admin\Notifier;

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

### Registering Notices

Two methods are available to register notices to be rendered during the
[admin_notices](https://developer.wordpress.org/reference/hooks/admin_notices/)
action. These are:

  1. Notifier::notifyAdmin() - For single site screens.
  2. Notifier::notifyNetwork() - For network admin screens.

Each of these methods accept a single parameter which must be an instance of
`Notice`. Various examples follow illustrating the use of these methods.

#### Display a notice on all admin screens:

```PHP
  Notifier()->notifyAdmin(new Notice('error', 'Something bad happened.'));
  Notifier()->notifyAdmin(new Notice('info', 'You should know about this.'));
  Notifier()->notifyAdmin(new Notice('success', 'Something good happened.'));
  Notifier()->notifyAdmin(new Notice('warning', 'Be afraid. Be very afraid.'));
```

#### Display a notice on all network admin screens:

```PHP
  Notifier()->notifyNetwork(new Notice('error', 'Something bad happened.'));
  Notifier()->notifyNetwork(new Notice('info', 'You should know about this.'));
  Notifier()->notifyNetwork(new Notice('success', 'Something good happened.'));
  Notifier()->notifyNetwork(new Notice('warning', 'Be afraid. Be very afraid.'));
```
