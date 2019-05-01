<?php

namespace App\Olive;

class Sense {
    /**
     * Location of the dictionary files
     * @var str 
     */
    private $dataFolder = '';

    /**
     * List of tokens to ignore
     * @var array 
     */
    private $ignoreList = [];

    /**
     * List of words with negative prefixes, e.g. isn't, arent't
     * @var array
     */
    private $negPrefixList = [];

    /**
     * Storage of cached dictionaries
     * @var array 
     */
    private $dictionary = [];

    /**
     * Min length of a token for it to be taken into consideration
     * @var int
     */
    private $minTokenLength = 1;

    /**
     * Max length of a taken for it be taken into consideration
     * @var int
     */
    private $maxTokenLength = 15;

    /**
     * Classification of opinions
     * @var array
     */
    private $classes = [ 'bet', 'nud', 'hat', 'que' ];

    /**
     * Token score per class
     * @var array 
     */
    private $classTokCounts = [
        'bet' => 0,
        'nud' => 0,
        'hat' => 0,
        'que' => 0,
    ];

    /**
     * Analyzed text score per class
     * @var array
     */
    private $classDocCounts = [
        'bet' => 0,
        'nud' => 0,
        'hat' => 0,
        'que' => 0,
    ];

    /**
     * Number of tokens in a text
     * @var int 
     */
    private $tokCount = 0;

    /**
     * Number of analyzed texts
     * @var int
     */
    private $docCount = 0;

    /**
     * Implication that the analyzed text has 1/7 chance of being in either of the 7 categories
     * @var array
     */
    private $prior = [
        'bet' => 25,
        'nud' => 25,
        'hat' => 25,
        'que' => 25,
    ];

    /**
     * Class constructor
     * @param str $dataFolder base folder
     * Sets defaults and loads/caches dictionaries
     */
    public function __construct($dataFolder = false)
    {
        # Set the base folder for the data models
        $this->setDataFolder($dataFolder);

        # Load and cache directories, get ignore and prefix lists
        $this->loadDefaults();
    }

    /**
     * Get scores for each class
     *
     * @param str $sentence Text to analyze
     * @return int Score
     */
    public function score($sentence)
    {
        # For each negative prefix in the list
        foreach ($this->negPrefixList as $negPrefix)
        {

            # Search if that prefix is in the document
            if (strpos($sentence, $negPrefix) !== false)
            {
                # Reove the white space after the negative prefix
                $sentence = str_replace($negPrefix . ' ', $negPrefix, $sentence);
            }
        }

        # Tokenise Document
        $tokens = $this->_getTokens($sentence);
        # Calculate the score in each category

        $total_score = 0;

        # Empty array for the scores for each of the possible categories
        $scores = [];

        # Loop through all of the different classes set in the $classes variable
        foreach ($this->classes as $class)
        {
            # In the scores array add another dimention for the class and set it's value to 1. EG $scores->neg->1
            $scores[$class] = 1;

            # For each of the individual words used loop through to see if they match anything in the $dictionary
            foreach ($tokens as $token)
            {
                # If statement so to ignore tokens which are either too long or too short or in the $ignoreList
                if (strlen($token) > $this->minTokenLength && strlen($token) < $this->maxTokenLength && !in_array($token, $this->ignoreList))
                {
                    # If dictionary[token][class] is set
                    if (isset($this->dictionary[$token][$class]))
                    {
                        # Set count equal to it
                        $count = $this->dictionary[$token][$class];
                    }
                    else
                    {
                        $count = 0;
                    }

                    # Score[class] is calcumeted by $scores[class] x $count +1 divided by the $classTokCounts[class] + $tokCount
                    $scores[$class] *= ($count + 1);
                }
            }

            # Score for this class is the prior probability multiplyied by the score for this class
            $scores[$class] = $this->prior[$class] * $scores[$class];
        }

        # Makes the scores relative percents
        foreach ($this->classes as $class)
        {
            $total_score += $scores[$class];
        }

        foreach ($this->classes as $class)
        {
            $scores[$class] = round($scores[$class] / $total_score, 3);
        }

        # Sort array in reverse order
        arsort($scores);

        return $scores;
    }

