<?php namespace JobLion\AppBundle;

/**
* An instance can load and save to a configuration file
*/
class ConfigFile
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
    * @return Config
    */
    public function create()
    {
        $this->configArray = array();

        $file = $this->rootDir.$this->configFile;
        if (is_file($file)) {
            throw new Exception('This Config File does already exists.');
        }

        $this->save();
        return $this;
    }

    /**
    * Loads the File $this->configFile and parses all Parameters into $this->configArray
    *
    * @return Config
    */
    public function load()
    {
        $file = $this->rootDir.$this->configFile;

        if (!is_file($file)) {
            throw new Exception("Config file '$file' not found!");
        }

        global $CONFIG;
        include $file;

        if (!is_array($CONFIG)) {
            throw new Exception('Config File is corrupt! (' . $CONFIG . ")");
        }

        $this->configArray = $CONFIG;

        return $this;
    }

    /**
    * Sets a Parameter to the given value and saves it to the Config File
    *
    * @param  string $parameter Name of the Parameter
    * @param  string $value     Value of the Parameter
    * @return Config
    */
    public function set($parameter, $value)
    {
        if (!is_array($this->configArray)) {
            throw new Exception('The Config File has to be loaded with load() first.');
        }

        $this->configArray[$parameter] = $value;

        $this->save();
        return $this;
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
            throw new Exception('The Config File has to be loaded with load() first.');
        }

        return $this->configArray[$parameter];
    }

    /**
    * Saves all Parameters with values to $this->configFile
    *
    * @return Config
    */
    private function save()
    {
        $file = $this->rootDir.$this->configFile;
        $content = $this->generateConfigFileContent();
        $status = file_put_contents($file, $content);

        if ($status === false) {
            throw new Exception("Config file could not be saved");
        }

        return $this;
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
