<?php
require_once "phing/Task.php";

class AtoumTask extends Task
{
    private $filesets = array();
    private $scorefile = array();
    private $configurationfiles = array();
    private $reporttitle = "Test de rapport";
    private $bootstrap = null;
    private $codecoverage = true;
    private $atoumpharpath = null;
    private $runner = false;

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
     * Build a list of files (from the fileset elements) and call the DocBlox parser
     * @return string
     */
    private function parseFiles()
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

        $this->log("Will test " . count($files) . " file(s)");
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
        } else {
            if (!class_exists('mageekguy\atoum\scripts\runner', false)){
                throw new Exception("Unknown class mageekguy\\atoum\\scripts\\runner.\n\rConsider setting atoumpharpath parameter or adding a bootstrap that includes Atoum");
            }
        }

        foreach ($this->parseFiles() as $file) {
           include($file);
        }
        $this->execute();
    }

    public function execute()
    {
        if ($this->runner === false){
            /*
            Write all on stdout.
            */
            $stdOutWriter = new mageekguy\atoum\writers\std\out();

            /*
            Generate a CLI report.
            */
            $vimReport = new mageekguy\atoum\reports\asynchronous\vim();
            $vimReport->addWriter($stdOutWriter);

            $this->runner = new \mageekguy\atoum\runner();
            $report = new \mageekguy\atoum\reports\realtime\cli();
            $writer = new \mageekguy\atoum\writers\std\out();

            $report->addWriter($writer);
            $this->runner->addReport($report);

            $this->runner->setDefaultReportTitle($this->getReporttitle());
            if ($this->codecoverage){
                $this->runner->enableCodeCoverage();
            } else {
                $this->runner->disableCodeCoverage();
            }
            $this->runner->setPhpPath('/usr/bin/php');
            $this->log ($this->runner->getPhpPath('php'));
        }

        $this->runner->run();
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = (string) $bootstrap;
    }

    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function setCodecoverage($codecoverage)
    {
        $this->codecoverage = (boolean) $codecoverage;
    }

    public function getCodecoverage()
    {
        return $this->codecoverage;
    }

    public function setConfigurationfiles($configurationfiles)
    {
        $this->configurationfiles = $configurationfiles;
    }

    public function getConfigurationfiles()
    {
        return $this->configurationfiles;
    }

    public function setReporttitle($reporttitle)
    {
        $this->reporttitle = (string) $reporttitle;
    }

    public function getReporttitle()
    {
        return $this->reporttitle;
    }

    public function setScorefile($scorefile)
    {
        $this->scorefile = (string) $scorefile;
    }

    public function getScorefile()
    {
        return $this->scorefile;
    }

    public function setAtoumpharpath($atoumpharpath)
    {
        $this->atoumpharpath = (string) $atoumpharpath;
    }

    public function getAtoumpharpath()
    {
        return $this->atoumpharpath;
    }
}