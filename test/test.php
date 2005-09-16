<?php

ini_set ('include_path', ini_get('include_path') . ':../');

define ('SIMPLETEST_DIR', 'simpletest/');
require_once (SIMPLETEST_DIR . 'unit_tester.php');
require_once (SIMPLETEST_DIR . 'reporter.php');

include_once ('cases/sanity.php');
include_once ('cases/parse.php');
include_once ('cases/entities.php');

$alltests =& new GroupTest ('All tests');
$alltests->addTestCase (new Sanity);
// $alltests->addTestCase (new ParseTestCase);
$alltests->addTestCase (new CompositeEntityTestCase);
$alltests->addTestCase (new EntityTypesTestCase);
$alltests->run (new TextReporter ());

?>
