<?php
/**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

namespace MyLoan\HomeCredit;

use HomeCredit\OneClickApi;

/**
 * Class AuthAPI
 * @package MyLoan\HomeCredit
 */
class AuthAPI
{
    /**
     * @var
     */
    private $apiAddress;
    /**
     * @var
     */
    private $apiUser;
    /**
     * @var
     */
    private $apiPassword;
    /**
     * @var
     */
    private $authorizationProcess;

    /**
     * AuthAPI constructor.
     */
    public function __construct()
    {
        $credentials = \MlcConfig::getConfigArray();
        $this->setApiAddress($credentials[\MlcConfig::API_URL]);
        $this->setApiUser($credentials[\MlcConfig::API_USER]);
        $this->setApiPassword($credentials[\MlcConfig::API_PASSWORD]);
    }

    /**
     * Vytvoří komunikační třídu z OneClickApi
     * @return OneClickApi\RestClient\Application
     */
    public function authorize()
    {
        $this->setAuthorizationProcess(new OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess(
            $this->getApiUser(),
            $this->getApiPassword()
        ));

        $httpClientFactory = new OneClickApi\HttpClientFactory([
          'baseUrl' => $this->getApiAddress()
        ]);

        $client = new OneClickApi\RestClient\Application(
            $httpClientFactory,
            $this->getAuthorizationProcess()
        );

        return $client;
    }

    /**
     * Provede přihlášení do Myloan
     */
    public function login()
    {
        return $this->authorize()->login();
    }

    /**
     * Zjistí jestli jsem přihlášený do Myloan
     * @return mixed
     */
    public function isLogged()
    {
        $this->login();
        return $this->getAuthorizationProcess()->isLoggedIn();
    }

    /**
     * Vrátí třídu pro autorizační proces z OneClickApi
     * @return mixed
     */
    public function getAuthorizationProcess()
    {
        return $this->authorizationProcess;
    }

    /**
     * Nastaví třídu pro aurizační proces z OneClickApi
     * @param mixed $authorizationProcess
     */
    public function setAuthorizationProcess($authorizationProcess)
    {
        $this->authorizationProcess = $authorizationProcess;
    }

    /**
     * Vrátí adresu API
     * @return mixed
     */
    public function getApiAddress()
    {
        return $this->apiAddress;
    }

    /**
     * Nastaví adresu API
     * @param mixed $apiAddress
     */
    public function setApiAddress($apiAddress)
    {
        $this->apiAddress = $apiAddress;
    }

    /**
     * Vratí uživatele pro Myloan
     * @return mixed
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

    /**
     * Nastaví uživatele pro Myloan
     * @param mixed $apiUser
     */
    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;
    }

    /**
     * Vrátí heslo pro Myloan
     * @return mixed
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * Nastaví heslo pro Myloan
     * @param mixed $apiPassword
     */
    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;
    }
}
