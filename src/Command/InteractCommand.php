<?php

namespace Markup\ContentfulBundle\Command;

use Markup\Contentful\SpaceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InteractCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('markup:contentful:interact')
            ->setDescription('A command for interaction with Contentful space through the Contentful APIs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentful = $this->getContainer()->get('markup_contentful');

        $dialog = $this->getHelper('dialog');

        $fetchSpace = function (OutputInterface $output) use ($contentful) {
            $space = $contentful->getSpace();
            if (!$space instanceof SpaceInterface) {
                $output->writeln('<error>Could not get space.</error>');

                return 1;
            }
            $output->writeln(sprintf('Got space with ID "%s" at revision %u.', $space->getId(), $space->getRevision()));

            return null;
        };

        $exit = function (OutputInterface $output) {
            $output->writeln('Bye!');

            return 0;
        };

        $actions = [
            'Exit' => $exit,
            'Get information about a named space' => $fetchSpace,
        ];

        while (true) {
            $actionIndex = $dialog->select(
                $output,
                'Please select:',
                array_keys($actions)
            );
            $action = $actions[array_keys($actions)[$actionIndex]];
            $exitCode = $action($output);
            if (is_int($exitCode)) {
                return $exitCode;
            }
        }
    }
}
