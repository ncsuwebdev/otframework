<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_View_Helper_CalculateTextColor
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * @package    Ot_View_Helper_CalculateTextColor
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 */

class Ot_View_Helper_ErrorOutput extends Zend_View_Helper_Abstract
{

    /**
     *
     */
    public function errorOutput($message, array $trackback)
    {
        $html = array();

        $html[] = '<h4 class="error-header">';

        if ($message == '') {
            $html[] = $this->view->translate('default-index-error:noMessage');
        } else {
            $html[] = $this->view->translate($message);
        }

        $html[] = '</h4>';

        if (count($trackback) && $this->view->configVar('showTrackbackOnError')) {
            $html[] = '<table class="table table-bordered table-striped table-condensed">';
            $html[] = '<thead>';
            $html[] = '<tr>';
            $html[] = '<th width="500">' . $this->view->translate('default-index-error:file') . '</th>';
            $html[] = '<th width="150">' . $this->view->translate('default-index-error:function') . '</th>';
            $html[] = '<th width="50">' . $this->view->translate('default-index-error:line') . '</th>';
            $html[] = '</tr>';
            $html[] = '</thead>';
            $html[] = '<tbody>';

            foreach ($trackback as $t) {

                $html[] = '<tr>';
                $html[] = '<td>' . ((isset($t['file']) ? $t['file'] : 'N/A')) . '</td>';
                $html[] = '<td>' . ((isset($t['function']) ? $t['function'] : 'N/A')) . '</td>';
                $html[] = '<td>' . ((isset($t['line']) ? $t['line'] : 'N/A')) . '</td>';
                $html[] = '</tr>';
            }

            $html[] = '</tbody>';
            $html[] = '</table>';
        }

        echo join(PHP_EOL, $html);
    }
}