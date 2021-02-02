<?php
/**
 * ResultFromWpError Tests
 *
 * @author Rowan Weathers
 * @license GPL-3.0-or-later
 * @version 2.0.0
 */

use RowanSaysWpHelpers\Result\ResultFromWpError;

require_once dirname(__DIR__) . '/Result/Result.php';

class Test_ResultFromWpError extends WP_UnitTestCase {
	public function test_itIsClass () {
		$this->assertTrue(class_exists('RowanSaysWpHelpers\Result\ResultFromWpError'));
  }
	public function test_itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Result\ResultFromWpError'))->isFinal());
  }
  public function test_itThrowsWhenParameterOneIsNull () {
    $this->expectException('\ArgumentCountError');
    $result = new ResultFromWpError();
  }
  public function test_itThrowsWhenParameterOneIsEmptyString () {
    $this->expectException('\InvalidArgumentException');
    $result = new ResultFromWpError('');
  }
  public function test_itConstructsWithParameterOneOnly () {
    $result = new ResultFromWpError('Testing');
    $this->assertIsObject($result);
  }
  public function test_itCountable () {
    $this->assertInstanceOf('\Countable', new ResultFromWpError('Testing'));
  }
  public function test_itIsResultInterface () {
    $this->assertInstanceOf('RowanSaysWpHelpers\Result\ResultInterface', new ResultFromWpError('Testing'));
  }
  public function test_itConstructsWithUndefinedStateWhenParamTwoIsNull () {
    $result = new ResultFromWpError('Testing');
    $this->assertFalse($result->failed());
    $this->assertFalse($result->passed());
  }
  public function test_itConstructsWithFailedStateWhenParamTwoIsWpError () {
    $result = new ResultFromWpError('Testing', new WP_Error());
    $this->assertTrue($result->failed());
    $this->assertFalse($result->passed());
  }
  public function test_itReadsScalarWpError () {
    $error = new WP_Error('404', 'Not Found');
    $result = new ResultFromWpError('Testing', $error);
    $expected =
      'Testing (failed)' . "\n" .
      '  * 404 - Not Found'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itReadsLinearWpError () {
    $error = new WP_Error(404, 'Not Found');
    $error->add(401, 'Unauthorized');
    $error->add(403, 'Forbidden');
    $result = new ResultFromWpError('Testing', $error);
    $expected =
      'Testing (failed)' . "\n" .
      '  * 404 - Not Found' . "\n" .
      '  * 401 - Unauthorized' . "\n" .
      '  * 403 - Forbidden'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
  public function test_itReadsRectangularWpError2 () {
    $error = new WP_Error(402, 'Payment Required');
    $error->add(404, 'Not Found');
    $error->add(402, 'Please, Pay');
    $error->add(404, 'I lost my wallet');
    $error->add(402, 'Need money please');
    $error->add(404, 'Well I guees now I need money because you need money?');

    $result = new ResultFromWpError('Testing', $error);

    $expected =
      'Testing (failed)' . "\n" .
      '  * 402 - Payment Required' . "\n" .
      '  * 402 - Please, Pay' . "\n" .
      '  * 402 - Need money please' . "\n" .
      '  * 404 - Not Found' . "\n" .
      '  * 404 - I lost my wallet' . "\n" .
      '  * 404 - Well I guees now I need money because you need money?'
    ;
    $this->assertEquals($result->toMarkdown(), $expected);
  }
}
