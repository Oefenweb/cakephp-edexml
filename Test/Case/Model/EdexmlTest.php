<?php
App::uses('Edexml', 'Edexml.Model');

class TestEdexml extends Edexml {

	public function schoolClasses($schoolClasses) {
		$this->_schoolClasses = $schoolClasses;
	}

/**
 * Test double of `parent::_convertKey`.
 *
 */
	public function convertKey($key) {
		return $this->_convertKey($key);
	}

/**
 * Test double of `parent::_convertNames`.
 *
 */
	public function convertNames($user) {
		return $this->_convertNames($user);
	}

/**
 * Test double of `parent::_convertSchool`.
 *
 */
	public function convertSchool($school) {
		return $this->_convertSchool($school);
	}

/**
 * Test double of `parent::_convertSchoolClass`.
 *
 */
	public function convertSchoolClass($schoolClass) {
		return $this->_convertSchoolClass($schoolClass);
	}

/**
 * Test double of `parent::_convertSchoolClasses`.
 *
 */
	public function convertSchoolClasses($schoolClasses) {
		return $this->_convertSchoolClasses($schoolClasses);
	}

/**
 * Test double of `parent::_convertStudent`.
 *
 */
	public function convertStudent($data) {
		return $this->_convertStudent($data);
	}

/**
 * Test double of `parent::_convertTeacher`.
 *
 */
	public function convertTeacher($data) {
		return $this->_convertTeacher($data);
	}

/**
 * Test double of `parent::_parse`.
 *
 */
	public function parse($filename) {
		return $this->_parse($filename);
	}

}

/**
 * Edexml Test
 *
 */
class EdexmlTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Edexml = ClassRegistry::init('TestEdexml');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Edexml);

		parent::tearDown();
	}

/**
 * testConvertKey method
 *
 * @return void
 */
	public function testConvertKey() {
		$this->assertNull($this->Edexml->convertKey('#001'));
		$this->assertEqual($this->Edexml->convertKey('1'), '1');
		$this->assertEqual($this->Edexml->convertKey('key'), 'key');
	}

/**
 * testConvertNames method
 *
 * @return void
 */
	public function testConvertNames() {
		$data = array(
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam'
		);
		$expected = array(
			'last_name' => $data['achternaam'],
			'first_name' => $data['roepnaam'],
		);
		$this->assertEqual($this->Edexml->convertNames($data), $expected);

		$data = array(
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		);
		$expected = array(
			'last_name' => $data['voorvoegsel'] . ' ' . $data['achternaam'],
			'first_name' => $data['roepnaam'],
		);
		$this->assertEqual($this->Edexml->convertNames($data), $expected);
	}

/**
 * testConvertSchool method
 *
 * @return void
 */
	public function testConvertSchool() {
		$data = array(
			'schoolkey' => 'schoolkey',
		);
		$expected = array(
			'key' => $data['schoolkey']
		);
		$this->assertEqual($this->Edexml->convertSchool($data), $expected);

		$data = array(
			'schoolkey' => '#001',
		);
		$expected = array(
			'key' => null
		);
		$this->assertEqual($this->Edexml->convertSchool($data), $expected);

		$data = array();
		$expected = array(
			'key' => null
		);
		$this->assertEqual($this->Edexml->convertSchool($data), $expected);
	}

/**
 * testConvertSchoolClass method
 *
 * @return void
 */
	public function testConvertSchoolClass() {
		$data = array(
			'@key' => 'key',
			'naam' => 'naam',
			'jaargroep' => '0'
		);
		$expected = array(
			'key' => $data['@key'],
			'name' => 'naam',
			'grade' => '1'
		);
		$this->assertEqual($this->Edexml->convertSchoolClass($data), $expected);
	}

/**
 * testConvertSchoolClasses method
 *
 * @return void
 */
	public function testConvertSchoolClasses() {
		$data = array(
			0 => array(
				'@key' => 'key',
				'naam' => 'naam',
				'jaargroep' => '0'
			)
		);
		$expected = array(
			'key' => array(
				'key' => 'key',
				'name' => 'naam',
				'grade' => '1'
			)
		);
		$this->assertEqual($this->Edexml->convertSchoolClasses($data), $expected);
	}

/**
 * testConvertStudent method
 *
 * @return void
 */
	public function testConvertStudent() {
		$data = array(
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geboortedatum' => '2005-07-19',
			'geslacht' => '0'
		);
		$expected = array(
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => '2005-07-19',
			'gender' => null,
			'grade' => null,
			'SchoolClass' => array()
		);
		$this->assertEqual($this->Edexml->convertStudent($data), $expected);

		$data = array(
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geboortedatum' => '2005-07-19',
			'geslacht' => '0',
			'groep' => array(
				'@key' => 'key'
			)
		);
		$expected = array(
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => '2005-07-19',
			'gender' => null,
			'grade' => 10,
			'SchoolClass' => array(
				0 => array(
					'grade' => 10
				)
			)
		);
		$schoolClasses = array(
			'key' => array(
				'grade' => 10
			)
		);
		$this->Edexml->schoolClasses($schoolClasses);
		$this->assertEqual($this->Edexml->convertStudent($data), $expected);

		$data = array(
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '1'
		);
		$expected = array(
			'key' => null,
			'first_name' => 'Roepnaam',
			'last_name' => 'Achternaam',
			'gender' => 'm',
			'date_of_birth' => null,
			'grade' => null,
			'SchoolClass' => array()
		);
		$this->assertEqual($this->Edexml->convertStudent($data), $expected);

		$data = array(
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '2'
		);
		$result = $this->Edexml->convertStudent($data);
		$this->assertEqual($result['last_name'], 'Achternaam');
		$this->assertEqual($result['gender'], 'f');

		$data = array(
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '9'
		);
		$result = $this->Edexml->convertStudent($data);
		$this->assertEqual($result['last_name'], 'Achternaam');
		$this->assertNull($result['gender']);

		$data = array(
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		);
		$result = $this->Edexml->convertStudent($data);
		$this->assertEqual($result['last_name'], 'Voorvoegsel Achternaam');
		$this->assertEqual($result['first_name'], 'Roepnaam');

		// Check dummy key for example #001
		// TODO: what to do when there are no identifiers?
		$data = array(
			'@key' => '#001',
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		);
		$result = $this->Edexml->convertStudent($data);
		$this->assertNull($result['key']);
	}

/**
 * testConvertTeacher method
 *
 * @return void
 */
	public function testConvertTeacher() {
		$data = array(
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
		);
		$expected = array(
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => null,
			'gender' => null,
			'grade' => null,
			'SchoolClass' => array()
		);
		$this->assertEqual($this->Edexml->convertTeacher($data), $expected);
	}

/**
 * testParseToArray method
 *
 * @return void
 */
	public function testParseToArray() {
		$this->assertFalse($this->Edexml->parseToArray(App::pluginPath('Edexml') . 'Test' . DS . 'File' . DS . 'sample-invalid.xml'));

		$data = $this->Edexml->parseToArray(App::pluginPath('Edexml') . 'Test' . DS . 'File' . DS . 'sample.xml');
		$this->assertTrue((boolean)$data);

		$result = $this->Edexml->convert($data);
		$this->assertTrue((boolean)$result);

		$this->assertEqual(count($result['Student']), count($data['EDEX']['leerlingen']['leerling']));
		$this->assertEqual(count($result['SchoolClass']), count($data['EDEX']['groepen']['groep']));
		$this->assertEqual(count($result['Teacher']), count($data['EDEX']['leerkrachten']['leerkracht']));
	}
}
