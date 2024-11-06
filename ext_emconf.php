<?php

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'T23 Inline Container',
    'description' => 'An extension to manage content elements inline in b13/container similar like possible in gridelementsteam/gridelements before.',
    'category' => 'misc',
    'author' => 'TEAM23',
    'author_email' => 'dreier@team23.de',
    'author_company' => 'TEAM23',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '0.0.13',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-12.9.99',
            'container' => '2.3.1-3.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'news' => '9.0.0-10.9.9'
        ],
    ],
];
