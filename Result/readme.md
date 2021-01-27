# Result

An instance created by the `Result` constructor

  * is immutable
  * implements the `ResultInterface`
  * represents the result of a specific action
  * has a state of : _passed_, _failed_, or _undefined_
  * has a log containing zero or more sub-actions
  * may contain a value

## Constructor

```PHP
$result = new Result(
  string $action,
  string ?$state = null,
  $value = null,
  iterable $log = []
);
```

### Parameters

  1. __Action__ `(string)` Required. Must be a non-empty string. The action
     should be written in the present tense and describe the action represented
     by the result.
  2. __State__ `(string|null)` Optional. Use this parameter to communicate if
     the action was successful or not. May be one of
     three values: `'passed'`, `'failed'`, or `null`. Defaults to `null`.
  3. __Value__ `(mixed)` Optional. Use this parameter to save a value. Any value
     is accepted. Defaults to `null`.
  4. __Log__ `(iterable<ResultInterface>)` Optional. An iterable value
     containing zero or more objects that implement `ResultInterface`. Defaults
     to an empty array.

### Properties

Instances of `Result` have no public properties.

### Methods

  1. __failed()__ : `(bool)` Was the result successful?
  2. __passed()__ : `(bool)` Was the result unsuccessful?
  3. __toMarkdown()__ : `(string)` Render a representation of the result in
     markdown. This consists of one line contained the _action_ and the _state_.
     Log entries will be rendered as an unordered list immediately following the action/state line. This method recursively renders all contained results.
  4. __toValue()__ : `(mixed)` Return the value passed as parameter three
     `$value`.

### Tutorials

  1. [Get Posts](./TutorialGetPosts.md)
