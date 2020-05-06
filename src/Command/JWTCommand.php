<?php

namespace App\Command;

use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JWTCommand
 * @package App\Command
 */
class JWTCommand extends Command
{
    use RandomStringGenerator;

    protected static $defaultName = 'app:generate:jwt';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * ApiKeyCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param JWTEncoderInterface $jwtEncoder
     */
    public function __construct(EntityManagerInterface $entityManager, JWTEncoderInterface $jwtEncoder)
    {
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('Generates an api key which can be used to authenticate and access certain endpoints where an auth0 user access token is not mandatory.')
            ->addArgument('name',InputArgument::REQUIRED, 'A name to remember why this access token was generated. Example: "Marketing Site Api Key"')
            ->addOption('--scopes', '-s', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Permission scopes this JWT is allowed to utilize.')
            ->addOption('--expiresIn','-exp', InputOption::VALUE_OPTIONAL, 'Number of seconds till api key expires.');
    }

    /**
     * Here is an example of how you can call this command
     * ./bin/console app:generate:jwt 'test' -s "create:portals" -s "update:portals" --expiresIn 3600
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = $input->getArgument('name');
        $scopes = $input->getOption('scopes');
        $expiresIn = $input->getOption('expiresIn');

        $data = [
            'id' => $this->generateRandomString(),
            'name' => $name,
            'scopes' => $scopes
        ];

        if($expiresIn) {
            $data['exp'] = time() + $expiresIn;
        }

        $token = $this->jwtEncoder->encode($data);

        $output->write($token);
    }
}