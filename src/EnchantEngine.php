<?php

namespace TinyMCE\Spellchecker;

use Exception;

/**
 * Enchant spellchecker.
 *
 * @author MrPropre
 * @author TinyMCE
 * @copyright Copyright, Moxiecode Systems AB
 * @license http://www.tinymce.com/license LGPL License
 */
class EnchantEngine extends Engine
{

    /**
     * Spellchecks an array of words.
     *
     * @param string $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param array $words Array of words to check.
     *
     * @return array Name/value object with arrays of suggestions.
     *
     * @throws Exception
     */
    public function getSuggestions(string $lang, array $words): array
    {
        $suggestions = array();
        $enchant = enchant_broker_init();
        $config = $this->getConfig();
        if (isset($config["enchant_dicts_path"])) {
            enchant_broker_set_dict_path($enchant, ENCHANT_MYSPELL, $config["enchant_dicts_path"]);
            enchant_broker_set_dict_path($enchant, ENCHANT_ISPELL, $config["enchant_dicts_path"]);
        }

        if (!enchant_broker_describe($enchant)) {
            throw new Exception("Enchant spellchecker not find any backends.");
        }

        $lang = $this->normalizeLangCode($enchant, $lang);
        if (enchant_broker_dict_exists($enchant, $lang)) {
            $dict = enchant_broker_request_dict($enchant, $lang);
            foreach ($words as $word) {
                if (!enchant_dict_check($dict, $word)) {
                    $suggs = enchant_dict_suggest($dict, $word);
                    if (!is_array($suggs)) {
                        $suggs = array();
                    }

                    $suggestions[$word] = $suggs;
                }
            }

            enchant_broker_free_dict($dict);
            enchant_broker_free($enchant);
        } else {
            enchant_broker_free($enchant);
            throw new Exception("Enchant spellchecker could not find dictionary for language: " . $lang);
        }

        return $suggestions;
    }

    /**
     * Return true/false if the engine is supported by the server.
     *
     * @return bool True/false if the engine is supported.
     */
    public function isSupported(): bool
    {
        return function_exists("enchant_broker_init");
    }

    /**
     * @param $enchant Brocker
     * @param $lang Language
     *
     * @return string
     */
    private function normalizeLangCode($enchant, string $lang): string
    {
        $variants = array(
            "en" => array("en_US", "en_GB")
        );
        if (isset($variants[$lang])) {
            array_unshift($variants, $lang);
            foreach ($variants[$lang] as $variant) {
                if (enchant_broker_dict_exists($enchant, $variant)) {
                    return $variant;
                }
            }
        }

        return $lang;
    }
}
