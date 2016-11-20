<?php
class Pleesher_ShowcaseAchievementAction extends ApiBase
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_id' => null,
			'goal_id' => null,
			'remove' => 0
		]);
	}

	public function execute()
	{
		// FIXME: check value
		$goal_id = (int)$this->getParameter('goal_id');

		$achievements = PleesherExtension::getAchievements($this->getUser()->getId());
		if (!in_array($goal_id, array_keys($achievements)))
			return false;

		$remove = $this->getParameter('remove') == '1';

		if ($remove)
			PleesherExtension::$pleesher->deleteObjectData('goal', $goal_id, $this->getUser()->getId(), 'showcased');
		else
			PleesherExtension::$pleesher->setObjectData('goal', $goal_id, $this->getUser()->getId(), 'showcased', true);

		return true;
	}
}
