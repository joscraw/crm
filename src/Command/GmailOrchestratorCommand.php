<?php

namespace App\Command;

use App\Entity\GmailAccount;
use App\Message\LoadGmailMessages;
use App\Repository\GmailAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class GmailOrchestratorCommand
 * @package App\Command
 */
class GmailOrchestratorCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var GmailAccountRepository
     */
    private $gmailRepository;

    protected static $defaultName = 'app:gmail:orchestrator';

    /**
     * GmailOrchestratorCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param GmailAccountRepository $gmailRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        GmailAccountRepository $gmailRepository
    ) {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->gmailRepository = $gmailRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Orchestrates syncing of all Gmail messages for each portal.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gmailAccounts = $this->gmailRepository->findAll();
        /** @var GmailAccount $gmailAccount */
        foreach($gmailAccounts as $gmailAccount) {
            $this->bus->dispatch(new LoadGmailMessages($gmailAccount->getId()));
            $output->writeln([sprintf('Adding portal %s to queue for loading gmail messages at gmail current history id %s...', $gmailAccount->getPortal()->getInternalIdentifier(), $gmailAccount->getCurrentHistoryId()), '============', '',]);
        }
    }
}