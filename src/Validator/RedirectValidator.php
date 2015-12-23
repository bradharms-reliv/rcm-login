<?php

namespace RcmLogin\Validator;

use Zend\Stdlib\ErrorHandler;
use Zend\Validator\Regex;

/**
 * Validator for login redirect
 *
 * @category  Reliv
 * @package   RcmLogin
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 */
class RedirectValidator extends Regex
{
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        ErrorHandler::start();
        $status = preg_match($this->pattern, $value);
        ErrorHandler::stop();

        if (!$status) {
            return true;
        }

        $this->error(self::NOT_MATCH);
        return false;
    }
}
