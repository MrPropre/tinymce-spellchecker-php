<?php

namespace TinyMCE\Spellchecker;

use Exception;

/**
 * PSpell spellchecker.
 *
 * @author MrPropre
 * @author TinyMCE
 * @copyright Copyright, Moxiecode Systems AB
 * @license http://www.tinymce.com/license LGPL License
 */
class PSpellEngine extends Engine
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
        $config = $this->getConfig();

        switch ($config['pspell.mode']) {
            case 'fast':
                $mode = PSPELL_FAST;
                break;

            case 'slow':
                $mode = PSPELL_SLOW;
                break;

            default:
                $mode = PSPELL_NORMAL;
        }

        // Setup PSpell link
        $plink = pspell_new(
            $lang,
            $config['pspell.spelling'],
            $config['pspell.jargon'],
            $config['pspell.encoding'] ?: 'utf-8',
            $mode
		);

        if (!$plink) {
            throw new Exception('No PSpell link found opened.');
        }

        $out_words = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (!pspell_check($plink, $word)) {
                $suggestions = pspell_suggest($plink, $word);
                foreach ($suggestions as &$suggestion) {
                    $suggestion = utf8_encode($suggestion);
                }
				$out_words[utf8_encode($word)] = $suggestions;
            }
        }

        return $out_words;
    }

    /**
     * Return true/false if the engine is supported by the server.
     *
     * @return bool True/false if the engine is supported.
     */
    public function isSupported(): bool
    {
        return function_exists('pspell_new');
    }
}
