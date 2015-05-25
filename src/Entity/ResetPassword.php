<?php
namespace RcmLogin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Lead form entity
 *
 * This object contains lead form data
 *
 * @category  Reliv
 * @author    Brian Janish <bjanish@relivinc.com>
 * @copyright 2013 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 *
 * @ORM\Entity
 * @ORM\Table(name="rcm_reset_pw")
 */
class ResetPassword
{
    /**
     * @var int Auto-Incremented Primary Key
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $resetId;

    /**
     * @var \DateTime when the customer began the app
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $rcn;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $hashKey;

    public function __construct()
    {
        $this->createdDate = new \DateTime();
        $this->hashKey = sha1(uniqid(mt_rand(), true));
    }

    /**
     * @return InputFilter
     */
    public function getInputFilter()
    {
        $inputFilter = new InputFilter();

        $factory = new InputFactory();

        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'rcn',
                    'required' => true,
                    'filters' => [
                        new \Zend\Filter\StripTags(),
                        new \Zend\Filter\StringTrim(),
                    ]
                ]
            )
        );

        return $inputFilter;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param string $hashKey
     */
    public function setHashKey($hashKey)
    {
        $this->hashKey = $hashKey;
    }

    /**
     * @return string
     */
    public function getHashKey()
    {
        return $this->hashKey;
    }

    /**
     * @param string $rcn
     */
    public function setRcn($rcn)
    {
        $this->rcn = $rcn;
    }

    /**
     * @return string
     */
    public function getRcn()
    {
        return $this->rcn;
    }

    /**
     * @param int $resetId
     */
    public function setResetId($resetId)
    {
        $this->resetId = $resetId;
    }

    /**
     * @return int
     */
    public function getResetId()
    {
        return $this->resetId;
    }
}
