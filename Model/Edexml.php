<?php
App::uses('AppModel', 'Model');
App::uses('Xml', 'Utility');

/**
 * Edexml Model.
 *
 */
class Edexml extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = false;

/**
 * The (translation) domain to be used for extracted validation messages in models.
 *
 * @var string
 */
	public $validationDomain = 'edexml';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [
		'file' => [
			'uploadError' => [
				'rule' => 'uploadError',
				'message' => 'File upload failed',
				'last' => true
			],
			'extension' => [
				'rule' => ['extension', ['xml']],
				'message' => 'Must be a xml file',
				'last' => true
			],
			'fileSize' => [
				'rule' => ['fileSize', '<=', '1MB'],
				'message' => 'Must be smaller than 1MB',
				'last' => true
			],
			'mimeType' => [
				'rule' => ['mimeType', ['application/xml']],
				'message' => 'Must be of type `xml`'
			],
			'edexml' => [
				'rule' => 'validateEdexml',
				'message' => 'Must be a valid Edexml file'
			]
		]
	];

/**
 * Year group to grade mapping
 *
 * @var array
 */
	protected $_yearGroupToGradeMapping = [
		'B' => 1,			// Baby's leeftijd 0 tot 12 maanden
		'D' => 1,			// Dreumesen leeftijd 1 tot 2 jaar
		0 => 1,				// Peutergroep / Kleutergroep 1
		1 => 1,				// Groep 1 / Kleutergroep 2
		2 => 2,				// Groep 2 / Kleutergroep 3
		3 => 3,				// Groep 3 / Klas 1
		4 => 4,				// Groep 4 / Klas 2
		5 => 5,				// Groep 5 / Klas 3
		6 => 6,				// Groep 6 / Klas 4
		7 => 7,				// Groep 7 / Klas 5
		8 => 8,				// Groep 8 / Klas 6
		11 => 9,			// Voortgezet onderwijs leerjaar 1 / Secundair onderwijs leerjaar 1 (Vlaanderen)
		12 => 10,			// Voortgezet onderwijs leerjaar 2 / Secundair onderwijs leerjaar 2 (Vlaanderen)
		13 => 11,			// Voortgezet onderwijs leerjaar 3 / Secundair onderwijs leerjaar 3 (Vlaanderen)
		14 => 12,			// Voortgezet onderwijs leerjaar 4 / Secundair onderwijs leerjaar 4 (Vlaanderen)
		15 => 13,			// Voortgezet onderwijs leerjaar 5 / Secundair onderwijs leerjaar 5 (Vlaanderen)
		16 => 14,			// Voortgezet onderwijs leerjaar 6 / Secundair onderwijs leerjaar 6 (Vlaanderen)
		'S' => 19,		// S(B)O (speciaal (basis)onderwijs / BuO (buitengewoon kleuter/lager onderwijs, Vlaanderen)
		'V' => null,	// VSO (voortgezet speciaal onderwijs) / BuSO (buitengewoon secundair onderwijs, Vlaanderen)
		'C' => null,	// Combinatiegroep (jaargroep per leerling vastgelegd)
		'N' => 19,		// Niet PO / VO
		'H' => null		// Historisch
	];

/**
 * School Classes
 *
 * @var array
 */
	protected $_schoolClasses = [];

/**
 * An Edexml file validation function to be used in Models.
 *
 * @param array $check Model data for a file upload ('field' => 'value')
 * @return bool Whether or not the Edexml file is valid
 */
	public function validateEdexml($check) {
		$value = array_shift($check);

		return (bool)$this->_parse($value['tmp_name']);
	}

/**
 * Convert XML file to DOMDocument and validate against Edexml XSD
 *
 * @param string $filename Filename of XML file
 * @return bool|DOMDocument A DOMDocument, or false on validation errors
 */
	protected function _parse($filename) {
		if (!file_exists($filename)) {
			return false;
		}

		// Enable user error handling
		libxml_use_internal_errors(true);
		libxml_clear_errors();

		$dom = Xml::build($filename, ['return' => 'domdocument']);
		if (!$dom) {
			return false;
		}

		$schemaFile = CakePlugin::path('Edexml') . 'File' . DS . 'EDEXML-2.1' . DS . 'EDEXML.structuur.xsd';
		if (!$dom->schemaValidate($schemaFile)) {
			foreach (libxml_get_errors() as $error) {
				CakeLog::error($this->_displayXmlError($error), 'debug');
			}

			libxml_clear_errors();

			return false;
		}

		return $dom;
	}

