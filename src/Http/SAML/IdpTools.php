<?php

namespace App\Http\SAML;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class IdpTools
{
    /**
     * @var IdpProvider
     */
    private $idpProvider;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * IdpTools constructor.
     * @param IdpProvider $idpProvider
     * @param string $projectDirectory
     */
    public function __construct(IdpProvider $idpProvider, string $projectDirectory)
    {
        $this->idpProvider = $idpProvider;
        $this->projectDirectory = $projectDirectory;
    }


    /**
     * Reads a SAMLRequest from the HTTP request and returns a messageContext.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The HTTP request.
     *
     * @return \LightSaml\Context\Profile\MessageContext
     *   The MessageContext that contains the SAML message.
     */
    public function readSAMLRequest($request){

        // We use the Binding Factory to construct a new SAML Binding based on the
        // request.
        $bindingFactory = new \LightSaml\Binding\BindingFactory();
        $binding = $bindingFactory->getBindingByRequest($request);

        // We prepare a message context to receive our SAML Request message.
        $messageContext = new \LightSaml\Context\Profile\MessageContext();

        // The receive method fills in the messageContext with the SAML Request data.
        /** @var \LightSaml\Model\Protocol\Response $response */
        $binding->receive($request, $messageContext);

        return $messageContext;
    }

    /**
     * Constructs a SAML Response for auth0 for testing purposes.
     *
     * @param $destination
     * @param $assertionConsumerServiceURL
     * @param $issuer
     * @param $audienceIssuer
     * @param $identity
     * @param null $responseId
     * @return \LightSaml\Model\Protocol\Response
     * @throws \Exception
     */
    public function createAuth0SAMLResponse($destination, $assertionConsumerServiceURL, $issuer, $audienceIssuer, $identity, $responseId = null) {

        $serializationContext = new \LightSaml\Model\Context\SerializationContext();

        $response = new \LightSaml\Model\Protocol\Response();

        $response
            ->addAssertion($assertion = new \LightSaml\Model\Assertion\Assertion())
            ->setStatus(new \LightSaml\Model\Protocol\Status(
                    new \LightSaml\Model\Protocol\StatusCode(
                        \LightSaml\SamlConstants::STATUS_SUCCESS)
                )
            )
            ->setID(\LightSaml\Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination($destination)
            ->setIssuer(new \LightSaml\Model\Assertion\Issuer($issuer));


        $subjectConfirmationData = (new \LightSaml\Model\Assertion\SubjectConfirmationData())
            ->setNotOnOrAfter(new \DateTime('+1 MINUTE'))
            ->setRecipient($assertionConsumerServiceURL);

        if($responseId) {
            $subjectConfirmationData->setInResponseTo($responseId);
        }

        $assertion
            ->setId(\LightSaml\Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setIssuer(new \LightSaml\Model\Assertion\Issuer($issuer))
            ->setSubject(
                (new \LightSaml\Model\Assertion\Subject())
                    ->setNameID(new \LightSaml\Model\Assertion\NameID(
                        $identity,
                        \LightSaml\SamlConstants::NAME_ID_FORMAT_UNSPECIFIED
                    ))
                    ->addSubjectConfirmation(
                        (new \LightSaml\Model\Assertion\SubjectConfirmation())
                            ->setMethod(\LightSaml\SamlConstants::CONFIRMATION_METHOD_BEARER)
                            ->setSubjectConfirmationData($subjectConfirmationData)
                    )
            )
            ->setConditions(
                (new \LightSaml\Model\Assertion\Conditions())
                    ->setNotBefore(new \DateTime())
                    ->setNotOnOrAfter(new \DateTime('+1 MINUTE'))
                    ->addItem(new \LightSaml\Model\Assertion\AudienceRestriction([$audienceIssuer]))
            )
            ->addItem(
                (new \LightSaml\Model\Assertion\AuthnStatement())
                    ->setAuthnInstant(new \DateTime('-10 MINUTE'))
                    ->setSessionIndex($assertion->getId())
                    ->setAuthnContext(
                        (new \LightSaml\Model\Assertion\AuthnContext())
                            ->setAuthnContextClassRef(\LightSaml\SamlConstants::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT)
                    )
            )->addItem(
                (new \LightSaml\Model\Assertion\AttributeStatement())
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        \LightSaml\ClaimTypes::EMAIL_ADDRESS,
                        $identity
                    ))
            );

        // Sign the response.
        $response->setSignature(new \LightSaml\Model\XmlDSig\SignatureWriter($this->idpProvider->getCertificate(), $this->idpProvider->getPrivateKey()));

        // Serialize to XML.
        $response->serialize($serializationContext->getDocument(), $serializationContext);

        // Set the postback url obtained from the trusted SPs as the destination.
        $response->setDestination($assertionConsumerServiceURL);

        return $response;
    }

    /**
     * Creates post binding form
     * @param \LightSaml\Model\Protocol\Response $response
     * @param Request $request
     * @return false|string
     * @throws \Exception
     */
    public function createPostBindingForm(\LightSaml\Model\Protocol\Response $response, Request $request) {

        $bindingFactory = new \LightSaml\Binding\BindingFactory();
        $postBinding = $bindingFactory->create(\LightSaml\SamlConstants::BINDING_SAML2_HTTP_POST);
        $messageContext = new \LightSaml\Context\Profile\MessageContext();
        $messageContext->setMessage($response);

        // Ensure we include the RelayState.
        $message = $messageContext->getMessage();
        $message->setRelayState($request->get('RelayState'));

        $messageContext->setMessage($message);

        // Return the Response.
        /** @var \Symfony\Component\HttpFoundation\Response $httpResponse */
        $httpResponse = $postBinding->send($messageContext);

        if(!$httpResponse->getContent()) {
            throw new \Exception("html for form could not be generated.");
        }

        return $httpResponse->getContent();
    }
}