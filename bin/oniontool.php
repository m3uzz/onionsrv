#!/usr/bin/env php
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
defined('BASE_DIR') 	|| define('BASE_DIR', realpath(dirname(__DIR__)));
defined('VENDOR_DIR') 	|| define('VENDOR_DIR', BASE_DIR . DS . 'vendor');
defined('CLIENT_DIR') 	|| define('CLIENT_DIR', BASE_DIR . DS . 'client');
defined('MODULE_DIR') 	|| define('MODULE_DIR', VENDOR_DIR . DS . 'm3uzz');
defined('CONFIG_DIR') 	|| define('CONFIG_DIR', MODULE_DIR . DS . 'onionsrv' . DS . 'src' . DS . 'OnionSrvTool' . DS . 'Config');
defined('LOG_DIR') 		|| define('LOG_DIR', BASE_DIR . DS . 'temp');

$_SERVER['argv'][] = "--m=onionSrvTool";

include VENDOR_DIR . DS . 'm3uzz' . DS . 'onionsrv' . DS . 'onionInit.php';