/**
 * Parse XML into array
 *
 * @param string $filename Filename of XML file to parse
 * @return mixed data (array), or false (boolean) on failure
 */
	public function parseToArray($filename) {
		$data = $this->_parse($filename);
		if ($data) {
			$data = Xml::toArray($data);
		}

		return $data;
	}

/**
 * Convert libXMLError to human-readable string
 *
 * @param libXMLError $error A libXMLError error
 * @return string Human-readable string
 */
	protected function _displayXmlError($error) {
		$return = '';
		$return .= str_repeat('-', $error->column) . "^\n";

		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$return .= "Warning $error->code: ";
				break;
			case LIBXML_ERR_ERROR:
				$return .= "Error $error->code: ";
				break;
			case LIBXML_ERR_FATAL:
				$return .= "Fatal Error $error->code: ";
				break;
		}

		$return .= trim($error->message) .
			"\n  Line: $error->line" .
			"\n  Column: $error->column";

		return "$return\n\n--------------------------------------------\n\n";
	}

/**
 * Convert Edexml key to reusable key
 *
 * @param string $key Edexml key
 * @return mixed Edexml key (string), or null (null) when key is not reusable
 */
	protected function _convertKey($key) {
		$result = null;
		if (is_string($key) && substr($key, 0, 1) !== '#') {
			$result = $key;
		}

		return $result;
	}

/**
 * Extract names (first and last name) from user element in extracted Edexml data
 *
 * @param array $user User element from extracted Edexml data
 * @return array Normalized data (first name and last name)
 */
	protected function _convertNames($user) {
		$result = [
			'first_name' => '',
			'last_name' => ''
		];

		if (!empty($user['voorvoegsel'])) {
			$result['last_name'] .= $user['voorvoegsel'] . ' ';
		}
		if (!empty($user['achternaam'])) {
			$result['last_name'] .= $user['achternaam'];
		}

		if (empty($result['first_name']) && !empty($user['roepnaam'])) {
			$result['first_name'] = $user['roepnaam'];
		}
		if (empty($result['first_name']) && !empty($user['voornamen'])) {
			$result['first_name'] = $user['voornamen'];
		}
		if (empty($result['first_name']) && !empty($user['voorletters-1'])) {
			$result['first_name'] = $user['voorletters-1'];
		}

		return $result;
	}

/**
 * Convert school element in extracted Edexml data to normalized data
 *
 * @param array $school School element from extracted Edexml data
 * @return array Normalized data
 */
	protected function _convertSchool($school) {
		$result = [
			'key' => null
		];
		if (!empty($school['schoolkey'])) {
			$result['key'] = $this->_convertKey($school['schoolkey']);
		}

		return $result;
	}

/**
 * Convert school class element in extracted Edexml data to normalized data
 *
 * @param array $schoolClass School class element from extracted Edexml data
 * @return array Normalized data
 */
	protected function _convertSchoolClass($schoolClass) {
		$result = [
			'key' => null,
			'grade' => null
		];
		if (!empty($schoolClass['@key'])) {
			$result['key'] = $this->_convertKey($schoolClass['@key']);
		}
		$result['name'] = $schoolClass['naam'];
		$result['grade'] = $this->_yearGroupToGradeMapping[$schoolClass['jaargroep']];

		return $result;
	}

/**
 * Convert school element in extracted Edexml data to normalized data
 *
 * @param array $schoolClasses School classes element from extracted Edexml data
 * @return array Normalized data
 */
	protected function _convertSchoolClasses($schoolClasses) {
		$result = [];
		if (!empty($schoolClasses)) {
			foreach ($schoolClasses as $schoolClass) {
				$result[$schoolClass['@key']] = $this->_convertSchoolClass($schoolClass);
			}
		}

		return $result;
	}

