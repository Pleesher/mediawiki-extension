<?php
class Pleesher_ErrorPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementsError');
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$html = PleesherExtension::render('error');
		$this->getOutput()->addHTML($html);
	}
}