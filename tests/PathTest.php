<?php
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase {
	public function testNormalize(): void {
		$this->assertSame('/var/www/html', Path::normalize('/var/www//html'));
		$this->assertSame('/home/user/docs', Path::normalize('/home/user/docs/./'));
		$this->assertNull(Path::normalize(null));
	}

	public function testJoin(): void {
		$this->assertSame('/var/www/html', Path::join('/var/www', 'html'));
		$this->assertSame('/var/www/html', Path::join('/var/www/', '/html/'));
		$this->assertNull(Path::join(null, 'html'));
		$this->assertNull(Path::join('/var/www', null));
	}

	public function testBasename(): void {
		$this->assertSame('file.txt', Path::basename('/var/www/file.txt'));
		$this->assertSame('html', Path::basename('/var/www/html/'));
		$this->assertNull(Path::basename(null));
	}

	public function testExtension(): void {
		$this->assertSame('txt', Path::extension('/var/www/file.txt'));
		$this->assertSame('', Path::extension('/var/www/file'));
		$this->assertNull(Path::extension(null));
	}

	public function testDirname(): void {
		$this->assertSame('/var/www', Path::dirname('/var/www/file.txt'));
		$this->assertSame('/var/www/html', Path::dirname('/var/www/html'));
		$this->assertNull(Path::dirname(null));
	}

	public function testIsAbsolute(): void {
		$this->assertTrue(Path::isAbsolute('/var/www/html'));
		$this->assertFalse(Path::isAbsolute('relative/path'));
		$this->assertNull(Path::isAbsolute(null));
	}

	public function testResolve(): void {
		$this->assertSame('/var/www/html', Path::resolve('/var/www', 'html'));
		$this->assertNull(Path::resolve('/var/www', null));
		$this->assertNull(Path::resolve(null, 'html'));
	}

	public function testToUrl(): void {
		$this->assertSame('/var/www/html', Path::toUrl('/var/www/html'));
		$this->assertSame('/var/www/html', Path::toUrl('\\var\\www\\html'));
		$this->assertNull(Path::toUri(null));
	}
}
