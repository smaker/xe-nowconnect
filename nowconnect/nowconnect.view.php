<?php
/**
 * @class nowconnectView
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 view class
 **/

class nowconnectView extends nowconnect
{
	/**
	 * @brief 현재 접속자
	 */
	function dispNowconnect()
	{
		if($this->module_info->skin)
		{
			$templatePath = (sprintf($this->module_path.'skins/%s', $this->module_info->skin));
		}
		else
		{
			$templatePath = ($this->module_path.'skins/default');
		}

		if(!$this->module_info->list_count)
		{
			$this->module_info->list_count = 30;
		}

		if(!$this->module_info->page_count)
		{
			$this->module_info->page_count = 10;
		}

		Context::set('module_info', $this->module_info);

		$this->setTemplatePath($templatePath);

		// 비정상적인 경로로 접근했을 때
		if(!$this->module_info->module_srl)
		{
			return $this->stop('msg_invalid_request');
		}

		// 목록 보기 권한이 없을 때
		if(!$this->grant->list)
		{
			return $this->stop('msg_not_permitted');
		}

		$logged_info = Context::get('logged_info');
		$oNowconnectModel = getModel('nowconnect');

		$args->exclude_admin = $this->module_info->exclude_admin;
		$args->list_count = $this->module_info->list_count;
		$args->page_count = $this->module_info->page_count;
		$args->page = Context::get('page');
		$args->hide_ipaddress = ($logged_info->is_admin == 'Y') ? FALSE : TRUE;

		if(!$args->page)
		{
			Context::set('page', 1);
		}

		$output = $oNowconnectModel->getConnectedUsers($args);

		// 중복 접속자 처리
		if($this->module_info->include_duplicated_user == 'Y')
		{
			$uid = session_id();
		}
		else
		{
			$uid = sha1(md5($_SERVER['REMOTE_ADDR']));
		}

		Context::set('user_list', $output->result->users);
		Context::set('page_navigation', $output->page_navigation);
		Context::set('total_count', $output->result->totalCount);

		$this->setTemplateFile('nowconnect');
	}
}

/* End of file : nowconnect.view.php */
/* Location : ./modules/nowconnect/nowconnect.view.php */