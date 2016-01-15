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

use Doctrine\ORM\Mapping as ORM;
use BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;
use BiberLtd\Bundle\MemberManagementBundle\Exceptions;

/**
 * @ORM\Table(
 *     name="member_group",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNMemberGroupDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNMemberGroupDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNMemberGroupDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxUMemberGroupId", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idxUMemberGroupCode", columns={"code"})
 *     }
 * )
 * @ORM\Entity
 *
 */
class MemberGroup extends CoreLocalizableEntity{
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
    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroupLocalization",
     *     mappedBy="group"
     * )
     * @var array
     */
    protected $localizations;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(type="string", unique=true, length=45, nullable=false)
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    public $date_added;

    /**
     * @ORM\Column(type="string", length=1, nullable=false, options={"default":"r"})
     * @var string
     */
    private $type;
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * @var int
     */
    private $count_members;
    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    private $site;

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code){
        if(!$this->setModified('code', $code)->isModified()) {
            return $this;
        }
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(){
        return $this->code;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type){
        if(!$this->setModified('type', $type)->isModified()) {
            return $this;
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @param int $count_members
     *
     * @return $this
     */
    public function setCountMembers(int $count_members){
        if(!$this->setModified('count_members', $count_members)->isModified()) {
            return $this;
        }
        $this->count_members = $count_members;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountMembers(){
        return $this->count_members;
    }

    /**
     * @param \BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site
     *
     * @return $this
     */
    public function setSite(\BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site){
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
        $this->site = $site;

        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    public function getSite(){
        return $this->site;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function incrementMemberCount(int $value){
        if(!is_integer($value)){
            return $this;
        }
        $this->count_members = $this->count_members + $value;
        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function decrementMemberCount(int $value){
        if(!is_integer($value)){
            return $this;
        }
        $new_value = $this->count_members - $value;
        if($new_value < 0){
            $new_value = 0;
        }
        $this->count_members = $new_value;
        return $this;
    }
}