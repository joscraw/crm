<?php

namespace App\Utils;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Dto\DataTransformer\DataTransformerFactory;
use App\Dto\DtoFactory;
use App\Mailer\ResetPasswordMailer;
use App\Repository\ApiTokenRepository;
use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\FolderRepository;
use App\Repository\FormRepository;
use App\Repository\GmailAttachmentRepository;
use App\Repository\GmailMessageRepository;
use App\Repository\GmailAccountRepository;
use App\Repository\GmailThreadRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PortalRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\WorkflowRepository;
use App\Http\SAML\IdpProvider;
use App\Http\SAML\IdpTools;
use App\Security\Auth\PermissionManager;
use App\Security\Auth0Service;
use App\Security\LoginFormAuthenticator;
use App\Service\GmailProvider;
use App\Service\ImageCacheGenerator;
use App\Service\PhpSpreadsheetHelper;
use App\Service\PortalResolver;
use App\Service\SessionStore;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

trait ServiceHelper
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Packages
     */
    protected $assetsManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ValidatorInterface $validator
     */
    protected $validator;

    /**
     * @var GuardAuthenticatorHandler $guardHandler,
     */
    protected $guardHandler;

    /**
     * @var LoginFormAuthenticator $authenticator
     */
    protected $authenticator;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var TokenStorageInterface
     */
    protected $securityToken;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var UploaderHelper
     */
    protected $uploaderHelper;

    /**
     * @var RecordRepository
     */
    protected $recordRepository;

    /**
     * @var KernelInterface
     */
    protected $appKernel;

    /**
     * @var CustomObjectRepository
     */
    protected $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    protected $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    protected $propertyGroupRepository;

    /**
     * @var GmailProvider
     */
    protected $gmailProvider;
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    protected $uploadsPath;

    /**
     * @var GmailAccountRepository
     */
    protected $gmailRepository;

    /**
     * @var GmailThreadRepository
     */
    protected $gmailThreadRepository;

    /**
     * @var GmailMessageRepository
     */
    protected $gmailMessageRepository;

    /**
     * @var GmailAttachmentRepository
     */
    protected $gmailAttachmentRepository;

    /**
     * @var ApiTokenRepository
     */
    protected $apiTokenRepo;

    /**
     * @var ImageCacheGenerator
     */
    protected $imageCacheGenerator;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var PermissionAuthorizationHandler
     */
    protected $permissionAuthorizationHandler;

    /**
     * @var FilterRepository
     */
    protected $filterRepository;

    /**
     * @var MessageBusInterface $bus
     */
    protected $bus;

    /**
     * @var PhpSpreadsheetHelper;
     */
    protected $phpSpreadsheetHelper;

    /**
     * @var UploaderHelper
     */
    protected $uploadHelper;

    /**
     * @var ReportRepository
     */
    protected $reportRepository;

    /**
     * @var MarketingListRepository
     */
    protected $marketingListRepository;

    /**
     * @var FolderRepository
     */
    protected $folderRepository;

    /**
     * @var ListFolderBreadcrumbs
     */
    protected $folderBreadcrumbs;

    /**
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var WorkflowRepository
     */
    protected $workflowRepository;

    /**
     * @var ResetPasswordMailer
     */
    protected $resetPasswordMailer;

    /**
     * @var SessionStore
     */
    protected $sessionStore;

    /**
     * @var IdpProvider
     */
    protected $idpProvider;

    /**
     * @var IdpTools
     */
    protected $idpTools;

    /**
     * @var Auth0Service
     */
    protected $auth0Service;

    /**
     * @var DtoFactory
     */
    protected $dtoFactory;

    /**
     * @var DataTransformerFactory
     */
    protected $dataTransformerFactory;

    /**
     * @var PermissionManager
     */
    protected $permissionManager;

    /**
     * @var PortalRepository
     */
    protected $portalRepository;

    /**
     * @var PortalResolver
     */
    protected $portalResolver;

    /**
     * ServiceHelper constructor.
     * @param EntityManagerInterface $entityManager
     * @param Packages $assetsManager
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     * @param ValidatorInterface $validator
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param Environment $twig
     * @param TokenStorageInterface $securityToken
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UploaderHelper $uploaderHelper
     * @param RecordRepository $recordRepository
     * @param KernelInterface $appKernel
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param GmailProvider $gmailProvider
     * @param SessionInterface $session
     * @param string $uploadsPath
     * @param GmailAccountRepository $gmailRepository
     * @param GmailThreadRepository $gmailThreadRepository
     * @param GmailMessageRepository $gmailMessageRepository
     * @param GmailAttachmentRepository $gmailAttachmentRepository
     * @param ApiTokenRepository $apiTokenRepo
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param CacheManager $cacheManager
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     * @param FilterRepository $filterRepository
     * @param MessageBusInterface $bus
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param UploaderHelper $uploadHelper
     * @param ReportRepository $reportRepository
     * @param MarketingListRepository $marketingListRepository
     * @param FolderRepository $folderRepository
     * @param ListFolderBreadcrumbs $folderBreadcrumbs
     * @param FormRepository $formRepository
     * @param DenormalizerInterface $denormalizer
     * @param RoleRepository $roleRepository
     * @param WorkflowRepository $workflowRepository
     * @param ResetPasswordMailer $resetPasswordMailer
     * @param SessionStore $sessionStore
     * @param IdpProvider $idpProvider
     * @param IdpTools $idpTools
     * @param Auth0Service $auth0Service
     * @param DtoFactory $dtoFactory
     * @param DataTransformerFactory $dataTransformerFactory
     * @param PermissionManager $permissionManager
     * @param PortalRepository $portalRepository
     * @param PortalResolver $portalResolver
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Packages $assetsManager,
        UserRepository $userRepository,
        RouterInterface $router,
        ValidatorInterface $validator,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        Environment $twig,
        TokenStorageInterface $securityToken,
        SerializerInterface $serializer,
        UserPasswordEncoderInterface $passwordEncoder,
        UploaderHelper $uploaderHelper,
        RecordRepository $recordRepository,
        KernelInterface $appKernel,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        GmailProvider $gmailProvider,
        SessionInterface $session,
        string $uploadsPath,
        GmailAccountRepository $gmailRepository,
        GmailThreadRepository $gmailThreadRepository,
        GmailMessageRepository $gmailMessageRepository,
        GmailAttachmentRepository $gmailAttachmentRepository,
        ApiTokenRepository $apiTokenRepo,
        ImageCacheGenerator $imageCacheGenerator,
        CacheManager $cacheManager,
        PermissionAuthorizationHandler $permissionAuthorizationHandler,
        FilterRepository $filterRepository,
        MessageBusInterface $bus,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        UploaderHelper $uploadHelper,
        ReportRepository $reportRepository,
        MarketingListRepository $marketingListRepository,
        FolderRepository $folderRepository,
        ListFolderBreadcrumbs $folderBreadcrumbs,
        FormRepository $formRepository,
        DenormalizerInterface $denormalizer,
        RoleRepository $roleRepository,
        WorkflowRepository $workflowRepository,
        ResetPasswordMailer $resetPasswordMailer,
        SessionStore $sessionStore,
        IdpProvider $idpProvider,
        IdpTools $idpTools,
        Auth0Service $auth0Service,
        DtoFactory $dtoFactory,
        DataTransformerFactory $dataTransformerFactory,
        PermissionManager $permissionManager,
        PortalRepository $portalRepository,
        PortalResolver $portalResolver
    ) {
        $this->entityManager = $entityManager;
        $this->assetsManager = $assetsManager;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->validator = $validator;
        $this->guardHandler = $guardHandler;
        $this->authenticator = $authenticator;
        $this->twig = $twig;
        $this->securityToken = $securityToken;
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
        $this->uploaderHelper = $uploaderHelper;
        $this->recordRepository = $recordRepository;
        $this->appKernel = $appKernel;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->gmailProvider = $gmailProvider;
        $this->session = $session;
        $this->uploadsPath = $uploadsPath;
        $this->gmailRepository = $gmailRepository;
        $this->gmailThreadRepository = $gmailThreadRepository;
        $this->gmailMessageRepository = $gmailMessageRepository;
        $this->gmailAttachmentRepository = $gmailAttachmentRepository;
        $this->apiTokenRepo = $apiTokenRepo;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->cacheManager = $cacheManager;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
        $this->filterRepository = $filterRepository;
        $this->bus = $bus;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadHelper = $uploadHelper;
        $this->reportRepository = $reportRepository;
        $this->marketingListRepository = $marketingListRepository;
        $this->folderRepository = $folderRepository;
        $this->folderBreadcrumbs = $folderBreadcrumbs;
        $this->formRepository = $formRepository;
        $this->denormalizer = $denormalizer;
        $this->roleRepository = $roleRepository;
        $this->workflowRepository = $workflowRepository;
        $this->resetPasswordMailer = $resetPasswordMailer;
        $this->sessionStore = $sessionStore;
        $this->idpProvider = $idpProvider;
        $this->idpTools = $idpTools;
        $this->auth0Service = $auth0Service;
        $this->dtoFactory = $dtoFactory;
        $this->dataTransformerFactory = $dataTransformerFactory;
        $this->permissionManager = $permissionManager;
        $this->portalRepository = $portalRepository;
        $this->portalResolver = $portalResolver;
    }


    /**
     * Returns the site url
     * @return string
     */
    public function getFullQualifiedBaseUrl() {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();
        return sprintf('%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'. $port : ''),
            $routerContext->getBaseUrl()
        );
    }

    protected function createLinkUrl($targetPage, $route, $routeParams) {
        return $this->router->generate($route, array_merge(
            $routeParams,
            array('page' => $targetPage)
        ));
    }
}
