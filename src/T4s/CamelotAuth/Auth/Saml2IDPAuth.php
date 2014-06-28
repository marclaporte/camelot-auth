<?php
/**
 * Camelot Auth
 *
 * @author Timothy Seebus <timothyseebus@tools4schools.org>
 * @license http://opensource.org/licences/MIT
 * @package CamelotAuth
 */
namespace T4s\CamelotAuth\Auth;

use T4s\CamelotAuth\Auth\Saml2\Bindings\Binding;
use T4s\CamelotAuth\Auth\Saml2\Core\Messages\AuthnRequest;
use T4s\CamelotAuth\Auth\Saml2\Saml2Auth;
use T4s\CamelotAuth\Auth\Saml2\Saml2Constants;

use T4s\CamelotAuth\Database\DatabaseInterface;
use T4s\CamelotAuth\Config\ConfigInterface;
use T4s\CamelotAuth\Session\SessionInterface;
use T4s\CamelotAuth\Cookie\CookieInterface;
use T4s\CamelotAuth\Messaging\MessagingInterface;
use T4s\CamelotAuth\Events\DispatcherInterface;

use T4s\CamelotAuth\Auth\Saml2\Metadata\IndexedEndpointType;

/*use T4s\CamelotAuth\Auth\Saml2\Messages\AuthnRequestMessage;
use T4s\CamelotAuth\Auth\Saml2\Messages\ResponseMessage;

use T4s\CamelotAuth\Auth\Saml2\bindings\Binding;
use T4s\CamelotAuth\Auth\Saml2\bindings\HTTPRedirectBinding;
use T4s\CamelotAuth\Auth\Saml2\bindings\HTTPPostBinding;
use T4s\CamelotAuth\Auth\Saml2\bindings\HTTPArtifactBinding;
*/

class Saml2IDPAuth extends Saml2Auth implements AuthInterface
{


	public $supportedBindings = [Saml2Constants::Binding_HTTP_POST];



	public function authenticate(array $credentials = null, $remember = false,$login = true)
	{
		// check if a idp entity id is set in the credentails
		if(isset($credentials['entityID']))
		{
			// override the provider
			$this->provider = $credentials['entityID'];
		}
		// check if the entity provider is valid
		
		if(strpos($this->path,'SingleLogoutService'))
		{
			//return $this->handleSingleLogoutRequest();
		}
		else if(strpos($this->path,'SingleSignOnService'))
		{
			return $this->handleAuthnRequest();
		}

		//return $this->handleNewSSORequest();
	}

    protected function handleAuthnRequest()
    {
        $binding = Binding::getBinding();

        $requestMessage = $binding->receive();

        if(!($requestMessage instanceof AuthnRequest))
        {
            throw new \Exception('Wrong message type recieved exspecting an AuthnRequest Message');
        }


        if(!$this->storage->get('metadata')->isValidEnitity($requestMessage->getIssuer()))
        {
            throw new \Exception('unknown EntityID : this IDP does not have a trust relationship with entityID '.$requestMessage->getIssuer());
        }


        if($requestMessage->getForceAuthn()|| $this->check() == false)
        {
            // we need to (re)authenticate the user


            // redirect to login uri
            // send with a state object containing redirect to url,method and request message
        }
    }
}