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
	public $fixtures = [];

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
		$this->assertEquals('1', $this->Edexml->convertKey('1'));
		$this->assertEquals('key', $this->Edexml->convertKey('key'));
	}

/**
 * testConvertNames method
 *
 * @return void
 */
	public function testConvertNames() {
		$data = [
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam'
		];
		$expected = [
			'last_name' => $data['achternaam'],
			'first_name' => $data['roepnaam'],
		];
		$this->assertEquals($expected, $this->Edexml->convertNames($data));

		$data = [
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		];
		$expected = [
			'last_name' => $data['voorvoegsel'] . ' ' . $data['achternaam'],
			'first_name' => $data['roepnaam'],
		];
		$this->assertEquals($expected, $this->Edexml->convertNames($data));
	}

/**
 * testConvertSchool method
 *
 * @return void
 */
	public function testConvertSchool() {
		$data = [
			'schoolkey' => 'schoolkey',
		];
		$expected = [
			'key' => $data['schoolkey']
		];
		$this->assertEquals($expected, $this->Edexml->convertSchool($data));

		$data = [
			'schoolkey' => '#001',
		];
		$expected = [
			'key' => null
		];
		$this->assertEquals($expected, $this->Edexml->convertSchool($data));

		$data = [];
		$expected = [
			'key' => null
		];
		$this->assertEquals($expected, $this->Edexml->convertSchool($data));
	}

/**
 * testConvertSchoolClass method
 *
 * @return void
 */
	public function testConvertSchoolClass() {
		$data = [
			'@key' => 'key',
			'naam' => 'naam',
			'jaargroep' => '0'
		];
		$expected = [
			'key' => $data['@key'],
			'name' => 'naam',
			'grade' => '1'
		];
		$this->assertEquals($expected, $this->Edexml->convertSchoolClass($data));
	}

/**
 * testConvertSchoolClasses method
 *
 * @return void
 */
	public function testConvertSchoolClasses() {
		$data = [
			0 => [
				'@key' => 'key',
				'naam' => 'naam',
				'jaargroep' => '0'
			]
		];
		$expected = [
			'key' => [
				'key' => 'key',
				'name' => 'naam',
				'grade' => '1'
			]
		];
		$this->assertEquals($expected, $this->Edexml->convertSchoolClasses($data));
	}

/**
 * testConvertStudent method
 *
 * @return void
 */
	public function testConvertStudent() {
		$data = [
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geboortedatum' => '2005-07-19',
			'geslacht' => '0'
		];
		$expected = [
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => '2005-07-19',
			'gender' => null,
			'grade' => null,
			'SchoolClass' => []
		];
		$this->assertEquals($expected, $this->Edexml->convertStudent($data));

		$data = [
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geboortedatum' => '2005-07-19',
			'geslacht' => '0',
			'groep' => [
				'@key' => 'key'
			]
		];
		$expected = [
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => '2005-07-19',
			'gender' => null,
			'grade' => 10,
			'SchoolClass' => [
				'key' => [
					'grade' => 10
				]
			]
		];
		$schoolClasses = [
			'key' => [
				'grade' => 10
			]
		];
		$this->Edexml->schoolClasses($schoolClasses);
		$this->assertEquals($expected, $this->Edexml->convertStudent($data));

		$data = [
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '1'
		];
		$expected = [
			'key' => null,
			'first_name' => 'Roepnaam',
			'last_name' => 'Achternaam',
			'gender' => 'm',
			'date_of_birth' => null,
			'grade' => null,
			'SchoolClass' => []
		];
		$this->assertEquals($expected, $this->Edexml->convertStudent($data));

		$data = [
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '2'
		];
		$result = $this->Edexml->convertStudent($data);
		$this->assertEquals('Achternaam', $result['last_name']);
		$this->assertEquals('f', $result['gender']);

		$data = [
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
			'geslacht' => '9'
		];
		$result = $this->Edexml->convertStudent($data);
		$this->assertEquals('Achternaam', $result['last_name']);
		$this->assertNull($result['gender']);

		$data = [
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		];
		$result = $this->Edexml->convertStudent($data);
		$this->assertEquals('Voorvoegsel Achternaam', $result['last_name']);
		$this->assertEquals('Roepnaam', $result['first_name']);

		// Check dummy key for example #001
		// TODO: what to do when there are no identifiers?
		$data = [
			'@key' => '#001',
			'achternaam' => 'Achternaam',
			'voorvoegsel' => 'Voorvoegsel',
			'roepnaam' => 'Roepnaam'
		];
		$result = $this->Edexml->convertStudent($data);
		$this->assertNull($result['key']);
	}

