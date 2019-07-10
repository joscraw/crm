<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Enqueue\Redis\RedisConnectionFactory;

class TestCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test';

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $name = "Josh";

        $connectionFactory = new RedisConnectionFactory([
            'host' => 'localhost',
            'port' => 6379,
            'scheme_extensions' => ['predis'],
        ]);

        $context = $connectionFactory->createContext();

        $fooQueue = $context->createQueue('aQueue');
        $consumer = $context->createConsumer($fooQueue);

        $message = $consumer->receive();


        // process a message

        $consumer->acknowledge($message);

        $output->writeln([
            $message->getBody(),
            '============',
            '',
        ]);

    }
}