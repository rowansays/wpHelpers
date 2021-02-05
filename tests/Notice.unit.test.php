<?php
/**
 * Notice tests
 */

namespace RowanSaysWpHelpers\Notifier\Test;

require_once dirname(__DIR__) . '/Notifier/Notifier.php';

use RowanSaysWpHelpers\Notifier\Notice;
use WP_UnitTestCase;

class NoticeConstructor extends WP_UnitTestCase {
	/** @test */
	public function itIsClass () {
		$this->assertTrue(class_exists('\RowanSaysWpHelpers\Notifier\Notice'));
  }
  /** @test */
	public function itIsFinal () {
    $this->assertTrue((new \ReflectionClass('RowanSaysWpHelpers\Notifier\Notice'))->isFinal());
  }
  /** @test */
	public function itConstructsEmptyInstances () {
    $notice = new Notice();
    $this->assertEquals($notice->getClasses(), ['notice', 'notice-info']);
    $this->assertEquals($notice->isRenderable(), true);
    $this->assertEquals($notice->getText(), '');
    $this->assertEquals($notice->getType(), 'info');
    $this->assertEquals($notice->getUserIds(), []);
  }
  /** @test */
	public function itAcceptsErrorForFirstParameter () {
    $notice = new Notice('error');
    $this->assertTrue(in_array('notice-error', $notice->getClasses()));
    $this->assertEquals($notice->getType(), 'error');
  }
  /** @test */
	public function itAcceptsInfoForFirstParameter () {
    $notice = new Notice('info');
    $this->assertTrue(in_array('notice-info', $notice->getClasses()));
    $this->assertEquals($notice->getType(), 'info');
  }
  /** @test */
	public function itAcceptsWarningForFirstParameter () {
    $notice = new Notice('warning');
    $this->assertTrue(in_array('notice-warning', $notice->getClasses()));
    $this->assertEquals($notice->getType(), 'warning');
  }
  /** @test */
	public function itAcceptsSuccessForFirstParameter () {
    $notice = new Notice('success');
    $this->assertTrue(in_array('notice-success', $notice->getClasses()));
    $this->assertEquals($notice->getType(), 'success');
	}
}
class NoticeConstructorExceptionalBehavior extends WP_UnitTestCase {
  /** @test */
	public function itThrowsExceptionWhenTextPropIsSet () {
    $this->expectException('\Exception');
    $notice = new Notice();
    $notice->text = 'No prayers for November to linger longer...';
  }
  /** @test */
  public function itThrowsErrorWhenTextPropIsReferenced () {
    $this->expectException('\Error');
    $notice = new Notice();
    $var = $notice->text;
  }
  /** @test */
  public function itThrowsExceptionWhenNewPropIsAdded () {
    $this->expectException('\Exception');
    $notice = new Notice();
    $notice->newProp = false;
  }
}
