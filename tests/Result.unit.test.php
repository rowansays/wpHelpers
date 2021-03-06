<?php
/**
 * Result Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 */

use RowanSaysWpHelpers\Result\Result;

require_once dirname(__DIR__) . '/Result/Result.php';

class TestResultConstructor extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('RowanSaysWpHelpers\Result\Result'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Result\Result'))->isFinal());
  }
  public function test_itThrowsWhenParameterOneIsNull () {
    $this->expectException('\ArgumentCountError');
    $result = new Result();
  }
  public function test_itThrowsWhenParameterOneIsEmptyString () {
    $this->expectException('\InvalidArgumentException');
    $result = new Result('');
  }
  public function test_itThrowsWhenParameterTwoIsEmptyString () {
    $this->expectException('\InvalidArgumentException');
    $result = new Result('Testing', '');
  }
  public function test_itConstructsWithParameterOneOnly () {
    $result = new Result('Testing');
    $this->assertIsObject($result);
  }
  public function test_itCountable () {
    $this->assertInstanceOf('\Countable', new Result('Testing'));
  }
  public function test_itIterable () {
    $this->assertTrue(is_iterable(new Result('Testing')));
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf('RowanSaysWpHelpers\Result\ResultInterface', new Result('Testing'));
  }
  public function test_itConstructsWithUndefinedState () {
    $result = new Result('Testing');
    $this->assertFalse($result->failed());
    $this->assertFalse($result->passed());
  }
  public function test_itConstructsWithNullAsParameterTwo () {
    $result = new Result('Testing', null);
    $this->assertFalse($result->failed());
    $this->assertFalse($result->passed());
  }
  public function test_itConstructsWithResultArrayAsLog () {
    $result = new Result('Testing', null, null, [new Result('I am sub-action')]);
    $this->assertEquals(count($result), 1);
  }
  public function test_itThrowsWhenLogContainsNonResult () {
    $this->expectException('\Exception');
    $result = new Result('Testing', null, null, [new \StdClass()]);
  }
}
class Test_Result_failed extends WP_UnitTestCase {
  public function test_itIsCallable () {
    $result = new Result('Testing');
    $this->assertIsCallable([$result, 'failed']);
  }
  public function test_itReturnsEmptyStringForUndefinedResults () {
    $result = new Result('Testing');
    $this->assertFalse($result->failed());
  }
  public function test_itReturnsEmptyStringForSuccessfulResults () {
    $result = new Result('Testing', 'passed');
    $this->assertFalse($result->failed());
  }
  public function test_itReturnsFailedForUnsuccessfulResults () {
    $result = new Result('Testing', 'failed');
    $this->assertTrue($result->failed());
    $this->assertTrue($result->failed('failed'));
  }
  public function test_itReturnsCustomErrorCode () {
    $result = new Result('Testing', 'customErrorCode');
    $this->assertTrue($result->failed());
    $this->assertTrue($result->failed('customErrorCode'));
  }
}
class Test_Result_toMarkdown extends WP_UnitTestCase {
  public function test_itIsCallable () {
    $result = new Result('Testing');
    $this->assertIsCallable([$result, 'toMarkdown']);
  }
  public function test_itRendersPassedTitle () {
    $result = new Result('Tell them all that the sky is falling', 'passed');
    $this->assertEquals(
      $result->toMarkdown(),
      'Tell them all that the sky is falling (passed)'
    );
  }
  public function test_itRendersFailedTitle () {
    $result = new Result('Tell them all that the sky is falling', 'failed');
    $this->assertEquals(
      $result->toMarkdown(),
      'Tell them all that the sky is falling (failed)'
    );
  }
  public function test_itRendersLinearResultWithOneLogItem () {
    $result = new Result('Tell them all that the sky is falling', 'failed', null, [
      new Result('Tell it to Henny Penny')
    ]);
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithTwoLogItems () {
    $result = new Result('Tell them all that the sky is falling', 'failed', null, [
      new Result('Tell it to Henny Penny'),
      new Result('Tell it to Ducky Lucky'),
    ]);
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny' . "\n" .
      '  * Tell it to Ducky Lucky'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithThreeLogItems () {
    $result = new Result('Tell them all that the sky is falling', 'failed', null, [
      new Result('Tell it to Henny Penny'),
      new Result('Tell it to Ducky Lucky'),
      new Result('Tell it to Goosey Loosey'),
    ]);
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny' . "\n" .
      '  * Tell it to Ducky Lucky' . "\n" .
      '  * Tell it to Goosey Loosey'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithFourLogItems () {
    $result = new Result('Tell them all that the sky is falling', 'failed', null, [
      new Result('Tell it to Henny Penny'),
      new Result('Tell it to Ducky Lucky'),
      new Result('Tell it to Goosey Loosey'),
      new Result('Tell it to Piggy Wiggly'),
    ]);
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny' . "\n" .
      '  * Tell it to Ducky Lucky' . "\n" .
      '  * Tell it to Goosey Loosey' . "\n" .
      '  * Tell it to Piggy Wiggly'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersTreeResultWithDepthOfOne () {
    $result = new Result('Tell them all that the sky is falling', 'failed', null, [
        new Result('Tell it to Henny Penny', 'passed', null, [
          new Result('Oh, Henny Penny!'),
          new Result('Have you heard?'),
          new Result('The sky is falling.'),
        ])
      ]
    );
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny (passed)' . "\n" .
      '    * Oh, Henny Penny!' . "\n" .
      '    * Have you heard?' . "\n" .
      '    * The sky is falling.'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
}
