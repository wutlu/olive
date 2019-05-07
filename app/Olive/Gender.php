<?php

namespace App\Olive;

class Gender
{
    private $males;
    private $females;

    private $dataFolder;
    private $sourceFolder;

    public function __construct()
    {
        $this->dataFolder = database_path('analysis/data');
        $this->sourceFolder = database_path('analysis/dictionaries');
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
            $name = str_slug(kebab_case($name));
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

                    $male = array_search($n, $this->males) !== false ? $male+5 : $male;
                    $female = array_search($n, $this->females) !== false ? $female+5 : $female;

                    $male = str_contains($n, $this->males) ? $male+1 : $male;
                    $female = str_contains($n, $this->females) ? $female+1 : $female;

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
