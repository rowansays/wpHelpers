<?php
/**
 * getMetaString() Unit Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use function RowanSaysWpHelpers\Meta\getMetaString;

require_once dirname(__DIR__) . '/Meta/Meta.php';

class TestGetMetaStringFromPost extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $id = wp_insert_post([
      'post_title' => 'Sample Post',
      'post_content' => 'This is just some sample post content.'
    ]);

    update_post_meta($id, '_wpHelpersNull', null);
    update_post_meta($id, '_wpHelpersInt', 123);
    update_post_meta($id, '_wpHelpersFloat', 1.23);
    update_post_meta($id, '_wpHelpersString', 'ABC');
    update_post_meta($id, '_wpHelpersArray', [1, 2, 3]);
    update_post_meta($id, '_wpHelpersObject', (object) ['A' => 1, 'B' => 2, 'C' => 3]);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_post($this->postId, true);
  }
  public function test_itPassesWhenMetaWasInsertedAsInt () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersInt');
    print_r($getMeta->toMarkdown());
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('123', $getMeta->toValue());
  }
  public function test_itPassesWhenMetaWasInsertedAsFloat () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersFloat');
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('1.23', $getMeta->toValue());
  }
  public function test_itPassesWhenMetaWasInsertedAsString () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersString');
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('ABC', $getMeta->toValue());
  }
  public function test_itFailsWhenMetaWasInsertedAsArray () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersArray');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('invalidType'));
  }
  public function test_itFailsWhenMetaWasInsertedAsObject () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersObject');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('invalidType'));
  }
  public function test_itFailsWhenMetaWasInsertedAsNull () {
    $getMeta = getMetaString('post', $this->postId, '_wpHelpersNull');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('emptyValue'));
  }
  public function test_itFailsWhenMetaDoesNotExist () {
    $getMeta = getMetaString('post', $this->postId, '_iDoNotExist');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('emptyValue'));
  }
}
