<?php

namespace BiberLtd\Bundle\MemberManagementBundle\Entity;
/**
 * @name        MembersOfSite
 * @package		BiberLtd\Bundle\CoreBundle\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.4
 * @date        30.04.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */

use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="members_of_site",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNMembersOfSiteDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNMembersOfSiteDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNMembersOfSiteDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMembersOfSite", columns={"member","site"})}
 * )
 */
class MembersOfSite extends CoreEntity
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $date_removed;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
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
 * v1.0.4                      30.04.2015
 * Can Berkol
 * **************************************
 * CR :: ORM structure has been updated.
 *
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