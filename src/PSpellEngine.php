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
    public function getSuggestions($lang, $words)
    {
        $config = $this->getConfig();
        switch ($config['PSpell.mode']) {
            case "fast":
                $mode = PSPELL_FAST;
                break;
            case "slow":
                $mode = PSPELL_SLOW;
                break;
            default:
                $mode = PSPELL_NORMAL;
        }

        // Setup PSpell link
        $plink = pspell_new($lang, $config['pspell.spelling'], $config['pspell.jargon'], $config['pspell.encoding'], $mode);
        if (!$plink) {
            throw new Exception("No PSpell link found opened.");
        }

        $outWords = array();
        foreach ($words as $word) {
            if (!pspell_check($plink, trim($word))) {
                $outWords[] = utf8_encode($word);
            }
        }

        return $outWords;
    }

    /**
     * Return true/false if the engine is supported by the server.
     *
     * @return bool True/false if the engine is supported.
     */
    public function isSupported()
    {
        return function_exists("pspell_new");
    }
}
