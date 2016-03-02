<?php

namespace CliTools\Console\Command\Docker;

/*
 * CliTools Command
 * Copyright (C) 2016 WebDevOps.io
 * Copyright (C) 2015 Markus Blaschke <markus@familie-blaschke.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use CliTools\Shell\CommandBuilder\CommandBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends AbstractCommand
{

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('docker:up')
             ->setDescription('Start docker container (with fast switching)')
             ->addOption(
                 'switch',
                 's',
                 InputOption::VALUE_NONE,
                 'Switch mode (shutdown previous started Docker instance)'
             );
    }

    /**
     * Execute command
     *
     * @param  InputInterface  $input  Input instance
     * @param  OutputInterface $output Output instance
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $dockerPath     = \CliTools\Utility\DockerUtility::searchDockerDirectoryRecursive();
        $lastDockerPath = $this->getApplication()
                               ->getSettingsService()
                               ->get('docker.up.last');
        $dockerComposeArgs = $this->getApplication()
                                  ->getConfigValue('dockerCompose', 'up');

        if (!empty($dockerPath)) {
            $dockerPath = dirname($dockerPath);
        }

        $output->writeln('<h2>Starting docker containers</h2>');

        if ($input->getOption('switch')) {
            // Stop last docker instance
            if ($dockerPath && $lastDockerPath) {
                // Only stop if instance is another one
                if ($dockerPath !== $lastDockerPath) {
                    $this->stopContainersFromPrevRun($lastDockerPath);
                }
            }
        }

        // Start current docker containers
        $this->output->writeln('<p>Start docker containers in "' . $dockerPath . '"</p>');
        $command = new CommandBuilder(null, $dockerComposeArgs);
        $ret     = $this->executeDockerCompose($command);

        // Store docker path in settings (last docker startup)
        if ($dockerPath) {
            $this->getApplication()
                 ->getSettingsService()
                 ->set('docker.up.last', $dockerPath);
        }

        return $ret;
    }

    /**
     * Stop last docker containers from previous run
     *
     * @param string $path Path
     */
    protected function stopContainersFromPrevRun($path)
    {
        $currentPath = getcwd();

        try {
            $this->output->writeln('<p>Trying to stop last running docker container in "' . $path . '"</p>');

            // Jump into last docker dir
            \CliTools\Utility\PhpUtility::chdir($path);

            // Stop docker containers
            $command = new CommandBuilder(null, 'stop');
            $this->executeDockerCompose($command);

            // Jump back
            \CliTools\Utility\PhpUtility::chdir($currentPath);
        } catch (\Exception $e) {
        }
    }
}
