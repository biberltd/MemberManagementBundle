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
 *     name="member_localization",
 *     options={"engine":"innodb","charset":"utf8","collate":"utf8_turkish_ci"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMemberLocalization", columns={"member","language"})}
 * )
 */
class MemberLocalization extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $biography;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $extra_data;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member", inversedBy="localizations")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false)
     * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
     */
    private $member;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
     */
    private $language;

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(\string $title){
        if(!$this->setModified('title', $title)->isModified()){
            return $this;
        }
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * @param string $biography
     *
     * @return $this
     */
    public function setBiography(\string $biography){
        if(!$this->setModified('biography', $biography)->isModified()){
            return $this;
        }
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return string
     */
    public function getBiography(){
        return $this->biography;
    }

    /**
     * @param string $extra_data
     *
     * @return $this
     */
    public function setExtraData(\string $extra_data){
        if(!$this->setModified('extra_data', $extra_data)->isModified()){
            return $this;
        }
        $this->extra_data = $extra_data;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraData(){
        return $this->extra_data;
    }

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