<?php
/**
 * This file is part of Onion Service
 *
 * Copyright (c) 2014-2016, Humberto Lourenço <betto@m3uzz.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Humberto Lourenço nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   PHP
 * @package    OnionSrv
 * @author     Humberto Lourenço <betto@m3uzz.com>
 * @copyright  2014-2016 Humberto Lourenço <betto@m3uzz.com>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/m3uzz/onionsrv
 */

namespace OnionSrv;
use OnionLib\Util;
use OnionSrv\Config;

class Debug
{
	
	/**
	 *
	 * @param int|string|array|object $pmVar
	 *        	- Valor a ser impresso
	 * @param boolean $pbForceDebug
	 *        	- Se true força a impressão mesmo que no config o debug esteja
	 *        	desabilitado
	 */
	public static function debug ($pmVar, $pbForceDebug = false)
	{
		if (DEBUG || $pbForceDebug)
		{
			if (isset($_SERVER['argv']))
			{
				$lsOpen = "\n";
				$lsClose = "\n";
			}
			else
			{
				$lsOpen = '<pre style="margin:50px;"><code><fieldset><legend>Onion Debug:</legend>';
				$lsClose = '</fieldset></code></pre>';
			}
			
			echo $lsOpen;
			self::displayDebug($pmVar);
			echo $lsClose;
		}
	}

	
	/**
	 *
	* @param int|string|array|object $pmVar
	*/
	public static function debugD ($pmVar)
	{
		self::debug($pmVar);

		if (DEBUG)
		{
			exit(200);
		}
	}
	
	
	/**
	 *
	 * @param int|string|array|object $pmVar        	
	 */
	public static function display ($pmVar)
	{
		self::debug($pmVar, true);
	}

	
	/**
	 *
	 * @param int|string|array|object $pmVar        	
	 */
	public static function displayD ($pmVar)
	{
		die(self::debug($pmVar, true));
	}

	
	/**
	 *
	 * @param int|string|array|object $pmVar        	
	 */
	public static function displayDebug ($pmVar)
	{
		if (is_object($pmVar))
		{
			var_dump($pmVar);
		}
		elseif (is_array($pmVar))
		{
			print_r($pmVar);
		}
		else
		{
			echo ($pmVar);
		}
	}
	
	
	/**
	 * 
	 * @param string $psMethod
	 * @param boolean $pbUnique
	 * @param boolean $pbForceShowTime
	 */
	public static function debugTimeStart ($psMethod, $pbUnique = false, $pbForceShowTime = false)
	{
		self::debugTimer($psMethod, 'START', $pbUnique, $pbForceShowTime);
	}
	
	
	/**
	 * 
	 * @param string $psMethod
	 * @param boolean $pbUnique
	 * @param boolean $pbForceShowTime
	 */
	public static function debugTimeEnd ($psMethod, $pbUnique = false, $pbForceShowTime = false)
	{
		self::debugTimer($psMethod, 'END', $pbUnique, $pbForceShowTime);
	}
	
	
	/**
	 * 
	 * @param string $psMethod
	 * @param string $psType
	 * @param boolean $pbUnique
	 * @param boolean $pbForceShowTime
	 */
	public static function debugTimer ($psMethod, $psType, $pbUnique = false, $pbForceShowTime = false)
	{
		global $gaTimer;
		
		if (TIMESHOW || $pbForceShowTime)
		{
			$laTime = explode(" ", microtime());
			$lnTime = $laTime[1] + $laTime[0];
			
			$lsMsg = $psMethod . " [" . $psType . "] " . $lnTime;
			
			if ($pbUnique)
			{
				if ($psType == 'START' && !isset($gaTimer[$psMethod]['START'][0]))
				{
					$gaTimer[$psMethod]['START'][0] = $lnTime;
					self::debug("{$psMethod} {$psType}: {$lnTime}", true);
				}
				elseif ($psType == 'END')
				{
					$gaTimer[$psMethod]['END'][0] = $lnTime;
					echo ".";
				}
			}
			else 
			{
				$gaTimer[$psMethod][$psType][] = $lnTime;
				self::debug("{$psMethod} {$psType}: {$lnTime}", true);
			}
			
			if ($psType == 'END')
			{
				if ($pbUnique)
				{
					$gaTimer[$psMethod]['TIME'][0] = $gaTimer[$psMethod]['END'][0] - $gaTimer[$psMethod]['START'][0];
				}
				else
				{
					$gaTimer[$psMethod]['TIME'][] = 1;
					$lnI = count($gaTimer[$psMethod]['TIME']) - 1;
					$gaTimer[$psMethod]['TIME'][$lnI] = $gaTimer[$psMethod]['END'][$lnI] - $gaTimer[$psMethod]['START'][$lnI];
					self::debug("{$psMethod} TIME: {$gaTimer[$psMethod]['TIME'][$lnI]}", true);
				}
			}
		}
	}
	
	
	/**
	 * 
	 * @param string $psMethod
	 * @param string $psType
	 * @param boolean $pbForceShowTime
	 */
	public static function showTime ($psMethod, $psType = null, $pbForceShowTime = false)
	{
		global $gaTimer;
		
		if (TIMESHOW || $pbForceShowTime)
		{
			if ($psType === null)
			{
				if (isset($gaTimer[$psMethod]))
				{
					self::debug("{$psMethod}:", true);
					self::debug($gaTimer[$psMethod], true);
				}
			}
			else 
			{
				if (isset($gaTimer[$psMethod][$psType]))
				{
					self::debug("{$psMethod} {$psType}:", true);
					self::debug($gaTimer[$psMethod][$psType], true);
				}
			}
		}
	}
	

	/**
	 *
	 * @param unknown $psMsgError
	 */
	public static function echoError ($psMsgError)
	{
		echo ("\e[31mERROR - " . $psMsgError . "\e[0m\n\n");
	}
	
	
	/**
	 *
	 * @param unknown $psMsgWarning
	 */
	public static function echoWarning ($psMsgWarning)
	{
		echo ("\e[33mWARNING - " . $psMsgWarning . "\e[0m\n\n");
	}
	
	
	/**
	 *
	 * @param unknown $psMsgSuccess
	 */
	public static function echoSuccess ($psMsgSuccess)
	{
		echo ("\e[32mSUCCESS - " . $psMsgSuccess . "\e[0m\n\n");
	}
	
	
	/**
	 *
	 * @param unknown $psMsgError
	 */
	public static function echoInfo ($psMsgInfo)
	{
		echo ("\e[34m" . $psMsgInfo . "\e[0m\n\n");
	}
	
	
	/**
	 * 
	 * @param unknown $psMsgError
	 */
	public static function exitError ($psMsgError)
	{
		die (self::echoError($psMsgError));
	}
	
	
	/**
	 *
	 * @param unknown $psMsgWarning
	 */
	public static function exitWarning ($psMsgWarning)
	{
		die (self::echoWarning($psMsgWarning));
	}
	
	
	/**
	 *
	 * @param unknown $psMsgSuccess
	 */
	public static function exitSuccess ($psMsgSuccess)
	{
		die (self::echoSuccess($psMsgSuccess));
	}
	
	
	/**
	 *
	 * @param unknown $psMsgError
	 */
	public static function exitInfo ($psMsgInfo)
	{
		die (self::echoInfo($psMsgInfo));
	}
}