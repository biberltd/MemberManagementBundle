<?php
/**
 * @name        MembersOfGroup
 * @package		BiberLtd\Bundle\CoreBundle\MemberManagementBundle
 *
 * @author		Can Berkol
 * @author		Murat Ünal
 *
 * @version     1.0.6
 * @date        08.06.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\MemberManagementBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="members_of_group",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNMembersOfGroupDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNMembersOfGroupDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNMembersOfGroupDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMembersOfGroup", columns={"member","group"})}
 * )
 */
class MembersOfGroup extends CoreEntity
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
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup")
	 * @ORM\JoinColumn(name="group", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	private $group;

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
	 * @name            setGroup()
	 *
	 * @author          Can Berkol
	 * @since			1.0.4
	 * @version         1.0.4
	 *
	 * @param           string          $member_group
	 *
	 * @return          object          $this
	 */
	public function setGroup($member_group){
		if(!$this->setModified('group', $member_group)->isModified()){
			return $this;
		}
		$this->group = $member_group;

		return $this;
	}
	/**
	 * @name            getGroup()
	 *
	 * @author          Can Berkol
	 * @since			1.0.4
	 * @version         1.0.4
	 *
	 * @return          string          $this->member_group
	 */
	public function getGroup(){
		return $this->group;
	}
}
/**
 * Change Log:
 * **************************************
 * v1.0.6                      08.06.2015
 * Can Berkol
 * **************************************
 * CR :: member_group properrty is changed to group.
 *
 * **************************************
 * v1.0.5                      03.05.2015
 * Can Berkol
 * **************************************
 * BF :: member_group was used instead of group. Fixed.
 *
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