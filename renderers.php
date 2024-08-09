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
 * LearnR renderer class. This file is currently only used to extend the mod_h5p and mod_hvp renderers.
 *
 * @package   theme_learnr
 * @copyright 2022 Nina Herrmann <nina.herrmann@gmx.de>
 * @copyright on behalf of Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_h5p\player;

/**
 * Extend the core_h5p renderer.
 *
 * @package   theme_learnr
 * @copyright 2022 Nina Herrmann <nina.herrmann@gmx.de>
 * @copyright on behalf of Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright Tina John <tina.john@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_learnr_core_h5p_renderer extends \core_h5p\output\renderer {

    /**
     * Add CSS styles when H5P content is displayed in core_h5p.
     *
     * @param array $styles Styles that will be applied.
     * @param array $libraries Libraries that wil be shown.
     * @param string $embedtype How the H5P is displayed.
     */
    public function h5p_alter_styles(&$styles, $libraries, $embedtype) {
        // Build the H5P CSS file URL.
        $h5pcssurl = new moodle_url('/theme/learnr/h5p/styles.php');

        // Add the CSS file path and a version (to support browser caching) to H5P.
        $styles[] = (object) array(
                'path' => $h5pcssurl->out(),
                'version' => '?ver='.theme_get_revision(),
        );
    }

    // Added tinjohn 20230802.
    public function h5p_alter_filtered_parameters(&$parameters, string $name, int $majorversion, int $minorversion) {
        //debug echo '<br>' . $h5pContentId . '<br>';
        if (class_exists('local_h5ptranslate\h5ptranslate')) {
            $translator = new \local_h5ptranslate\h5ptranslate();            
            $translator->geth5ptranslation($parameters);
        } else {
            // Handle the case where the plugin is not available
            debugging('The local_h5ptranslate plugin is not available.', DEBUG_DEVELOPER);
        }
    }

}

// Only if mod_hvp is installed.
if (file_exists($CFG->dirroot.'/mod/hvp/renderer.php')) {
    // Load the mod_hvp renderer.
    require_once($CFG->dirroot.'/mod/hvp/renderer.php');

    // If the mod_hvp_renderer exists now.
    if (class_exists('mod_hvp_renderer')) {
        /**
         * Add CSS styles when H5P content is displayed in mod_hvp.
         *
         * @package   theme_learnr
         * @copyright 2022 Nina Herrmann <nina.herrmann@gmx.de>
         * @copyright on behalf of Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
         * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
         */
        class theme_learnr_mod_hvp_renderer extends mod_hvp_renderer {

            /**
             * Add styles when an H5(V)P is displayed.
             *
             * @param array $styles Styles that will be applied.
             * @param array $libraries Libraries that wil be shown.
             * @param string $embedtype How the H5P is displayed.
             */
            public function hvp_alter_styles(&$styles, $libraries, $embedtype) {
                // Build the H5P CSS file URL.
                $h5pcssurl = new moodle_url('/theme/learnr/h5p/styles.php');

                // Add the CSS file path and a version (to support browser caching) to H5P.
                $styles[] = (object)array(
                        'path' => $h5pcssurl->out(),
                        'version' => '?ver='.theme_get_revision(),
                );
            }
        }
    }
}
