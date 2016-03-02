<?php

namespace CliTools\Utility;

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

use CliTools\Iterator\Filter\Typo3RecursiveDirectoryFilter;
use Symfony\Component\Console\Input\InputInterface;

class Typo3Utility
{

    const PASSWORD_TYPE_MD5_SALTED = 'md5_salted';
    const PASSWORD_TYPE_MD5        = 'md5';

    /**
     * Generate TYPO3 password
     *
     * @param    string $password Password
     * @param    string $type     Type of password (see constants)
     *
     * @return    null|string
     */
    public static function generatePassword($password, $type = null)
    {
        $ret = null;

        if ($type === null) {
            $type = self::PASSWORD_TYPE_MD5_SALTED;
        }

        switch ($type) {

            // ##############
            // Salted MD5
            // ##############
            case self::PASSWORD_TYPE_MD5_SALTED:
                // Salted md5
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $salt  = '$1$' . substr(str_shuffle($chars), 0, 6) . '$';
                $ret   = crypt($password, $salt);
                break;

            // ##############
            // MD5
            // ##############
            case self::PASSWORD_TYPE_MD5:
                $ret = md5($password);
                break;
        }

        return $ret;
    }


    /**
     * Guess best TYPO3 base path
     *
     * @param  string         $basePath     System base path
     * @param  InputInterface $input        Input instance
     * @param  null|string    $inputArgName Input option name for base path
     *
     * @return null|string
     * @throws \RuntimeException
     */
    public static function guessBestTypo3BasePath($basePath, $input = null, $inputArgName = null)
    {
        $ret = null;

        $userPath = null;

        if ($input !== null && $input instanceof InputInterface && $inputArgName !== null) {
            if ($input->hasArgument($inputArgName)) {
                $userPath = $input->getArgument($inputArgName);
            }
        }

        if (empty($userPath)) {
            // No user path specified, only use base path
            $ret = $basePath;
        } else {
            // check if path is an absolute path
            if (strpos($userPath, '/') === 0) {
                $ret = $userPath;
            } else {
                // relative path? try to guess the best match

                $guessPath = $basePath . '/' . $userPath;
                if (is_dir($guessPath)) {
                    $ret = $guessPath;
                }
            }
        }

        if ($ret === null) {
            throw new \RuntimeException('Could not guess TYPO3 base path');
        }

        return $ret;
    }


    /**
     * Get TYPO3 instance paths
     *
     * @param  string $basePath Base path
     * @param  int    $maxDepth Max depth
     *
     * @return array
     */
    public static function getTypo3InstancePathList($basePath, $maxDepth = 3)
    {
        $ret = array();

        // ####################
        // Iterators
        // ####################
        $dirIterator = new \RecursiveDirectoryIterator($basePath);
        $dirIterator = new Typo3RecursiveDirectoryFilter($dirIterator);

        $dirIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);
        $dirIterator->setMaxDepth($maxDepth);

        // ####################
        // Find and loop through TYPO3 instances
        // ####################

        foreach ($dirIterator as $dirEntry) {
            /** @var \DirectoryIterator $dirEntry */
            $dirPath = $dirEntry->getRealPath();

            // Check if current dir is possible typo3 instance
            if (is_dir($dirPath . '/typo3conf/')) {
                // seems to be a valid typo3 path
                $ret[] = $dirPath;
            }
        }

        return $ret;
    }
}
