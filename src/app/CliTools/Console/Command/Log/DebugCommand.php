<?php

namespace CliTools\Console\Command\Log;

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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends \CliTools\Console\Command\AbstractCommand
{

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('log:debug')
             ->setAliases(array('debug'))
             ->setDescription('Show up debugging log')
             ->addArgument(
                 'grep',
                 InputArgument::OPTIONAL,
                 'Grep'
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
        // Read grep value
        $grep = null;
        if ($input->hasArgument('grep')) {
            $grep = $input->getArgument('grep');
        }

        $output->writeln('<h2>Starting debug log tail</h2>');

        // Show log
        $logList = array(
            //	'/var/log/apache2/access.log',
            '/var/log/apache2/error.log',
            '/var/log/php-fpm/dev.error.log',
        );

        $optionList = array(
            '--mark-interval 5',
            '-n 0',
        );

        return $this->showLog($logList, $input, $output, $grep, $optionList);
    }
}
