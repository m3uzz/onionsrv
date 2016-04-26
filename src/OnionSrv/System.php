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
use OnionSrv\Event;
use OnionSrv\Debug;
use OnionSrv\Config;

class System
{
	
	/**
	 * 
	 * @param string $psQuestion
	 * @return string
	 */
	public static function prompt ($psQuestion)
	{
		echo "\e[34m" . $psQuestion . "\e[0m ";
		
		$lrRandle = fopen("php://stdin", "r");
		$lsAnswer = fgets($lrRandle);
		fclose($lrRandle);
		
		return trim($lsAnswer);
	}

	
	/**
	 * Manipulate a URI to return an URI parsed array:
	 *
	 * @param string $psUri        	
	 * @return array [scheme]
	 *         [host]
	 *         [port]
	 *         [user]
	 *         [pass]
	 *         [path]
	 *         [query]
	 *         [fragment]
	 *         [dirname]
	 *         [basename]
	 *         [extension]
	 *         [filename]
	 *         [first]
	 *         [last]
	 *         [params]
	 */
	public static function parseUri ($psUri)
	{
		$laUri = parse_url($psUri);
		
		$laUri = array_merge($laUri, pathinfo($laUri['path']));
		
		$laDirs = explode("/", $laUri['dirname']);
		
		$laUri['first'] = $laDirs[1];
		$laUri['last'] = array_pop($laDirs);
		
		$laUri['params'] = (isset($laUri['query']) ? explode("&", $laUri['query']) : "");
		
		$laUri['extension'] = (isset($laUri['extension'])) ? strtolower($laUri['extension']) : "";
		
		return $laUri;
	}

	
	/**
	 *
	 * @param string $psExtension        	
	 * @return string
	 */
	public static function setHeader ($psExtension)
	{
		// Setando o mime type para o cabeçalho do arquivo
		switch ($psExtension)
		{
			case "json":
				$lsHeader = "application/json";
				break;
			case "css":
				$lsHeader = "text/css";
				break;
			case "js":
				$lsHeader = "application/x-javascript";
				break;
			case "jpeg":
			case "jpg":
			case "gif":
			case "png":
			case "ico":
			case "svg":
				$lsHeader = "image/" . $psExtension;
				break;
			case "swf":
				$lsHeader = "application/x-shockwave-flash";
				break;
			case "pdf":
				$lsHeader = "application/pdf";
				break;
			case "txt":
				$lsHeader = "text/plain";
				break;
			case "mp3":
			case "oge":
			case "wma":
				$lsHeader = "audio/" . $psExtension;
				break;
			case "wmv":
			case "flv":
			case "mpeg":
			case "mp4":
			case "avi":
				$lsHeader = "video/" . $psExtension;
				break;
			case "otf":
				$lsHeader = "application/x-font-opentype";
				break;
			case "eot":
				$lsHeader = "application/vnd.ms-fontobject";
				break;
			case "svg":
				$lsHeader = "image/svg+xml";
				break;
			case "ttf":
				$lsHeader = "application/x-font-ttf";
				break;
			case "woff":
				$lsHeader = "application/font-woff";
				break;
			case "gz":
				$lsHeader = "application/x-gzip";
				break;				
			default:
				return true;
		}
		
		return $lsHeader;
	}

	
	/**
	 *
	 * @param string $psDir        	
	 * @return string
	 */
	public static function setBaseDir ($psDir)
	{
		// Verificando o diretório base para determinar o caminho do arquivo
		switch ($psDir)
		{
			case "data":
			case "download":
			case "layout":
				$lsBase = CLIENT_DIR;
				break;
			case "backend":
				$lsBase = 'layout';
				break;
			case "frontend":
				$lsBase = 'layout';
				break;
			case "exception":
				$lsBase = 'layout';
				break;
			case "commons":
			default:
				$lsBase = 'layout';
		}
		
		return $lsBase;
	}

	
	/**
	 * Route a external file as css, js, json, imgs, etc to be loaded by php script.
	 * It means that the file don't need to be in a public dir.
	 *
	 * @param string $psPragma        	
	 * @return boolean
	 */
	public static function fileRoute ($psPragma = null)
	{
		// Check if REQUEST_URI is setted and not a root dir.
		if (isset($_SERVER['REQUEST_URI']) && ! empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "/")
		{
			$laUri = self::parseUri($_SERVER['REQUEST_URI']);
			
			// If the extension is diferent of .php, .phtml or empty.
			// It is a external file and need to be treated and routed.
			if ($laUri['extension'] !== "php" && $laUri['extension'] !== "phtml" && ! empty($laUri['extension']))
			{
				$lsHeader = self::setHeader($laUri['extension']);
				
				$lsBase = self::setBaseDir($laUri['first']);
				
				if ($laUri['basename'] === "onion-app.json")
				{
					self::loadAppJson();
				}
				elseif ($laUri['first'] === "download")
				{
					self::downloadFile($lsBase . $laUri['path'], $lsHeader, $psPragma);
				}
				elseif ($laUri['first'] === "data" || ! DIRECT_ASSETS)
				{
					self::getFile($lsBase . $laUri['path'], $lsHeader, $psPragma);
				}
				else
				{
					return false;
				}
				
				return true;
			}
		}
		
		return false;
	}

	
	
