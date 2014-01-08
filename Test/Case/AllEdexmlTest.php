<?php
/**
 * All Edexml plugin tests
 */
class AllEdexmlTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Edexml test');

		$path = CakePlugin::path('Edexml') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}