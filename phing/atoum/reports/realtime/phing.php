<?php

namespace mageekguy\atoum\reports\realtime;

use
	mageekguy\atoum,
	mageekguy\atoum\cli\prompt,
	mageekguy\atoum\cli\colorizer,
	mageekguy\atoum\reports\realtime,
	mageekguy\atoum\report\fields\test,
	mageekguy\atoum\report\fields\runner
;

class phing extends realtime
{
    protected $showProgress;
    protected $showMissingCodeCoverage;
    protected $showDuration;
    protected $showMemory;
    protected $showCodeCoverage;
    protected $codeCoverageReportPath;
    protected $codeCoverageReportUrl;

	public function __construct($showProgress, $showCodeCoverage, $showMissingCodeCoverage, $showDuration, $showMemory, $codeCoverageReportPath, $codeCoverageReportUrl)
	{
		parent::__construct(null, null);

        $this->showProgress = $showProgress;
        $this->showCodeCoverage = $showCodeCoverage;
        $this->showMissingCodeCoverage = $showMissingCodeCoverage;
        $this->showDuration = $showDuration;
        $this->showMemory = $showMemory;
        $this->codeCoverageReportPath = $codeCoverageReportPath;
        $this->codeCoverageReportUrl = $codeCoverageReportUrl;

		$firstLevelPrompt = new prompt(PHP_EOL);
		$firstLevelColorizer = new colorizer('1;36');

		$secondLevelPrompt = new prompt(' ', $firstLevelColorizer);

		$failureColorizer = new colorizer('0;31');
		$failurePrompt = clone $secondLevelPrompt;
		$failurePrompt->setColorizer($failureColorizer);

		$errorColorizer = new colorizer('0;33');
		$errorPrompt = clone $secondLevelPrompt;
		$errorPrompt->setColorizer($errorColorizer);

		$exceptionColorizer = new colorizer('0;35');
		$exceptionPrompt = clone $secondLevelPrompt;
		$exceptionPrompt->setColorizer($exceptionColorizer);

		$this
			->addRunnerField(new runner\atoum\phing(
						$firstLevelPrompt,
						$firstLevelColorizer
					),
					array(atoum\runner::runStart)
				)
			->addRunnerField(new runner\php\path\cli(
						$firstLevelPrompt,
						$firstLevelColorizer
					),
					array(atoum\runner::runStart)
				)
			->addRunnerField(new runner\php\version\cli(
						$firstLevelPrompt,
						$firstLevelColorizer,
						$secondLevelPrompt
					),
					array(atoum\runner::runStart)
				);
        if ($this->showCodeCoverage)
        {
            $this->addRunnerField(new runner\tests\coverage\phing(
                    $firstLevelPrompt,
                    $secondLevelPrompt,
                    new prompt('  ', $firstLevelColorizer),
                    $firstLevelColorizer,
                    null,
                    null,
                    $this->showMissingCodeCoverage
                ),
                array(atoum\runner::runStop)
            );
        }
        if ($this->showDuration)
        {
            $this->addRunnerField(new runner\duration\phing(
                        $firstLevelPrompt,
                        $firstLevelColorizer
                    ),
                    array(atoum\runner::runStop)
                );
        }
        if ($this->showMemory){
            $this->addRunnerField(new runner\tests\memory\phing(
                            $firstLevelPrompt,
                            $firstLevelColorizer
                        ),
                        array(atoum\runner::runStop)
                    );
        }
        $this
			->addRunnerField(new runner\result\cli(
						$firstLevelPrompt,
						new colorizer('0;37', '42'),
						new colorizer('0;37', '41')
					),
					array(atoum\runner::runStop)
				)
			->addRunnerField(new runner\failures\cli(
						$firstLevelPrompt,
						$failureColorizer,
						$failurePrompt
					),
					array(atoum\runner::runStop)
				)
			->addRunnerField(
				new runner\outputs\cli(
						$firstLevelPrompt,
						$firstLevelColorizer,
						$secondLevelPrompt
					),
					array(atoum\runner::runStop)
				)
			->addRunnerField(new runner\errors\cli(
						$firstLevelPrompt,
						$errorColorizer,
						$errorPrompt
					),
					array(atoum\runner::runStop)
				)
			->addRunnerField(new runner\exceptions\cli(
						$firstLevelPrompt,
						$exceptionColorizer,
						$exceptionPrompt
					),
					array(atoum\runner::runStop)
				);
        if ($this->showProgress)
        {
			$this->addTestField(new test\run\phing(
						$firstLevelPrompt,
						$firstLevelColorizer
					),
					array(atoum\test::runStart)
				)
			->addTestField(new test\event\phing());
            if ($this->showDuration)
            {
                $this->
                    addTestField(new test\duration\phing(
                                $secondLevelPrompt
                            ),
                            array(atoum\test::runStop)
                        );
            }
            if ($this->showMemory)
            {
                $this->addTestField(new test\memory\phing(
                            $secondLevelPrompt
                        ),
                        array(atoum\test::runStop)
                    );
            }
        }

        if ($this->getCodecoveragereportpath())
        {
            $coverageField = new atoum\report\fields\runner\coverage\html('', $this->getCodecoveragereportpath());
            if ($this->codeCoverageReportUrl === null){
                $coverageField->setRootUrl("file:////".realpath($this->getCodecoveragereportpath()));
            } else {
                $coverageField->setRootUrl($this->getCodecoveragereporturl());
            }
            $this->addRunnerField($coverageField, array(atoum\runner::runStop));
        }
	}

    public function getShowCodeCoverage()
    {
        return $this->showCodeCoverage;
    }

    public function getShowDuration()
    {
        return $this->showDuration;
    }

    public function getShowMemory()
    {
        return $this->showMemory;
    }

    public function getShowMissingCodeCoverage()
    {
        return $this->showMissingCodeCoverage;
    }

    public function getShowProgress()
    {
        return $this->showProgress;
    }

    public function getCodecoveragereportpath()
    {
        return $this->codeCoverageReportPath;
    }
   public function getCodecoveragereporturl()
    {
        return $this->codeCoverageReportUrl;
    }
}