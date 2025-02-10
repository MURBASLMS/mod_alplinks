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

namespace mod_alplinks\output;

use core\context\module;
use moodle_url;

/**
 * Class mobile
 *
 * @package    mod_alplinks
 * @copyright  2025 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * View.
     *
     * @param array $args The method arguments.
     */
    public static function view_page($args) {
        global $OUTPUT, $DB;

        $args = (object) $args;
        $cm = get_coursemodule_from_id('alplinks', $args->cmid, 0, false, MUST_EXIST);
        $alplinks  = $DB->get_record('alplinks', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = module::instance($cm->id);

        // Capabilities check.
        require_login($args->courseid, false, $cm, true, true);
        require_capability('mod/alplinks:view', $context);

        // Flag as viewed.
        $event = \mod_alplinks\event\course_module_viewed::create(array(
            'objectid' => $cm->instance,
            'context' => $context,
        ));
        $event->add_record_snapshot($cm->modname, $alplinks);
        $event->trigger();

        // Format content.
        $alplinks->name = format_string($alplinks->name);
        [$alplinks->intro, $alplinks->introformat] =  \core_external\util::format_text(
            $alplinks->intro,
            $alplinks->introformat,
            $context,
            'mod_alplinks',
            'intro'
        );

        // Launch URL.
        $url = new moodle_url('/mod/alplinks/launch.php', [
            'id' => $cm->course,
            'linkid' => $alplinks->alplinkid . 'alplinks',
        ]);

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_alplinks/mobile_view', [
                        'alplinks' => $alplinks,
                        'url' => $url->out(false),
                    ]),
                ],
            ],
            'javascript' => '',
            'otherdata' => [],
            'files' => []
        ];
    }

}
