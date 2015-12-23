<?php
/**
 * Filter for login redirect
 *
 * This filter cleans up passed redirect urls and ensures that
 * successful logins are not redirected away from the site
 *
 * PHP version 5.4
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @package   RcmLogin
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2015 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      http://github.com/reliv
 */
namespace RcmLogin\Filter;

use Zend\Filter\FilterInterface;

/**
 * Filter for login redirect
 *
 * This filter cleans up passed redirect urls and ensures that
 * successful logins are not redirected away from the site
 *
 * @category  Reliv
 * @package   RcmLogin
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 */
class RedirectFilter implements FilterInterface
{
    /**
     * Filter the redirect url
     *
     * @param mixed $value
     * @return string|null
     */
    public function filter($value)
    {
        if (!$this->isValid($value)) {
            return null;
        }

        return urldecode(filter_var($value, FILTER_SANITIZE_URL));
    }

    /**
     * Insure that the redirect is not redirecting away from the site
     *
     * @param mixed $value
     * @return string|null
     */
    protected function isValid($value)
    {
        return !preg_match('/.+:\/\/|\/\//i', $value);
    }
}
