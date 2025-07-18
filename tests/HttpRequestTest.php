<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HttpRequest::class)]
class HttpRequestTest extends TestCase {
	public static function tearDownAfterClass(): void {
		$cookiejar =  dirname(__FILE__) . "/cookiejar";
		if (file_exists($cookiejar)) unlink($cookiejar);
	}

	/**
	 * Helper to skip test if httpbin.dev times out (CURLE_OPERATION_TIMEDOUT = 28)
	 */
	protected function skipOnTimeout(\Throwable $e) {
		if (method_exists($e, 'getCode') && $e->getCode() === CURLE_OPERATION_TIMEDOUT) {
			$this->markTestSkipped('Skipped due to httpbin.dev timeout');
		}
		throw $e;
	}

	public function testWithMethodUrlOnly() {
		try {
			$data = \Http\Request::with("https://httpbin.dev/post")
				->post(["data" => "foo"])
				->getResponse()
				->asObject();

			$this->assertEquals($data->form->data[0], "foo");
		} catch (\Throwable $e) {
			$this->skipOnTimeout($e);
		}
	}

	public function testWithMethodTwoParams() {
		try {
			$data = \Http\Request::with("POST", "https://httpbin.dev/post")
				->send(["data" => "foo"])
				->getResponse()
				->asObject();

			$this->assertEquals("foo", $data->form->data[0]);
		} catch (\Throwable $e) {
			$this->skipOnTimeout($e);
		}
	}

	public function testWithMethodThrowsException() {
		$this->expectException(\ValueError::class);
		\Http\Request::with("https://httpbin.dev/post", "https://httpbin.dev/post");
	}

	public function testGetHttpCode() {
		$httpCode = (new \Http\Request("https://httpbin.dev/status/301"))
			->setOption(CURLOPT_FOLLOWLOCATION, false)
			->head()
			->getResponse()
			->getHttpCode();

		$this->assertEquals(301, $httpCode);
	}

	/**
	 * Tests GET request works as expected
	 */
	public function testGetRequest() {
		$iRequest = (new Http\Request("https://httpbin.dev/get"))->get();
		$this->assertEquals(200, $iRequest->getResponse()->getHttpCode());
	}

	/**
	 * Tests POST request works as expected
	 */
	public function testPostRequest() {
		$iRequest = (new Http\Request("https://httpbin.dev/post"))->post();
		$this->assertEquals(200, $iRequest->getResponse()->getHttpCode());
	}

	/**
	 * Tests PATCH request works as expected
	 */
	public function testPatchRequest() {
		$iRequest = (new Http\Request("https://httpbin.dev/patch"))->patch();
		$this->assertEquals(200, $iRequest->getResponse()->getHttpCode());
	}

	/**
	 * Tests PUT request works as expected
	 */
	public function testPutRequest() {
		$iRequest = (new Http\Request("https://httpbin.dev/put"))->put();
		$this->assertEquals(200, $iRequest->getResponse()->getHttpCode());
	}

	/**
	 * Now test that we can actually put stuff.
	 */
	public function testPutData() {
		$iRequest = new Http\Request("https://httpbin.dev/put");
		$response = $iRequest->put(http_build_query(["foo" => "bar"]))->getResponse()->asObject();

		$this->assertTrue(isset($response->form->foo));
	}

	/**
	 * Tests DELETE request works as expected
	 */
	public function testDeleteRequest() {
		$iRequest = (new Http\Request("https://httpbin.dev/delete"))->delete();
		$this->assertEquals(200, $iRequest->getResponse()->getHttpCode());
	}

	/**
	 * Test that requests who does not return a successful response code fails with an exception
	 */
	public function testFailedRequest() {
		$this->expectException(\Http\HttpError::class);
		$this->expectExceptionCode(418);

		$iRequest = new \Http\Request("https://httpbin.dev/status/418");
		$iResponse = $iRequest->get();

		$this->assertFalse($iResponse->isSuccess());
	}

	/**
	 * Tests cookies can be set and parsed accordingly.
	 */
	public function testCanRecieveCookies() {
		$cookiejar = dirname(__FILE__) . "/cookiejar";

		$iRequest = new \Http\Request("https://httpbin.dev/cookies/set?name1=value1&value2=value2&value3=value3");
		$iRequest->setCookiejar($cookiejar);
		$iResponse = $iRequest->get()->getResponse();

		$responseCookies = $iResponse->getHeaders("Set-Cookie");

		$expectedCookies = [
			"name1=value1; HttpOnly",
			"value2=value2; HttpOnly",
			"value3=value3; HttpOnly"
		];

		// Flatten cookies array if needed
		$responseCookies = is_array($responseCookies) ? array_values($responseCookies) : [$responseCookies];

		// Assert that all expected cookies are present and no excess
		sort($responseCookies);
		sort($expectedCookies);
		$this->assertSame($expectedCookies, $responseCookies);
	}

	/**
	 * Quick test that we get a useful object from an XML response
	 */
	public function testParseXmlPositive() {
		$iRequest = new Http\Request("https://httpbin.dev/xml");
		$xml = $iRequest->get()->getResponse()->asXml();
		$this->assertInstanceOf("SimpleXMLElement", $xml);
	}

