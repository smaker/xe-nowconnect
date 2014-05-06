<?php
/**
 * @class nowconnectController
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 controller class
 **/

class nowconnectController extends nowconnect
{
	/**
	 * 예외 act
	 */
	private $except_act = array('dispMemberLogout' => 1);

	/**
	 * @brief 초기화
	 */
	public function init()
	{
	}

	/**
	 * after_module_proc에 대응하는 트리거
	 */
	public function triggerAfterModuleProc(&$oModule)
	{
		$module = $oModule->module;
		$module_info = $oModule->module_info;
		$act = $module_info->act;
		if(!$act) $act = $oModule->act;

		if(Context::getResponseMethod() != 'HTML' && !($act == 'dispNowconnect' && Context::getResponseMethod() == 'XMLRPC')) return new Object();

		// 관리자 페이지에 있는 경우 접속자 정보를 수집하지 않습니다
		if(Context::get('module') == 'admin')
		{
			return new Object();
		}

		// 로그아웃 페이지는 예외 처리
		if(isset($this->except_act[$act]))
		{
			return new Object();
		}

		// 로그인 정보를 구합니다
		$logged_info = Context::get('logged_info');

		if($logged_info->is_admin != 'Y')
		{

			$oCacheHandler = CacheHandler::getInstance('object');
			if($oCacheHandler->isSupport())
			{
				$cache_key = 'isBot:'. md5($_SERVER['USER-AGENT']);
				$cache_key2 = 'isMobileBot:'. md5($_SERVER['USER-AGENT']);
				$isBot = $oCacheHandler->get($cache_key);
				$isMobileBot = $oCacheHandler->get($cache_key2);
			}

			if($isBot == NULL || $isBot == '')
			{
				if(!class_exists('Mobile_Detect'))
				{
					require _XE_PATH_ . 'modules/nowconnect/libs/Mobile_Detect.php';
					$detect = new Mobile_Detect(null, $_SERVER['HTTP_USER_AGENT']);
				}
				$isBot = $detect->is('Bot');
				if($oCacheHandler->isSupport())
				{
					$oCacheHandler->put($cache_key, $isBot);
				}
			}

			if($isMobileBot == NULL || $isMobileBot == '')
			{
				if(!class_exists('Mobile_Detect'))
				{
					require _XE_PATH_ . 'modules/nowconnect/libs/Mobile_Detect.php';
					$detect = new Mobile_Detect(null, $_SERVER['HTTP_USER_AGENT']);
				}

				$isMobileBot = $detect->is('MobileBot');
				if($oCacheHandler->isSupport())
				{
					$oCacheHandler->put($cache_key2, $isMobileBot);
				}
			}
		
			if($isBot || $isMobileBot)
			{
				return new Object();
			}
		}

		// nowconnectModel 객체 생성
		$oNowconnectModel = getModel('nowconnect');

		// 현재 접속자 모듈일 경우, DB에서 모듈 정보를 가져오지 않도록 합니다
		if($module_info->module == 'nowconnect')
		{
			$nowconnect_info = &$module_info;
			if(!isset($nowconnect_info->active))
			{
				$nowconnect_info->active = 'Y';
			}
		}
		else
		{
			// 현재 접속자 모듈 정보를 DB에서 가져옵니다
			$nowconnect_info = $oNowconnectModel->getNowconnectInfo();
		}

		// API 키를 입력하지 않았다면 실행을 중단합니다
		if(!$nowconnect_info->api_key)
		{
			return new Object();
		}

		// 현재 접속자 기능이 비활성화 되어 있으면 실행을 중단합니다
		if($nowconnect_info->active != 'Y')
		{
			return new Object();
		}

		// 접속자 현황 수집 대상이 회원이고 로그인을 하지 않았다면 실행을 중단합니다
		if($nowconnect_info->nowconnect_target == 'member' && !$logged_info)
		{
			return new Object();
		}

		// 브라우저 제목을 가져옵니다
		$location = Context::getBrowserTitle();
		// 현재 페이지에 브라우저 제목이 없는 경우
		if(!$location)
		{
			// 모듈 정보에 등록된 브라우저 제목으로 대체
			$location = $module_info->browser_title;
		}

		// act 값으로 정확한 현재 위치를 구합니다
		$locationByAct = _getLocationByAct($act);
		// act 값으로 현재 위치를 가져왔다면
		if($locationByAct)
		{
			// 끝에 덧붙임
			$location .= ' - ' . $locationByAct;
		}

		$exclude_ip_list = explode(PHP_EOL, $nowconnect_info->exclude_ip);

		for($i=0,$c=count($exclude_ip_list);$i<$c;$i++)
		{
			$ip = str_replace('.', '\.', str_replace('*','(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)',$exclude_ip_list[$i]));
			if(preg_match('/^'.$ip.'$/', $_SERVER['REMOTE_ADDR'], $matches))
			{
				return new Object();
			}
		}

		// 암호화 옵션
		$options = array(
			'key'		=>	$nowconnect_info->api_key,
			'mode'		=>	'ecb',
			'algorithm'	=>	'blowfish',
			'base64'	=>	true
		);

		try
		{
			// 암호화를 위한 Crypt 객체 생성
			$oCrypt = new Crypt($options);
		}
		catch(Exception $e)
		{
			// mcrypt 확장 기능이 설치되어 있지 않으면 실행 중단
			return new Object();
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

		// 접속자를 구분하기 위한 고유값을 템플릿에서 쓸 수 있도록 Context::set()
		Context::set('myUID', $uid);

		// 로그인하지 않은 경우 임의로 닉네임을 생성함
		if(!$nick_name)
		{
			$nick_name = '손님' . substr(sha1($uid),0, 5);
		}

		$uri = $_SERVER['REQUEST_URI'];

		if($act == 'dispNowconnect' && Context::getResponseMethod() == 'XMLRPC' && $_SESSION['NOWCONNECT_LOCATION_URL'])
		{
			$uri = $_SESSION['NOWCONNECT_LOCATION_URL'];
		}

		$_SESSION['NOWCONNECT_LOCATION_URL'] = $_SERVER['REQUEST_URI'];

		$user_info = array(
			'_id' => $uid,
			'mid' => $module_info->mid,
			'member_srl' => $member_srl,
			'nick_name' => $nick_name,
			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
			'is_admin' => $logged_info->is_admin ? $logged_info->is_admin : 'N',
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

		// 사용자 정보를 암호화합니다
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