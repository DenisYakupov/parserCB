<?php

namespace Command;

use api\ApiModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpdataCommand
 */
class UpdateCommand extends Command
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName("update")
            ->setDescription("Command update DB");
    }

    /**
     * Execute method of command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $cacheName =  $_SERVER['DOCUMENT_ROOT'] . 'parserCB.xml.cache';
        unlink($cacheName);

        $update = new ApiModel();

        if ($update->update()) {
            $message = 'обновление прошло успешно';
        }else {
            $message = 'неудача';
        }

        $output->writeln($message);
    }
}