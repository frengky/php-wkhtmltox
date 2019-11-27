<?php

namespace Frengky\WkHtml\Tests;

use PHPUnit\Framework\TestCase;

use Frengky\WkHtml\PDF;
use Frengky\WkHtml\Image;

final class WkHtmlTests extends TestCase {

    public function testImageSuccess() {
        $contents = 'Hello, <strong>World</strong>!';
        $result = Image::fromHtml($contents)
                        ->set('--format', 'jpg')
                        ->make();

        if (! empty($result)) {
            file_put_contents('tests/samples/test-success.jpg', $result);
        }

        $this->assertTrue(!empty($result));
    }

    public function testPDFSuccess() {
        $contents = 'Hello, <strong>World</strong>!';
        $return = PDF::fromHtml($contents)
                    ->set('--page-size', 'A4')
                    ->set('--orientation', 'Portrait')
                    ->saveAs('tests/samples/test-success.pdf');

        $this->assertTrue(!empty($return));
    }

    public function testPDFFailed() {
        $contents = 'Hello, <strong>World</strong>!';
        $return = PDF::fromHtml($contents)
                    ->set('--xxx', 'x')
                    ->saveAs('tests/samples/test-failed.pdf');
                    
        $this->assertFalse($return);
    }

    protected function tearDown(): void {
        if (file_exists('tests/samples/test-success.pdf')) {
            unlink('tests/samples/test-success.pdf');
        }
    }
}