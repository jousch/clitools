<?php

namespace CliTools\Console\Command\System;

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

use CliTools\Iterator\Filter\ProcProcessDirectoryFilter;
use CliTools\Utility\FormatUtility;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwapCommand extends \CliTools\Console\Command\AbstractCommand
{

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('system:swap')
             ->setDescription('List swap usage');
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
        $this->elevateProcess($input, $output);

        $dirIterator = new \DirectoryIterator('/proc');
        $dirIterator = new ProcProcessDirectoryFilter($dirIterator);

        $procList  = array();
        $swapTotal = 0;
        foreach ($dirIterator as $dirEntry) {
            /** @var \DirectoryIterator $dirEntry */
            $processStatsPath = $dirEntry->getRealPath();

            // Get process name and swap

            $processName = trim(file_get_contents($processStatsPath . '/comm'));
            $processSwap = $this->getProcessSwap($processStatsPath);

            if (!empty($processSwap)) {
                $swapTotal += $processSwap;

                if (!empty($procList[$processName])) {
                    // existing proc
                    $procList[$processName]['swap'] += $processSwap;
                } else {
                    // new proc
                    $procList[$processName] = array(
                        'name' => $processName,
                        'swap' => $processSwap,
                    );
                }
            }
        }

        // ########################
        // Sort
        // ########################

        uasort(
            $procList,
            function ($a, $b) {
                return $a['swap'] > $b['swap'];
            }
        );

        // ########################
        // Output
        // ########################
        if (!empty($procList)) {
            /** @var \Symfony\Component\Console\Helper\Table $table */
            $table = new Table($output);
            $table->setHeaders(array('Process', 'Swap'));

            foreach ($procList as $procRow) {
                $procRow['swap'] = FormatUtility::bytes($procRow['swap']);
                $table->addRow(array_values($procRow));
            }

            // Stats: average
            $table->addRow(new TableSeparator());
            $statsRow                = array();
            $statsRow['name']        = 'Total';
            $statsRow['table_count'] = FormatUtility::bytes($swapTotal);
            $table->addRow(array_values($statsRow));

            $table->render();
        } else {
            $output->writeln('<info>No swap usage (or not detectable)</info>');
        }

        return 0;
    }

    /**
     * Get swap from process (in bytes)
     *
     * @param  string $processStatsPath Path to process (eg. /proc/123)
     *
     * @return int|null
     */
    protected function getProcessSwap($processStatsPath)
    {
        $ret       = 0;
        $smapsFile = $processStatsPath . '/smaps';

        if (is_readable($smapsFile)) {
            $smaps = file_get_contents($smapsFile);

            if (!empty($smaps)) {
                $smaps = explode("\n", $smaps);

                foreach ($smaps as $smapsLine) {
                    if (preg_match('/^Swap:[\s]*([0-9]+)[\s]*kB/i', $smapsLine, $matches)) {
                        $ret += $matches[1];
                    }
                }
            } else {
                $ret = 0;
            }
        }

        // Swap is messured in kb, but we need bytes
        if (!empty($ret)) {
            $ret *= 1024;
        }

        return $ret;
    }
}
