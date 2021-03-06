<?php
$lang->nowconnect = '현재 접속자';
$lang->nowconnect_list = '현재 접속자 목록';
$lang->connected_users = '현재 접속자 수';

$lang->cmd_create_nowconnect = '현재 접속자 모듈 생성';
$lang->cmd_nowconnect_config = '현재 접속자 모듈 정보';

$lang->about_create_nowconnect = '현재 접속자 모듈이 생성되지 않았습니다. 헌재 접속자 모듈은 하나만 생성 가능합니다.';

$lang->locationList = array(
	'dispMemberInfo' => '회원정보 보기',
	'dispMemberModifyInfo' => '회원정보 수정',
	'dispMemberModifyPassword' => '비밀번호 변경',
	'dispMemberFindAccount' => '비밀번호 찾기',
	'dispMemberResendAuthMail' => '인증메일 재발송',
	'dispMemberLoginForm' => '로그인',
	'dispMemberSignUpForm' => '회원가입',
	'dispMemberLeave' => '회원 탈퇴',
	'dispBoardWrite' => '글쓰기',
	'dispBoardDelete' => '글삭제',
	'dispModuleChangeLang' => '언어 변경',
	'dispMenuMenu' => '메뉴 보기',
	'rss' => 'RSS Feed',
	'atom' => 'Atom Feed',
	'Unknown' => '알 수 없음',
	'Mobile' => '모바일'
);

$lang->no = '번호';
$lang->yes = '예';
$lang->not = '아니오';

$lang->current_location = '현재 위치';
$lang->api_key = 'API 키';
$lang->api_site_url = '대표 URL';

$lang->nowconnect_target = '접속자 현황 수집 대상';
$lang->about_nowconnect_target = '접속자 현황을 수집할 접속자를 선택해 주세요.';
$lang->ncxe_all_member = '모든 접속자';
$lang->ncxe_logged_member_only = '로그인한 접속자만';
$lang->about_exclude_admin = '현재 접속자 목록에서 최고관리자를 제외할 수 있습니다.';
$lang->exclude_admin = '최고관리자 제외';
$lang->about_exclude_admin = '현재 접속자 목록에서 최고관리자를 제외할 수 있습니다.';
$lang->use_realtime = '실시간 업데이트 기능';
$lang->realtime_update = '실시간 업데이트';
$lang->realtime_duration = '실시간 업데이트 주기';
$lang->about_use_realtime = '페이지 새로고침 없이 현재 접속자 목록을 다시 가져옵니다.';
$lang->about_realtime_duration = '접속자 목록을 얼마나 자주 새로고침할 지 입력해주세요. (1000 = 1초)';
$lang->about_api_key = '발급받은 현재 접속자 API 키를 정확히 입력해 주세요.';
$lang->about_api_site_url = '사이트의 대표 URL을 입력해주세요. (API 키 발급 시 입력한 사이트 URL을 입력해주세요.)';
$lang->exclude_ip = '제외 IP';
$lang->about_exclude_ip = '특정 IP를 접속자 현황 수집에서 제외할 수 있습니다.<br>여러 개의 IP를 입력하려면 줄바꿈을 해서 입력해주세요.';
$lang->include_duplicated_user = '중복 접속자 포함';
$lang->about_include_duplicated_user = 'IP가 동일한 접속자를 접속자 현황 수집에서 포함할 지 여부를 결정합니다.';
$lang->not_include = '포함하지 않음';
$lang->include = '포함';

$lang->cmd_refresh = '새로고침';

$lang->about_nowconnect_list_count = '한 페이지에 표시될 접속자 수를 지정할 수 있습니다. (기본 20개)';

$lang->click_to_enable_realtime_update = '클릭하여 실시간 업데이트 기능을 켜거나 끌 수 있습니다.';

$lang->msg_api_key_required = 'API 키를 입력해주세요.';
$lang->msg_api_site_url_required = '대표 URL을 입력해주세요.';

$lang->msg_invalid_api_site_url = '라이선스가 등록되지 않은 사이트입니다. 대표 URL을 확인해주세요.';
$lang->msg_invalid_api_key = '등록되지 않은 API 키입니다.';

$lang->mcrypt_extension_required = 'mcrypt 확장 기능이 설치되어 있지 않습니다.'. PHP_EOL . '설치 후 사용 가능합니다.';