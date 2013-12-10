<?php
use Fol\Config;

class ConfigTest extends PHPUnit_Framework_TestCase {
	public function testConfig () {
		$config = new Config(__DIR__.'/configtest');

		//Load configuration
		$c = $config->get('demo');

		$this->assertEquals($c['value1'], 1);
		$this->assertEquals($c['value2'], 'two');

		//Modify configuration
		$config->set('demo', [
			'value1' => 'one',
			'value3' => 3
		]);

		$c = $config->get('demo');

		$this->assertEquals($c['value1'], 'one');
		$this->assertEquals($c['value2'], null);
		$this->assertEquals($c['value3'], 3);

		//Delete and reload again
		$config->delete('demo');
		$c = $config->get('demo');

		$this->assertEquals($c['value1'], 1);
		$this->assertEquals($c['value2'], 'two');
	}
}
