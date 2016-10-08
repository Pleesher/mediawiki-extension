<?php
class Pleesher_GoalDetailsPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementDetails');
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$request = $this->getRequest();
		$goal_code = $subPage;

		$user = $this->getUser();
		$user_id = $user->getId();
		$goal = PleesherExtension::getGoal($goal_code, ['user_id' => $user_id > 0 ? $user_id : null]);
		$achievers = PleesherExtension::getAchievers($goal_code);

		$html = PleesherExtension::render('goal_details', [
			'user' => $user,
			'goal' => $goal,
			'achievers' => $achievers
		]);

		$this->getOutput()->setPageTitle(wfMessage('pleesher.goal_details.title')->params($goal->title)->inContentLanguage()->escaped());
		$this->getOutput()->addHTML($html);
	}
}