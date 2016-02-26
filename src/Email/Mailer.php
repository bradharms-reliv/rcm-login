<?php

namespace RcmLogin\Email;

use RcmLogin\Entity\ResetPassword;
use RcmUser\User\Entity\User;

/**
 * Class Mailer
 *
 * PHP version 5
 *
 * @category  Reliv
 * @package   Mailer
 * @author    James Jervis <jjervis@relivinc.com>
 * @copyright 2016 Reliv International
 * @license   License.txt
 * @version   Release: <package_version>
 * @link      https://github.com/reliv
 */
interface Mailer
{
    /**
     * sendRestPasswordEmail
     *
     * @param ResetPassword $resetPw
     * @param User          $user
     * @param array         $instanceConfig
     *
     * @return mixed
     */
    public function sendRestPasswordEmail(
        ResetPassword $resetPw,
        User $user,
        $instanceConfig
    );
}
