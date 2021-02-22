<?php

namespace TinyMCE\Spellchecker;

use Exception;

/**
 * Base class for all spellcheckers this takes in the words to check
 * spelling on and returns the suggestions.
 *
 * @author MrPropre
 * @author Johan Sörlin <johan.sorlin@tiny.cloud>
 * @copyright Copyright, Tiny Technologies
 * @license http://www.tiny.cloud/license GNU Lesser General Public License v2.1
 */
class Engine
{

    /**
     * @var array
     * @static
     */
    private static $engines = [];

    /**
     * @var array
     */
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Spellchecks an array of words.
     *
     * @param string $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param array $words Array of words to check.
     *
     * @return array Name/value object with arrays of suggestions.
     */
    public function getSuggestions(string $lang, array $words): array
    {
        return [];
    }

    /**
     * Return true/false if the engine is supported by the server.
     *
     * @return bool True/false if the engine is supported.
     */
    public function isSupported(): bool
    {
        return true;
    }

    /**
     * Sets the config array used to create the instance.
     *
     * @param array $config Name/value array with config options.
     *
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Returns the config array used to create the instance.
     *
     * @return array Name/value array with config options.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $tinymce_spell_checker_config
     *
     * @return void
     *
     * @throws Exception
     */
    public static function processRequest(array $tinymce_spell_checker_config): void
    {
        $engine = self::get($tinymce_spell_checker_config['engine']);
        $engine = new $engine($tinymce_spell_checker_config);

        header('Content-Type: application/json');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $method = self::getParam('method', 'spellcheck');
        $lang = self::getParam('lang', 'en_US');
        $text = self::getParam('text');

        if ($method === 'spellcheck') {
            try {
                if (!$text) {
                    throw new Exception('Missing input parameter \'text\'.');
                }

                if (!$engine->isSupported()) {
                    throw new Exception('Current spellchecker isn\'t supported.');
                }

                $words = self::getWords($text, $tinymce_spell_checker_config['ignored_words'] ?? []);

                echo json_encode([
                    'words' => $engine->getSuggestions($lang, $words)
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'error' => $e->getMessage()
                ]);
            }
        } elseif ($method) {
            echo json_encode([
                'error' => 'Unsupported method: ' . $method
            ]);
        } else {
            echo json_encode([
                'error' => 'Invalid JSON input'
            ]);
        }
    }

    /**
     * Returns an request value by name without magic quoting.
     *
     * @param string $name Name of parameter to get.
     * @param string|bool $default_value Default value to return if value not found.
     *
     * @return string Request value by name without magic quoting or default value.
     */
    public static function getParam(string $name, $default_value = false): string
    {
        if (isset($_POST[$name])) {
            $req = $_POST;
        } elseif (isset($_GET[$name])) {
            $req = $_GET;
        } else {
            return $default_value;
        }

        // Handle magic quotes
        if (ini_get('magic_quotes_gpc')) {
            if (is_array($req[$name])) {
                $out = [];

                foreach ($req[$name] as $name => $value) {
                    $out[stripslashes($name)] = stripslashes($value);
                }

                return $out;
            }

            return stripslashes($req[$name]);
        }

        return $req[$name];
    }

    /**
     * @param string $name
     * @param string $class_name
     *
     * @return void
     */
    public static function add(string $name, string $class_name): void
    {
        self::$engines[$name] = $class_name;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public static function get(string $name): ?string
    {
        if (!isset(self::$engines[$name])) {
            return null;
        }

        return self::$engines[$name];
    }

    /**
     * @param string $text
     * @param array $ignored_words Optional ignored words list
     *
     * @return array
     */
    public static function getWords(string $text, array $ignored_words = []): array
    {
        preg_match_all('(\w{3,})u', $text, $matches);
        $words = $matches[0];
        
        // Exclude words with numbers in them
        foreach ($words as $index => $word) {
            if (preg_match('/[0-9]+/', $word)) {
                array_splice($words, $index, 1);
            }
        }

        // Exclude ignored words
		foreach ($ignored_words as $index => $ignored_word) {
			if (in_array($ignored_word, $words)) {
                array_splice($words, $index, 1);
			}
		}

        return $words;
    }
}
