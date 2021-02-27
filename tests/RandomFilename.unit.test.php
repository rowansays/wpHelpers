<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/File/RandomFilename.php';

use RowanSaysWpHelpers\File\RandomFilename;

final class TestRandomFilename extends WP_UnitTestCase {
  const DIR = __DIR__ . '/Images';
  private function assertFileRegex (string $ext) {
    $regex = '/^[a-z0-9]{32}\.' . $ext . '$/';
    $object = new RandomFilename($ext);
    $this->assertRegExp($regex, strval($object));
  }
  public function test___construct_itThrowsWhenParameterOneIsNotProvided () {
    $this->expectException('\Throwable');
    new RandomFilename();
  }
  public function test___construct_itThrowsWhenParameterOneIsEmptyString () {
    $this->expectException('\Throwable');
    new RandomFilename('');
  }
  public function test___construct_itThrowsWhenParameterOneHasUppercaseLetter () {
    $this->expectException('\Throwable');
    new RandomFilename('A');
  }
  public function test___construct_itAllowsLowercaseExtension () {
    $this->assertFileRegex('abc');
  }
  public function test___construct_itAllowsNumericExtension () {
    $this->assertFileRegex('123');
  }
  public function test___construct_itAllowsLowercaseNumericExtension () {
    $this->assertFileRegex('1a2b3c');
  }
  public function test_fromString_itThrowsWhenParamOneIsNotProvided () {
    $this->expectException('\Throwable');
    RandomFilename::fromString();
  }
  public function test_fromString_itThrowsWhenParamOneIsEmptyString () {
    $this->expectException('\Throwable');
    RandomFilename::fromString('');
  }
  public function test_fromString_itCreatesWhenValidFilenameIsProvided () {
    $file = 'abcdefabcdefabcdefabcdefabcdef00.qwerty';
    $name = RandomFilename::fromString($file);
    $this->assertEquals($file, strval($name));
  }
  public function test_fromString_itThrowsWhenNameHasTooFewCharacters () {
    $this->expectException('\Throwable');
    (new RandomFilename('jpg'))->withName('0123456789abcdef0123456789abcde.jpg');
  }
  public function test_fromString_itThrowsWhenNameHasTooManyCharacters () {
    $this->expectException('\Throwable');
    RandomFilename::fromString('0123456789abcdef0123456789abcdef0.html');
  }
  public function test_fromString_itThrowsForInvalidNameCharacters () {
    $this->expectException('\Throwable');
    RandomFilename::fromString('ghijklmnopqrstuvwxyz!@#$%^&*()_+.html');
  }
  public function test_fromString_itReturnsInstanceOfRandonFilename () {
    $a = RandomFilename::fromString('0123456789abcdef0123456789abcdef.jpg');
    $this->assertInstanceOf('\RowanSaysWpHelpers\File\RandomFilename', $a);
  }

  public function test_exists_itReturnsTrueWhenFileExists () {
    $a = RandomFilename::fromString('0123456789abcdef0123456789abcdef.png');
    $this->assertTrue($a->exists(self::DIR));
  }
  public function test_exists_itReturnsFalseForNonexistantFile () {
    $a = RandomFilename::fromString('aaaaaaaaaabbbbbbbbbbccccccccccdd.png');
    $this->assertFalse($a->exists(self::DIR));
  }
  public function test_unique_itReturnsSelfWhenFilenameIsAlreadyUnique () {
    $a = RandomFilename::fromString('aaaaaaaaaabbbbbbbbbbccccccccccdd.png');
    $b = $a->unique(self::DIR);
    $this->assertTrue($a === $b);
    $this->assertEquals(strval($a), strval($b));
  }
  public function test_unique_itReturnsNewInstanceWhenFileExists () {
    $a = RandomFilename::fromString('0123456789abcdef0123456789abcdef.png');
    $b = $a->unique(self::DIR);
    $this->assertFalse($a === $b);
    $this->assertNotEquals(strval($a), strval($b));
  }
}
