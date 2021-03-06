<?php

namespace Markup\ContentfulBundle\Command;

use Markup\Contentful\Contentful;
use Markup\Contentful\SpaceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InteractCommand extends Command
{
    /**
     * @var Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('markup:contentful:interact')
            ->setDescription('A command for interaction with Contentful space through the Contentful APIs')
            ->addArgument('space', InputArgument::REQUIRED, 'The name of the Contentful space to interact with');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $space = strval($input->getArgument('space'));

        //compatibility with symfony 2.3 and 3.0 - 3.0 has question helper instead of dialog
        if (!$this->getHelperSet()->has('dialog')) {
            $askChoiceQuestion = function ($question, $choices) use ($input, $output) {
                return $this->getHelper('question')->ask(
                    $input,
                    $output,
                    new ChoiceQuestion($question, $choices)
                );
            };
        } else {
            $askChoiceQuestion = function ($question, $choices) use ($output) {
                return $this->getHelper('dialog')->select(
                    $output,
                    $question,
                    $choices
                );
            };
        }

        $fetchSpace = function (OutputInterface $output) use ($space) {
            $space = $this->contentful->getSpace($space);
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
            $actionIndex = $askChoiceQuestion('Please select:', array_keys($actions));
            $action = $actions[array_keys($actions)[$actionIndex]];
            $exitCode = $action($output);
            if (is_int($exitCode)) {
                return $exitCode;
            }
        }
    }
}
