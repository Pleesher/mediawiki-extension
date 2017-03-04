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

		$html = PleesherExtension::render('admin');
		$this->getOutput()->addHTML($html);
	}
}
