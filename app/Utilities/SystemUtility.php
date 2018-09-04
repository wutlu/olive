<?php

namespace App\Utilities;

use App\Models\Log;

class SystemUtility
{
    # system activity
    public static function log(string $message, string $module, int $level = 1)
    {
        $uuid = md5(implode('.', [ $module, $level ]));

        try
        {
            $log = Log::updateOrCreate(
                [
                    'uuid' => $uuid
                ],
                [
                    'message' => $message,
                    'module' => $module,
                    'level' => $level
                ]
            );

            $log->increment('hit');
        }
        catch (\Exception $e)
        {
            //
        }
    }

    /**
     * Return RAM Total in Bytes.
     *
     * @return int Bytes
     */
    public static function getRamTotal()
    {
        $result = 0;

        if (PHP_OS == 'WINNT')
        {
            $lines = null;
            $matches = null;

            exec('wmic ComputerSystem get TotalPhysicalMemory /Value', $lines);

            if (preg_match('/^TotalPhysicalMemory\=(\d+)$/', $lines[2], $matches))
            {
                $result = $matches[1];
            }
        }
        else
        {
            $fh = fopen('/proc/meminfo', 'r');

            while ($line = fgets($fh))
            {
                $pieces = [];

                if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces))
                {
                    $result = $pieces[1];
                    $result = $result * 1024;

                    break;
                }
            }

            fclose($fh);
        }

        return Term::humanFileSize($result);
    }

    /**
     * Return free RAM in Bytes.
     *
     * @return int Bytes
     */
    public static function getRamFree()
    {
        $result = 0;

        if (PHP_OS == 'WINNT')
        {
            $lines = null;
            $matches = null;

            exec('wmic OS get FreePhysicalMemory /Value', $lines);

            if (preg_match('/^FreePhysicalMemory\=(\d+)$/', $lines[2], $matches))
            {
                $result = $matches[1] * 1024;
            }
        }
        else
        {
            $fh = fopen('/proc/meminfo', 'r');

            while ($line = fgets($fh))
            {
                $pieces = [];

                if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces))
                {
                    $result = $pieces[1] * 1024;

                    break;
                }
            }
            fclose($fh);
        }

        return Term::humanFileSize($result);
    }

    /**
     * Return harddisk infos.
     *
     * @param sring $path Drive or path
     * @return array Disk info
     */
    public static function getDiskSize($path = '/')
    {
        $result = [];

        $result['size'] = 0;
        $result['free'] = 0;
        $result['used'] = 0;

        if (PHP_OS == 'WINNT')
        {
            $lines = null;

            exec('wmic logicaldisk get FreeSpace^,Name^,Size /Value', $lines);

            foreach ($lines as $index => $line)
            {
                if ($line != "Name=$path")
                {
                    continue;
                }

                $result['free'] = explode('=', $lines[$index - 1])[1];
                $result['size'] = explode('=', $lines[$index + 1])[1];
                $result['used'] = $result['size'] - $result['free'];

                break;
            }
        }
        else
        {
            $lines = null;

            exec(sprintf('df /P %s', $path), $lines);

            foreach ($lines as $index => $line)
            {
                if ($index != 1)
                {
                    continue;
                }

                $values = preg_split('/\s{1,}/', $line);

                $result['size'] = Term::humanFileSize($values[1] * 1024);
                $result['free'] = Term::humanFileSize($values[3] * 1024);
                $result['used'] = Term::humanFileSize($values[2] * 1024);

                break;
            }
        }

        return $result;
    }

    /**
     * Get CPU Load Percentage.
     *
     * @return float load percentage
     */
    public static function getCpuLoadPercentage()
    {
        $result = -1;
        $lines = null;

        if (PHP_OS == 'WINNT')
        {
            $matches = null;

            exec('wmic.exe CPU get loadpercentage /Value', $lines);

            if (preg_match('/^LoadPercentage\=(\d+)$/', $lines[2], $matches))
            {
                $result = $matches[1];
            }
        }
        else
        {
            $checks = [];

            foreach ([0, 1] as $i)
            {
                $cmd = '/proc/stat';
                $lines = [];

                $fh = fopen($cmd, 'r');

                while ($line = fgets($fh))
                {
                    $lines[] = $line;
                }

                fclose($fh);

                foreach ($lines as $line)
                {
                    $ma = [];

                    if (!preg_match('/^cpu  (\d+) (\d+) (\d+) (\d+) (\d+) (\d+) (\d+) (\d+) (\d+) (\d+)$/', $line, $ma))
                    {
                        continue;
                    }

                    $total = $ma[1] + $ma[2] + $ma[3] + $ma[4] + $ma[5] + $ma[6] + $ma[7] + $ma[8] + $ma[9];

                    $ma['total'] = $total;
                    $checks[] = $ma;

                    break;
                }

                if ($i == 0)
                {
                    sleep(1);
                }
            }

            $diffIdle = $checks[1][4] - $checks[0][4];

            $diffTotal = $checks[1]['total'] - $checks[0]['total'];

            $diffUsage = (1000 * ($diffTotal - $diffIdle) / $diffTotal + 5) / 10;
            $result = $diffUsage;
        }

        return (float) $result;
    }
}
