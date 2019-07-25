<?php

namespace App\Olive;

use Term;

class Gender
{
    private $males;
    private $females;

    private $dataFolder;

    public function __construct()
    {
        $this->dataFolder = storage_path('app/analysis');
    }

    /**
     * Load Names
     *
     * @return mixed
     */
    public function loadNames()
    {
        foreach ([ 'males' => 'male', 'females' => 'female' ] as $key => $gender)
        {
            $fn = $this->dataFolder.'/data.gender-'.$gender.'.php';

            if (file_exists($fn))
            {
                $temp = file_get_contents($fn);

                $this->{$key} = unserialize($temp);
            }
            else
            {
                return 'File does not exist: '.$fn;
            }
        }
    }

    /**
     * Gender Detector
     *
     * @return string
     */
    public function detector(array $names)
    {
        $male = 0;
        $female = 0;

        foreach ($names as $name)
        {
            $name = kebab_case($name);
            $name = Term::convertAscii($name, [ 'delimiter' => '' ]);
            $name = preg_replace('/(.)\\1+/', '$1', $name);

            $explode_name = explode('-', $name);

            $arr_names = [];

            foreach ($explode_name as $n)
            {
                if (strlen($n) >= 2)
                {
                    $nn = preg_quote($n, '~');
                    $male = $male + count(preg_grep('~'.$nn.'~', $this->males));
                    $female = $female + count(preg_grep('~'.$nn.'~', $this->females));

                    $male = array_search($n, $this->males) !== false ? $male+4 : $male;
                    $female = array_search($n, $this->females) !== false ? $female+4 : $female;

                    $male = str_contains($n, $this->males) ? $male+2 : $male;
                    $female = str_contains($n, $this->females) ? $female+2 : $female;

                    $arr_names[] = $n;
                }
            }
        }

        $gender = 'unknown';

        if ($male > 0 || $female > 0)
        {
            if ($male > $female)
            {
                $gender = 'male';
            }
            else if ($male < $female)
            {
                $gender = 'female';
            }
        }

        return $gender;
    }
}
