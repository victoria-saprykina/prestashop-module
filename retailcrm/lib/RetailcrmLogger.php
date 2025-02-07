<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DIGITAL RETAIL TECHNOLOGIES SL
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    DIGITAL RETAIL TECHNOLOGIES SL <mail@simlachat.com>
 * @copyright 2020 DIGITAL RETAIL TECHNOLOGIES SL
 * @license   https://opensource.org/licenses/MIT  The MIT License
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class RetailcrmLogger
 * @author    DIGITAL RETAIL TECHNOLOGIES SL <mail@simlachat.com>
 * @license   GPL
 * @link      https://retailcrm.ru
 */
class RetailcrmLogger
{
    static $cloneToStdout;

    /**
     * Set to true if you want all output to be cloned into STDOUT
     *
     * @param bool $cloneToStdout
     */
    public static function setCloneToStdout($cloneToStdout)
    {
        self::$cloneToStdout = $cloneToStdout;
    }

    /**
     * Write entry to log
     *
     * @param string $caller
     * @param string $message
     */
    public static function writeCaller($caller, $message)
    {
        $result = sprintf(
            '[%s] @ [%s] %s' . PHP_EOL,
            date(DATE_RFC3339),
            $caller,
            $message
        );

        error_log(
            $result,
            3,
            static::getLogFile()
        );

        if (self::$cloneToStdout) {
            self::output($result, '');
        }
    }

    /**
     * Write entry to log without caller name
     *
     * @param string $message
     */
    public static function writeNoCaller($message)
    {
        $result = sprintf(
            '[%s] %s' . PHP_EOL,
            date(DATE_RFC3339),
            $message
        );

        error_log(
            $result,
            3,
            static::getLogFile()
        );

        if (self::$cloneToStdout) {
            self::output($result, '');
        }
    }

    /**
     * Output message to stdout
     *
     * @param string $message
     * @param string $end
     */
    public static function output($message = '', $end = PHP_EOL)
    {
        if (php_sapi_name() == 'cli') {
            echo $message . $end;
        }
    }

    /**
     * Output error info to stdout
     *
     * @param $exception
     * @param string $header
     */
    public static function printException($exception, $header = 'Error while executing a job: ', $toOutput = true)
    {
        $method = $toOutput ? 'output' : 'writeNoCaller';

        self::$method(sprintf('%s%s', $header, $exception->getMessage()));
        self::$method(sprintf('%s:%d', $exception->getFile(), $exception->getLine()));
        self::$method('');
        self::$method($exception->getTraceAsString());
    }

    /**
     * Write debug log record
     *
     * @param string $caller
     * @param mixed $message
     */
    public static function writeDebug($caller, $message)
    {
        if (RetailcrmTools::isDebug()) {
            static::writeNoCaller(sprintf(
                '(DEBUG) <%s> %s',
                $caller,
                print_r($message, true)
            ));
        }
    }

    /**
     * Debug log record with multiple entries
     *
     * @param string       $caller
     * @param array|string $messages
     */
    public static function writeDebugArray($caller, $messages)
    {
        if (RetailcrmTools::isDebug()) {
            if (!empty($caller) && !empty($messages)) {
                $result = is_array($messages) ? substr(
                    array_reduce(
                        $messages,
                        function ($carry, $item) {
                            $carry .= ' ' . print_r($item, true);
                            return $carry;
                        }
                    ),
                    1
                ) : $messages;

                self::writeDebug($caller, $result);
            }
        }
    }

    /**
     * Returns log file path
     *
     * @return string
     */
    public static function getLogFile()
    {
        if (!defined('_PS_ROOT_DIR_')) {
            return '';
        }

        return self::getLogDir() . '/retailcrm_' . self::getLogFilePrefix() . '_' . date('Y_m_d') . '.log';
    }

    /**
     * Returns log files directory based on current Prestashop version
     *
     * @return string
     */
    public static function getLogDir()
    {
        $logDir = version_compare(_PS_VERSION_, '1.7', '<') ? '/log' : '/var/logs';

        return _PS_ROOT_DIR_ . $logDir;
    }

    /**
     * Returns log file prefix based on current environment
     *
     * @return string
     */
    private static function getLogFilePrefix()
    {
        if (php_sapi_name() == 'cli') {
            if (isset($_SERVER['TERM'])) {
                return 'cli';
            } else {
                return 'cron';
            }
        }

        return 'web';
    }

    /**
     * Removes module log files that are older than 30 days
     */
    public static function clearObsoleteLogs()
    {
        $logFiles = self::getLogFiles();

        foreach ($logFiles as $logFile) {
            if (filemtime($logFile) < strtotime('-30 days')) {
                unlink($logFile);
            }
        }
    }

    /**
     * Retrieves log files basic info for advanced tab
     *
     * @return array
     */
    public static function getLogFilesInfo()
    {
        $fileNames = [];
        $logFiles = self::getLogFiles();

        foreach ($logFiles as $logFile) {
                $fileNames[] = [
                    'name' => basename($logFile),
                    'path' => $logFile,
                    'size' => number_format(filesize($logFile), 0, '.', ' ') . ' bytes',
                    'modified' => date('Y-m-d H:i:s', filemtime($logFile)),
                ];
            }

        return $fileNames;
    }

    /**
     * Retrieves log files paths
     *
     * @return Generator|void
     */
    private static function getLogFiles()
    {
        $logDir = self::getLogDir();

        if (!is_dir($logDir)) {
            return;
        }

        $handle = opendir($logDir);
        while (($file = readdir($handle)) !== false) {
            if (self::checkFileName($file) !== false) {
                yield "$logDir/$file";
            }
        }

        closedir($handle);
    }

    /**
     * Checks if given logs filename relates to the module
     *
     * @param string $file
     * @return false|string
     */
    public static function checkFileName($file)
    {
        $logDir = self::getLogDir();
        if (preg_match('/^retailcrm[a-zA-Z0-9-_]+.log$/', $file)) {
            $path = "$logDir/$file";
            if (is_file($path)) {
                return $path;
            }
        }

        return false;
    }
}