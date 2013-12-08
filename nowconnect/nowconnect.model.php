<?php
/**
 * @class nowconnectModel
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 model class
 **/

class nowconnectModel extends nowconnect
{
	/**
	 * @brief 초기화
	 */
	function init()
	{
	}

	/**
	 * 현재 접속자 수 API
	 */
	function getNowconnectUserCount()
	{
		$args = new stdClass;
		$args->excludeAdmin = Context::get('exclude_admin');

		$totalCount = $this->getNowconnectedUserCount($args);

		$this->add('totalCount', $totalCount);
	}

	/**
	 * 현재 접속자 수를 가져옴
	 */
	function getNowconnectedUserCount($args)
	{
		if(!$args->site_srl)
		{
			$site_module_info = Context::get('site_module_info');
			$args->site_srl = (int)$site_module_info->site_srl;
		}

		if(!$args->period_time) $args->period_time = 3;
		$args->last_update = date('YmdHis', time() - $args->period_time*60);

		$module_info = $this->getNowconnectInfo();
		if(!$module_info->api_key)
		{
			return NULL;
		}

		if($this->module_info->module == 'nowconnect')
		{
			if($args->nowconnect_target == $module_info->nowconnect_target && $args->exclude_admin == $module_info->exclude_admin)
			{
				$totalCount = Context::get('total_count');
				if(!is_null($totalCount))
				{
					return (int)$totalCount;
				}
			}
		}

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase('json');
		$oCommunicator->setApiKey($module_info->api_key);
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/api/');

		$params = array();

		// 관리자를 제외할 경우 excludeAdmin 피라미터 추가
		if($args->exclude_admin == 'Y')
		{
			$params['excludeAdmin'] = 'Y';
		}

		$output = $oCommunicator->post('users/count')->param($params)->send();
		return (int)$output->getResult()->result->totalCount;
	}

	/**
	 * @brief Get a list of currently connected users
	 * Requires "object" argument because multiple arguments are expected
	 * limit_count : the number of objects
	 * page : the page number
	 * period_time: "n" specifies the time range in minutes since the last update
	 * mid: a user who belong to a specified mid
	 **/
	function getConnectedUsers($args, $isPage = TRUE) {
		if(!$args->site_srl)
		{
			$site_module_info = Context::get('site_module_info');
			$args->site_srl = (int)$site_module_info->site_srl;
		}

		if(!$args->list_count) $args->list_count = 20;
		if(!$args->page_count) $args->page_count = 10;
		if(!$args->page) $args->page = 1;
		if(!$args->period_time) $args->period_time = 3;
		$args->last_update = date('YmdHis', time() - $args->period_time*60);

		$module_info = $this->getNowconnectInfo();
		if(!$module_info->api_key)
		{
			return NULL;
		}

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase();
		$oCommunicator->setApiKey($module_info->api_key);
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/api/');

		$params = array();

		if($isPage)
		{
			$params['isPage'] = 'Y';
			$params['listCount'] = $args->list_count;
			$params['page'] = $args->page;
		}

		// 관리자를 제외할 경우 excludeAdmin 피라미터 추가
		if($args->exclude_admin == 'Y')
		{
			$params['excludeAdmin'] = 'Y';
		}

		if($args->nowconnect_target == 'member')
		{
			$params['target'] = 'member';
		}

		$output = $oCommunicator->post('users')->param($params)->send();
		$tmp = $output->result();
		if(!$tmp)
		{
			$tmp = new stdClass;
			$tmp->result = array();
		}

		if($isPage)
		{
			$tmp->page_navigation = new PageHandler($tmp->result->totalCount, $tmp->result->totalPage, $args->page, $args->page_count);
			$tmp->page = $tmp->page_navigation->cur_page;
		}

		return $tmp;
	}

	/**
	 * 현재 접속자 모듈 정보를 가져옵니다
	 */
	function getNowconnectInfo()
	{
		static $module_info;

		// 현재 보고 있는 페이지가 현재 접속자 페이지라면 현재 모듈 정보를 그대로 return
		if($this->module_info->module == 'nowconnect')
		{
			return ($module_info = $this->module_info);
		}

		// 모듈 정보가 있으면 그대로 return
		if(isset($module_info))
		{
			return $module_info;
		}

		// 현재 접속자 모듈의 module_srl을 가져옴
		$output = executeQuery('nowconnect.getNowconnect');
		if(!$output->data->module_srl) return NULL;

		// moduleModel 객체 생성
		$oModuleModel = getModel('module');

		// 모듈 정보를 구해서 return
		$module_info = $oModuleModel->getModuleInfoByModuleSrl($output->data->module_srl);

		return $module_info;
	}
}