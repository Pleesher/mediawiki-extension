<?php
class Pleesher_CheckAchievementsAction extends ApiBase
{
	public function execute()
	{
		PleesherExtension::$pleesher->checkAchievementsQueued($this->getUser()->getId());
		$this->getResult()->addValue(null, 'success', 1);
	}
}