	/**
	 */
	public static function loadAppJson ()
	{
		$laOnionApp = Onion\Register::getApplicationArray();
		//Debug::displayD($gaOnionApp, true);
		header('Content-type: application/json');
		echo Json::encode($gaOnionApp);
		
		exit(200);
	}

	
	/**
	 * 
	 * @return string
	 */
	public static function getPutData ()
	{
		$lsData = "";
		
		$lrFp = fopen('php://input', 'r');
		
		if ($lrFp)
		{
			while(!feof($lrFp))
			{
				$lsData .= fgets($lrFp, 1024);
			}				
		}
		
		fclose($lrFp);
		
		return $lsData;
	}
	
	
	/**
	 * Metodo de carregamento de arquivo local
	 *
	 * @param string $psLk        	
	 * @return string
	 */
	public static function localRequest ($psFilePath)
	{
		// Garantido a separação de diretorio correta para o SO
		$psFilePath = preg_replace(array(
			"/\//",
			"/\\\/"
		), DS, $psFilePath);
		
		if (file_exists($psFilePath))
		{
			// Se o arquivo existir no servidor local ele é carregado
			$lsRetorno = file_get_contents($psFilePath);
			
			if (! $lsRetorno)
			{
				// Caso ocorra algum erro na leitura do arquivo é gerado um log
				// de erro;
				Event::log(array(
					"Read file error! (" . $psFilePath . ")"
				), 'error');
				return false;
			}
		}
		else
		{
			// Caso o arquivo não exista é gerado um log de erro
			Event::log(array(
				"File not found! (" . $psFilePath . ")"
			), 'error');
			return false;
		}
		
		// Retornando o conteúdo do arquivo
		return $lsRetorno;
	}

	
	/**
	 * getFile: Carrega um arquivo js, css, img, vídeo, audio ...
	 *
	 * @param string $psFile caminho do arquivo
	 * @param string $psContentType        	
	 * @param string $psPragma        	
	 *
	 * @return void
	 */
	public static function getFile ($psFile, $psContentType, $psPragma)
	{
		$lsContent = self::localRequest($psFile);
		
		if ($lsContent)
		{
			header('Content-type: ' . $psContentType);
			
			if (strtolower($psPragma) === 'public')
			{
				header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($psFile)) . " GMT");
				header("Expires: " . gmdate("D, d M Y H:i:s", (filemtime($psFile) + 31536000)) . " GMT");
				header("Cache-Control: maxage=31536000");
				$hash = md5($lsContent);
				header("ETag: \"{$hash}\"");
			}
			else
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Last-Modified: " . date('D, d M Y H:i:s') . " GMT");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				header("Pragma: no-cache");
			}
			
			header("Content-Length: " . filesize($psFile));
			
			echo $lsContent;
			
			exit(200);
		}
		else
		{
			header("HTTP/1.0 404 Not Found");
			exit(404);
		}
	}

	
	/**
	 * 
	 * @param string $psFile
	 * @param string $psHeader
	 * @param string $psPragma
	 */
	public static function downloadFile ($psFile, $psHeader, $psPragma)
	{
		// TODO: Implementar downloadFile
		if (self::isAuth())
		{
			self::getFile($psFile, $psContentType, $psPragma);
		}
	}

	
	/**
	 * 
	 * @param string $psFile
	 * @return array
	 */
	public static function subHash ($psFile)
	{
		$laDirLevel['1'] = substr($psFile, 0, 1);
		$laDirLevel['2'] = substr($psFile, 1, 1);
		$laDirLevel['3'] = substr($psFile, 2, 2);
		
		preg_match("/^([a-z0-9]+)/", $psFile, $laFile);
		
		if (isset($laFile[0]))
		{
			$laDirLevel['4'] = $laFile[0];
		}
		if (isset($laDirLevel['4']))
		{
			$laDirLevel['4'] = substr($laDirLevel['4'], 4);
		}
		
		return $laDirLevel;
	}

	
	/**
	 * 
	 * @param string $psFile
	 * @param int $pnChmod
	 * @param string $pnChown
	 * @param string $pnChown
	 */
	public static function setCHMOD ($psFile, $pnChmod = null, $psChown = null, $psChgrp = null)
	{
		$laLogConf = Config::getOptions('system');
		
		if ($pnChmod === null && isset($laLogConf['chmod']))
		{
			$pnChmod = $laLogConf['chmod'];
		}
		
		if ($psChown === null && isset($laLogConf['chown']))
		{
			$psChown = $laLogConf['chown'];
		}
		
		if ($psChgrp === null && isset($laLogConf['chgrp']))
		{
			$psChgrp = $laLogConf['chgrp'];
		}
	
		Debug::debug("chmod {$pnChmod} {$psFile}");
		@chmod($psFile, $pnChmod);
		Debug::debug("chown {$psChown} {$psFile}");
		@chown($psFile, $psChown);
		Debug::debug("chgrp {$psChgrp} {$psFile}");
		@chgrp($psFile, $psChgrp);
	}

	
	/**
	 * 
	 * @param string $psPath
	 * @param int $pnChmod
	 * @param string $pnChown
	 * @param string $pnChown
	 */
	public static function createDir ($psPath, $pnChmod = null, $psChown = null, $psChgrp = null)
	{
		if (!file_exists($psPath))
		{
			mkdir($psPath);
			
			self::setCHMOD($psPath, $pnChmod, $psChown, $psChgrp);
		}
	}
	
	
	/**
	 * createDateDir: parse uma data e devolve o caminho para o arquivo
	 *
	 * @param string $psPath
	 * @param string $psDate
	 *
	 * @return string
	 */
	public static function createDateDir ($psPath, $psDate)
	{
		if (isset($psPath) && isset($psDate))
		{
			$psDate = preg_replace("/-| |:/", "/", $psDate);
			$laDirLevel = explode("/", $psDate);
				
			foreach ($laDirLevel as $lsDir)
			{
				$psPath .= DS . $lsDir;
	
				if (! file_exists($psPath))
				{
					Debug::debug("mkdir {$psPath}");
					mkdir($psPath);
						
					self::setCHMOD($psPath);
				}
			}
				
			return $psPath;
		}
	}
	
	
	/**
	 * createBalancedDir: parse no arquivo hash e devolve o caminho para o arquivo
	 *
	 * @param string $psPath        	
	 * @param string $psFile        	
	 *
	 * @return string
	 */
	public static function createBalancedDir ($psPath, $psFile)
	{
		if (isset($psPath) && isset($psFile))
		{
			$laDirLevel = self::subHash($psFile);
			
			foreach ($laDirLevel as $lsDir)
			{
				$psPath .= DS . $lsDir;
				
				if (! file_exists($psPath))
				{
					mkdir($psPath);
					
					self::setCHMOD($psPath);
				}
			}
			
			return $psPath;
		}
	}

	
	/**
	 * getBalancedDir: parse no arquivo hash e devolve o caminho para o arquivo
	 *
	 * @param string $psPath        	
	 * @param string $psFile        	
	 *
	 * @return boolean string
	 */
	public static function getBalancedDir ($psFile, $psPath = UPLOAD_RPATH_IMG) // TODO:
	                                                                            // Definir
	                                                                            // UPLOAD_RPATH_IMG
	{
		$laDirLevel = self::subHash($psFile);
		
		$lsPath = $psPath;
		
		$lsRealPath = realpath(CLIENT_DIR . DS . $lsPath);
		
		foreach ($laDirLevel as $lsDir)
		{
			$lsPath .= DS . $lsDir;
			$lsRealPath .= DS . $lsDir;
			
			if (! file_exists($lsRealPath))
			{
				return false;
			}
		}
		
		return $lsPath . DS;
	}

	
	/**
	 *
	 * @param string $psFile        	
	 * @param string $psPath        	
	 */
	public static function rmBalancedFile ($psFile, $psPath)
	{
		$lsFileDir = self::getBalancedDir($psFile, $psPath);
		$lsFileDir = realpath(CLIENT_DIR . DS . $lsFileDir);
		
		if (file_exists($lsFileDir . DS . $psFile))
		{
			Debug::debug('rm ' . $lsFileDir . DS . $psFile);
			self::removeFile($lsFileDir . DS . $psFile);
		}
		else
		{
			Debug::debug($lsFileDir . $psFile . ' not found.');
		}
	}

	
	/**
	 * Shell command
	 *
	 * @param string $psCommand	string a ser executada no sistema
	 * @param string $psCommand2        	
	 * @return array
	 */
	public static function execute ($psCommand, $psCommand2 = "")
	{
		$laOutput = array();
		$lnReturn = 0;
		
		$lsReturn = exec($psCommand . " 2>&1 " . $psCommand2, $laOutput, $lnReturn);
		
		if ($lnReturn != 0)
		{
			Event::log("FILESYSTEM: " . $lsReturn, 'execute', 'error');
		}
		
		return $laOutput;
	}

	
	/**
	 *
	 * @param string $psTempName        	
	 * @param string $psDestini        	
	 * @return boolean
	 */
	public static function moveUploadedFile ($psTempName, $psDestini)
	{
		$lbOk = true;
		$laPath = pathinfo($psDestini);
		
		$laDir = explode("/", $psDestini);
		
		if (empty($laDir[0]))
		{
			unset($laDir[0]);
			$laDir[1] = "/" . $laDir[1];
		}
		
		$lnCountArray = count($laDir);
		$lsFile = $laDir[$lnCountArray - 1];
		unset($laDir[$lnCountArray - 1]);
		
		foreach ($laDir as $lsDir)
		{
			$lsDirx .= $lsDir;
			
			if (strcmp($lsDirx, "..") != 0)
			{
				if (! is_dir($lsDirx))
				{
					mkdir($lsDirx);
				}
			}
			
			$lsDirx .= "/";
		}
		
		if (! move_uploaded_file($psTempName, $lsDirx . $lsFile))
		{
			$lbOk = false;
		}
		else
		{
			self::setCHMOD($lsDirx . $lsFile);
		}
		
		return $lbOk;
	}

	
	/**
	 * Cria link simbolico no sistema
	 *
	 * @param string $psOrigem        	
	 * @param string $psLink        	
	 * @return void
	 */
	public static function simblink ($psOrigem, $psLink)
	{
		if (file_exists($psOrigem))
		{
			Debug::debug("ln -s {$psOrigem} {$psLink}");
			self::execute("ln -s {$psOrigem} {$psLink}");
		}
		else
		{
			Debug::debug($psOrigem . ' not found.');
		}
	}

	
	/**
	 * Create or update a new file in system
	 *
	 * @param string $psFileName path and file name
	 * @param string $psContent
	 * @param string $psMod file save mod (APPEND, NEW)
	 * @return void
	 */
	public static function saveFile ($psFileName, $psContent, $psMod = "NEW")
	{
		$lnMod = null;
		
		if ($psMod === "APPEND")
		{
			$lnMod = FILE_APPEND;
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

	
	/**
	 *
	 * @param string $psFileNome path and file name to remove
	 * @return boolean
	 */
	public static function removeFile ($psFileNome)
	{
		if (is_file($psFileNome))
		{
			if (!unlink($psFileNome))
			{
				throw new \Exception("Failed when tring to remove file $psFileNome from the system!");
			}
		}
		else
		{
			Debug::debug($psFileNome . ' not found.');
		}
		
		return true;
	}

	
	/**
	 *
	 * @param string $psPath
	 */
	public static function removeDir ($psPath)
	{
		if (is_dir($psPath))
		{
			Debug::debug('rm -rf ' . $psPath);
			self::execute("rm -rf $psPath");
		}
		else
		{
			Debug::debug($psPath . ' not found.');
		}
	}
	
	
	/**
	 *
	 * @param string $psFile        	
	 */
	public static function compact ($psFile)
	{
		if (file_exists($psFile))
		{
			Debug::debug('gzip -f ' . $psFile);
			self::execute("gzip -f $psFile");
		}
		else
		{
			Debug::debug($psFile . ' not found.');
		}
	}
	
	
	/**
	 * 
	 * @param string $psPath
	 * @param string $psName
	 */
	public static function tar ($psPath)
	{
		if (file_exists($psPath))
		{
			$lsPath = strstr("/", $psPath, true);
			$lsDir = strstr("/", $psParh);
			Debug::debug('tar -cf ' . $psPath);
			self::execute("cd {$lsPath}; tar -cf {$psPath}.tar .{$lsDir}");
		}
		else
		{
			Debug::debug($psPath . ' not found.');
		}
	}
	
	
	/**
	 *
	 * @param string $psPath
	 * @param string $psName
	 */
	public static function tarGz ($psPath, $psDir)
	{
		if (file_exists($psPath))
		{
			Debug::debug("cd {$psPath}; tar -czf {$psDir}.tar.gz {$psDir}");
			self::execute("cd {$psPath}; tar -czf {$psDir}.tar.gz {$psDir}");
		}
		else
		{
			Debug::debug($psPath . ' not found.');
		}
	}
	
	
	/**
	 * 
	 * @param array $paArray
	 * @return string
	 */
	public static function arrayToFile ($paArray)
	{
		return "<?php\nreturn array(\n" . self::arrayToString($paArray) . ");";
	}
	
	
	/**
	 * 
	 * @param array $paArray
	 * @param string $psIdentation
	 * @throws Exceptin
	 * @return string
	 */
	public static function arrayToString ($paArray, $psIdentation = "\t")
	{
		if (is_array($paArray))
		{
			$lsString = "";
			
			foreach ($paArray as $lsKey => $lmValue)
			{
				if (is_array($lmValue))
				{
					$lsString .= $psIdentation . "'$lsKey' => array(\n" . self::arrayToString($lmValue, $psIdentation . "\t") . $psIdentation ."),\n";
				}
				elseif (is_bool($lmValue))
				{
					$lsString .= $psIdentation . "'$lsKey' => " . ($lmValue ? 'true' : 'false') . ",\n";
				}
				else 
				{
					if (preg_match("/\/\/array/", $lmValue))
					{
						$lsString .= $psIdentation . "'$lsKey' => " . substr($lmValue, 2) . ",\n";
					}
					else 
					{
						$lsString .= $psIdentation . "'$lsKey' => '$lmValue',\n";
					}
				}
			}
			
			return $lsString;
		}
		else 
		{
			throw new Exceptin("The param value should be an array!");
		}
	}
}