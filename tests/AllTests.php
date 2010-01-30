<?php
// library
require_once '../lib/Imaged.php';

// PHPUnit framework
require_once 'PHPUnit/Framework.php';

// tests
require_once 'LoadingTest.php';
require_once 'ResampleTest.php';
require_once 'FitCropTogetherTest.php';

// file constants
require_once 'files.php';

// all tests suite
class AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('LoadingTest');
        $suite->addTestSuite('ResampleTest');
        $suite->addTestSuite('FitCropTogetherTest');

        return $suite;
    }

}

