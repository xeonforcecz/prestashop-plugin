<?php

namespace HomeCredit\OneClickApi\RestClient\AuthorizationProcess;

use GuzzleHttp6\Client;

interface IAuthorizationProcess
{

	public function login(Client $client);
	public function isLoggedIn();
	public function getAuthHeaders();
	public function reset();

}
