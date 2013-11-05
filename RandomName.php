<?php
/**
 * RandomName can generate a list or singular name based on certain parameters specified by the constructor method
 * 
 * @author Thomas Norberg <tnorberg@thomasnorberg.com>
 * @package MilTech
 *
 */
class RandomName {
	
	/**
	 *
	 * @var array
	 */
	private $vowels = array (
			'a',
			'e',
			'i',
			'o',
			'u' 
	);
	
	/**
	 *
	 * @var array
	 */
	private $consonants;
	
	/**
	 *
	 * @var boolean
	 */
	private $useTextFiles = false;
	
	/**
	 *
	 * @var array
	 */
	private $firstPattern = array (
			'C',
			'V',
			'C',
			'C',
			'V' 
	);
	
	/**
	 *
	 * @var array
	 */
	private $lastPattern = array (
			'C',
			'V',
			'V',
			'C',
			'V',
			'C',
			'V' 
	);
	
	/**
	 *
	 * @var string
	 */
	private $namesTxtExt = "NamesList.txt";
	
	/**
	 *
	 * @var array
	 */
	private $nameslist = array (
			'male.first.NamesList.txt' => 'http://www.census.gov/genealogy/www/data/1990surnames/dist.male.first',
			'female.first.NamesList.txt' => 'http://www.census.gov/genealogy/www/data/1990surnames/dist.female.first',
			'last.NamesList.txt' => 'http://www.census.gov/genealogy/www/data/1990surnames/dist.all.last' 
	);
	
	/**
	 *
	 * @var array
	 */
	private $gender = array (
			'male',
			'female' 
	);
	
	/**
	 *
	 * @var string
	 */
	private $firstNameString = null;
	
	/**
	 *
	 * @var string
	 */
	private $lastNameString = null;
	
	/**
	 *
	 * @var array
	 */
	private $firstNames = null;
	
	/**
	 *
	 * @var array
	 */
	private $lastNames = null;
	
	/**
	 *
	 * @var boolean
	 */
	private $forceUpdate = false;
	
	/**
	 *
	 * @var string
	 */
	private $directoryPath = null;
	
	/**
	 *
	 * @return the $directoryPath
	 */
	private function getDirectoryPath() {
		return $this->directoryPath;
	}
	
	/**
	 *
	 * @param string $directoryPath        	
	 */
	private function setDirectoryPath($directoryPath) {
		$this->directoryPath = $directoryPath;
	}
	
	/**
	 *
	 * @return the $forceUpdate
	 */
	private function getForceUpdate() {
		return $this->forceUpdate;
	}
	
	/**
	 *
	 * @param boolean $forceUpdate        	
	 */
	public function setForceUpdate($forceUpdate) {
		$this->forceUpdate = $forceUpdate;
		$this->init ();
	}
	
	/**
	 *
	 * @param boolean $useTextFiles
	 *        	Whether to use the text files for random name generation or
	 *        	use built in methods
	 * @param boolean $forceUpdate
	 *        	Force the update for the site list in the $nameslist variable
	 * @param string $firstPattern
	 *        	First Name pattern when $useTextFiles is false Format is
	 *        	CVCVVC where C = consonant and V = vowel
	 * @param string $lastPattern
	 *        	Last Name pattern when $useTextFiles is false Format is CVCVVC
	 *        	where C = consonant and V = vowel
	 */
	function __construct($useTextFiles = true, $forceUpdate = false, $firstPattern = null, $lastPattern = null) {
		$this->useTextFiles = $useTextFiles;
		$this->forceUpdate = $forceUpdate;
		if (! $this->useTextFiles) {
			if (is_string ( $firstPattern )) {
				$this->setFirstPattern ( $firstPattern );
			}
			if (is_string ( $lastPattern )) {
				$this->setLastPattern ( $lastPattern );
			}
		} else {
			$this->setNamesPath ( 'names' );
		}
		$this->init ();
	}
	
	/**
	 * Initialize all the functions and methods needed for the object
	 */
	private function init() {
		$useTextFiles = $this->getUseTextFiles ();
		if (! $useTextFiles) {
			$consonants = range ( 'a', 'z' );
			$vowels = $this->getVowels ();
			foreach ( $vowels as $vowel ) {
				$key = array_search ( $vowel, $consonants );
				if ($key !== false) {
					unset ( $consonants [$key] );
				}
			}
			$this->setConsonants ( $consonants );
		} else if ($useTextFiles) {
			$directoryPath = $this->getDirectoryPath ();
			if ($this->getForceUpdate ()) {
				$this->generateTextFiles ();
			}
			if (is_null ( $this->getFirstNames () )) {
				$file = $directoryPath . $this->getRandomGender () . '.first.' . $this->namesTxtExt;
				$this->setFirstNames ( $file );
			}
			if (is_null ( $this->getLastNames () )) {
				$file = $directoryPath . 'last.' . $this->namesTxtExt;
				$this->setLastNames ( $file );
			}
		}
	}
	
