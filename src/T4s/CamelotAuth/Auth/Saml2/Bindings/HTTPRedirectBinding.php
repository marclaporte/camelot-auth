<?php namespace T4s\CamelotAuth\Auth\Saml2\bindings;

use T4s\CamelotAuth\Auth\Saml2\Messages\AbstractMessage;
use T4s\CamelotAuth\Auth\Saml2\bindings\Binding;
use T4s\CamelotAuth\Auth\Saml2\Saml2Constants;
/**
* 
*/
class HTTPRedirectBinding extends Binding
{
	
	protected $parameters = array();

	/**
	 * The certificate used to sign the request
	 *
	 * @var string
	 */

	protected $signingCertificate = null;


	public function setRelayState($relayState)
	{
		$this->parameters['RelayState'] = $relayState;
	}

	public function getRelayState()
	{
		if(isset($this->parameters['RelayState']))
		{
			return $this->parameters['RelayState'];
		}
		return null;
	}

	/**
	 * sets the certificate that should be used to sign the request
	 *
	 *
	 */
	public function setSigningCertificate($certificate)
	{
		$this->signingCertificate = $certificate;
	}




	public function send(AbstractMessage $message)
	{

		if(is_null($this->destination))
		{
			$this->destination = $message->getDestination();
		}

		$messageString = $message->generateUnsignedMessage();
		$messageString = $message->getXMLMessage()->saveXML($messageString);

		$messageString = gzdeflate($messageString);
		$messageString = base64_encode($messageString);

		if($message instanceof RequestMessage)
		{
			$msg = 'SAMLRequest=';
		}else{
			$msg = 'SAMLResponse=';
		}
		$msg .=urlencode($messageString);
		
		$msg .= http_build_query($this->parameters);

		if($message->signRequest() && !is_null($this->signingCertificate))
		{
			$msg .= '&SigAlg='.urlencode($this->signingCertificate->type);
			$signature = $this->signingCertificate->sign($msg);
			$msg .= '&Signature='.urlencode(base64_encode($Signature));
		}

		if(is_null($this->destination) || $this->destination == '' ||  preg_match("/\\s/", $this->destination))
		{
			throw new \Exception("No destination set for this Request");
			
		}

		header('Location: ' . $this->destination.'?'.$msg);
		header('Pragma: no-cache');
		header('Cache-Control: no-cache, must-revalidate');
        exit;
	}

	public function receive()
	{
		if(array_key_exists('SAMLResponse', $_GET))
		{
			$message = $_GET['SAMLResponse'];
		}
		else if(array_key_exists('SAMLRequest', $_GET))
		{
			$message = $_GET['SAMLRequest'];
		}
		else
		{
			throw new \Exception("missing SAMLRequest or SAMLResponse parameter");
		}

			$encoding = Saml2Constants::Binding_Encoding_DEFLATE;
		if(array_key_exists('SAMLEncoding',$_GET))
		{
			$encoding = $_GET['SAMLEncoding'];
		}

		$message = base64_decode($message);
		switch ($encoding) {
			case Saml2Constants::Binding_Encoding_DEFLATE:
				$message = gzinflate($message);
				break;
			
			default:
				throw new \Exception("Unknown SAMLEncoding");
				
				break;
		}

		return $message;
	}



	
}