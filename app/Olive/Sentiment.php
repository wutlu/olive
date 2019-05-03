<?php

namespace App\Olive;

class Sentiment {
    /**
     * Location of the dictionary files
     * 
     * @var str 
     */
    private $dataFolder;
    private $sourceFolder;

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
        $this->dataFolder = database_path('analysis/data');
        $this->sourceFolder = database_path('analysis/dictionaries');
    }

    /**
     * Motor seçimi.
     * 
     * @return mixed
     */
    public function engine(string $engine)
    {
        switch ($engine)
        {
            case 'sentiment':
                $this->classes = [ 'pos', 'neg', 'neu', 'hte' ];
                $this->classTokCounts = [
                    'pos' => 0,
                    'neg' => 0,
                    'neu' => 0,
                    'hte' => 0,
                ];
                $this->classDocCounts = [
                    'pos' => 0,
                    'neg' => 0,
                    'neu' => 0,
                    'hte' => 0,
                ];
                $this->prior = [
                    'pos' => 25,
                    'neg' => 25,
                    'neu' => 25,
                    'hte' => 25,
                ];
                $this->ignoreList = $this->getList('ign');
            break;
            case 'illegal':
                $this->classes = [ 'bet', 'nud' ];
                $this->classTokCounts = [
                    'bet' => 0,
                    'nud' => 0,
                ];
                $this->classDocCounts = [
                    'bet' => 0,
                    'nud' => 0,
                ];
                $this->prior = [
                    'bet' => 50,
                    'nud' => 50,
                ];
                $this->ignoreList = $this->getList('ign.illegal');
            break;
            case 'consumer':
                $this->classes = [ 'que', 'req', 'cmp', 'nws' ];
                $this->classTokCounts = [
                    'que' => 0,
                    'req' => 0,
                    'cmp' => 0,
                    'nws' => 0,
                ];
                $this->classDocCounts = [
                    'que' => 0,
                    'req' => 0,
                    'cmp' => 0,
                    'nws' => 0,
                ];
                $this->prior = [
                    'que' => 25,
                    'req' => 25,
                    'cmp' => 25,
                    'nws' => 25,
                ];
            break;
        }

        $this->load();
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
                if (strlen($token) > $this->minTokenLength && strlen($token) < $this->maxTokenLength && !in_array($token, $this->ignoreList))
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

        return $scores;
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
            require_once $this->sourceFolder.'/source.'.$class.'.php';

            $data = array_map(function($word) {
              return str_slug($word, ' ');
            }, $data);

            file_put_contents($this->dataFolder.'/data.'.$class.'.php', serialize($data));

            unset($data);
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
    private function _getTokens(string $string)
    {
        $string = str_replace('#', ' ', $string);
        $string = str_slug(kebab_case($string), '-');
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
