<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service local plugin cardboxx external functions and service definitions.
 *
 * @package    mod_cardboxx
 * @copyright  2015 Caio Bressan Doneda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [

    'mod_cardboxx_deletetopic' => [
        'classname'    => 'mod_cardboxx_external',
        'methodname'   => 'deletetopic',
        'classpath'    => 'mod/cardboxx/externallib.php',
        'description'  => 'Delete topic in a cardboxx instance.',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'mod/cardboxx:edittopics',
    ],
    'mod_cardboxx_renametopic' => [
        'classname'    => 'mod_cardboxx_external',
        'methodname'   => 'renametopic',
        'classpath'    => 'mod/cardboxx/externallib.php',
        'description'  => 'Rename topic in a cardboxx instance.',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'mod/cardboxx:edittopics',
    ],
];
