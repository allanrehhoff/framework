<?php
	class EscapeTest extends \PHPUnit\Framework\TestCase {
		public function testEscapeString() {
			$string = "<script></script>";
			$result = \Escape::string($string);

			$this->assertEquals("&lt;script&gt;&lt;/script&gt;", $result);
		}

		public function testEscapeArray() {
			$array = ["<script></script>"];
			$result = \Escape::array($array);

			$this->assertSame(["&lt;script&gt;&lt;/script&gt;"], $result);
		}

		public function testEscapeObject() {
			$object = (object)["key" => "<script></script>"];
			$result = \Escape::object($object);

			$this->assertEquals("&lt;script&gt;&lt;/script&gt;", $result->key);
		}

		public function testEscapeArrayRecursive() {
			$array = ["<script></script>", ["<style></style>"]];
			$result = \Escape::array($array);

			$this->assertSame(["&lt;script&gt;&lt;/script&gt;", ["&lt;style&gt;&lt;/style&gt;"]], $result);
		}

		public function testEscapeObjectRecursive() {
			$object = (object)["key" => (object)["value" => "<script></script>"]];
			$result = \Escape::object($object);

			$this->assertEquals("&lt;script&gt;&lt;/script&gt;", $result->key->value);
		}
	}