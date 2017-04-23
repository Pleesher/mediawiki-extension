<?php
class Pleesher_DisabledPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementsDisabled');
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$html = PleesherExtension::render('disabled');
		$this->getOutput()->addHTML($html);
	}
}