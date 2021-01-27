## Example 1

In this example we will wrap the WordPress core function `get_posts()` in a
function that returns an instance of `Result`.

```PHP
function getPostsById () : Result {
  $posts = get_posts();
  return new Result('Querying WordPress for posts');
}
```

This function illustrates the main purpose of this library: to keep track of the
things that your code does. Here, we ask WordPress for posts and then we return
an instance of `Result` which describes what has happened.

There are a couple problems with this function. It should:

  1. enable specific posts to be queried.
  2. report on its state: did it pass or fail?
  3. return any posts returned by `get_posts()`
  4. posses a more informative log

If we were to called this function and render the result:

```PHP
echo '<pre>' . getPostsById()->toMarkdown() . '</pre>';
```

The mardown would look like this:

```MD
Querying WordPress for posts
```

## Example 2: Add Parameters

```PHP
function getPostsById (int ...$ids) : Result {
  // Query for posts
  $posts = get_posts(['include' => $ids]);

  // Return a new result instance.
  return new Result('Querying WordPress for posts');
}
```

Here, we've allowed the function to accept zero or more integer as parameters.
These parameters are then passed as the value of `include` to `get_posts()`.
We can now check number one off our list:

  1. ~~enable specific posts to be queried.~~
  2. report on its state: did it pass or fail?
  3. return any posts returned by `get_posts()`
  4. posses a more informative log

## Example 3: Determine State

```PHP
function getPostsById (int ...$ids) : Result {
  // Query for posts
  $posts = get_posts(['include' => $ids]);

  // Determine the state of the result.
  $state = count($ids) > 0 && count($ids) === count($posts) ? 'passed' : 'failed';

  // Return a new result instance.
  return new Result('Querying WordPress for posts', $state);
}
```

While there are a few ways to determine whether this action passed or failed,
I've chosen to use define success as the combination of two ideas: _all
requested posts are returned_ and _at least one post is returned_. The
`Result::toMarkdown()` will now reflect the state of the function. One of the
following strings will be rendered:

```MD
Querying WordPress for posts (passed)
```

```MD
Querying WordPress for posts (failed)
```

We can now check number two off our list:

  1. ~~enable specific posts to be queried.~~
  2. ~~report on its state: did it pass or fail?~~
  3. return any posts returned by `get_posts()`
  4. posses a more informative log

## Example 3: Return a value

```PHP
function getPostsById (int ...$ids) : Result {
  // Query for posts
  $posts = get_posts(['include' => $ids]);

  // Determine the state of the result.
  $state = count($ids) > 0 && count($ids) === count($posts) ? 'passed' : 'failed';

  // Return a new result instance.
  return new Result('Querying WordPress for posts', $state, $posts);
}
```

Here, weve just passed the value stored in `$posts` as the third parameter of
the constructor. No changes will be noticeable in `Result::toMarkdown()` but,
`Result::toValue()` will now return all of the posts returned from `get_posts()`
which means we can cross number 3 off our list:

  1. ~~enable specific posts to be queried.~~
  2. ~~report on its state: did it pass or fail?~~
  3. ~~return any posts returned by `get_posts()`~~
  4. posses a more informative log

## Example 3: Enhance Log

```PHP
function getPostsById (int ...$ids) : Result {
  // Query for posts
  $posts = get_posts(['include' => $ids]);

  // Define a log with an initial entry.
  $log = [
    new Result(sprintf(
      '%d out of %d requested posts were found', count($posts), count($ids)
    )),
  ];

  // Determine the state of the result.
  $state = count($ids) === count($posts) ? 'passed' : 'failed';

  // Define the value that we will stored inside of our result.
  $value = $posts;

  // Define a log of things that we know about the action.
  $log = [
    new Result(sprintf('Number of posts requested: %d', count($ids))),
    new Result(sprintf('Number of posts found: %d', count($posts))),
  ];

  // Return a new result instance.
  return new Result('Querying the WordPress database for posts', $state, $value, $log);
}
```

The first thing that we do here is to create a `$log` variable in which we will
store an array of `Result` instances.


### Render the result in markdown
```PHP
echo '<pre>' . $getPosts->toMarkdown() . </pre>;
```

The following string will be returned when all __seven__ of the ids passed to
`getPostsById()` represent a published post.

```MD
Querying the WordPress database for posts (passed)
  * Number of posts requested: 7
  * Number of posts found: 7
```

When only __three__ ids represent published posts:

```MD
Querying the WordPress database for posts (failed)
  * Number of posts requested: 7
  * Number of posts found: 3
```

When __none__ of the ids represent published posts:

```MD
Querying the WordPress database for posts (failed)
  * Number of posts requested: 7
  * Number of posts found: 0
```

The string returned by `Result::toMarkdown()` contains only markdown. No method
is provided to convert the markdown into HTML. If you need to do this, consider
using a library like [Parsedown](https://github.com/erusev/parsedown).