/**
 * Convert student element in extracted Edexml data to normalized data
 *
 * @param array $student Student element from extracted Edexml data
 * @return array Normalized data
 */
	protected function _convertStudent($student) {
		$result = [
			'key' => null,
			'date_of_birth' => null,
			'gender' => null,
			'grade' => null,
			'SchoolClass' => []
		];

		if (!empty($student['@key'])) {
			$result['key'] = $this->_convertKey($student['@key']);
		}

		if (!empty($student['@eckid'])) {
			$result['eckid'] = $student['@eckid'];
		}

		$result = array_merge($result, $this->_convertNames($student));
		if (!empty($student['geboortedatum'])) {
			if (strtotime($student['geboortedatum'])) {
				$result['date_of_birth'] = $student['geboortedatum'];
			}
		}

		if (!empty($student['geslacht'])) {
			switch ($student['geslacht']) {
				case 1:
					$result['gender'] = 'm';
					break;
				case 2:
					$result['gender'] = 'f';
					break;
			}
		}

		if (!empty($student['groep']['@key'])) {
			$result['SchoolClass'][$student['groep']['@key']] = $this->_schoolClasses[$student['groep']['@key']];
			$result['grade'] = $this->_schoolClasses[$student['groep']['@key']]['grade'];
		}

		if (!empty($student['jaargroep'])) {
			$result['grade'] = $this->_yearGroupToGradeMapping[$student['jaargroep']];
		}

		return $result;
	}

/**
 * Convert teacher element in extracted Edexml data to normalized data
 *
 * @param array $teacher Teacher element from extracted Edexml data
 * @return array Normalized data
 */
	protected function _convertTeacher($teacher) {
		$result = [
			'key' => null,
			'date_of_birth' => null,
			'gender' => null,
			'grade' => null,
			'email_address' => null,
			'SchoolClass' => []
		];

		if (!empty($teacher['@key'])) {
			$result['key'] = $this->_convertKey($teacher['@key']);
		}

		if (!empty($teacher['@eckid'])) {
			$result['eckid'] = $teacher['@eckid'];
		}

		$result = array_merge($result, $this->_convertNames($teacher));

		if (!empty($teacher['emailadres'])) {
			$result['email_address'] = $teacher['emailadres'];
		}

		if (!empty($teacher['groepen']['groep'])) {
			if (!Hash::numeric(array_keys($teacher['groepen']['groep']))) {
				$teacher['groepen']['groep'] = [$teacher['groepen']['groep']];
			}

			foreach ($teacher['groepen']['groep'] as $groep) {
				$result['SchoolClass'][$groep['@key']] = $this->_schoolClasses[$groep['@key']];
			}
		}

		return $result;
	}

/**
 * Convert extracted Edexml data to normalized data
 *
 * @param array $data Extracted Edexml data
 * @return array Normalized data
 */
	public function convert($data) {
		$result = [];

		$result['school'] = $this->_convertSchool($data['EDEX']['school']);

		if (!empty($data['EDEX']['groepen']['groep'])) {
			if (!Hash::numeric(array_keys($data['EDEX']['groepen']['groep']))) {
				$data['EDEX']['groepen']['groep'] = [$data['EDEX']['groepen']['groep']];
			}

			$this->_schoolClasses = $this->_convertSchoolClasses($data['EDEX']['groepen']['groep']);
			$result['SchoolClass'] = $this->_schoolClasses;
		}

		if (!empty($data['EDEX']['leerlingen']['leerling'])) {
			if (!Hash::numeric(array_keys($data['EDEX']['leerlingen']['leerling']))) {
				$data['EDEX']['leerlingen']['leerling'] = [$data['EDEX']['leerlingen']['leerling']];
			}
			foreach ($data['EDEX']['leerlingen']['leerling'] as $i => $student) {
				$result['Student'][$i] = $this->_convertStudent($student);
			}
		}

		if (!empty($data['EDEX']['leerkrachten']['leerkracht'])) {
			if (!Hash::numeric(array_keys($data['EDEX']['leerkrachten']['leerkracht']))) {
				$data['EDEX']['leerkrachten']['leerkracht'] = [$data['EDEX']['leerkrachten']['leerkracht']];
			}

			foreach ($data['EDEX']['leerkrachten']['leerkracht'] as $i => $teacher) {
				$result['Teacher'][$i] = $this->_convertTeacher($teacher);
			}
		}

		return $result;
	}
}
