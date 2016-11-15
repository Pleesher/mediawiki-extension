<?php
class Pleesher_GoalDetailsPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementDetails');
	}

	public function execute($subPage)
	{
		$view_helper = new Pleesher_ViewHelper();

		if (empty($subPage))
			return $this->getOutput()->redirect($view_helper->pageUrl('Special:Achievements'));

		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$request = $this->getRequest();
		$goal_code = $subPage;

		$user = $this->getUser();
		$user_id = $user->getId();

		$goal = null;
		try {
			$goal = PleesherExtension::getGoal($goal_code, ['user_id' => $user_id > 0 ? $user_id : null]);
			$achievers = PleesherExtension::getAchievers($goal_code);
			$html = PleesherExtension::render('goal_details', [
				'user' => $user,
				'goal' => $goal,
				'achievers' => $achievers
			]);

			$this->getOutput()->setPageTitle($view_helper->text('pleesher.goal_details.title', [$goal->title]));
			$this->getOutput()->addHTML($html);

		} catch (\Pleesher\Client\Exception\Exception $e) {
			$this->getOutput()->setPageTitle($view_helper->text('pleesher.error.title'));
			$this->getOutput()->addHTML($view_helper->text('pleesher.error.text.' . ($e->getErrorCode() ?: 'generic'), $e->getErrorParameters() ?: []));
		}
	}
}