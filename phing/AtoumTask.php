<?php
require_once "phing/Task.php";


class AtoumTask extends Task
{
    private $runner = false;

    private $filesets = array();
    private $configurationfiles = array();
    private $bootstrap = null;

    private $codecoverage = false;
    private $codecoveragereportpath = null;
    private $codecoveragereporturl = null;
    private $atoumpharpath = null;
    private $atoumautoloaderpath = null;
    private $phppath = null;

    private $showprogress = true;
    private $showduration = true;
    private $showmemory = true;
    private $showcodecoverage = true;
    private $showmissingcodecoverage = true;

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     *
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * Build a list of files from the fileset elements
     * @return array
     */
    private function getFiles()
    {
        $files = array();

        // filesets
        foreach ($this->filesets as $fs) {
            $ds    = $fs->getDirectoryScanner($this->project);
            $dir   = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $file) {
                $files[] = $dir . FileSystem::getFileSystem()->getSeparator() . $file;
            }
        }

        return $files;
    }

    /**
     * The message passed in the buildfile.
     */
    private $message = null;

    /**
     * The setter for the attribute "message"
     */
    public function setMessage($str) {
        $this->message = $str;
    }

    /**
     * The init method: Do init steps.
     */
    public function init() {
        //nothing to do
    }

    /**
     * The main entry point method.
     */
    public function main() {
        if ($this->codecoverage && !extension_loaded('xdebug')) {
            throw new Exception("AtoumTask depends on Xdebug being installed to gather code coverage information.");
	    }

        if ($this->bootstrap) {
            require_once $this->bootstrap;
        }

        define('mageekguy\\atoum\\scripts\\runner\\autorun', true);
        if (!empty($this->atoumpharpath)) {
            require_once('phar://'.$this->atoumpharpath.'/classes/autoloader.php');
        } elseif (!empty($this->atoumautoloaderpath)){
            require_once($this->atoumautoloaderpath);
        } else {
            if (!class_exists('mageekguy\atoum\scripts\runner', false)){
                throw new Exception("Unknown class mageekguy\\atoum\\scripts\\runner.\n\rConsider setting atoumpharpath parameter");
            }
        }

        require_once "./atoum/reports/realtime/phing.php";
        require_once "./atoum/report/fields/runner/tests/coverage/phing.php";
        require_once "./atoum/report/fields/runner/tests/memory/phing.php";
        require_once "./atoum/report/fields/runner/atoum/phing.php";
        require_once "./atoum/report/fields/runner/duration/phing.php";
        require_once "./atoum/report/fields/test/run/phing.php";
        require_once "./atoum/report/fields/test/event/phing.php";
        require_once "./atoum/report/fields/test/memory/phing.php";
        require_once "./atoum/report/fields/test/duration/phing.php";

        //including files to test
        foreach ($this->getFiles() as $file) {
           include($file);
        }
        $this->execute();
    }

    public function execute()
    {
        if ($this->runner === false){
            $this->runner = new \mageekguy\atoum\runner();
            $report = new \mageekguy\atoum\reports\realtime\phing(
                                                                    $this->getShowProgress(),
                                                                    $this->getShowCodeCoverage(),
                                                                    $this->getShowMissingCodeCoverage(),
                                                                    $this->getShowDuration(),
                                                                    $this->getShowMemory(),
                                                                    $this->getCodecoveragereportpath(),
                                                                    $this->getCodecoveragereporturl()
                                                                 );
            $writer = new \mageekguy\atoum\writers\std\out();

            $report->addWriter($writer);
            $this->runner->addReport($report);

            if ($this->codecoverage){
                $this->runner->enableCodeCoverage();
            } else {
                $this->runner->disableCodeCoverage();
            }
            if ($this->phppath !== null){
                $this->runner->setPhpPath($this->phppath);
            }
        }

        $this->runner->run();

        $score = $this->runner->getScore();
        if (count($score->getErrors()) > 0
            || count($score->getFailAssertions()) > 0
            || count($score->getExceptions()) > 0){
            throw new BuildException("Tests did not pass");
        }
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = (string) $bootstrap;
        return $this;
    }

    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function setCodecoverage($codecoverage)
    {
        $this->codecoverage = (boolean) $codecoverage;
        return $this;
    }

    public function getCodecoverage()
    {
        return $this->codecoverage;
    }

    public function setConfigurationfiles($configurationfiles)
    {
        $this->configurationfiles = $configurationfiles;
        return $this;
    }

    public function getConfigurationfiles()
    {
        return $this->configurationfiles;
    }

    public function setAtoumpharpath($atoumpharpath)
    {
        $this->atoumpharpath = (string) $atoumpharpath;
        return $this;
    }

    public function getAtoumpharpath()
    {
        return $this->atoumpharpath;
    }

    public function setPhppath($phppath)
    {
        $this->phppath = (string) $phppath;
        return $this;
    }

    public function getPhppath()
    {
        return $this->phppath;
    }

    public function setshowcodecoverage($showcodecoverage)
    {
        $this->showcodecoverage = (boolean) $showcodecoverage;
        return $this;
    }

    public function getshowcodecoverage()
    {
        return $this->showcodecoverage;
    }

    public function setshowduration($showdurationReport)
    {
        $this->showduration = (boolean) $showdurationReport;
        return $this;
    }

    public function getshowduration()
    {
        return $this->showduration;
    }

    public function setshowmemory($showmemoryReport)
    {
        $this->showmemory = (boolean) $showmemoryReport;
        return $this;
    }

    public function getshowmemory()
    {
        return $this->showmemory;
    }

    public function setshowmissingcodecoverage($showmissingcodecoverage)
    {
        $this->showmissingcodecoverage = (boolean) $showmissingcodecoverage;
        return $this;
    }

    public function getshowmissingcodecoverage()
    {
        return $this->showmissingcodecoverage;
    }

    public function setshowprogress($showprogress)
    {
        $this->showprogress = (boolean) $showprogress;
        return $this;
    }

    public function getshowprogress()
    {
        return $this->showprogress;
    }

    public function setAtoumautoloaderpath($atoumautoloaderpath)
    {
        $this->atoumautoloaderpath = $atoumautoloaderpath;
    }

    public function getAtoumautoloaderpath()
    {
        return $this->atoumautoloaderpath;
    }

    public function setCodecoveragereportpath($codecoveragereportpath)
    {
        $this->codecoveragereportpath = (string) $codecoveragereportpath;
        return $this;
    }

    public function getCodecoveragereportpath()
    {
        return $this->codecoveragereportpath;
    }

    public function setCodecoveragereporturl($codecoveragereporturl)
    {
        $this->codecoveragereporturl = (string) $codecoveragereporturl;
        return $this;
    }

    public function getCodecoveragereporturl()
    {
        return $this->codecoveragereporturl;
    }
}