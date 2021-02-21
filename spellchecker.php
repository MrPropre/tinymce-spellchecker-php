<?php

/**
 * spellcheck.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

require_once 'vendor/autoload.php';

use TinyMCE\Spellchecker\Engine;
use TinyMCE\Spellchecker\EnchantEngine;
use TinyMCE\Spellchecker\PSpellEngine;

$tinymce_spell_checker_config = [
    'engine' => 'enchant', // enchant, pspell

    // Enchant options
    'enchant_dicts_path' => './dicts',

    // PSpell options
    'pspell.mode' => 'fast',
    'pspell.spelling' => '',
    'pspell.jargon' => '',
    'pspell.encoding' => ''
];

Engine::add("enchant", EnchantEngine::class);
Engine::add("pspell", PSpellEngine::class);
Engine::processRequest($tinymce_spell_checker_config);
