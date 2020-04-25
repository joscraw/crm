<?php

namespace App\Http\SAML;

/**
 * SAML Instructions
 *
 * Terminology:
 *
 * moving forward we will be using the terminology application A / IDP and application B / SP interchangeably
 *
 * Identity Provider (IDP): application A. In this case the tecspec CRM.
 *
 * Service Provider (SP): application B,C,D or any application we are requiring services from. In this case auth0
 *
 * SAML: Security Assertion Markup Language. An open standard for exchanging authentication
 * and authorization data between application A and application B with the end goal being application A can authenticate
 * into application B without having to login using application B's login form.
 *
 * SAML Messages: This is the XML payload. That's literally all this is. Nothing exciting here. Messages can be sent from application
 * A to B or from application B to A depending on how application A is intending to login to application B.
 *
 * Serialized: The process of converting an object or object tree into XML.
 *
 * Bindings: Define how serialized SAML Messages will be encoded and transferred from application A to B or from application B to A.
 *
 * SAML Response: This is a SAML message that ONLY application A (IDP) sends to application B (SP).
 * This message contains the security assertions and other bearer information which must be included in the payload.
 *
 * Assertion Consumer Service: This is the URL that lives on application B (SP) that we actually send the SAML Response to.
 *
 * Issuer: This is also known as the entityId. If the IDP sends a message they will attach their own unique entityId. If
 * the SP sends a message they will atach their own entityId. The entityId is almost always a URL or URN.
 *
 * SAML Request (AuthnRequest): A SAML message that application B (SP) sends to Application A (IDP) in order to
 * kick off a SP-Init flow. Note: This type of message is ONLY ever sent with this flow type.
 *
 * There are 2 components to SAML. The first is the Identity Provider (IDP) and the second is the Service Provider (SP).
 * SAML is needed when application A (IDP) desires access to application B (SP) without wanting to login twice! This is more
 * commonly referred to as Single Sign On (SSO). There are 2 methods that are most commonly used for Single Sign On.
 *
 *
 * The first method is referred to as IDP-Init. The IDP-Init flow is as follows:
 *
 *
 *
 * High-Level-Overview
 *
 * Flow: Application A -> Application B
 *
 * Application A creates a SAML Response and sends it to application B. This gets sent out in 1 of 2 ways.
 * Option one is to just throw it in the URL and send it as a GET REQUEST like so: https://eni.shutterfly.com/p3/saml/SSO?SAMLRequest=BASE64.ENCODED.MESSAGE%3D%3D
 * This is referred to as a "Redirect binding". Notice how the XML is base 64 encoded? Take special note of the "Bindings" definition above.
 * Option two is to just send it as a POST REQUEST in an actual HTML form! Ha! Not as complicated as we all thought huh? This is referred to
 * as "Post binding". Again, take special note of the "Bindings" definition above. The one thing to note
 * is that both of these options need to actually happen from application A's browser. This can make things a bit tricky
 * as all the logic for creating the SAML Response exists on the server and is created either manually (which would be a nightmare),
 * or with whatever PHP Library you want to use: LightSAML, SimpleSamlPHP, etc. That being said, this forces us to have to be creative
 * and send a response back to the browser that looks like this: https://cl.ly/04072f393ad0 Notice how the SAML Response is base 64 encoded
 * just like the first example, but this time actually nested inside a FORM!. The only difference is we are auto-submitting the form on page load. A couple more quick notes.
 * The SAML Response needs additional data added to thee XML payload. There are a lot of good tutorials out there to see what all needs to be added!
 * The last thing you need to know is that before you send the Response it needs to be digitally signed, and also possibly encrypted. Check
 * with the Service provider prior to encrypting as they may not require/want this. If they do want this then you will either need
 * their public key which you can then use to decypt/encrypt messages or you will need to give them your public key so they can do the same.
 * Generating a public key (crt) and a private key is super easy and can be done with openssh. Once you do that just throw it anywhere in your application
 * that will be accessible from your codebase.
 *
 *
 *
 * The Second method is referred to as SP-Init. The SP-Init flow is as follows:
 *
 *
 * High-Level-Overview
 *
 * Flow: Application B -> Application A -> Application B
 *
 * The user visits application B's (IDP) website and is presented with a login form. The login form will have a button for Single Sign On.
 * Once the user clicks that button they are redirected back to a single URL which was setup by application A (IDP). This URL
 * is better known as.... Wait for it.... the IDP's "Super Spectacular Shiny Single Strenuous & Mildly Stressful Sign On Service". JK. It's actually much simpler
 * then that. It's just: "Single Sign On Service Endpoint"! The SP takes special care in adding some
 * very important query params to that URL prior to redirection.
 * This redirect is commonly referred to as the SAML Request (AuthnRequest) and looks like so:
 * http://p3.test/shutterfly/single-sign-on-service?SAMLRequest=fZFRT4MwFIX%2FCuk7tHRsc80gwe3BJVMXQR98MQXKaFJa7C3q%2Fr0wNJmJ2VuTnvOdnHPXwFvVsbR3jX4S770A5321SgM7f8Sot5oZDhKY5q0A5kqWpfd7RgPCOmucKY1CXgogrJNGb4yGvhU2E%2FZDluL5aR%2BjxrkOGMYj0Q0Bgaxw1siiMEq4JgAweERSfHjMcuRtB4nUfKRN3sHazYLRiaHpnRO2VicMUh%2BV8EEetW%2B0D1Mg8nbbGL0tq6igdFWTeUEjflMuRF2tKkqKZREtazIbZAC92GlwXLsYUUKJTyI%2FDHMSstmchYtX5B1%2B%2Bt1KXQ1p18coJhGwuzw%2F%2BFOVF2HhXGMQoGQ9DsDOwfZi5OtY%2FrssSv7bcXxj6Nb4gj0FdexhgO22B6NkefJSpcznxgruRIxChJPJ8vf0yTc%3D&RelayState=ss%3Amem%3A9a641715e0871cdccc654ce4d5df304f228c364400f4336692aa3373adf4ef13
 * I know that URL looks super daunting! But it's really not. You have two params in the query. SAMLRequest && RelayState. RelayState is just
 * a short string generated by the SP while SAMLRequest is just base64encoded XML which the IDP will make sense of on the server!
 * Once redirected back to application A the rest is fairly simple. The user is presented with application A's login form.
 * The user enters their credentials and are authenticated into application A's platform. Once authenticated then the entire process from the IDP-Init flow (Flow 1) repeats itself.
 * Application A creates the XML SAML Response, attaches the necessary data, digitally signs it, possibly encrypts and sends using either
 * the "Redirect binding" or the "Post binding". A couple more quick notes. The SAML Response needs to have additional data attached
 * to the XML as well, just like in the IDP-Init flow. This data can be extracted from the query parameters which we received from the SAML Request (AuthnRequest).
 * Whew! And there you have it.
 *
 *
 * There is another super duper important thing to take note of. The metadata files. Both the IDP and the SP are responsible for
 * creating their own and then passing it off to each-other respectively. The metadata file provides important information to both corresponding parties. The SP's metadata file
 * must contain at LEAST 2 valuable pieces of information. First their entityId. And second their Attribute Consume Service Endpoint.
 * This is simply the endpoint the IDP is allowed to send the SAML Response to! The IDP's metadata file must contain at LEAST 3
 * pieces of information. First the IDP's entityId. Second the IDP's Single Sign On Service Endpoint. Which take note, is only
 * actually used by the SP in the SP-Init flow. And last but not least, our public key (cert). There are a couple ways for you to
 * generate your metadata file. Option one, you can use a super cool online tool! https://www.samltool.com/idp_metadata.php
 * From there you just plugin your info and click download! That's it! Once you have your metadata.xml you can usually just email
 * it to the SP. Sometimes they will have a portal or website you can login and upload it to. Option 2 is to embed the metadata right
 * into the actual SAML Response! I would say this is the preferred solution as your public key (cert), which is embedded inside
 * the metadata, will expire at some point. If you go with the email route you will have to reach out every 365 days or so and keep
 * sending them your newly created public key (cert). Embedding it into your SAML Response allows the SP to grab the new cert
 * right from the actual payload each time you send them a Response. Same goes for the SP. They need to send
 * you their metadata file as well or embed the metadata into their Messages. One could almost argue that the SP should send
 * theirs first as a plain file, as this contains information you need to develop! Such as the
 * Attribute Consume Service Endpoint which we (The IDP) send our SAML Response to.
 *
 * One last note! The above tutorial is primarily focused on how the SAML flow works from an Identity Provider's
 * POV and does NOT touch on what happens on the Service Provider's side. Thanks!
 *
 *
 * Class IdpProvider
 * @package App\SAML
 */
