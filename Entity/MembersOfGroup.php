<?php
/**
 * @name        MembersOfGroup
 * @package		BiberLtd\Core\MemberManagementBundle
 *
 * @author		Murat Ünal
 * @version     1.0.3
 * @date        08.09.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Core\Bundles\MemberManagementBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="members_of_group",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idx_n_members_of_group_date_added", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_members_of_group", columns={"member","member_group"})}
 * )
 */
class MembersOfGroup extends CoreEntity
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
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\MemberManagementBundle\Entity\MemberGroup")
     * @ORM\JoinColumn(name="member_group", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member_group;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="referrer", referencedColumnName="id", onDelete="SET NULL")
     */
    private $referrer;
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
     * @name            setMemberGroup()
     *  				Sets $member_group property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $member_group
     *
     * @return          object          $this
     */
    public function setMemberGroup($member_group){
        if(!$this->setModified('member_group', $member_group)->isModified()){
            return $this;
        }
        $this->member_group = $member_group;

        return $this;
    }
    /**
     * @name            getMemberGroup()
     *  				Gets $member_group property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->member_group
     */
    public function getMemberGroup(){
        return $this->member_group;
    }
    /**
     * @name            setReferrer()
     *  				Sets $referrer property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $referrer
     *
     * @return          object          $this
     */
    public function setReferrer($referrer){
        if(!$this->setModified('referrer', $referrer)->isModified()){
            return $this;
        }
        $this->referrer = $referrer;

        return $this;
    }
    /**
     * @name            getReferrer()
     *  				Gets $referrer property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->referrer
     */
    public function getReferrer(){
        return $this->referrer;
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
 * M Namespaces are fixed.
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getDateAdded()
 * A setDateAdded()
 * A getMember()
 * A setMember()
 * A getMemberGroup()
 * A setMemberGroup()
 * A getReferrer()
 * A setReferrer()
 *
 */