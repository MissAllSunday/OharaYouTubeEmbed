<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OharaYTEmbedTest extends TestCase
{
	public function setUp(): void
	{
		require_once '../Sources/OharaYTEmbed.php';
		require_once '../Themes/default/languages/OharaYTEmbed.english.php';
	}

	public function testOYTE_bbc_add_code()
	{
		$codes = [];
		OYTE_bbc_add_code($codes);

		$this->assertCount(3, $codes);
		$this->assertSame('youtube', $codes[0]['tag']);
		$this->assertSame('yt', $codes[1]['tag']);
		$this->assertSame('vimeo', $codes[2]['tag']);
	}

	public function testOYTE_bbc_add_button()
	{
		$buttons = [];
		OYTE_bbc_add_button($buttons);

		$this->assertCount(2, $buttons);
		$this->assertSame('youtube', $buttons[-1][0]['image']);
		$this->assertSame('vimeo', $buttons[0][0]['image']);
	}

	public function testOYTE_settings()
	{
		$config_vars = [];

		OYTE_settings($config_vars);

		$this->assertCount(7, $config_vars);
		$this->assertSame('Ohara Youtube|Vimeo Embed mod', $config_vars[0]);
		$this->assertSame('', $config_vars[6]);
	}

	#[DataProvider('OYTE_MainProvider')]
	public function testOYTE_Main(string $data, string $expectedResult)
	{
		$result = OYTE_Main($data);

		$this->assertSame($expectedResult, $result);
	}

	public static function OYTE_MainProvider(): array
	{
		$expectedResult = '<div class="oharaEmbed youtube" id="oh_4ShOpJPHRxA"><noscript><a href="https://youtube.com/watch?v=4ShOpJPHRxA">https://youtube.com/watch?v=4ShOpJPHRxA</a></noscript></div>';
		return [
			'no data' => [
				'data' => '',
				'expectedResult' => 'Not a valid youtube URL'
			],
			'youtube ID' => [
				'data' => '4ShOpJPHRxA',
				'expectedResult' => $expectedResult
			],
			'full url' => [
				'data' => 'https://www.youtube.com/watch?v=4ShOpJPHRxA',
				'expectedResult' => $expectedResult
			],
			'schemaless' => [
				'data' => '//www.youtube.com/watch?v=4ShOpJPHRxA',
				'expectedResult' => $expectedResult
			],
			'domain' => [
				'data' => 'youtube.com/watch?v=4ShOpJPHRxA',
				'expectedResult' => $expectedResult
			],
		];
	}

	#[DataProvider('OYTE_VimeoProvider')]
	public function testOYTE_Vimeo(string $data, string $expectedResult)
	{
		$result = OYTE_Vimeo($data);

		$this->assertSame($expectedResult, $result);
	}

	public static function OYTE_VimeoProvider(): array
	{
		$expectedResult = '<div class="oharaEmbed vimeo"><iframe src="https://player.vimeo.com/video/19258789?dnt=1&amp;app_id=122963" width="480" height="272" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" title="Lykke Li - I Follow Rivers (Director: Tarik Saleh)"></iframe></div>';
		return [
			'no data' => [
				'data' => '',
				'expectedResult' => 'Not a valid vimeo URL'
			],
			'full url' => [
				'data' => 'https://vimeo.com/19258789',
				'expectedResult' => $expectedResult
			],
			'schemaless' => [
				'data' => '//vimeo.com/19258789',
				'expectedResult' => $expectedResult
			],
		];
	}

	public function testOYTE_Preparse(): void
	{
		global $context;

		OYTE_css();

		$this->assertStringContainsString('max-width: 480px', $context['html_headers']);
		$this->assertStringContainsString('max-height: 270px', $context['html_headers']);
		$this->assertStringContainsString('padding-bottom: 270px', $context['html_headers']);
		$this->assertStringContainsString('@media screen and (min-width: 768px)', $context['html_headers']);
	}
}
