<?php namespace JobLion\Database;

use PHPUnit\Framework\TestCase;

class ConfigFileTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        // delete possible old test config
        $oldTestConfig = __DIR__.'/../../conf/config.phpUnitTest.php';
        if (file_exists($oldTestConfig)) {
            unlink($oldTestConfig);
        }
    }

    public static function tearDownAfterClass()
    {
        self::setUpBeforeClass();
    }

    public function testConfigFileCanBeCreatedAndLoaded()
    {
        $configTestOption = true;
        $configTestOption2 = 'Test String';

        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->create()
               ->set('testOption', $configTestOption)
               ->set('testOption2', $configTestOption2);

        $this->assertFileExists('conf/config.phpUnitTest.php');

        // Check if the config file can be loaded again
        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->load();

        $testOption = $config->get('testOption');
        $testOption2 = $config->get('testOption2');

        $this->assertEquals($testOption, $configTestOption);
        $this->assertEquals($testOption2, $configTestOption2);
    }

    public function testOptionCanBeAddedToExistingConfig()
    {
        $configTestOption3 = false;

        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->load()
               ->set('testOption3', $configTestOption3);

        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->load();
        $testOption3 = $config->get('testOption3');
        $this->assertEquals($testOption3, $configTestOption3);
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testLoadNonExistingConfig()
    {
        $config = new ConfigFile('conf/doesNotExist.php');
        $success = $config->load();
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testOptionsCanNotBeReadWhenNotLoaded()
    {
        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->get('testOption');
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testOptionCanNotBeAddedWhenNotLoaded()
    {
        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->set('testOption', true);
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testConfigCanNotBeOverwritten()
    {
        $config = new ConfigFile('conf/config.phpUnitTest.php');
        $config->create();
    }
}
