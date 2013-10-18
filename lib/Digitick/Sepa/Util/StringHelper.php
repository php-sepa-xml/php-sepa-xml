<?php

namespace Digitick\Sepa\Util;

/**
 * Created by JetBrains PhpStorm.
 * User: srohweder
 * Date: 10/18/13
 * Time: 5:57 AM
 * To change this template use File | Settings | File Templates.
 */




class StringHelper {

    /**
     * @param string $inputString
     * @return string
     */
    public static function sanitizeString($inputString) {
        $searches =     array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß');
        $replacements = array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss');

        return str_replace($searches, $replacements, $inputString);
    }
}