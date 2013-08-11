<?php
/**
 * @class nowconnectView
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 view class
 **/

class nowconnectView extends nowconnect
{
	/**
	 * @brief 초기화
	 */
	function init()
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
	}

	/**
	 * @brief 현재 접속자 보기
	 */
	function dispNowconnect()
	{
		if(!$this->module_info->module_srl)
		{
			return $this->stop('msg_invalid_request');
		}

		if(!$this->grant->list)
		{
			return $this->stop('msg_not_permitted');
		}

		$logged_info = Context::get('logged_info');
		$oNowconnectModel = &getModel('nowconnect');

		$args->exclude_admin = $this->module_info->exclude_admin;
		$args->list_count = $this->module_info->list_count;
		$args->page_count = $this->module_info->page_count;
		$args->page = Context::get('page');
		$args->hide_ipaddress = ($logged_info->is_admin == 'Y') ? FALSE : TRUE;

		$output = $oNowconnectModel->getConnectedUsers($args);

		$responseMethod = Context::getResponseMethod();
		switch($responseMethod)
		{
			case 'XMLRPC':
				$selectedTheme = Context::get('selectedTheme');
				$oTemplateHandler = TemplateHandler::getInstance();
				Context::set('user_list', $output->result);
				Context::set('page_navigation', $output->page_navigation);
				Context::set('total_count', $output->totalCount);
				Context::set('from_ajax', true);
				$html = $oTemplateHandler->compile($this->getTemplatePath(), '_nowconnect.list');
				$this->add('html', $html);
				break;
			case 'HTML':
				Context::set('user_list', $output->result);
				Context::set('page_navigation', $output->page_navigation);
				Context::set('total_count', $output->totalCount);
				$this->setTemplateFile('nowconnect');
				break;
		}
	}
}

/* End of file : nowconnect.view.php */
/* Location : ./modules/nowconnect/nowconnect.view.php */