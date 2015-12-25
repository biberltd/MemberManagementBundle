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
 *     name="files_of_member",
 *     options={"charset":"utf8","engine":"innodb","collate":"utf8_turkish_ci"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUFilesOfMember", columns={"member","file"})}
 * )
 */
class FilesOfMember extends CoreEntity
{
    /** 
     * @ORM\Column(type="integer", length=10, nullable=false, options={"default":0})
     * @var int
     */
    private $count_view;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
	 * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false)
	 * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
	 */
	private $member;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\File")
	 * @ORM\JoinColumn(name="file", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BiberLtd\Bundle\FileManagementBundle\Entity\File
	 */
	private $file;

	/**
	 * @param int $count_view
	 *
	 * @return $this
	 */
    public function setCountView(\integer $count_view) {
        if(!$this->setModified('count_view', $count_view)->isModified()) {
            return $this;
        }
		$this->count_view = $count_view;
		return $this;
    }

	/**
	 * @return int
	 */
    public function getCountView() {
        return $this->count_view;
    }

	/**
	 * @param \BiberLtd\Bundle\FileManagementBundle\Entity\File $file
	 *
	 * @return $this
	 */
    public function setFile(\BiberLtd\Bundle\FileManagementBundle\Entity\File $file) {
        if(!$this->setModified('file', $file)->isModified()) {
            return $this;
        }
		$this->file = $file;
		return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\FileManagementBundle\Entity\File
	 */
    public function getFile() {
        return $this->file;
    }

	/**
	 * @param \BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member
	 *
	 * @return $this
	 */
    public function setMember(\BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member) {
        if(!$this->setModified('member', $member)->isModified()) {
            return $this;
        }
		$this->member = $member;
		return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
	 */
    public function getMember() {
        return $this->member;
    }

}