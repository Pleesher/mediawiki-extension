<?php
class Pleesher_MarkNotificationsReadAction extends Pleesher_Action
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), ['event_ids' => []]);
	}

	protected function doExecute()
	{
		$event_ids = explode('|', $this->getParameter('event_ids'));
		// FIXME: check result/error
		$result = PleesherExtension::$pleesher->markNotificationsRead($this->getUser()->getId(), $event_ids);

		$this->getResult()->addValue(null, 'success', $result ? 1 : 0);
	}
}
