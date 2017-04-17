<?php
class PleesherClient extends \Pleesher\Client\Client
{
	public function call($verb, $url, array $data = array())
	{
		if (PleesherExtension::getSettingValue('disabled', '0') == '1')
			throw new PleesherDisabledException();

		return parent::call($verb, $url, $data);
	}
}