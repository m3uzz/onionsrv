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

class Config
{
	
	/**
	 * Load the Onion default configs and merge with client configs.
	 * 
	 * @return array
	 */
	public static function loadConfigs ($psConfigFile = 'srv-config.php')
	{
		$lsConfDir = CONFIG_DIR . DS . $psConfigFile;
		
		$laConfig = array();
			
		if (file_exists($lsConfDir))
		{
			$laConfig = include $lsConfDir;
		}
		
		return $laConfig;
	}

	
	/**
	 * Merge options recursively
	 *
	 * @param array $paArray1        	
	 * @param mixed $paArray2        	
	 * @return array
	 */
	public static function merge (array $paArray1, $paArray2 = null)
	{
		if (is_array($paArray2))
		{
			foreach ($paArray2 as $lmKey => $lmVal)
			{
				if (is_array($paArray2[$lmKey]))
				{
					$paArray1[$lmKey] = (array_key_exists($lmKey, $paArray1) &&
							 is_array($paArray1[$lmKey])) ? self::merge(
									$paArray1[$lmKey], $paArray2[$lmKey]) : $paArray2[$lmKey];
				}
				else
				{
					$paArray1[$lmKey] = $lmVal;
				}
			}
		}
		
		return $paArray1;
	}

	
	/**
	 * Load the application options
	 *
	 * @param string $psOption
	 * @return array
	 */
	public static function getOptions ($psOption = null, $psConfigFile = 'srv-config.php')
	{
		$gaConfig = self::loadConfigs($psConfigFile);
		
		if ($psOption !== null && isset($gaConfig[$psOption]))
		{
			return $gaConfig[$psOption];
		}
		else
		{
			return $gaConfig;
		}
	}
	
	
	/**
	 * 
	 */
	public static function setTimeZone ()
	{
		$laTimeZone = self::getOptions('time-zone');
		date_default_timezone_set($laTimeZone);
	}
	
	
	/**
	 * 
	 * @param boolean $pbDebug
	 * @param boolean $pbPhpError
	 * @param boolean $pbTime
	 * @param boolean $pbTest
	 */
	public static function setDebugMod ($pbDebug=false, $pbPhpError=false, $pbTime=false, $pbTest = false)
	{
		$lbDebugMod = false;
		$laDebug = self::getOptions('debug');
		
		if (isset($laDebug['enable']))
		{
			$lbDebugMod = $laDebug['enable'];
		}
		
		if ($pbDebug && $lbDebugMod)
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);			
			defined('DEBUG') || define('DEBUG', true);
			defined('TIMESHOW') || define('TIMESHOW', true);
		}
		else
		{
			if ($pbPhpError && $lbDebugMod)
			{
				error_reporting(E_ALL);
				ini_set("display_errors", 1);
			}
			else 
			{
				error_reporting(NULL);
				ini_set("display_errors", 0);
			}
			
			defined('DEBUG') || define('DEBUG', false);
		}
		
		if ($pbTime && $lbDebugMod)
		{
			defined('TIMESHOW') || define('TIMESHOW', true);
		}
		else
		{
			defined('TIMESHOW') || define('TIMESHOW', false);
		}
		
		if ($pbTest && $lbDebugMod)
		{
			defined('TESTMOD') || define('TESTMOD', true);
		}
		else 
		{
			defined('TESTMOD') || define('TESTMOD', false);
		}
	}
}