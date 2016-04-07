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
 * @package    Onion Service
 * @author     Humberto Lourenço <betto@m3uzz.com>
 * @copyright  2014-2016 Humberto Lourenço <betto@m3uzz.com>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/m3uzz/onionsrv
 */

namespace OnionSrv;
use OnionSrv\Debug;
use OnionSrv\Config;
use OnionLib\Util;

class Event
{

	/**
	 * 
	 * @param mixed $pmLine
	 * @param string $psFileName
	 * @param string $psSeparate
	 * @param boolean $pbForceLog
	 */
	public static function log ($pmLine, $psFileName = 'log', $psSeparate = "\t", $pbForceLog = false)
	{
		$laLogConf = Config::getOptions('log');

		$lsFileName = $laLogConf['folder'];
		
		if (!empty($psFileName))
		{
			$lsFileName .= DS . $psFileName;
		}
		else
		{
			$lsFileName .= DS . 'log';
		}
		
		if (Util::toBoolean($laLogConf['enable']) || $pbForceLog)
		{		
			$lsLine = $pmLine;
		
			if (is_array($pmLine))
			{
				$lsLine = implode ($psSeparate, $pmLine);
			}
		
			$lsLog = date('Y-m-d H:i:s', time()) . $psSeparate . $lsLine . "\n";
		
			if (file_exists($lsFileName))
			{
				self::saveFile($lsFileName, $lsLog, 'APPEND');
			}
			else
			{
				self::saveFile($lsFileName, $lsLog);
			}
		}
		else 
		{
			Debug::debug($pmLine);
		}
	}	

	
	/**
	 * 
	 * @param string $psFile
	 * @param number $pnChmod
	 * @param number $pnChown
	 * @param number $pnChown
	 */
	public static function setCHMOD ($psFile, $pnChmod = 0664, $pnChown = 'root', $pnChown = 'root')
	{
		$laLogConf = Config::getOptions('log');
		
		if (isset($laLogConf['chmod']))
		{
			$pnChmod = $laLogConf['chmod'];
		}
		
		if (isset($laLogConf['chown']))
		{
			$pnChown = $laLogConf['chown'];
		}
		
		if (isset($laLogConf['chgrp']))
		{
			$pnChgrp = $laLogConf['chgrp'];
		}
	
		chmod($psFile, $pnChmod);
		chown($psFile, $pnChown);
		chgrp($psFile, $pnChgrp);
	}
	
	
	/**
	 * 
	 * @param string $psFileName
	 * @param string $psContent
	 * @param string $psMod
	 * @throws \Exception
	 * @return boolean
	 */
	public static function saveFile ($psFileName, $psContent, $psMod = "NEW")
	{
		$lnMod = null;
	
		if ($psMod === "APPEND")
		{
			$lnMod = FILE_APPEND;
		}
	
		if (file_exists($psFileName))
		{
			self::setCHMOD($psFileName);
		}
		
		if ( file_put_contents($psFileName, $psContent, $lnMod))
		{
			self::setCHMOD($psFileName);
		}
		else
		{
			throw new \Exception("Failed when tring to write in $psFileName!");
		}
	
		return true;
	}	
}