/**
 * testConvertTeacher method
 *
 * @return void
 */
	public function testConvertTeacher() {
		$data = [
			'@key' => 'key',
			'achternaam' => 'Achternaam',
			'roepnaam' => 'Roepnaam',
		];
		$expected = [
			'key' => 'key',
			'last_name' => 'Achternaam',
			'first_name' => 'Roepnaam',
			'date_of_birth' => null,
			'gender' => null,
			'grade' => null,
			'SchoolClass' => []
		];
		$this->assertEquals($expected, $this->Edexml->convertTeacher($data));
	}

/**
 * testParseToArray method
 *
 * @return void
 */
	public function testParseToArray() {
		$this->assertFalse($this->Edexml->parseToArray(CakePlugin::path('Edexml') . 'Test' . DS . 'File' . DS . 'EDEXML-1.0.3' . DS . 'sample-invalid.xml'));

		$data = $this->Edexml->parseToArray(CakePlugin::path('Edexml') . 'Test' . DS . 'File' . DS . 'EDEXML-1.0.3' . DS . 'sample.xml');
		$this->assertTrue((bool)$data);

		$result = $this->Edexml->convert($data);
		$this->assertTrue((bool)$result);

		$this->assertEquals(count(count($data['EDEX']['leerlingen']['leerling']), $result['Student']));
		$this->assertEquals(count($data['EDEX']['groepen']['groep']), count($result['SchoolClass']));
		$this->assertEquals(count($data['EDEX']['leerkrachten']['leerkracht']), count($result['Teacher']));
	}

/**
 * testConvertSingleItemData method
 *
 * @return void
 */
	public function testConvertSingleItemData() {
		$data = [
			'EDEX' => [
				'school' => [
					'schooljaar' => '2012-2013',
					'brincode' => '99XX',
					'dependancecode' => '99',
					'schoolkey' => '99999',
					'aanmaakdatum' => '2013-03-27T12:43:06',
					'auteur' => 'Cito LeerlingVolgSysteem versie 4.6',
					'xsdversie' => '1.03'
				],
				'groepen' => [
					'groep' => [
						'@key' => '49',
						'naam' => '1A',
						'jaargroep' => '1',
						'mutatiedatum' => '2005-08-29T22:19:18'
					]
				],
				'leerlingen' => [
					'leerling' => [
						'@key' => '3580',
						'achternaam' => 'Achternaam',
						'voorvoegsel' => 'v. d.',
						'roepnaam' => 'Roepnaam',
						'geboortedatum' => '2009-08-07',
						'geslacht' => '2',
						'jaargroep' => '4',
						'etniciteit' => '9',
						'land' => '?',
						'land_vader' => '?',
						'land_moeder' => '?',
						'gewicht_nieuw' => '?',
						'gewicht' => '?',
						'mutatiedatum' => '2011-01-24T15:33:29'
					]
				],
				'leerkrachten' => [
					'leerkracht' => [
						'@key' => '75',
						'achternaam' => 'Achternaam',
						'roepnaam' => 'Roepnaam',
						'groepen' => [
							'groep' => [
								'@key' => '49'
							]
						],
						'mutatiedatum' => '2004-02-07T14:03:18'
					]
				],
				'@xsi:noNamespaceSchemaLocation' => 'EDEXML.structuur.xsd'
			]
		];

		$result = $this->Edexml->convert($data);
		$this->assertTrue(Hash::numeric(array_keys($result['SchoolClass'])));
		$this->assertTrue(Hash::numeric(array_keys($result['Student'])));
		$this->assertTrue(Hash::numeric(array_keys($result['Teacher'])));
	}

}
