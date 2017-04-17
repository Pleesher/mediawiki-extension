<?php
class Pleesher_AdminPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementsAdmin', PleesherExtension::ADMIN_RIGHT);
	}

	function getGroupName() {
		return 'pleesher';
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$pleesher_disabled = PleesherExtension::getSettingValue('disabled', '0') == '1';

		$html = PleesherExtension::render('admin', ['pleesher_disabled' => $pleesher_disabled]);
		$this->getOutput()->addHTML($html);
	}
}
