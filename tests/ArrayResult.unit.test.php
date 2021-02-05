<?php
/**
 * ArrayResult Unit Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use RowanSaysWpHelpers\Result\ArrayResult;

require_once dirname(__DIR__) . '/Result/Result.php';

class TestArrayResultConstructor extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('RowanSaysWpHelpers\Result\ArrayResult'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Result\ArrayResult'))->isFinal());
  }
  public function test_itConstructsWithMinimumParameters () {
    $result = new ArrayResult('Testing', null, []);
    $this->assertEquals($result->toValue(), []);
  }
  public function test_itRequiresParamOne () {
    $this->expectException('\TypeError');
    $result = new ArrayResult();
  }
  public function test_itRequiresParamTwo () {
    $this->expectException('\TypeError');
    $result = new ArrayResult('Testing');
  }
  public function test_itRequiresParamThree () {
    $this->expectException('\TypeError');
    $result = new ArrayResult('Testing', 'passed');
  }
  public function test_itThrowsWhenParamThreeInNotAnArray () {
    $this->expectException('\TypeError');
    $result = new ArrayResult('Testing', null, null);
  }
  public function test_itCountable () {
    $this->assertInstanceOf(
      '\Countable',
      new ArrayResult('Testing', null, [])
    );
  }
  public function test_itIterable () {
    $this->assertTrue(is_iterable(new ArrayResult('Testing', null, [])));
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf(
      'RowanSaysWpHelpers\Result\ResultInterface',
      new ArrayResult('Testing', null, [])
    );
  }
}
