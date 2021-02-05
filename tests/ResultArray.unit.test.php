<?php
/**
 * ResultArray Unit Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use RowanSaysWpHelpers\Result\ResultArray;

require_once dirname(__DIR__) . '/Result/Result.php';

class TestResultArrayConstructor extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('RowanSaysWpHelpers\Result\ResultArray'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Result\ResultArray'))->isFinal());
  }
  public function test_itConstructsWithMinimumParameters () {
    $result = new ResultArray('Testing', null, []);
    $this->assertEquals($result->toValue(), []);
  }
  public function test_itRequiresParamOne () {
    $this->expectException('\TypeError');
    $result = new ResultArray();
  }
  public function test_itRequiresParamTwo () {
    $this->expectException('\TypeError');
    $result = new ResultArray('Testing');
  }
  public function test_itRequiresParamThree () {
    $this->expectException('\TypeError');
    $result = new ResultArray('Testing', 'passed');
  }
  public function test_itThrowsWhenParamThreeInNotAnArray () {
    $this->expectException('\TypeError');
    $result = new ResultArray('Testing', null, null);
  }
  public function test_itCountable () {
    $this->assertInstanceOf(
      '\Countable',
      new ResultArray('Testing', null, [])
    );
  }
  public function test_itIterable () {
    $this->assertTrue(is_iterable(new ResultArray('Testing', null, [])));
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf(
      'RowanSaysWpHelpers\Result\ResultInterface',
      new ResultArray('Testing', null, [])
    );
  }
}
