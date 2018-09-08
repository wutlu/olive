<?php

namespace App\Utilities;

use App\Models\Log;
use App\Models\Setting;

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

    # system setting
    public static function setting(string $key)
    {
        return Setting::where('key', $key)->value('value');
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
    public static function getDiskSize()
    {
        $paths = [ '/' ];

        $result = [];

        foreach ($paths as $key => $path)
        {

            $result[$key]['total'] = 0;
            $result[$key]['free'] = 0;
            $result[$key]['used'] = 0;

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

                    $result[$key]['free'] = explode('=', $lines[$index - 1])[1];
                    $result[$key]['total'] = explode('=', $lines[$index + 1])[1];
                    $result[$key]['used'] = $result[$key]['total'] - $result[$key]['free'];

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

                    $result[$key]['total'] = Term::humanFileSize($values[1] * 1024);
                    $result[$key]['free'] = Term::humanFileSize($values[3] * 1024);
                    $result[$key]['used'] = Term::humanFileSize($values[2] * 1024);

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the number of available CPU cores
     * 
     *  Should work for Linux, Windows, Mac & BSD
     * 
     * @return integer 
     */
    public static function getCpuNumber()
    {
        $numCpus = 1;

        if (is_file('/proc/cpuinfo'))
        {
            $cpuinfo = file_get_contents('/proc/cpuinfo');

            preg_match_all('/^processor/m', $cpuinfo, $matches);

            $numCpus = count($matches[0]);
        }
        else if (PHP_OS == 'WINNT')
        {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');

            if (false !== $process)
            {
                fgets($process);

                $numCpus = intval(fgets($process));

                pclose($process);
            }
        }
        else
        {
            $process = @popen('sysctl -a', 'rb');

            if (false !== $process)
            {
                $output = stream_get_contents($process);

                preg_match('/hw.ncpu: (\d+)/', $output, $matches);

                if ($matches)
                {
                    $numCpus = intval($matches[1][0]);
                }

                pclose($process);
            }
        }
        
        return $numCpus;
    }

}