    /**
     * Load and cache dictionary
     *
     * @param str $class
     * @return boolean
     */
    public function setDictionary($class)
    {
        /**
         *  For some people this file extention causes some problems!
         */
        $fn = "{$this->dataFolder}data.{$class}.php";

        if (file_exists($fn))
        {
            $temp = file_get_contents($fn);
            $words = unserialize($temp);
        }
        else
        {
            echo 'File does not exist: ' . $fn;
        }

        # Loop through all of the entries
        foreach ($words as $word)
        {
            $this->docCount++;
            $this->classDocCounts[$class]++;

            # Trim word
            $word = trim($word);

            # If this word isn't already in the dictionary with this class
            if (!isset($this->dictionary[$word][$class]))
            {
                # Add to this word to the dictionary and set counter value as one. This function ensures that if a word is in the text file more than once it still is only accounted for one in the array
                $this->dictionary[$word][$class] = 1;
            }

            $this->classTokCounts[$class]++;
            $this->tokCount++;
        }

        return true;
    }

    /**
     * Set the base folder for loading data models
     * @param str  $dataFolder base folder
     * @param bool $loadDefaults true - load everything by default | false - just change the directory
     */
    public function setDataFolder($dataFolder = false, $loadDefaults = false)
    {
        if ($dataFolder == false)
        {
            $this->dataFolder = database_path('analysis/data/');
        }
        else
        {
            if (file_exists($dataFolder))
            {
                $this->dataFolder = $dataFolder;
            }
            else
            {
                echo 'Error: could not find the directory - '.$dataFolder;
            }
        }

        if ($loadDefaults !== false)
        {
            $this->loadDefaults();
        }
    }

    /**
     * Load and cache directories, get ignore and prefix lists
     */
    private function loadDefaults()
    {
        foreach ($this->classes as $class)
        {
            if (!$this->setDictionary($class))
            {
                echo "Error: Dictionary for class '$class' could not be loaded";
            }
        }

        if (!isset($this->dictionary) || empty($this->dictionary))
        {
            echo 'Error: Dictionaries not set';
        }

        # Run function to get ignore list
        $this->ignoreList = $this->getList('ign');

        # If ingnoreList not get give error message
        if (!isset($this->ignoreList))
        {
            echo 'Error: Ignore List not set';
        }

        # Get the list of negative prefixes
        $this->negPrefixList = $this->getList('prefix');

        # If neg prefix list not set give error
        if (!isset($this->negPrefixList))
        {
            echo 'Error: Ignore List not set';
        }
    }

    /**
     * Break text into tokens
     *
     * @param str $string   String being broken up
     * @return array An array of tokens
     */
    private function _getTokens($string)
    {
        $string = str_slug(kebab_case($string));
        $string = preg_replace('/(.)\\1+/', '$1', $string);

        $matches = explode('-', $string);

        return $matches;
    }

    /**
     * Load and cache additional word lists
     *
     * @param str $type
     * @return array
     */
    public function getList($type)
    {
        $wordList = [];

        $fn = "{$this->dataFolder}data.{$type}.php";

        if (file_exists($fn))
        {
            $temp = file_get_contents($fn);
            $words = unserialize($temp);
        }
        else
        {
            return 'File does not exist: ' . $fn;
        }

        foreach ($words as $word)
        {
            # Remove any slashes
            $word = stripcslashes($word);

            # Trim word
            $trimmed = trim($word);

            # Push results into $wordList array
            array_push($wordList, $trimmed);
        }

        return $wordList;
    }

    /**
     * Deletes old data/data.* files
     * Creates new files from updated source fi
     */
    public function reloadDictionaries($dictionaries = '')
    {
        $this->classes[] = 'ign';
        $this->classes[] = 'prefix';

        foreach($this->classes as $class)
        {
            $fn = "{$this->dataFolder}data.{$class}.php";

            if (file_exists($fn))
            {
                unlink($fn);
            } 
        }

        $dictionaries = $dictionaries ? $dictionaries : database_path('analysis/dictionaries/');

        foreach($this->classes as $class)
        {
            $dict = "{$dictionaries}source.{$class}.php";

            require_once($dict);

            $data = $class;

            $fn = "{$this->dataFolder}data.{$class}.php";

            file_put_contents($fn, serialize($$data));
        }
    }
}
