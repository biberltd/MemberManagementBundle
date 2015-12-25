<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
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
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMembersOfGroup", columns={"member","member_group"})}
 * )
 */
class MembersOfGroup extends CoreEntity
{
    /** 
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    public $date_added;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var \DateTime
	 */
	public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	public $date_removed;
    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
     */
    private $member;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup")
	 * @ORM\JoinColumn(name="member_group", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup
	 */
	private $group;

    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

	/**
	 * @param \BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member
	 *
	 * @return $this
	 */
    public function setMember(\BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member){
        if(!$this->setModified('member', $member)->isModified()){
            return $this;
        }
        $this->member = $member;

        return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
	 */
    public function getMember(){
        return $this->member;
    }

	/**
	 * @param \BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup $member_group
	 *
	 * @return $this
	 */
	public function setGroup(\BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup $member_group){
		if(!$this->setModified('group', $member_group)->isModified()){
			return $this;
		}
		$this->group = $member_group;

		return $this;
	}

	/**
	 * @return \BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup
	 */
	public function getGroup(){
		return $this->group;
	}
}