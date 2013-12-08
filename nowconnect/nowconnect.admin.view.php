<?php
/**
 * @class nowconnectAdminView
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 admin view class
 **/

class nowconnectAdminView extends nowconnect
{
	/**
	 * @brief 초기화
	 */
	function init()
	{
		// nowconnectModel의 객체 생성
		$oNowconnectModel = getModel('nowconnect');

		// nowconnect 모듈 정보를 구해서 Context::set()
		$this->module_info = $oNowconnectModel->getNowconnectInfo();
		Context::set('module_info', $this->module_info);
		Context::set('module_srl', $this->module_info->module_srl);

		// XSS 방지
		$security = new Security();
		$security->encodeHTML('module_info.');

		// 관리자용 템플릿 폴더 지정
		$this->setTemplatePath($this->module_path.'tpl');
	}

	/**
	 * @brief 현재 접속자 목록
	 */
	function dispNowconnectAdminNowList()
	{
		// 로그인 정보를 구함
		$logged_info = Context::get('logged_info');

		// nowconnectModel의 객체 생성
		$oNowconnectModel = getModel('nowconnect');

		// 기본 설정값 지정
		$args->list_count = 30;

		// 현재 접속자 목록을 가져옴
		$output = $oNowconnectModel->getConnectedUsers($args);

		// 템플릿에서 쓸 수 있도록 Context::set()
		Context::set('user_list', $output->result->users);
		Context::set('page_navigation', $output->page_navigation);
		Context::set('page', $output->page);
		Context::set('total_page', $output->result->totalPage);
		Context::set('total_count', $output->result->totalCount);

		// 템플릿 파일 지정
		$this->setTemplateFile('nowconnect_list');
	}

	/**
	 * 기본 설정
	 */
	function dispNowconnectAdminGlobalConfig()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('nowconnect');

		Context::set('config', $config);

		// 템플릿 파일 지정
		$this->setTemplateFile('globalConfig');
	}

	/**
	 * @brief 현재 접속자 모듈 설정
	 */
	function dispNowconnectAdminModuleConfig()
	{
		// moduleModel의 객체 생성
		$oModuleModel = getModel('module');

		// layoutModel의 객체 생성
		$oLayoutModel = getModel('layout');

		// 생성한 모듈 분류를 가져옴
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);

		// 스킨 목록을 가져옴
		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list', $skin_list);

		// 레이아웃 목록을 가져옴
		$layout_list = $oLayoutModel->getLayoutList();
		Context::set('layout_list', $layout_list);

		if(!$this->module_info->module_srl)
		{
			$this->setTemplateFile('create');
		}
		else
		{
			$this->setTemplateFile('config');
		}

		// XSS 방지
		$security = new Security();
		$security->encodeHTML('module_category..');
	}

	function dispNowconnectAdminGrantInfo()
	{
		// Common module settings page, call rights
		$oModuleAdminModel = getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);

		$this->setTemplateFile('grant_list');
	}

	/**
	 * @brief 스킨 관리
	 */
	function dispNowconnectAdminSkinInfo()
	{
		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
		Context::set('skin_content', $skin_content);

		$this->setTemplateFile('skin_info');
	}
}

/* End of file : nowconnect.admin.view.php */
/* Location : ./modules/nowconnect/nowconnect.admin.view.php */