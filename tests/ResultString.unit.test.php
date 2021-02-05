<?php
/**
 * ResultString Unit Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use RowanSaysWpHelpers\Result\ResultString;

require_once dirname(__DIR__) . '/Result/Result.php';

class TestResultStringConstructor extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('RowanSaysWpHelpers\Result\ResultString'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Result\ResultString'))->isFinal());
  }
  public function test_itConstructsWithMinimumParameters () {
    $result = new ResultString('Testing', null, 'Purple Rain');
    $this->assertEquals($result->toValue(), 'Purple Rain');
  }
  public function test_itRequiresParamOne () {
    $this->expectException('\TypeError');
    $result = new ResultString();
  }
  public function test_itRequiresParamTwo () {
    $this->expectException('\TypeError');
    $result = new ResultString('Testing');
  }
  public function test_itRequiresParamThree () {
    $this->expectException('\TypeError');
    $result = new ResultString('Testing', 'passed');
  }
  public function test_itThrowsWhenParamThreeInNotString () {
    $this->expectException('\TypeError');
    $result = new ResultString('Testing', null, null);
  }
  public function test_itCountable () {
    $this->assertInstanceOf(
      '\Countable',
      new ResultString('Testing', null, '')
    );
  }
  public function test_itIterable () {
    $this->assertTrue(is_iterable(new ResultString('Testing', null, '')));
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf(
      'RowanSaysWpHelpers\Result\ResultInterface',
      new ResultString('Testing', null, '')
    );
  }
}
