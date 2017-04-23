<?php
class Pleesher_SetSettingAction extends Pleesher_AdminAction
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'key' => null,
			'value' => null
		]);
	}

	protected function doExecute()
	{
		$key = $this->getParameter('key');
		$value = $this->getParameter('value');

		switch ($key)
		{
			case 'disabled':
				$value = !!$value;
				break;

			default:
				$this->getResult()->addValue(null, 'success', 0);
				return;
		}

		$result = PleesherExtension::setSettingValue($key, $value ? '1' : '0');

		$this->getResult()->addValue(null, 'success', $result ? 1 : 0);
	}
}
