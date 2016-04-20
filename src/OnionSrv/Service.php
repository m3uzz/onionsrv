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

class Service
{
	/**
	 * 
	 * @param string $psService
	 * @param string $psClass
	 * @param string $psMethod
	 * @param array $paParams
	 * @return string
	 */
	private static function run(array $paService, array $paParams)
	{
		//Verificando se o service existe
		if(file_exists($paService['service']))
		{
			//Carregando o service
			include_once($paService['service']);
			
			//Verificando se a classe existe
			if(class_exists($paService['class']))
			{
				//Criando o objeto da classe
				$loObj = new $paService['class']($paParams, $paService);

				//Verificando se o objeto foi criado e se o metodo existe
				if(is_object($loObj) && method_exists($loObj, $paService['method']))
				{
					//Executando o metodo
					$loObj->$paService['method']($paParams);
				}
				else
				{
					//Se o metodo não foi encontrado, retornar 404 not found para o client
					if (DEBUG)
					{
						Debug::debug("Method Not Found (" . $paService['action'] . ")");	
					}
					else 
					{
						header("HTTP/1.1 404 Method Not Found (" . $paService['action'] . ")");
					}
					
					exit(404);	
				}	
			}
			else
			{
				//Se a classe não foi encontrada, retornar 404 not found para o client
				if (DEBUG)
				{
					Debug::debug("Class Not Found (" . $paService['controller'] . ")");
				}
				else
				{
					header("HTTP/1.1 404 Class Not Found (" . $paService['controller'] . ")");
				}
				
				exit(404);
			}
		}
		else
		{
			//Se o service não foi encontrado, retornar 404 not found para o client
			if (DEBUG)
			{
				Debug::debug("Service Not Found (" . $paService['module'] . ")");
			}
			else
			{
				header("HTTP/1.1 404 Service Not Found (" . $paService['module'] . ")");
			}
			
			exit(404);
		}
	}
		
	public static function serviceRoute()
	{
		global $goLoader;
		
		Debug::debug($_SERVER['REQUEST_URI']);
		
		if(isset($_SERVER['REQUEST_URI']))
		{
			$laParams = null;
			
			if(isset($_GET))
			{
				$laParams['GET'] = $_GET;
			}
			
			if(isset($_POST))
			{
				$laParams['POST'] = $_POST;
			}

			if(isset($_SERVER['Data-type']) && $_SERVER['Data-type'] == 'json' && isset($_SERVER['data']))
			{
				$laParams['JSON'] = $_SERVER['data'];
			}
				
			if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT')
			{
				$laParams['PUT'] = System::getPutData();
			}
						
			if(isset($_FILES))
			{
				$laParams['FILES'] = $_FILES;
			}
			
			//Separando o caminho e verificando quantos elementos tem
			$laRequestUri = explode("/", $_SERVER['REQUEST_URI']);
			
			$lnCount = 0;
			$lsQueryString = "?" . $_SERVER['QUERY_STRING'];
			
			//Removendo elementos vazios
			foreach($laRequestUri as $lsPathUrl)
			{
				if(!empty($lsPathUrl) && $lsPathUrl != $lsQueryString)
				{
					$laPathUrl = explode('-', $lsPathUrl);
					$lsValueName = $laPathUrl[0];
					unset($laPathUrl[0]);
						
					if (count($laPathUrl) > 0)
					{
						foreach ($laPathUrl as $lsValue)
						{
							$lsValueName .= ucfirst($lsValue);
						}
					}
					
					$laPath[] = $lsValueName;
					$lnCount++;
				}
			}
			
			if ($lnCount == 0)
			{
				$laPath[0] = 'index';
				$lnCount = 1;
			}
			
			$laService['module'] = ucfirst($laPath[0]);
			
			switch ($lnCount)
			{
				case 1:
					$lsPath = Autoload::getNamespace($laService['module'], $goLoader);
					$lsService = $lsPath . DS . $laService['module'] . DS . 'controller' . DS . $laService['module'] . "Controller.php";
					$lsClass = '\\' . $laService['module'] . '\\Controller\\' . $laService['module'] . 'Controller';
					
					$laService['controller'] = $laService['module'];
					$laService['action'] = $laPath[0];
				break;
				case 2:
					$lsPath = Autoload::getNamespace($laService['module'], $goLoader);
					$lsService = $lsPath . DS . $laService['module'] . DS . 'controller' . DS . $laService['module'] . "Controller.php";
					$lsClass = '\\' . $laService['module'] . '\\Controller\\' . $laService['module'] . 'Controller';
					
					$laService['controller'] = $laService['module'];
					$laService['action'] = $laPath[1];
					break;
				default:
					$lsPath = Autoload::getNamespace($laService['module'], $goLoader);
					$lsService = $lsPath . DS . $laService['module'] . DS . 'controller' . DS .  ucfirst($laPath[1]) . "Controller.php";
					$lsClass = '\\' . $laService['module'] . '\\' . 'Controller' . '\\' .  ucfirst($laPath[1]) . "Controller";
					
					$laService['controller'] = ucfirst($laPath[1]);
					$laService['action'] = $laPath[2];
			}
			
			if (TESTMOD)
			{
				$lsMethod = $laService['action'] . 'Test';
			}
			else
			{
				$lsMethod = $laService['action'] . 'Action';
			}
				
			$laService['service'] = $lsService;
			$laService['class'] = $lsClass;
			$laService['method'] = $lsMethod;
			
			Debug::debug($laService);
			Debug::debug($laParams);

			self::run($laService, $laParams);
		}
		else 
		{
			//Se o service não foi encontrado, retornar 404 not found para o client
			if (DEBUG)
			{
				Debug::debug("Service Not Found");
			}
			else
			{
				header("HTTP/1.1 404 Service Not Found");
			}
			
			exit(404);
		}
	}
}