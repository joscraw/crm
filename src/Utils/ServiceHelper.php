<?php

namespace App\Utils;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Repository\ApiTokenRepository;
use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\GmailAttachmentRepository;
use App\Repository\GmailMessageRepository;
use App\Repository\GmailAccountRepository;
use App\Repository\GmailThreadRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\GmailProvider;
use App\Service\ImageCacheGenerator;
use App\Service\PhpSpreadsheetHelper;
use App\Service\UploaderHelper;
use App\Service\WorkflowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

trait ServiceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     * @var GuardAuthenticatorHandler $guardHandler,
     */
    private $guardHandler;

    /**
     * @var LoginFormAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TokenStorageInterface
     */
    private $securityToken;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var KernelInterface
     */
    private $appKernel;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var GmailProvider
     */
    private $gmailProvider;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * @var GmailAccountRepository
     */
    private $gmailRepository;

    /**
     * @var GmailThreadRepository
     */
    private $gmailThreadRepository;

    /**
     * @var GmailMessageRepository
     */
    private $gmailMessageRepository;

    /**
     * @var GmailAttachmentRepository
     */
    private $gmailAttachmentRepository;

    /**
     * @var ApiTokenRepository
     */
    private $apiTokenRepo;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * @var FilterRepository
     */
    private $filterRepository;

    /**
     * @var WorkflowProcessor
     */
    private $workflowProcessor;

    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * @var UploaderHelper
     */
    private $uploadHelper;

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
     * @param WorkflowProcessor $workflowProcessor
     * @param MessageBusInterface $bus
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param UploaderHelper $uploadHelper
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
        WorkflowProcessor $workflowProcessor,
        MessageBusInterface $bus,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        UploaderHelper $uploadHelper
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
        $this->workflowProcessor = $workflowProcessor;
        $this->bus = $bus;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadHelper = $uploadHelper;
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
}
