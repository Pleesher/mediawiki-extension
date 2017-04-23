<?php
class Pleesher_GoalListPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('Achievements');
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

		$user = $this->getUser();
		$user_id = $user->getId();
		$goals = PleesherExtension::getGoals(['user_id' => $user_id > 0 ? $user_id : null]);
		$user_merge_url = null;

		foreach ($goals as $goal)
		{
			$category = isset(PleesherExtension::$goal_data[$goal->code]->category) ? PleesherExtension::$goal_data[$goal->code]->category : 'other';
			if (!isset($goals_by_category[$category]))
				$goals_by_category[$category] = [];
			$goals_by_category[$category][] = $goal;
		}

		$goals_by_category = array_replace(array_flip(PleesherExtension::$goal_categories), $goals_by_category);
		$goals_by_category = array_filter($goals_by_category, function($category_goals) {
			return is_array($category_goals) && count($category_goals) > 0;
		});

		$html = PleesherExtension::render('goals_by_category', [
			'user' => $user,
			'goals_by_category' => $goals_by_category,
			'user_merge_url' => $user_merge_url
		]);
		$this->getOutput()->addHTML($html);
	}
}
