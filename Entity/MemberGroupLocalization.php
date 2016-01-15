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
 *     name="member_group_localization",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxUMemberGroupLocalization", columns={"member_group","language"}),
 *         @ORM\UniqueConstraint(name="idxUMemberGroupLocalizationUrlKey", columns={"language","url_key"})
 *     }
 * )
 */
class MemberGroupLocalization extends CoreEntity
{
    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     * @var string
     */
    private $name;

    /** 
     * @ORM\Column(type="string", length=55, nullable=false)
     * @var string
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(
	 *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup",
	 *     inversedBy="localizations"
	 * )
	 * @ORM\JoinColumn(name="member_group", referencedColumnName="id", nullable=false)
	 * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup
	 */
	private $group;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false)
     * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
     */
    private $language;

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
    public function setName(string $name){
        if(!$this->setModified('name', $name)->isModified()){
            return $this;
        }
        $this->name = $name;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getName(){
        return $this->name;
    }

	/**
	 * @param string $url_key
	 *
	 * @return $this
	 */
    public function setUrlKey(string $url_key){
        if(!$this->setModified('url_key', $url_key)->isModified()){
            return $this;
        }
        $this->url_key = $url_key;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getUrlKey(){
        return $this->url_key;
    }

	/**
	 * @param string $description
	 *
	 * @return $this
	 */
    public function setDescription(string $description){
        if(!$this->setModified('description', $description)->isModified()){
            return $this;
        }
        $this->description = $description;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getDescription(){
        return $this->description;
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

	/**
	 * @param \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language
	 *
	 * @return $this
	 */
    public function setLanguage(\BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language){
        if(!$this->setModified('language', $language)->isModified()){
            return $this;
        }
        $this->language = $language;

        return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
	 */
    public function getLanguage(){
        return $this->language;
    }
}