<?php

use PHPUnit\Framework\TestCase;
use Core\Template;
use Core\Assets;
use Core\TemplateFactory;

final class TemplateTest extends TestCase {
	private $template;
	private $mockAssets;
	private $mockConfiguration;

	protected function setUp(): void {
		// Set $_SERVER values for URL testing
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['PHP_SELF'] = '/index.php';

		// Mock dependencies
		$this->mockAssets = $this->createMock(Assets::class);
		$this->mockConfiguration = $this->createMock(\Configuration::class);

		// Mock Registry::getConfiguration() static method
		$registryMock = $this->getMockBuilder(\Registry::class)
			->disableOriginalConstructor()
			->onlyMethods(['getConfiguration'])
			->getMock();

		$registryMock->method('getConfiguration')->willReturn($this->mockConfiguration);

		// Instantiate Template using TemplateFactory for consistency
		$this->template = \TemplateFactory::new();
	}

	public function testConstructorInitializesTemplateCorrectly() {
		$this->mockConfiguration->method('get')->willReturnMap([
			['theme', 'default'],
			['version', ['version' => '1.0', 'expose' => true]],
			['assets', (object)[
				'js' => (object)['footer' => ['assets/js/jquery-3.5.1.min.js']],
				'css' => (object)['header' => ['assets/css/screen.css', 'assets/css/responsive.css']]
			]]
		]);

		$this->assertEquals('default', $this->template->getName());
		$this->assertInstanceOf(Assets::class, $this->template->getAssets());
	}

	public function testGetConfigurationLoadsCorrectConfiguration() {
		$configFilePath = STORAGE . "/config/default.theme.jsonc";
		$this->mockConfiguration->expects($this->once())
			->method('__construct')
			->with($this->equalTo($configFilePath));

		$config = $this->template->getConfiguration();
		$this->assertInstanceOf(\Configuration::class, $config);
	}

	public function testGetPathGeneratesCorrectTemplatePath() {
		$this->mockConfiguration->method('get')->willReturn('default');
		$templatePath = APP_PATH . "/Templates/default/partials/sidebar.tpl.php";

		$result = $this->template->getPath('partials/sidebar');
		$this->assertEquals($templatePath, $result);
	}

	public function testGetDirectoryUriGeneratesCorrectUri() {
		$baseUrl = 'http://example.com';
		$this->mockConfiguration->method('get')->willReturnMap([
			['version', ['version' => '1.0', 'expose' => true]]
		]);

		// Mock \Url::getBaseurl() by setting $_SERVER variables as done in setUp()
		$result = $this->template->getDirectoryUri('assets/js/app.js');
		$expectedUri = "{$baseUrl}/Templates/default/assets/js/app.js?v=1.0";
		$this->assertEquals($expectedUri, $result);
	}

	public function testMaybeAddVersionNumberAddsVersionWhenConditionsMet() {
		$url = 'http://example.com/Templates/default/assets/style.css';
		$this->mockConfiguration->method('get')->willReturnMap([
			['version', ['version' => '1.0', 'expose' => true]]
		]);

		$result = $this->template->getDirectoryUri('assets/style.css');
		$this->assertStringContainsString('?v=1.0', $result);
	}

	public function testRegisterAssetsAddsCorrectAssetsToAssetsClass() {
		$assetsConfig = (object)[
			'js' => (object)['footer' => ['assets/js/jquery-3.5.1.min.js']],
			'css' => (object)['header' => ['assets/css/screen.css', 'assets/css/responsive.css']]
		];

		$this->mockAssets->expects($this->once())
			->method('addJavascript')
			->with($this->stringContains('jquery-3.5.1.min.js'));

		$this->mockAssets->expects($this->exactly(2))
			->method('addStylesheet')
			->withConsecutive(
				[$this->stringContains('screen.css'), 'header'],
				[$this->stringContains('responsive.css'), 'header']
			);

		$reflection = new \ReflectionClass($this->template);
		$method = $reflection->getMethod('registerAssets');
		$method->setAccessible(true);
		$method->invoke($this->template, $assetsConfig);
	}
}
