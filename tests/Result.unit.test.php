<?php
/**
 * Class SampleTest
 *
 * @package WpHelpers
 */

require_once dirname(__DIR__) . '/Result/Result.php';

use Please\Change\Me\Result;

class TestResultConstructor extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('\Please\Change\Me\Result'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('Please\Change\Me\Result'))->isFinal());
  }
  public function test_itConstructsWithNoParameters () {
    $result = new Result();
    $this->assertEquals(count($result), 0);
  }
  public function test_itCountable () {
    $this->assertInstanceOf('\Countable', new Result());
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf('Please\Change\Me\ResultInterface', new Result());
  }
  public function test_itConstructsWithUndefinedState () {
    $result = new Result();
    $this->assertFalse($result->failed());
    $this->assertFalse($result->passed());
  }
}

class TestResultRenderText extends WP_UnitTestCase {
  public function test_itRendersPassedTitle () {
    $result = new Result('Tell them all that the sky is falling');
    $result->pass();
    $this->assertEquals(
      $result->renderText(),
      'Tell them all that the sky is falling (passed)'
    );
  }
  public function test_itRendersFailedTitle () {
    $result = new Result('Tell them all that the sky is falling');
    $result->fail();
    $this->assertEquals(
      $result->renderText(),
      'Tell them all that the sky is falling (failed)'
    );
  }
  public function test_itRendersLinearResultWithOneLogItem () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '    1. Tell it to Henny Penny'
    ;
    $this->assertEquals($result->renderText(), $expected);
  }
  public function test_itRendersLinearResultWithTwoLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '    1. Tell it to Henny Penny' . "\n" .
      '    2. Tell it to Ducky Lucky'
    ;
    $this->assertEquals($result->renderText(), $expected);
  }
  public function test_itRendersLinearResultWithThreeLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->log('Tell it to Goosey Loosey');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '    1. Tell it to Henny Penny' . "\n" .
      '    2. Tell it to Ducky Lucky' . "\n" .
      '    3. Tell it to Goosey Loosey'
    ;
    $this->assertEquals($result->renderText(), $expected);
  }
  public function test_itRendersLinearResultWithFourLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->log('Tell it to Goosey Loosey');
    $result->log('Tell it to Piggy Wiggly');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '    1. Tell it to Henny Penny' . "\n" .
      '    2. Tell it to Ducky Lucky' . "\n" .
      '    3. Tell it to Goosey Loosey' . "\n" .
      '    4. Tell it to Piggy Wiggly'
    ;
    $this->assertEquals($result->renderText(), $expected);
  }
  public function test_itRendersTreeResultWithDepthOfOne () {
    $result = (new Result('Tell them all that the sky is falling'))
      ->merge((new Result('Tell it to Henny Penny'))
        ->log('Oh, Henny Penny!')
        ->log('Have you heard?')
        ->log('The sky is falling.')
        ->pass()
      )->fail()
    ;
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '    1. Tell it to Henny Penny (passed)' . "\n" .
      '        1. Oh, Henny Penny!' . "\n" .
      '        2. Have you heard?' . "\n" .
      '        3. The sky is falling.'
    ;
    $this->assertEquals($result->renderText(), $expected);
  }
}

class TestResultRenderMarkdown extends WP_UnitTestCase {
  public function test_itRendersPassedTitle () {
    $result = new Result('Tell them all that the sky is falling');
    $result->pass();
    $this->assertEquals(
      $result->toMarkdown(),
      'Tell them all that the sky is falling (passed)'
    );
  }
  public function test_itRendersFailedTitle () {
    $result = new Result('Tell them all that the sky is falling');
    $result->fail();
    $this->assertEquals(
      $result->toMarkdown(),
      'Tell them all that the sky is falling (failed)'
    );
  }
  public function test_itRendersLinearResultWithOneLogItem () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithTwoLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny' . "\n" .
      '  * Tell it to Ducky Lucky'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithThreeLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->log('Tell it to Goosey Loosey');
    $result->fail();
    $expected =
      'Tell them all that the sky is falling (failed)' . "\n" .
      '  * Tell it to Henny Penny' . "\n" .
      '  * Tell it to Ducky Lucky' . "\n" .
      '  * Tell it to Goosey Loosey'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itRendersLinearResultWithFourLogItems () {
    $result = new Result('Tell them all that the sky is falling');
    $result->log('Tell it to Henny Penny');
    $result->log('Tell it to Ducky Lucky');
    $result->log('Tell it to Goosey Loosey');
    $result->log('Tell it to Piggy Wiggly');
    $result->fail();
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
    $result = (new Result('Tell them all that the sky is falling'))
      ->merge((new Result('Tell it to Henny Penny'))
        ->log('Oh, Henny Penny!')
        ->log('Have you heard?')
        ->log('The sky is falling.')
        ->pass()
      )->fail()
    ;
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
