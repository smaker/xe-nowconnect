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

		$exclude_ip_list = explode("\n", $nowconnect_info->exclude_ip);
		if(count($exclude_ip_list) > 0)
		{
			if(in_array($_SERVER['REMOTE_ADDR'], $exclude_ip_list))
			{
				return new Object();
			}
		}

		$member_srl = (int)$logged_info->member_srl;
		$nick_name = $logged_info->nick_name;
		$uid = session_id();

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
			'mid' => $mid,
			'member_srl' => $member_srl,
			'nick_name' => $nick_name,
			'uid' => $uid,
			'user-agent' => $user_agent,
			'is_admin' => $logged_info->is_admin,
			'isMobileDevice' => Mobile::isMobileCheckByAgent(),
			'isMobile' => Mobile::isFromMobilePhone(),
			'location' => array(
				'title' => $location,
				'uri' => $uri
			),
			'ipaddress' => $_SERVER['REMOTE_ADDR'],
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

		$oCrypt = new Crypt($options);

		$user_info = $oCrypt->encrypt(serialize($user_info));

		$params = array(
			'site_url' => $nowconnect_info->api_site_url,
			'user_info' => $user_info
		);

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase('json');
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/');

		// API 요청
		$output = $oCommunicator->post('api/tick', $params);

		return new Object();
	}
}

/* End of file : nowconnect.controller.php */
/* Location : ./modules/nowconnect/nowconnect.controller.php */