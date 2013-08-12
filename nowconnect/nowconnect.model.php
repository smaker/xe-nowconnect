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
		$excludeAdmin = (Context::get('exclude_admin') == 'Y');
		// 관리자를 제외할 경우 관리자 회원 번호를 모두 가져옵니다.
		if($excludeAdmin)
		{
			$adminOutput = executeQueryArray('nowconnect.getAdminUsers');
			if(count($adminOutput->data))
			{
				foreach($adminOutput->data as $key => $val)
				{
					$admin_member_srls[] = $val->member_srl;
				}
			}

			$admin_member_srl = implode(',', $admin_member_srls);
		}

		if(!$args->period_time) $args->period_time = 3;
		$args->last_update = date('YmdHis', time() - $args->period_time*60);
		$args->but_member_srl = $admin_member_srl;

		$output = executeQueryArray('nowconnect.getConnectedUserCount', $args);
		if(!$output->toBool()) return $output;	

		$this->add('count', $output->data->count);
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

		if(!$module_info->api_site_url)
		{
			return NULL;
		}

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase('json');
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/');

		$params = array(
			'site_url' => $module_info->api_site_url
		);

		// 관리자를 제외할 경우 excludeAdmin 피라미터 추가
		if($args->exclude_admin == 'Y')
		{
			$params['excludeAdmin'] = 'Y';
		}

		$output = $oCommunicator->post('api/users/count', $params);
		return $output->getResult()->totalCount;
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

		if(!$module_info->api_site_url)
		{
			return NULL;
		}

		// Communicator 객체 생성
		$oCommunicator = new CommuniCatorBase('json');
		$oCommunicator->setServer('http://api.ncxe.funnyxe.kr/');

		$params = array(
			'site_url' => $module_info->api_site_url
		);

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

		$output = $oCommunicator->post('api/users', $params);
		$tmp = $output->getResult();
		if(!$tmp)
		{
			$tmp = new stdClass;
			$tmp->result = array();
		}

		if($isPage)
		{
			$tmp->page_navigation = new PageHandler($tmp->totalCount, $tmp->totalPage, $args->page, $args->page_count);
			$tmp->page = $tmp->page_navigation->cur_page;
		}

		return $tmp;
	}

	/**
	 * 현재 접속자 모듈 정보를 가져옵니다
	 */
	function getNowconnectInfo() {
		// 현재 접속자 모듈의 module_srl을 가져옴
		$output = executeQuery('nowconnect.getNowconnect');
		if(!$output->data->module_srl) return NULL;

		// moduleModel 객체 생성
		$oModuleModel = getModel('module');

		// 모듈 정보를 구해서 return
		return $oModuleModel->getModuleInfoByModuleSrl($output->data->module_srl);
	}
}