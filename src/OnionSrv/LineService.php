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
use OnionSrv\Debug;
use OnionSrv\System;
use OnionSrv\Autoload;
use OnionSrv\Help;

class LineService
{
	/**
	 * 
	 * @param string $psService
	 * @param string $psClass
	 * @param string $psMethod
	 * @param array $paParams
	 * @return string
	 */
	private static function run (array $paService, array $paParams)
	{
		global $gbHelp;
		
		//Verificando se o service existe
		if (file_exists($paService['service']))
		{
			//Carregando o service
			include_once ($paService['service']);
			
			//Verificando se a classe existe
			if (class_exists($paService['class']))
			{
				//Criando o objeto da classe
				$loObj = new $paService['class']($paParams, $paService);
				
				if (is_object($loObj) && $loObj->_bHelp)
				{
					return;
				}
				
				//Verificando se o objeto foi criado e se o metodo existe
				if (is_object($loObj) && method_exists($loObj, $paService['method']))
				{
					//Executando o metodo
					$loObj->$paService['method']($paParams);
				}
				else
				{
					//Se o metodo não foi encontrado
					Debug::exitError("Method not found!");
				}	
			}
			else
			{
				//Se a classe não foi encontrada
				Debug::exitError("Class not found!");
			}
		}
		else
		{
			$loHelp = new Help;
			$loHelp->factory(dirname(dirname(dirname(dirname($paService['service'])))) . DS . 'config');
			$loHelp->setModuleHelp($loHelp->getModuleHelp($paService['module']));
			$loHelp->display();
		}
	}
		
	
	/**
	 * 
	 */
	public static function serviceRoute ()
	{
		global $goLoader;
		
		Debug::debug($_SERVER['argv']);

		if (isset($_SERVER['argv']) && is_array($_SERVER['argv']))
		{
			$lsModule = '';
			$lsController = '';
			$lsAction = '';
			$laParams = array();
			
			foreach ($_SERVER['argv'] as $lsArg)
			{
				$laArg = explode("=", $lsArg);
				
				switch ($laArg[0])
				{
					case '-m':
						$lsModule = $laArg[1];
					break;
					case '-c':
						$lsController = $laArg[1];
					break;
					case '-a':
						$lsAction = $laArg[1];
					break;
					default:
						if (isset($laArg[1]))
						{
							$laParams['ARG'][$laArg[0]] = $laArg[1];
						}
					break;									
				}				
			}			
			
			if (empty($lsController))
			{
				$lsController = $lsModule;
			}
			
			if (empty($lsAction))
			{
				$lsAction = $lsController;
			}
				
			$lsPath = Autoload::getNamespace(ucfirst($lsModule), $goLoader);

			$lsService = $lsPath . DS . ucfirst($lsModule) . DS . 'Controller' . DS .  ucfirst($lsController) . "Controller.php";
			$lsClass = '\\' . ucfirst($lsModule) . '\\' . 'Controller' . '\\' .  ucfirst($lsController) . "Controller";
				
			if (TESTMOD)
			{
				$lsMethod = $lsAction . 'Test';
			}
			else 
			{
				$lsMethod = $lsAction . 'Action';
			}
			
			$laService['service'] = $lsService;
			$laService['class'] = $lsClass;
			$laService['method'] = $lsMethod;
			$laService['module'] = ucfirst($lsModule);
			$laService['controller'] = ucfirst($lsController);
			$laService['action'] = $lsAction;
				
			Debug::debug($laService);
			Debug::debug($laParams);

			self::run($laService, $laParams);
		}
		else 
		{
			//Se o service não foi encontrado
			Debug::exitError("Params not found!");
		}
	}	
}