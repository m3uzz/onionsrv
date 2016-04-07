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
use OnionSrv\System;
use OnionSrv\Config;
use OnionSrv\Event;

class Access
{
	public static function hasAccess ()
	{
		$lsIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
		$lsClient = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
		$lsToken = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : "";
		$lbReturn = false;
		$lsStatus = "DENIED";
		
		Debug::debug(array($lsIp, $lsClient, $lsToken));
		
		$laAccess = Config::getOptions('access');
		Debug::debug($laAccess);

		if (isset($laAccess[$lsIp]))
		{
			Debug::debug('1');
			
			if (isset($laAccess[$lsIp]['user-agent'][$lsClient]))
			{
				Debug::debug('1.1');
				
				if ($laAccess[$lsIp]['user-agent'][$lsClient] == $lsToken)
				{
					Debug::debug('1.1.1');
					$lbReturn = true;
				}
			}
			elseif (isset($laAccess[$lsIp]['user-agent']['*']))
			{
				Debug::debug('1.2');
				
				if ($laAccess[$lsIp]['user-agent']['*'] == $lsToken)
				{
					Debug::debug('1.2.1');
					$lbReturn = true;
				}
			}
		}
		elseif (isset($laAccess['*']))
		{
			Debug::debug('2');
			
			if (isset($laAccess['*']['user-agent'][$lsClient]))
			{
				Debug::debug('2.1');
				
				if ($laAccess['*']['user-agent'][$lsClient] == $lsToken)
				{
					Debug::debug('2.1.1');
					$lbReturn = true;
				}
			}
			elseif (isset($laAccess['*']['user-agent']['*']))
			{
				Debug::debug('2.2');
				
				if ($laAccess['*']['user-agent']['*'] == $lsToken)
				{
					Debug::debug('2.2.2');
					$lbReturn = true;
				}
			}
		}
		
		if ($lbReturn)
		{
			$lsStatus = "PERMITED";
		}

		Event::log(array("ip:[{$lsIp}]", "user-agent:[{$lsClient}]", "token:[{$lsToken}]", "status:[{$lsStatus}]"), 'access');
		
		return $lbReturn;
	}
}