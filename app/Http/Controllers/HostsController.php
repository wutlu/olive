<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Crawlers\Host;

class HostsController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Hosts Dosyası
     *
     * @return view
     */
    public static function hostsFile()
    {
        $path = '/etc/hosts';

        $file = fopen($path, 'r') or die('Unable to open file!');

        $text = fread($file, filesize($path));

        fclose($file);

        $ip_list = Host::orderBy('ip_address', 'ASC')->get();

        return view('hosts.file', compact('text', 'ip_list'));
    }
}