class IdpProvider
{
    /**#@+
     * Service Providers Entity Ids
     * @var int
     */
    const AUTH0 = 'urn:auth0:dev-rpv5hibi:SAMP-SP';
    /**#@-*/

    /**
     * @var string
     */
    private $siteBaseUrl;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * Map of ENV specific domain to corresponding SP entity IDS and Assertion Consumer Services
     *
     * @var array
     */
    private $trustedServiceProviders = [
        'https://www.crm.dev' => [
            self::AUTH0 => [
                'SingleLogoutService' => 'https://dev-rpv5hibi.auth0.com/logout',
                'AssertionConsumerService' => 'https://dev-rpv5hibi.auth0.com/login/callback?connection=SAMP-SP'
            ]
        ]
        // todo Need to add other environments here along with the providers you want to initialize for those envs
    ];

    /**
     * Map of ENV specific domain to corresponding SP entity IDS and Assertion Consumer Services
     *
     * @var array
     */
    public static $customClaimTypes = [
        self::AUTH0 => [
            'user_first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/user_first_name',
            'user_last_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/user_last_name',
            'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/email',
            'address1' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/address1'
        ]
    ];

    /**
     * IdpProvider constructor.
     * @param string $siteBaseUrl
     * @param string $projectDirectory
     */
    public function __construct(string $siteBaseUrl, string $projectDirectory)
    {
        $this->siteBaseUrl = $siteBaseUrl;
        $this->projectDirectory = $projectDirectory;
    }

