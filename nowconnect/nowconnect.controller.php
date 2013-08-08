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
		if(Context::getResponseMethod() != 'HTML') return new Object();

		$module = $oModule->module;
		$module_info = $oModule->module_info;
		$act = $module_info->act;
		if(!$act) $act = $oModule->act;

		$location = Context::getBrowserTitle();
		if(!$location || ($module != $module_info->module))
		{
			$location = $this->_getLocationByAct($act);
		}

		$logged_info = Context::get('logged_info');

		$oNowconnectModel = getModel('nowconnect');
		$nowconnect_info = $oNowconnectModel->getNowconnectInfo();

		if(!$nowconnect_info->api_key)
		{
			return new Object();
		}

		$member_srl = (int)$logged_info->member_srl;
		$nick_name = $logged_info->nick_name;
		$uid = session_id();

		// 로그인하지 않은 경우 임의로 닉네임을 발급함
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

		$user_info = array(
			'mid' => $mid,
			'member_srl' => $member_srl,
			'nick_name' => $nick_name,
			'uid' => $uid,
			'user-agent' => $user_agent,
			'is_admin' => $logged_info->is_admin,
			'location' => array(
				'title' => $location,
				'uri' => $uri
			),
			'ipaddress' => $_SERVER['REMOTE_ADDR'],
			'extraOption' => array(
				'hide' => $_COOKIE['NCXE_HIDE_MODE']
			)
		);

		$options = array(
			'key'		=>	$nowconnect_info->api_key, # required
			'mode'		=>	'ecb',                 # optional
			'algorithm'	=>	'blowfish',            # optional
			'base64'	=>	true                   # optional default
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

		// API 키 지정
		$oCommunicator->setApiKey($module_info->api_key);

		// API 요청
		$output = $oCommunicator->post('api/tick', $params);

		return new Object();
	}
}

/* End of file : nowconnect.controller.php */
/* Location : ./modules/nowconnect/nowconnect.controller.php */