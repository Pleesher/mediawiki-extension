<?php
class Pleesher_GoalDetailsPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementDetails');
	}

	function getGroupName() {
		return 'pleesher';
	}

	public function execute($subPage)
	{
		if (empty($subPage))
			return $this->getOutput()->redirect(PleesherExtension::$view_helper->pageUrl('Special:Achievements'));

		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$request = $this->getRequest();
		$goal_code = $subPage;

		$user = $this->getUser();
		$user_id = $user->getId();

		$goal = PleesherExtension::getGoal($goal_code, ['user_id' => $user_id > 0 ? $user_id : null]);
		if (!is_object($goal))
		{
			header('Location: ' . PleesherExtension::$view_helper->pageUrl('Special:AchievementsError', true));
			die;
		}

		$achievers = PleesherExtension::getAchievers($goal_code);
		$html = PleesherExtension::render('goal_details', [
			'user' => $user,
			'goal' => $goal,
			'achievers' => $achievers
		]);

		$this->getOutput()->setPageTitle(PleesherExtension::$view_helper->text('pleesher.goal_details.title', [$goal->title]));
		$this->getOutput()->addHTML($html);
	}
}