    /**
     * @param $spEntityId
     * @return bool
     */
    public function isTrustedServiceProvider($spEntityId) {

        return !empty($this->trustedServiceProviders[$this->siteBaseUrl]) &&
            array_key_exists($spEntityId, $this->trustedServiceProviders[$this->siteBaseUrl]);
    }

    /**
     * Retrieves the Assertion Consumer Service.
     *
     * @param string The Service Provider Entity Id
     * @return string The Assertion Consumer Service Url.
     * @throws \Exception
     */
    public function getServiceProviderAssertionConsumerService($spEntityId) {

        if(empty($this->trustedServiceProviders[$this->siteBaseUrl][$spEntityId]['AssertionConsumerService'])) {
            return false;
        }

        return $this->trustedServiceProviders[$this->siteBaseUrl][$spEntityId]['AssertionConsumerService'];
    }

    /**
     * Returns our Identity Provider Identifier
     *
     * @return string
     */
    public function getIdentityProviderId(){
        return $this->siteBaseUrl;
    }

    /**
     * Retrieves the certificate from the IdP.
     *
     * @return \LightSaml\Credential\X509Certificate
     */
    public function getCertificate(){
        return \LightSaml\Credential\X509Certificate::fromFile($this->projectDirectory . '/cert/saml.crt');
    }

    /**
     * Retrieves the private key from the Idp.
     *
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    public function getPrivateKey(){
        return \LightSaml\Credential\KeyHelper::createPrivateKey($this->projectDirectory . '/cert/saml.pem', '', true);
    }
}