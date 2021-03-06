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

defined('DS') 			|| define('DS', DIRECTORY_SEPARATOR);
defined('PS')  			|| define('PS', PATH_SEPARATOR);
defined('EOF') 			|| define('EOF', chr(13).chr(10));

defined('BASE_DIR') 	|| define('BASE_DIR', realpath(dirname(dirname(dirname(__DIR__)))));
defined('CLIENT_DIR') 	|| define('CLIENT_DIR', BASE_DIR . DS . 'client');
defined('VENDOR_DIR') 	|| define('VENDOR_DIR', BASE_DIR . DS . 'vendor');
defined('CONFIG_DIR') 	|| define('CONFIG_DIR', CLIENT_DIR . DS . 'config');
defined('DATA_DIR') 	|| define('DATA_DIR', CLIENT_DIR . DS . 'data');

// Composer autoloading
if (file_exists(VENDOR_DIR . DS . 'autoload.php')) {
	$goLoader = include VENDOR_DIR . DS . 'autoload.php';
}

$gbPhpError = false;
$gbDebug = false;
$gbTime = false;
$gbHelp = false;
$gbTest = false;
$gbPrompt = false;
$gaTimer = array();

if (PHP_SAPI == "cli" && isset($_SERVER['argv']) && is_array($_SERVER['argv']))
{
	foreach ($_SERVER['argv'] as $gsArg)
	{
		if ($gsArg === '--error' || $gsArg === '-e')
		{
			$gbPhpError = true;
		}
		
		if ($gsArg === '--debug' || $gsArg === '-d')
		{
			$gbDebug = true;
			$gbTime = true;
		}

		if ($gsArg === '--time' || $gsArg === '-t')
		{
			$gbTime = true;
		}
		
		if ($gsArg === '--help' || $gsArg === '-h')
		{
			$gbHelp = true;
		}
		
		if ($gsArg === '--test' || $gsArg === '-T')
		{
			$gbTest = true;
		}
		
		if ($gsArg === '--prompt' || $gsArg === '-p')
		{
			$gbPrompt = true;
		}
	}
}
else
{
	if (isset($_GET['error']))
	{
		$gbPhpError = true;
	}
	
	if (isset($_GET['debug']))
	{
		$gbDebug = true;
		$gbTime = true;
	}
	
	if (isset($_GET['time']))
	{
		$gbTime = true;
	}

	if (isset($_GET['test']))
	{
		$gbTest = true;
	}
}

use OnionSrv\Config;

Config::setDebugMod($gbDebug, $gbPhpError, $gbTime, $gbTest, $gbPrompt);
Config::setTimeZone();

OnionSrv\Autoload::autoload($goLoader);

use OnionSrv\Debug;

Debug::debugTimeStart("init");

if (PHP_SAPI == "cli")
{
	$goService = new OnionSrv\LineService();
	$goService->serviceRoute();
}
else
{
	if (OnionSrv\Access::hasAccess())
	{
		$goService = new OnionSrv\HttpService();
		$goService->serviceRoute();
	}
	else
	{
		header('HTTP/1.1 403 Forbidden');
		exit(403);
	}
}

Debug::debugTimeEnd("init");

Debug::debug($gaTimer, TIMESHOW);