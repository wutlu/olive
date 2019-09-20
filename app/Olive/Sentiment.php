<?php

namespace App\Olive;

use App\Models\Analysis;
use Term;

class Sentiment {
    /**
     * Location of the dictionary files
     * 
     * @var str 
     */
    private $dataFolder;

    /**
     * List of tokens to ignore
     * 
     * @var array 
     */
    private $ignoreList = [];

    /**
     * Storage of cached dictionaries
     * 
     * @var array 
     */
    private $dictionary = [];

    /**
     * Min length of a token for it to be taken into consideration
     * 
     * @var int
     */
    private $minTokenLength = 2;

    /**
     * Max length of a taken for it be taken into consideration
     * 
     * @var int
     */
    private $maxTokenLength = 16;

    /**
     * Classification of opinions
     * 
     * @var array
     */
    public $classes = [];

    /**
     * Token score per class
     * 
     * @var array 
     */
    private $classTokCounts = [];

    /**
     * Analyzed text score per class
     * 
     * @var array
     */
    private $classDocCounts = [];

    /**
     * Number of tokens in a text
     * 
     * @var int 
     */
    private $tokCount = 0;

    /**
     * Number of analyzed texts
     * 
     * @var int
     */
    private $docCount = 0;

    /**
     * Implication that the analyzed text has 1/x chance of being in either of the x categories
     * 
     * @var array
     */
    public $prior = [];

    public function __construct()
    {
        $this->dataFolder = storage_path('app/analysis');
    }

    /**
     * Motor seçimi.
     * 
     * @return mixed
     */
    public function engine(string $engine)
    {
        $item = config('system.analysis')[$engine];

        $this->classes = array_keys($item['types']);

        foreach ($item['types'] as $k => $v)
        {
            $this->classTokCounts[$k] = 0;
            $this->classDocCounts[$k] = 0;
            $this->prior[$k] = $v['per'];
        }

        if (@$item['ignore'])
        {
            $this->ignoreList = $this->getList($item['ignore']);
        }

        $this->load();
    }

    /**
     * Get net result
     *
     * @param str $sentence Text to analyze
     * @return int Score
     */
    public function net($arr)
    {
        $scores = $this->score($arr);
        $max = array_keys($scores, max($scores));

        return count($max) > 2 ? null : [
            'id' => $max[0],
            'name' => config('system.analysis.category.types')['category-'.$max[0]]['title']
        ];
    }

    /**
     * Get scores for each class
     *
     * @param str $sentence Text to analyze
     * @return int Score
     */
    public function score($sentence)
    {
        $tokens = $this->_getTokens($sentence);

        $total_score = 0;

        $scores = [];

        foreach ($this->classes as $class)
        {
            $scores[$class] = 1;

            foreach ($tokens as $token)
            {
                if (strlen($token) >= $this->minTokenLength && strlen($token) < $this->maxTokenLength && !in_array($token, $this->ignoreList))
                {
                    $count = isset($this->dictionary[$token][$class]) ? $this->dictionary[$token][$class] : 0;

                    $scores[$class] *= ($count + 1);
                }
            }

            $scores[$class] = $this->prior[$class] * $scores[$class];
        }

        foreach ($this->classes as $class)
        {
            $total_score += $scores[$class];
        }

        foreach ($this->classes as $class)
        {
            $scores[$class] = round($scores[$class] / $total_score, 3);
        }

        arsort($scores);

        $data = [];

        foreach ($scores as $key => $score)
        {
            $ext = explode('-', $key);

            $data[$ext[1]] = $score;
        }

        return $data;
    }

    /**
     * İlgili duygunun tanımlanması.
     *
     * @return boolean
     */
    public function setDictionary(string $class)
    {
        $fn = $this->dataFolder.'/data.'.$class.'.php';

        if (file_exists($fn))
        {
            $temp = file_get_contents($fn);
            $words = unserialize($temp);
        }
        else
        {
            echo 'File does not exist: '.$fn;
        }

        foreach ($words as $word)
        {
            $this->docCount++;
            $this->classDocCounts[$class]++;

            $word = trim($word);

            if (!isset($this->dictionary[$word][$class]))
            {
                $this->dictionary[$word][$class] = 1;
            }

            $this->classTokCounts[$class]++;
            $this->tokCount++;
        }

        return true;
    }

    /**
     * Ham değerlerin işlenmesi.
     * 
     * @return mixed
     */
    public function update()
    {
        foreach($this->classes as $class)
        {
            $data = [];

            $query = Analysis::where('group', $class)->get();

            if (count($query))
            {
                foreach ($query as $q)
                {
                    $word = str_slug(kebab_case($q->word));
                    $word = preg_replace('/(.)\\1+/', '$1', $word);

                    $data[] = $word;

                    $q->update(
                        [
                            'word' => $word,
                            'compiled' => true
                        ]
                    );
                }

                file_put_contents($this->dataFolder.'/data.'.$class.'.php', serialize($data));
            }
        }
    }

    /**
     * Kullanılacak duyguların yüklenmesi..
     * 
     * @return mixed
     */
    private function load()
    {
        foreach ($this->classes as $class)
        {
            if (!$this->setDictionary($class))
            {
                echo 'Error: Dictionary for class '.$class.' could not be loaded';
            }
        }

        if (!isset($this->dictionary) || empty($this->dictionary))
        {
            echo 'Error: Dictionaries not set';
        }
    }

    /**
     * Gelen metindeki tüm değerlerin işlenebilir hale getirilmesi.
     *
     * bkz;
     * AhmetSeviyor "ahmet-seviyor" olup, dize haline getirilir.
     *
     * @return array
     */
    public static function _getTokens(string $string)
    {
        $string = Term::convertAscii($string, [
            'replacements' => [ '(\#)' => ' ' ]
        ]);

        $string = str_slug(kebab_case($string));
        $string = preg_replace('/(.)\\1+/', '$1', $string);

        return explode('-', $string);
    }

    /**
     * Load and cache additional word lists
     *
     * @return array
     */
    public function getList(string $type)
    {
        $wordList = [];

        $fn = $this->dataFolder.'/data.'.$type.'.php';

        if (file_exists($fn))
        {
            $temp = file_get_contents($fn);
            $words = unserialize($temp);
        }
        else
        {
            return 'File does not exist: '.$fn;
        }

        foreach ($words as $word)
        {
            array_push($wordList, $word);
        }

        return $wordList;
    }
}
