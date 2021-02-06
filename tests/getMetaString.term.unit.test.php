<?php
/**
 * getMetaString() Unit Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use function RowanSaysWpHelpers\Meta\getMetaString;

require_once dirname(__DIR__) . '/Meta/Meta.php';

class TestGetMetaString_Term_NonExistent extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itFailsWhenMetaDoesNotExist () {
    $getMeta = getMetaString('term', $this->postId, '_iDoNotExist');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('emptyValue'));
  }
}
class TestGetMetaString_Term_Null extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersNull', null);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itFailsWhenMetaWasInsertedAsNull () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersNull');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('emptyValue'));
  }
}
class TestGetMetaString_Term_Int extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersInt', 123);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itPassesWhenMetaWasInsertedAsInt () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersInt');
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('123', $getMeta->toValue());
  }
}
class TestGetMetaString_Term_Float extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersFloat', 1.23);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itPassesWhenMetaWasInsertedAsFloat () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersFloat');
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('1.23', $getMeta->toValue());
  }
}
class TestGetMetaString_Term_String extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersString', 'ABC');

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itPassesWhenMetaWasInsertedAsString () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersString');
    $this->assertTrue($getMeta->passed());
    $this->assertEquals('ABC', $getMeta->toValue());
  }
}
class TestGetMetaString_Term_Array extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersArray', [1, 2, 3]);
    update_term_meta($id, '_wpHelpersObject', (object) ['A' => 1, 'B' => 2, 'C' => 3]);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itFailsWhenMetaWasInsertedAsArray () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersArray');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('invalidType'));
  }
}
class TestGetMetaString_Term_Object extends WP_UnitTestCase {
  private int $postId = 0;
  public function setUp(): void {
    $insertTerm = wp_insert_term('Sample Term', 'post_tag');
    $id = $insertTerm['term_id'];

    update_term_meta($id, '_wpHelpersObject', (object) ['A' => 1, 'B' => 2, 'C' => 3]);

    $this->postId = $id;
  }
  public function tearDown(): void {
   wp_delete_term($this->termId, 'post_tag');
  }
  public function test_itFailsWhenMetaWasInsertedAsObject () {
    $getMeta = getMetaString('term', $this->postId, '_wpHelpersObject');
    $this->assertTrue($getMeta->failed());
    $this->assertEquals(1, count($getMeta));
    $this->assertEquals('', $getMeta->toValue());
    $this->assertTrue($getMeta->failed('invalidType'));
  }
}
