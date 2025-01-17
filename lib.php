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
 * Notificationeabc enrolment plugin.
 *
 * This plugin notifies users when an event occurs on their enrolments (enrol, unenrol, update enrolment)
 *
 * @package    enrol_notificationeabc
 * @copyright  2017 e-ABC Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Osvaldo Arriola <osvaldo@e-abclearning.com>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/moodlelib.php');

/**
 * Lib class
 *
 * @package    enrol_notificationeabc
 * @copyright  2017 e-ABC Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Osvaldo Arriola <osvaldo@e-abclearning.com>
 */
class enrol_notificationeabc_plugin extends enrol_plugin
{
    /** @var log.*/
    private $log = '';

    // Funcion que envia las notificaciones a los usuarios.

    /**
     *  Send mail method
     * @param stdClass $user instancia usuario
     * @param stdClass $course instancia curso
     * @param int $type aviso matriculacion, actualizacion o desmatriculacion
     * @return bool
     */
    public function enviarmail(stdClass $user, stdClass $course, $type) {
        global $CFG, $DB, $COURSE, $SESSION;

        $course->url = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

        $enrol = $DB->get_record('enrol', array('enrol' => 'notificationeabc', 'courseid' => $course->id, 'status' => 0));
        $activeglobalenrol = $this->get_config('activarglobal');
        $activarglobalunenrolalert = $this->get_config('activarglobalunenrolalert');
        $activarglobalenrolupdated = $this->get_config('activarglobalenrolupdated');
        $mensajeenrol = $this->get_config('location');
        $plainmensajeenrol = strip_tags($mensajeenrol);
        $mensajeunenrol = $this->get_config('unenrolmessage');
        $plainmensajeunenrol = strip_tags($mensajeunenrol);
        $mensajeupdateenrol = $this->get_config('updatedenrolmessage');
        $plainmensajeupdateenrol = strip_tags($mensajeupdateenrol);
        $enrolsubject = $this->get_config('enrolsubject');
        $unenrolsubject = $this->get_config('unenrolsubject');
        $updatedenrolmessage = $this->get_config('updatedenrolmessage');

        // Use language of enrol user
        $originallang = force_current_language($this->get_email_language($user));

        switch ((int)$type) {
            case 1:
                // Si no se configuro un mensaje personalizado se envia uno por defecto basico.
                if (!empty($enrol) && !empty($enrol->customtext1) && !empty($enrol->customint1) ) {
                    $texto = strip_tags($enrol->customtext1);
                    if (!empty($texto)) {
                        $mensaje = $this->process_mensaje($enrol->customtext1, $user, $course);
                    } else {
                        $mensaje = $this->process_mensaje(get_string('filelockedmail', 'enrol_notificationeabc'), $user, $course);
                    }

                } else if (!empty($activeglobalenrol) && !empty($plainmensajeenrol)) {
                    $mensaje = $this->process_mensaje($mensajeenrol, $user, $course);
                } else {
                    $mensaje = $this->process_mensaje(get_string('filelockedmail', 'enrol_notificationeabc'), $user, $course);
                }
                
                if (!empty($enrolsubject)) {
                    $emailsubject = $this->process_subject($enrolsubject, $user, $course);
                } else {
                    $emailsubject = $this->process_subject(get_string('subject', 'enrol_notificationeabc'), $user, $course);
                }
                break;
            case 2:

                if (!empty($enrol) && !empty($enrol->customtext2) && !empty($enrol->customint2)) {
                    $texto = strip_tags($enrol->customtext2);
                    if (!empty($texto)) {
                        $mensaje = $this->process_mensaje($enrol->customtext2, $user, $course);
                    } else {
                        $mensaje = $this->process_mensaje(get_string('unenrolmessagedefault', 'enrol_notificationeabc'), $user, $course);
                    }

                } else if (!empty($activarglobalunenrolalert) && !empty($plainmensajeunenrol)) {
                    $mensaje = $this->process_mensaje($mensajeunenrol, $user, $course);
                } else {
                    $mensaje = $this->process_mensaje(get_string('unenrolmessagedefault', 'enrol_notificationeabc'), $user, $course);
                }
                
                if (!empty($unenrolsubject)) {
                    $emailsubject = $this->process_subject($unenrolsubject, $user, $course);
                } else {
                    $emailsubject = $this->process_subject(get_string('subject', 'enrol_notificationeabc'), $user, $course);
                }
                break;
            case 3:

                if (!empty($enrol) && !empty($enrol->customtext3) && !empty($enrol->customint3)) {
                    $texto = strip_tags($enrol->customtext3);
                    if (!empty($texto)) {
                        $mensaje = $this->process_mensaje($enrol->customtext3, $user, $course);
                    } else {
                        $mensaje = $this->process_mensaje(get_string('updatedenrolmessagedefault', 'enrol_notificationeabc'), $user, $course);
                    }

                } else if (!empty($activarglobalenrolupdated) && !empty($plainmensajeupdateenrol)) {
                    $mensaje = $this->process_mensaje($mensajeupdateenrol, $user, $course);
                } else {
                    $mensaje = $this->process_mensaje(get_string('updatedenrolmessagedefault', 'enrol_notificationeabc'), $user, $course);
                }
                
                if (!empty($updatedenrolsubject)) {
                    $emailsubject = $this->process_subject($updatedenrolsubject, $user, $course);
                } else {
                    $emailsubject = $this->process_subject(get_string('subject', 'enrol_notificationeabc'), $user, $course);
                }
                break;

            default:
                break;
        }

        // Clear forced language from session
        force_current_language($originallang);
        unset($SESSION->forcelang);

        $soporte = core_user::get_support_user();

        $sender = get_admin();

        if (!empty($enrol) && !empty($enrol->customchar1)) {
            $sender->email = $enrol->customchar1;
        } else {
            $sender->email = $soporte->email;
        }

        if (!empty($enrol) && !empty($enrol->customchar2)) {
            $sender->firstname = $enrol->customchar2;
        } else {
            $sender->firstname = $soporte->firstname;
        }

        $sender->lastname = $soporte->lastname;

        $eventdata = new \core\message\message();
        $eventdata->courseid = $course->id;
        $eventdata->modulename = 'moodle';
        $eventdata->component = 'enrol_notificationeabc';
        $eventdata->name = 'notificationeabc_enrolment';
        $eventdata->userfrom = $sender;
        $eventdata->userto = $user->id;
        $eventdata->subject = $emailsubject;
        $eventdata->fullmessage = '';
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $mensaje;
        $eventdata->smallmessage = '';
        $strdata = new stdClass();
        $strdata->username = $user->username;
        $strdata->coursename = $course->fullname;
        if (message_send($eventdata)) {
            $this->log .= get_string('succefullsend', 'enrol_notificationeabc', $strdata);
            return true;
        } else {
            $this->log .= get_string('failsend', 'enrol_notificationeabc', $strdata);
            return false;
        }
    } // End of function.

