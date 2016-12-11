<?php
class Pleesher_GoalListPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('Achievements');
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

		$html = PleesherExtension::render('goals', [
			'user' => $user,
			'goals' => $goals
		]);
		$this->getOutput()->addHTML($html);
	}
}