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
 * Prints a particular instance of alplinks
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_alplinks
 * @copyright  2017 Tristan Mackay t.mackay@murdoch.edu.au
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace alplinks with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... alplinks instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('alplinks', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $alplinks  = $DB->get_record('alplinks', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $alplinks  = $DB->get_record('alplinks', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $alplinks->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('alplinks', $alplinks->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_alplinks\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $alplinks);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/alplinks/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($alplinks->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('alplinks-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($alplinks->intro) {
    echo $OUTPUT->box(format_module_intro('alplinks', $alplinks, $cm->id), 'generalbox mod_introbox', 'alplinksintro');
}
//redirect for testing.
//redirect("launcher.php?id=".$course->id.'&linkid='.$alplinks->alplinkid, 'Loading', 1);

$launchcontainer = true;
// Replace the following lines with you own code.
if ( $launchcontainer ) {
 
    echo "<script language=\"javascript\">//<![CDATA[\n";
    echo "window.open('launch.php?id=".$course->id."&linkid=".$alplinks->alplinkid."alplinks', '_blank')";
    echo "//]]\n";
    echo "</script>\n";
      $url = new moodle_url('launch.php?id='.$course->id.'&linkid='.$alplinks->alplinkid.'alplinks');
      echo html_writer::start_tag('p');
      echo html_writer::link($url,"Click here to access Echo360 Link", array('target' => '_blank'));
      echo html_writer::end_tag('p');
} else {
    echo '<iframe id="contentframe" height="600px" width="100%" type="text/html" src="launch.php?id='.$course->id.'&linkid='.$alplinks->alplinkid.'" frameborder="0"></iframe>';
}

// Finish the page.
echo $OUTPUT->footer();
