<?php

namespace App\Command;

use App\Mailer\ResetPasswordMailer;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use App\Service\WorkflowProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test';

    /**
     * @var WorkflowProcessor
     */
    private $workflowProcessor;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * WorkflowHandler constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param ResetPasswordMailer $resetPasswordMailer
     * @param UserRepository $userRepository
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        ResetPasswordMailer $resetPasswordMailer,
        UserRepository $userRepository
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->resetPasswordMailer = $resetPasswordMailer;
        $this->userRepository = $userRepository;

        parent::__construct();
    }




    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $user = $this->userRepository->find(1);
        $this->resetPasswordMailer->send($user);

        $output->writeln([
            'done',
            '============',
            '',
        ]);

    }
}