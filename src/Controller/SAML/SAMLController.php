<?php

namespace App\Controller\SAML;

use App\Entity\User;
use App\SAML\IdpProvider;
use App\Utils\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SAMLController
 * @package App\Controller\SAML
 *
 * @Route("saml")
 */
class SAMLController extends AbstractController
{

    use ServiceHelper;

    /**
     * @Route("/auth0/assertion", name="saml_auth0_assertion", options = { "expose" = true })
     * @param Request $request
     * @return \LightSaml\Model\Protocol\Response|Response
     * @throws \Exception
     */
    public function auth0Assertion(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        return new Response("assertion handled");

    }


    /**
     * This endpoint supports SDP-Initiated and IDP-Initiated. Here's how to test.
     *
     * SDP-Initiated:
     *
     * 1. Login to https://auth0.com/ with these test credentials I created:
     *
     * email: crawmer@crc-inc.com
     * password: Ao3&!Im61G2LFPk%ma19
     *
     * 2. Follow the steps of this quick 1 minute video https://cl.ly/1699d17d3d09
     *
     * IDP-Initiated: (This is what we are going to be using for Shutterfly)
     *
     * 1. Visit this URL directly in the browser: http://p3.test/saml/auth0/SSO
     *
     * 2. Follow the steps of this quick 30 second video https://cl.ly/3a9fb3665387
     *
     * 3. You can see even though it says Ooops something went wrong, that when you inspect the logs there is
     * actually a valid login! The only reason it says something went wrong is because auth0 doesn't recommend
     * IDP-Initiated logins due to them being a smidge less secure.
     *
     *
     * @IsGranted({"ROLE_SUPER_ADMIN_USER", "ROLE_USER"})
     *
     * @Route("/auth0/SSO", name="saml_auth0_sso", options = { "expose" = true })
     * @param Request $request
     * @return \LightSaml\Model\Protocol\Response|Response
     * @throws \Exception
     */
    public function auth0SingleSignOn(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        /*return new Response("xdebug force refresh");*/

        $request = Request::createFromGlobals();

        if($request->query->has('SAMLRequest') && $request->query->has('RelayState')) {
            // SDP-Initiated
            try {
                $samlRequest = $this->idpTools->readSAMLRequest($request);
            } catch (\Exception $exception) {
                return new Response(sprintf("Error reading SAML Request: %s.", $exception->getMessage()));
            }
            $audienceIssuer = $samlRequest->getMessage()->getIssuer()->getValue();
            $responseId = $samlRequest->getMessage()->getID();
        } else {
            // IDP-Initiated
            $audienceIssuer = IdpProvider::AUTH0;
            $responseId = null;
        }


        $destination = $this->idpProvider->getServiceProviderAssertionConsumerService($audienceIssuer);
        $assertionConsumerServiceURL = $this->idpProvider->getServiceProviderAssertionConsumerService($audienceIssuer);
        $issuer = $this->idpProvider->getIdentityProviderId();

        // todo kind of a silly check and can be removed in the future.
        //  But just incase you are doing a lot of testing without being logged in.
        $identity = $user instanceof User ? $user->getEmail() : 'joshcrawmer4@yahoo.com';

        if(!$this->idpProvider->isTrustedServiceProvider($audienceIssuer)) {
            return new Response(
                sprintf("Untrusted Service Provider: %s.", $audienceIssuer));
        }

        try {
            $response = $this->idpTools->createAuth0SAMLResponse($destination, $assertionConsumerServiceURL, $issuer, $audienceIssuer, $identity, $responseId);
        } catch (\Exception $exception) {
            return new Response(sprintf("Error creating SAML Response: %s.", $exception->getMessage()));
        }

        try {
            $form = $this->idpTools->createPostBindingForm($response, $request);
        } catch (\Exception $exception) {
            return new Response(sprintf("Error creating Post Binding Form: %s.", $exception->getMessage()));
        }

        return new Response($form);
    }

    /**
     * @Route("/auth0/SingleLogout", name="saml_auth0_single_logout", options = { "expose" = true })
     * @param Request $request
     * @return \LightSaml\Model\Protocol\Response|Response
     * @throws \Exception
     */
    public function auth0SingleLogout(Request $request) {

        // do nothing

        /** @var User $user */
        $user = $this->getUser();

        return new Response("Single Logout");
    }
}