	/**
	 * Use this to reset while in a loop or use the getListOfNames method
	 *
	 * {@source}
	 *
	 * @see RandomName::getListOfNames()
	 */
	public function reset() {
		$this->firstNameString = null;
		$this->lastNameString = null;
	}
	
	/**
	 *
	 * @return multitype:string
	 */
	private function randVowel() {
		$vowels = $this->getVowels ();
		return $vowels [array_rand ( $vowels, 1 )];
	}
	
	/**
	 *
	 * @return multitype:string
	 */
	private function randConsonant() {
		$consonants = $this->getConsonants ();
		return $consonants [array_rand ( $consonants, 1 )];
	}
	
	/**
	 *
	 * @return multitype:string
	 */
	private function getRandomGender() {
		$gender = $this->gender;
		return $gender [array_rand ( $gender, 1 )];
	}
	
	/**
	 *
	 * @param string $file        	
	 * @return multitype:
	 */
	private function getNamesFromFile($file) {
		if (! file_exists ( $file )) {
			$this->generateTextFiles ();
		}
		if (! file_exists ( $file )) {
			die ( $file . ': Does Not exist please find the specified file!' );
		}
		$contents = file_get_contents ( $file );
		$names = explode ( '|', $contents );
		return $names;
	}
	
	/**
	 *
	 * @return string
	 */
	private function getRandomFirstName() {
		$names = $this->getFirstNames ();
		return ucfirst ( strtolower ( $names [array_rand ( $names, 1 )] ) );
	}
	
	/**
	 *
	 * @return string
	 */
	private function getRandomLastName() {
		$names = $this->getLastNames ();
		return ucfirst ( strtolower ( $names [array_rand ( $names, 1 )] ) );
	}
	
	/**
	 * gets a random first name
	 *
	 * @return string
	 */
	public function getFirstName() {
		if ($this->getUseTextFiles ()) {
			$this->setFirstNameString ( $this->getRandomFirstName () );
			return $this->firstNameString;
		} else {
			$thePattern = $this->getFirstPattern ();
			$this->setFirstNameString ( $this->returnPattern ( $thePattern ) );
			return $this->firstNameString;
		}
	}
	
	/**
	 * gets a random last name
	 *
	 * @return string
	 */
	public function getLastName() {
		if ($this->getUseTextFiles ()) {
			$this->setLastNameString ( $this->getRandomLastName () );
			return $this->lastNameString;
		} else {
			$thePattern = $this->getLastPattern ();
			$this->setLastNameString ( $this->returnPattern ( $thePattern ) );
			return $this->lastNameString;
		}
	}
	
	/**
	 * gets a random first and last name sorted by the $order param
	 *
	 * @param string $separator
	 *        	the separator between the first and last names - default: ' '
	 * @param string $order
	 *        	the order of the names first|last - default: first
	 * @return string
	 */
	public function getName($separator = ' ', $order = 'first') {
		if (! $this->getFirstNameString ()) {
			$this->getFirstName ();
		}
		if (! $this->getLastNameString ()) {
			$this->getLastName ();
		}
		if ($order == 'first') {
			return $this->firstNameString . $separator . $this->lastNameString;
		} else if ($order == 'last') {
			return $this->lastNameString . $separator . $this->firstNameString;
		}
	}
	
	/**
	 * get a random list of names
	 *
	 * {@source}
	 * 
	 * @param int $limit
	 *        	set the limit of users to return - default: 10
	 * @param boolean $alphabetical
	 *        	sort alphabetically - default: true
	 * @param boolean $firstOnly
	 *        	only return first names - default: false
	 * @param boolean $lastOnly
	 *        	only return last names - default: false
	 * @param string $separator
	 *        	the separator between the first and last names - default: ' '
	 * @param string $order
	 *        	the order of the names first|last - default: first
	 * @return array
	 */
	public function getListOfNames($limit = 10, $alphabetical = true, $firstOnly = false, $lastOnly = false, $separator = ' ', $order = 'first') {
		$this->reset ();
		$names = array ();
		if ($firstOnly && ! $lastOnly) {
			for($i = 0; $i < $limit; $i ++) {
				$names [] = $this->getFirstName ();
				$this->reset ();
			}
		} else if (! $firstOnly && $lastOnly) {
			for($i = 0; $i < $limit; $i ++) {
				$names [] = $this->getLastName ();
				$this->reset ();
			}
		} else {
			for($i = 0; $i < $limit; $i ++) {
				$names [] = $this->getName ( $separator, $order );
				$this->reset ();
			}
		}
		if ($alphabetical) {
			sort ( $names );
		}
		return ( array ) $names;
	}
	
