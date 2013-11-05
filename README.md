RandomNames
===========

A Class that uses data from http://www.census.gov to generate random names

Usage
-----
```php
include ('RandomName.php');
$randomname = new RandomName ();
// setForceUpdate will force the script to pull from census.gov again
// $randomname->setForceUpdate(true);
// reset will set the firstNameString and lastNameString to null
// $randomname->reset();
echo "<pre>";
var_dump ( $randomname->getFirstName () );
echo "</pre>";
echo "<pre>";
var_dump ( $randomname->getListOfNames ( 10, true, true, true, ', ', 'last' ) );
echo "</pre>";
// setUseTextFiles to false to use built in method to create names NOTE: not the
// best but I thought I would throw it in there.
$randomname->setUseTextFiles ( false );
// $randomname->setConsonants(array('b','z'));
// $randomname->setVowels(array('a','i'));
echo "<pre>";
var_dump ( $randomname->getListOfNames ( 10, true, true, true, ', ', 'last' ) );
echo "</pre>";
```
