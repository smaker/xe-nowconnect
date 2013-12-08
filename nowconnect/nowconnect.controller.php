<?php
/**
 * @class nowconnectController
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 controller class
 **/

class nowconnectController extends nowconnect
{
	/**
	 * @brief 초기화
	 */
	function init()
	{
	}

	/**
	 * after_module_proc에 대응하는 트리거
	 */
	function triggerAfterModuleProc(&$oModule)
	{
		$module = $oModule->module;
		$module_info = $oModule->module_info;
		$act = $module_info->act;
		if(!$act) $act = $oModule->act;

		if(Context::getResponseMethod() != 'HTML' && !($act == 'dispNowconnect' && Context::getResponseMethod() == 'XMLRPC')) return new Object();

		$location = Context::getBrowserTitle();
		$locationByAct = _getLocationByAct($act);
		if(!$location)
		{
			$location = $module_info->browser_title;
		}

		if($locationByAct)
		{
			$location .= ' - ' . $locationByAct;
		}

		$logged_info = Context::get('logged_info');

		$oNowconnectModel = getModel('nowconnect');

		// 현재 접속자 모듈일 경우, DB에서 모듈 정보를 가져오지 않도록 합니다
		if($module_info->module == 'nowconnect')
		{
			$nowconnect_info = $module_info;
		}
		else
		{
			// 현재 접속자 모듈 정보를 DB에서 가져옵니다
			$nowconnect_info = $oNowconnectModel->getNowconnectInfo();
		}

		if(!$nowconnect_info->api_key)
		{
			return new Object();
		}

		if($nowconnect_info->nowconnect_target == 'member' && !$logged_info)
		{
			return new Object();
		}

		$ipaddress = $_SERVER['REMOTE_ADDR'];
		$exclude_ip_list = explode("\n", $nowconnect_info->exclude_ip);
		$count = count($exclude_ip_list);

		for($i=0;$i<$count;$i++)
		{
			$ip = str_replace('.', '\.', str_replace('*','(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)',$exclude_ip_list[$i]));
			if(preg_match('/^'.$ip.'$/', $ipaddress, $matches))
			{
				return new Object();
			}
		}

		$member_srl = (int)$logged_info->member_srl;
		$nick_name = $logged_info->nick_name;

		// 중복 접속자 처리
		if($nowconnect_info->include_duplicated_user == 'Y')
		{
			$uid = sha1(md5(session_id()));
		}
		else
		{
			$uid = sha1(md5($_SERVER['REMOTE_ADDR']));
		}

		Context::set('myUID', $uid);

		// 로그인하지 않은 경우 임의로 닉네임을 생성함
		if(!$nick_name)
		{
			$nick_name = '손님'.substr(sha1($uid),0, 5);
		}

		if(!$logged_info->is_admin)
		{
			$logged_info->is_admin = 'N';
		}

		$mid = $module_info->mid;
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		$uri = $_SERVER['REQUEST_URI'];
		
		if($act == 'dispNowconnect' && Context::getResponseMethod() == 'XMLRPC' && $_SESSION['NOWCONNECT_LOCATION_URL'])
		{
			$uri = $_SESSION['NOWCONNECT_LOCATION_URL'];
		}

		$_SESSION['NOWCONNECT_LOCATION_URL'] = $_SERVER['REQUEST_URI'];

		$user_info = array(
			'_id' => $uid,
			'mid' => $mid,
			'member_srl' => $member_srl,
			'nick_name' => $nick_name,
			'user-agent' => $user_agent,
			'is_admin' => $logged_info->is_admin,
			'isMobileDevice' => Mobile::isMobileCheckByAgent(),
			'isMobile' => Mobile::isFromMobilePhone(),
			'location' => array(
				'title' => $location,
				'uri' => $uri
			),
			'ipaddress' => $ipaddress,
			'extraOption' => array(
				'hide' => $_COOKIE['NCXE_HIDE_MODE']
			)
		);

		// 암호화 옵션
		$options = array(
			'key'		=>	$nowconnect_info->api_key,
			'mode'		=>	'ecb',
			'algorithm'	=>	'blowfish',
			'base64'	=>	true
		);

		try
		{
			$oCrypt = new Crypt($options);
		}
		catch(Exception $e)
		{
			return new Object();
		}

		$user_info = $oCrypt->encrypt(serialize($user_info));

		if($act == 'dispNowconnect' && Context::getResponseMethod() == 'XMLRPC')
		{
			$realtime = TRUE;
		}
		$params = array(
			'realtime' => $realtime,
			'user_info' => $user_info
		);

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase('json');
		$oCommunicator->setApiKey($nowconnect_info->api_key);
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/api/');

		// API 요청
		$oCommunicator->post('tick')->param($params)->send();

		return new Object();
	}
}

/* End of file : nowconnect.controller.php */
/* Location : ./modules/nowconnect/nowconnect.controller.php */