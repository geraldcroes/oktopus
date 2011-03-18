<?php
/**
 * @package Oktopus
 * @author "Gérald Croës <gerald@croes.org>"
 * @copyright "Gérald Croës <gerald@croes.org>" 
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file 
 */

namespace Oktopus;

interface ITimer
{
    public function start();
    public function stop();
}

/**
 * This class is an easy way to measure the elapsed time beetween two operations
 * 
 * @package Oktopus
 * <code>
 *    //Simple timer
 *    $timer = new Oktopus\Timer ();
 *    $timer->start ();
 *    
 *    //Following code is supposed to take a while....
 *    //....The code....
 *    $duration = $timer->stop ();
 * </code>
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
     * Retourne le temps actuel de la machine
     * 
     * @return int Temps courant en millisecondes
     */
	private function _getMicroTime ()
	{
		return microtime(true);
	}
   
	/**
	 * Retourne le temps passé (en secondes) entre deux chiffres en microsecondes
	 * 
	 * @param int $pStartTime Temps de début en microsecondes
	 * @param int $pStopTime Temps d'arrêt en microsecondes
	 * @return float
	 */
	private function _elapsedTime ($pStartTime, $pStopTime)
	{
		return max (0, intval (($pStopTime - $pStartTime) * 1000) / 1000);
	}
}

class ExecutionTimer
{
    private $_timer;

    public function __construct (\Oktopus\ITimer $pTimer)
    {
        $this->_timer = $pTimer;
    }

    public function getElapsedTimeFor ($pCallable, array $pParameters = array())
    {
        if (!is_callable($pCallable)) {
            throw new BadMethodCallException('The given parameter is not a valid callback');
        }

        $this->_timer->start();
        call_user_func_array($pCallable, $pParameters);
        return $this->_timer->stop();
    }
}