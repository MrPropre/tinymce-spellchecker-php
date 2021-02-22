<?php

/**
 * Example code.
 *
 * @author MrPropre
 * @author Johan Sörlin <johan.sorlin@tiny.cloud>
 * @copyright Copyright, Tiny Technologies
 * @license http://www.tiny.cloud/license GNU Lesser General Public License v2.1
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use TinyMCE\Spellchecker\Engine;
use TinyMCE\Spellchecker\EnchantEngine;
use TinyMCE\Spellchecker\PSpellEngine;

Engine::add('enchant', EnchantEngine::class);
Engine::add('pspell', PSpellEngine::class);

$tinymce_spell_checker_config = [
    'engine' => 'enchant', // enchant, pspell
    'ignored_words' => [],

    // Enchant options
    'enchant_dicts_path' => './dicts',

    // PSpell options
    'pspell.mode' => 'fast',
    'pspell.spelling' => '',
    'pspell.jargon' => '',
    'pspell.encoding' => ''
];

Engine::processRequest($tinymce_spell_checker_config);
