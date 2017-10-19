<?php namespace JobLion\JobLion;

/**
* An instance can load and save to a configuration file
*/
class Config
{
    /**
    * The Path to the Root of this Project
     *
    * @var string
    */
    private $rootDir = __DIR__.'/../../';
    /**
    * The Path to the Config File starting from the Root of the Project
     *
    * @var string
    */
    private $configFile;
    /**
    * Contains all Config Parameters and its values
     *
    * @var array
    */
    private $configArray;


    /**
    * Create an instance
    *
    * @param  string $configFile The Config File to be used
    * @access public
    */
    public function __construct($configFile="conf/config.php")
    {
        $this->configFile = $configFile;
    }

    /**
    * Creates a new Config File
    *
    * @return bool  True if File could be created, False otherwise
    */
    public function create()
    {
        $this->configArray = array();

        $file = $this->rootDir.$this->configFile;
        if (is_file($file)) {
            throw new Exception\Exception('This Config File does already exists.');
        }


        return $this->save();
    }

    /**
    * Loads the File $this->configFile and parses all Parameters into $this->configArray
    *
    * @return bool  True if File could be loaded, False if the File does not exist
    */
    public function load()
    {
        $file = $this->rootDir.$this->configFile;

        if (!is_file($file)) {
            return false;
        }

        global $CONFIG;
        include $file;

        if (!is_array($CONFIG)) {
            throw new Exception\Exception('Config File is corrupt! (' . $CONFIG . ")");
        }

        $this->configArray = $CONFIG;

        return true;
    }

    /**
    * Sets a Parameter to the given value and saves it to the Config File
    *
    * @param  string $parameter Name of the Parameter
    * @param  string $value     Value of the Parameter
    * @return bool              True if File could be saved, False otherwise
    */
    public function set($parameter, $value)
    {
        if (!is_array($this->configArray)) {
            throw new Exception\Exception('The Config File has to be loaded with load() first.');
        }

        $this->configArray[$parameter] = $value;
        return $this->save();
    }

    /**
    * Gets the given Parameter and returns the Value
    *
    * @param  string $parameter Name of the Parameter
    * @return string              Value of the Parameter
    */
    public function get($parameter)
    {
        if (!is_array($this->configArray)) {
            throw new Exception\Exception('The Config File has to be loaded with load() first.');
        }

        return $this->configArray[$parameter];
    }

    /**
    * Saves all Parameters with values to $this->configFile
    *
    * @return bool  True if all Parameters could be saved to the Config File, False otherwise
    */
    private function save()
    {
        $file = $this->rootDir.$this->configFile;
        $content = $this->generateConfigFileContent();
        $status = file_put_contents($file, $content);

        if ($status === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Generates The Content of a Config File
    *
    * @return string  Content of the File
    */
    private function generateConfigFileContent()
    {
        $content = '<?php'."\n".

        $content = '$CONFIG = '.var_export($this->configArray, true);

        $content .= ";\n".
                    '?>';
        return $content;
    }
}
