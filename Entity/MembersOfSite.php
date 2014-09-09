<?php

namespace BiberLtd\Core\Bundles\MemberManagementBundle\Entity;
/**
 * @name        MembersOfSite
 * @package		BiberLtd\Core\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.3
 * @date        09.08.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */

use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="members_of_site",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idx_n_members_of_site_date_added", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_members_of_site", columns={"member","site"})}
 * )
 */
class MembersOfSite extends CoreEntity
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $site;

    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            setMember()
     *  				Sets $member property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $member
     *
     * @return          object          $this
     */
    public function setMember($member){
        if(!$this->setModified('member', $member)->isModified()){
            return $this;
        }
        $this->member = $member;

        return $this;
    }
    /**
     * @name            getMember()
     *  				Gets $member property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->member
     */
    public function getMember(){
        return $this->member;
    }
    /**
     * @name            setSite()
     *  				Sets $site property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $site
     *
     * @return          object          $this
     */
    public function setSite($site){
        if(!$this->setModified('site', $site)->isModified()){
            return $this;
        }
        $this->site = $site;

        return $this;
    }
    /**
     * @name            getSite()
     *  				Gets $site property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->site
     */
    public function getSite(){
        return $this->site;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.3                      Can Berkol
 * 08.09.2013
 * **************************************
 * M Extends CoreEntity.
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 05.08.2013
 * **************************************
 * M Namespace is fixed
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getDateAdded()
 * A set_date_added()
 * A getMember()
 * A setMember()
 * A getSite()
 * A setSite()
 *
 */