    // Common function to process placeholders in content
    /**
     * Proccess message method
     * @param String $mensaje el mensaje en bruto
     * @param stdClass $user instancia usuario
     * @param stdClass $course instancia curso
     * @return String el mensaje procesado
     */
    public function process_placeholders($mensaje, stdClass $user, stdClass $course) {
        global $CFG;
        $m = $mensaje;
        $url = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id));
        $m = str_replace('{COURSENAME}', format_string($course->fullname), $m);
        $m = str_replace('{USERNAME}', $user->username, $m);
        $m = str_replace('{NOMBRE}', $user->firstname, $m);
        $m = str_replace('{FIRSTNAME}', $user->firstname, $m); // Supports also EN placeholder name
        $m = str_replace('{APELLIDO}', $user->lastname, $m);
        $m = str_replace('{LASTNAME}', $user->lastname, $m); // Supports also EN placeholder name
        $m = str_replace('{URL}', $url, $m);
        return $m;
    }

    // Procesa el mensaje para aceptar marcadores.
    /**
     * Proccess message method
     * @param String $mensaje el mensaje en bruto
     * @param stdClass $user instancia usuario
     * @param stdClass $course instancia curso
     * @return String el mensaje procesado
     */
    public function process_mensaje($mensaje, stdClass $user, stdClass $course) {
        global $CFG;
        $m = $mensaje;
        $m = $this->process_placeholders($m, $user, $course);
        $m = format_text($m);
        return $m;
    }


    // Process email subject
    /**
     * Proccess message method
     * @param String $mensaje el mensaje en bruto
     * @param stdClass $user instancia usuario
     * @param stdClass $course instancia curso
     * @return String el mensaje procesado
     */
    public function process_subject($mensaje, stdClass $user, stdClass $course) {
        global $CFG;
        $m = $mensaje;
        $m = $this->process_placeholders($m, $user, $course);
        $m = format_string($m);
        return $m;
    }


    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;
        $numenrol = $DB->count_records('enrol', array('courseid' => $courseid, 'enrol' => 'notificationeabc'));
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('enrol/notificationeabc:manage', $context) or $numenrol >= 1) {
            return null;
        }
        return new moodle_url('/enrol/notificationeabc/edit.php', array('courseid' => $courseid));
    }


    /**
     * Returns defaults for new instances.
     * @return array
     */
    public function get_instance_defaults() {

        $fields = array();
        $fields['customtext1'] = $this->get_config('location');
        $fields['customtext2'] = $this->get_config('unenrolmessage');
        $fields['customtext3'] = $this->get_config('updatedenrolmessage');
        $fields['status'] = 1;
        $fields['customint1'] = 0;
        $fields['customint2'] = 0;
        $fields['customint3'] = $this->get_config('activeenrolalert');
        $fields['customint4'] = $this->get_config('activeunenrolalert');
        $fields['customint5'] = $this->get_config('activeenrolupdatedalert');
        $fields['customchar1'] = $this->get_config('emailsender');
        $fields['customchar2'] = $this->get_config('namesender');
        $fields['customsubject1'] = $this->get_config('enrolsubject');
        $fields['customsubject2'] = $this->get_config('unenrolsubject');
        $fields['customsubject3'] = $this->get_config('updatedenrolsubject');

        return $fields;
    }

    /**
     * Get icons
     * @param stdClass $instance
     * @return array
     * @throws coding_exception
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'notificationeabc') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/notificationeabc:manage', $context)) {
            $editlink = new moodle_url(
                "/enrol/notificationeabc/edit.php",
                array('courseid' => $instance->courseid, 'id' => $instance->id)
            );
            $icons[] = $OUTPUT->action_icon(
                $editlink,
                new pix_icon(
                    'i/edit',
                    get_string('edit'),
                    'core',
                    array('class' => 'icon')
                )
            );
        }

        return $icons;
    }


    /**
     * get info icons method
     * @param array $instances
     * @return array
     */
    public function get_info_icons(array $instances) {
        $icons = array();
        $icons[] = new pix_icon('email', get_string('pluginname', 'enrol_notificationeabc'), 'enrol_notificationeabc');
        return $icons;
    }

    // Detect the proper email lanugage, taken reference from current_language() in lib/moodlelib.php
    /**
     * Returns the code for the current language
     *
     * @category string
     * @return string
     */
    function get_email_language(stdClass $user) {
        global $CFG, $PAGE, $SESSION;

        if (!empty($PAGE->cm->lang)) {
            // Activity language, if set.
            $return = $PAGE->cm->lang;

        } else if (!empty($PAGE->course->id) && $PAGE->course->id != SITEID && !empty($PAGE->course->lang)) {
            // Course language can override all other settings for this page.
            $return = $PAGE->course->lang;

        } else if (!empty($user->lang)) {
            $return = $user->lang;

        } else if (isset($CFG->lang)) {
            $return = $CFG->lang;

        } else {
            $return = 'en';
        }

        // Just in case this slipped in from somewhere by accident.
        $return = str_replace('_utf8', '', $return);

        return $return;
    }


} // End of class.

