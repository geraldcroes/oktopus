<?php
/**
 * @package Oktopus
 * @author "Gérald Croës <gerald@croes.org>"
 * @copyright "Gérald Croës <gerald@croes.org>" 
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file 
 */

namespace Oktopus;

/**
 * A simple timer interface
 * @package Oktopus
 */
interface ITimer
{
    /**
     * @abstract
     * @return void
     */
    public function start();
    public function stop();
}

/**
 * This class is an easy way to measure the elapsed time beetween two operations
 * @example
 * <code>
 *    //Simple timer
 *    $timer = new Oktopus\Timer ();
 *    $timer->start ();
 *    
 *    //Following code is supposed to take a while....
 *    //....The code....
 *    $duration = $timer->stop ();
 * </code>
 * @package Oktopus
 */
class Timer implements ITimer
{
	/**
	 * Current timers
	 * 
	 * @var array
	 */
	private $_timers = array ();
	
	/**
	 * Construct
	 * 
	 * @param boolean $pAutoStart default true
	 */
	public function __construct ($pAutoStart = true)
	{
	    if ($pAutoStart === true) {
	        $this->start();
	    }
	}

	/**
	 * Starts the timer
	 * 
	 * @return int the start time 
	 */
	public function start ()
	{
		$time = $this->_getMicroTime ();
		array_push ($this->_timers, $time);
		return $time;
	}

	/**
	 * Stops the current timer
	 * 
	 * @return float
	 */
	public function stop ()
	{
		$stop = $this->_getMicroTime ();
		$start = array_pop ($this->_timers);
		return $this->_elapsedTime ($start, $stop);
	}

	/**
     * Gets the current time
     * 
     * @return int Current time
     */
	private function _getMicroTime ()
	{
		return microtime(true);
	}
   
	/**
	 * Gets the elapsed time between two microtime
	 * 
	 * @param int $pStartTime
	 * @param int $pStopTime
	 * @return float
	 */
	private function _elapsedTime ($pStartTime, $pStopTime)
	{
		return max (0, intval (($pStopTime - $pStartTime) * 1000) / 1000);
	}
}