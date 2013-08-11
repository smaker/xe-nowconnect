<?php
if(!class_exists('Crypt'))
{
	require_once(_XE_PATH_.'modules/nowconnect/libs/Crypt.php');
}
if(!class_exists('ApiBase'))
{
require_once(_XE_PATH_.'modules/nowconnect/libs/ApiBase.php');
}

if(!class_exists('CommunicatorBase'))
{
	require_once(_XE_PATH_.'modules/nowconnect/libs/Communicator.php');
}

/**
 * @class nowconnect
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 high class
 **/

class nowconnect extends ModuleObject
{
	/**
	 * 모듈 설치
	 */
	public function moduleInstall()
	{
		$oModuleController = getController('module');
		$oModuleController->insertTrigger('moduleObject.proc', 'nowconnect', 'controller', 'triggerAfterModuleProc', 'after');
		return new Object();
	}

	/**
	 * 모듈 삭제
	 */
	public function moduleUninstall()
	{
		return new Object();
	}

	/**
	 * 업데이트가 필요한지 확인
	 **/
	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		if(!$oModuleModel->getTrigger('moduleObject.proc', 'nowconnect', 'controller', 'triggerAfterModuleProc', 'after'))
		{
			return true;
		}

		return false;
	}

	/**
	 * 모듈 업데이트
	 **/
	function moduleUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = &getController('module');

		if(!$oModuleModel->getTrigger('moduleObject.proc', 'nowconnect', 'controller', 'triggerAfterModuleProc', 'after'))
		{
			$oModuleController->insertTrigger('moduleObject.proc', 'nowconnect', 'controller', 'triggerAfterModuleProc', 'after');
		}

		return new Object(0, 'success_updated');
	}

	/**
	 * 캐시 파일 재생성
	 **/
	function recompileCache()
	{
	}
}

/**
 * 액션값으로 현재 위치를 구합니다
 */
function _getLocationByAct($act)
{
	$locationList = Context::getLang('locationList');
	return $locationList[$act];
}

function getUserAgentInfo($user_agent = null)
{
	if(!$user_agent)
	{
		$user_agent = $_SERVER['USER_AGENT'];
	}

	$user_agent = strtolower($user_agent);
	if(strpos($user_agent, 'Mac OS X'))
	{
		$platform = 'Mac OS X';
	}
	if(strpos($user_agent, 'Windows 6.2'))
	{
		$platform = 'Windows 8';
	}
	elseif(strpos($user_agent, 'Windows 6.1'))
	{
		$platform = 'Windows 7';
	}
	elseif(strpos($user_agent, 'Windows 6.0'))
	{
		$platform = 'Windows Vista';
	}
	elseif(strpos($user_agent, 'Windows 5.2'))
	{
		$platform = 'Windows XP 64bit';
	}
	elseif(strpos($user_agent, 'Windows 5.1'))
	{
		$platform = 'Windows XP';
	}
	elseif(strpos($user_agent, 'Windows 5.0'))
	{
		$platform = 'Windows NT';
	}

	$info = array(
		'platform' => $platform,
		'browser' => $browser
	);
	return $info;
}

function isBotUser()
{
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	//$GooglebotIP = array('66.249.67.*', '66.249.71.0 - 66.249.71.206');

	$crawlers = array('Googlebot', 'Feedfetcher-Google', 'Yeti/1.0', 'Cowbot', 'NaverRobot', 'NAVER Blog Rssbot', 'Daumoa', 'DaumSearch validator', 'DAUM RSS Robot', 'bingbot', 'AhrefsBot', 'YandexBot', 'Ezooms', 'archive');
	foreach($crawlers as $key => $crawler)
	{
		if(strpos($user_agent, strtolower($crawler)) !== FALSE)
		{
			return true;
		}
	}

	return false;
}

if (!function_exists('http_build_query')) {
function http_build_query($data, $prefix='', $sep='', $key='') {
   $ret = array();
   foreach ((array)$data as $k => $v) {
       if (is_int($k) && $prefix != null) $k = urlencode($prefix . $k);
       if (!empty($key)) $k = $key.'['.urlencode($k).']';
       
       if (is_array($v) || is_object($v))
           array_push($ret, http_build_query($v, '', $sep, $k));
       else    array_push($ret, $k.'='.urlencode($v));
   }
 
   if (empty($sep)) $sep = ini_get('arg_separator.output');
   return implode($sep, $ret);
}}
/* End of file : nowconnect.class.php */
/* Location : ./modules/nowconnect/nowconnect.class.php */