	/**
	 * Test GET request parameters are send as expected
	 */
	public function testGetRequestParams() {
		$iRequest = (new Http\Request("https://httpbin.dev/get?john=doe"))->get();
		$response = json_decode($iRequest->getResponse());
		$this->assertNotEmpty($response->args);
		$this->assertEquals("doe", $response->args->john[0]);

		$params = ["meal" => "pizza", "toppings" => ["cheese", "ham", "pineapple", "bacon"]];
		$iRequest2 = (new Http\Request("https://httpbin.dev/get"))->get($params);
		$response2 = json_decode($iRequest2->getResponse(), true);
		$this->assertNotEmpty($response2["args"]);

		/*
		 * I check the keys this way because the returned keys are in this format:
		 * Array (
     	 * 	'meal' => 'pizza'
		 * 	'toppings[0]' => 'cheese'
		 * 	'toppings[1]' => 'ham'
		 * 	'toppings[2]' => 'pineapple'
		 * 	'toppings[3]' => 'bacon'
		 * 	'toppings' => Array (...)
 		 * )
 		 * 
		 * When what I really wanted was this:
		 * Array (
		 *	    [meal] => pizza
		 *	    [toppings] => Array (
		 *	    	[0] => cheese
		 *	    	[1] => ham
		 *	    	[2] => pineapple
		 *	    	[3] => bacon
		 *	    )
		 *	)
		 */
		$args = $response2["args"];
		foreach ($params["toppings"] as $key => $value) {
			$key2check = "toppings[" . $key . ']';
			if (!isset($args[$key2check][0]) || $args[$key2check][0] != $value) {
				$this->fail("A response key/value was not properly returned.");
			}
		}
	}

	/**
	 * Test set header
	 */
	public function testSetHeader() {
		$header = 'Content-Type: application/json';
		$iRequest = new Http\Request();
		$iRequest->setHeader($header);

		$this->assertContains($header, $iRequest->headers);
	}

	/**
	 * Test authorization
	 */
	public function testSetAuthorization() {
		$username = 'user';
		$password = 'pass';
		$authType = CURLAUTH_BASIC;

		$iRequest = new \Http\Request();
		$iRequest->setAuthorization($username, $password, $authType);

		$this->assertEquals($authType, $iRequest->getOption(CURLOPT_HTTPAUTH));
		$this->assertEquals("$username:$password", $iRequest->getOption(CURLOPT_USERPWD));
	}

	/**
	 * Test we are able to send a header
	 */
	public function testHeaders() {
		$ourHeaders = [
			"X-Firstname" => "John",
			"X-Lastname" => "Doe",
		];

		$iRequest = new Http\Request("https://httpbin.dev/headers");
		foreach ($ourHeaders as $key => $value) {
			$iRequest->setHeader($key . ": " . $value);
		}

		$response = json_decode($iRequest->get()->getResponse(), true);

		$this->assertNotEmpty($response["headers"]);

		foreach($ourHeaders as $key => $value) {
			$this->assertEquals($value, $response["headers"][$key][0]);
		}
		/*
		$responseHeadersInCommonWtihOurHeaders = array_intersect($response["headers"], $ourHeaders);

		// This should assert that we got all our headers back.
		$this->assertEquals($ourHeaders, $responseHeadersInCommonWtihOurHeaders);

		*/
	}

	/**
	 * Test user agent spoofing
	 */
	public function testUserAgentSpoofing() {
		$agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201";

		$iRequest = new Http\Request("https://httpbin.dev/user-agent");
		$iRequest->setOption(CURLOPT_USERAGENT, $agent);
		$response = $iRequest->get()->getResponse();
		$response = json_decode($response, true);

		$this->assertEquals($agent, $response["user-agent"]);
	}

	/**
	 * Test some poor developer wont end up in a black hole somewhere.
	 */
	public function testMaxRedirs() {
		$numRedirs = 10;

		$this->expectException(\Http\ConnectionError::class);
		$this->expectExceptionCode(CURLE_TOO_MANY_REDIRECTS);

		$iRequest = new Http\Request("https://httpbin.dev/redirect/" . $numRedirs);
		$iRequest->setOption(CURLOPT_MAXREDIRS, 5);
		$iRequest->get();
	}

	/**
	 * Oh god, i'm not done yet, let's find out if we're able to do a basic HTTP authentication
	 */
	public function testBasicAuth() {
		$u = "john";
		$p = "doe";

		$iRequest = new Http\Request("https://httpbin.dev/basic-auth/" . $u . '/' . $p);
		$iRequest->setAuthorization($u, $p);
		$iResponse = $iRequest->get()->getResponse();

		$this->assertEquals(200, $iResponse->getHttpCode());

		$iResponse = $iResponse->asObject();
		$this->assertTrue($iResponse->authorized);
	}

	/**
	 * I cannot guarantee that this will ever work...
	 */
	public function testDigestAuth() {
		$qop = "auth";
		$u = "john";
		$p = "wayne";

		$url = "https://httpbin.dev/digest-auth/" . $qop . "/" . $u . "/" . $p . "/MD5";

		$iRequest = new \Http\Request($url);
		$iRequest->setVerbose();
		$iRequest->setAuthorization("john", "wayne");
		$iResponse = $iRequest->get()->getResponse();

		$this->assertTrue($iResponse->isSuccess());
	}

	/**
	 * Test posting a file.
	 */
	public function testPostFileRequest() {
		// Create a temporary file, for the purpose of this test.
		// Could be any file path
		$time = time();
		$tmpfile =  tempnam("/tmp", $time);
		$tmpfileHandle = fopen($tmpfile, "r+");
		fwrite($tmpfileHandle, $time);

		// As of PHP 5.5 CURLFile objects should be used instead for POSTing files.
		$cfile = new CURLFile($tmpfile, mime_content_type($tmpfile), 'tmpfile.txt');
		$data = array('tmpfile' => $cfile);

		// Let's now do the more fancy part
		$iRequest = new Http\Request("https://httpbin.org/post");
		$iRequest->setHeader("Content-Type", "multipart/form-data");
		$res = $iRequest->post($data)->getResponse();
		// Time to validate the data we got.
		$res = json_decode($res);

		$this->assertNotEmpty($res->files);
		$this->assertEquals($time, $res->files->tmpfile);
	}
}
