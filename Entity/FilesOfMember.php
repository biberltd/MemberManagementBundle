<?php
/**
 * @name        FilesOfMember
 * @package		BiberLtd\Core\MemberManagementBundle
 *
 * @author		Can Berkol
 *
 * @version     1.0.1
 * @date        18.12.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */

namespace BiberLtd\Bundle\MemberManagementBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="files_of_member",
 *     options={"charset":"utf8","engine":"innodb","collate":"utf8_turkish_ci"},
 *     indexes={@ORM\Index(name="idx_n_files_of_member_date_added", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_files_of_member", columns={"member","file"})}
 * )
 */
class FilesOfMember extends CoreEntity
{
    /** 
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $count_view;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member",
     *     inversedBy="files_of_members"
     * )
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false)
     */
    private $member;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\File",
     *     inversedBy="files_of_members"
     * )
     * @ORM\JoinColumn(name="file", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $file;

    /**
     * @name                  setCountView ()
     *                                     Sets the count_view property.
     *                                     Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           integer $count_view
     *
     * @return          object                $this
     */
    public function setCountView($count_view) {
        if(!$this->setModified('count_view', $count_view)->isModified()) {
            return $this;
        }
		$this->count_view = $count_view;
		return $this;
    }

    /**
     * @name            getCountView ()
     *                               Returns the value of count_view property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          integer           $this->count_view
     */
    public function getCountView() {
        return $this->count_view;
    }

    /**
     * @name                  setFile ()
     *                                Sets the file property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           BiberLtd\Bundle\FileManagementBundle\Entity\File $file
     *
     * @return          object                $this
     */
    public function setFile($file) {
        if(!$this->setModified('file', $file)->isModified()) {
            return $this;
        }
		$this->file = $file;
		return $this;
    }

    /**
     * @name            getFile ()
     *                          Returns the value of file property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\FileManagementBundle\Entity\File           $this->file
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @name                  setMember ()
     *                                  Sets the member property.
     *                                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member
     *
     * @return          object                $this
     */
    public function setMember($member) {
        if(!$this->setModified('member', $member)->isModified()) {
            return $this;
        }
		$this->member = $member;
		return $this;
    }

    /**
     * @name            getMember ()
     *                            Returns the value of member property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\MemberManagementBundle\Entity\Member           $this->member
     */
    public function getMember() {
        return $this->member;
    }

}
/**
* Change Log:
* **************************************
* v1.0.1                      Can Berkol
* 18.12.2013
* **************************************
* U set methods now call setModified()
*/