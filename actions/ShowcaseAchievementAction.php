<?php
class Pleesher_ShowcaseAchievementAction extends Pleesher_Action
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_id' => null,
			'goal_id' => null,
			'remove' => 0
		]);
	}

	protected function doExecute()
	{
		// FIXME: check value
		$goal_id = (int)$this->getParameter('goal_id');

		$achievements = PleesherExtension::getAchievements($this->getUser()->getId());
		$remove = $this->getParameter('remove') == '1';

		// FIXME: why no !isset($achievements[$goal_id]) simply??
		if (!$remove && !in_array($goal_id, array_keys($achievements)))
		{
			$this->getResult()->addValue(null, 'success', 0);
			return;
		}

		$result = $remove
			? PleesherExtension::$pleesher->deleteObjectData('goal', $goal_id, $this->getUser()->getId(), 'showcased')
			: PleesherExtension::$pleesher->setObjectData('goal', $goal_id, $this->getUser()->getId(), 'showcased', true);

		$this->getResult()->addValue(null, 'success', $result ? 1 : 0);
	}
}