	/**
	 *
	 * @param string $thePattern        	
	 * @return string
	 */
	private function returnPattern($thePattern) {
		$randomName = '';
		foreach ( $thePattern as $pattern ) {
			if ($pattern == 'C') {
				$randomName .= $this->randConsonant ();
			} elseif ($pattern == 'V') {
				$randomName .= $this->randVowel ();
			}
		}
		return ucfirst ( $randomName );
	}
	
	/**
	 *
	 * @param string $url        	
	 * @param string $textFile        	
	 * @return boolean
	 */
	private function updateTextFile($url, $textFile) {
		$matches = null;
		$contents = file_get_contents ( $url );
		if ($contents) {
			preg_match_all ( '/[a-z]{3,}/i', $contents, $matches );
			if (! empty ( $matches )) {
				$directoryPath = $this->getDirectoryPath ();
				$filename = $directoryPath . $textFile;
				$data = implode ( '|', $matches [0] );
				$txtFileContents = file_put_contents ( $filename, $data );
				if ($txtFileContents) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 */
	private function generateTextFiles() {
		foreach ( $this->nameslist as $textFile => $url ) {
			$this->updateTextFile ( $url, $textFile );
		}
	}
	
	/**
	 *
	 * @return the $firstNames
	 */
	private function getFirstNames() {
		return $this->firstNames;
	}
	
	/**
	 *
	 * @return the $lastNames
	 */
	private function getLastNames() {
		return $this->lastNames;
	}
	
	/**
	 *
	 * @param string $file        	
	 */
	private function setFirstNames($file) {
		$this->firstNames = $this->getNamesFromFile ( $file );
	}
	
	/**
	 *
	 * @param string $file        	
	 */
	private function setLastNames($file) {
		$this->lastNames = $this->getNamesFromFile ( $file );
	}
	
	/**
	 *
	 * @return the $firstNameString
	 */
	private function getFirstNameString() {
		return $this->firstNameString;
	}
	
	/**
	 *
	 * @return the $lastNameString
	 */
	private function getLastNameString() {
		return $this->lastNameString;
	}
	
	/**
	 *
	 * @param string $firstNameString        	
	 */
	private function setFirstNameString($firstNameString) {
		$this->firstNameString = $firstNameString;
	}
	
	/**
	 *
	 * @param string $lastNameString        	
	 */
	private function setLastNameString($lastNameString) {
		$this->lastNameString = $lastNameString;
	}
	
	/**
	 *
	 * @return the $namesPath
	 */
	private function getNamesPath() {
		return $this->namesPath;
	}
	
	/**
	 *
	 * @param string $namesPath        	
	 */
	private function setNamesPath($namesPath) {
		$directoryPath = dirname ( __FILE__ ) . '/';
		$namesPathSlash = $namesPath . '/';
		$fullPath = $directoryPath . $namesPathSlash;
		if (! is_dir ( $fullPath )) {
			$success = mkdir ( $fullPath );
			if ($success) {
				die ( $fullPath . ': Was not a directory but has been created please refresh!' );
			} else {
				die ( $fullPath . ': Cannot be created with this script please check the folder permissions!' );
			}
		}
		$this->setDirectoryPath ( $fullPath );
		$this->namesPath = $namesPath;
	}
	
	/**
	 *
	 * @return the $useTextFiles
	 */
	private function getUseTextFiles() {
		return $this->useTextFiles;
	}
	
	/**
	 *
	 * @param boolean $useTextFiles        	
	 */
	public function setUseTextFiles($useTextFiles) {
		$this->firstNames = null;
		$this->lastNames = null;
		$this->reset ();
		$this->useTextFiles = $useTextFiles;
		$this->init ();
	}
	
	/**
	 *
	 * @return the $firstPattern
	 */
	private function getFirstPattern() {
		return $this->firstPattern;
	}
	
	/**
	 *
	 * @return the $lastPattern
	 */
	private function getLastPattern() {
		return $this->lastPattern;
	}
	
	/**
	 *
	 * @param string $firstPattern        	
	 */
	private function setFirstPattern($firstPattern) {
		$this->firstPattern = str_split ( $firstPattern );
	}
	
	/**
	 *
	 * @param string $lastPattern        	
	 */
	private function setLastPattern($lastPattern) {
		$this->lastPattern = str_split ( $lastPattern );
	}
	
	/**
	 *
	 * @return the $vowels
	 */
	private function getVowels() {
		return $this->vowels;
	}
	
	/**
	 *
	 * @return the $consonants
	 */
	private function getConsonants() {
		return $this->consonants;
	}
	
	/**
	 * for use when RandomName::useTextFiles is false 
	 *
	 * @param multitype:string $vowels        	
	 */
	public function setVowels($vowels) {
		$this->vowels = $vowels;
	}
	
	/**
	 * for use when RandomName::useTextFiles is false
	 *
	 * @param multitype:string $consonants        	
	 */
	public function setConsonants($consonants) {
		$this->consonants = $consonants;
	}
}
