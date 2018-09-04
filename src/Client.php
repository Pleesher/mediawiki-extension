<?php
class PleesherClient extends \Pleesher\Client\Client
{
	public function call($verb, $url, array $data = [])
	{
		if (PleesherExtension::isDisabled())
			throw new PleesherDisabledException();

		return parent::call($verb, $url, $data);
	}
}