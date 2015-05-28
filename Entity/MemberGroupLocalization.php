<?php
/**
 * @name        MemberGroupLocalization
 * @package		BiberLtd\Bundle\CoreBundle\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.5
 * @date        30.04.2015
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
     */
    private $name;

    /** 
     * @ORM\Column(type="string", length=55, nullable=false)
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(
	 *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup",
	 *     inversedBy="localizations"
	 * )
	 * @ORM\JoinColumn(name="member_group", referencedColumnName="id", nullable=false)
	 */
	private $group;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false)
     */
    private $language;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            set_name()
     *  				Sets $name property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $name
     *
     * @return          object          $this
     */
    public function setName($name){
        if(!$this->setModified('name', $name)->isModified()){
            return $this;
        }
        $this->name = $name;

        return $this;
    }
    /**
     * @name            getName()
     *  				Gets $name property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->name
     */
    public function getName(){
        return $this->name;
    }
    /**
     * @name            setUrlKey()
     *  				Sets $url_key property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $url_key
     *
     * @return          object          $this
     */
    public function setUrlKey($url_key){
        if(!$this->setModified('url_key', $url_key)->isModified()){
            return $this;
        }
        $this->url_key = $url_key;

        return $this;
    }
    /**
     * @name            getUrlKey()
     *  				Gets $url_key property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->url_key
     */
    public function getUrlKey(){
        return $this->url_key;
    }
    /**
     * @name            setDescription()
     *  				Sets $description property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $description
     *
     * @return          object          $this
     */
    public function setDescription($description){
        if(!$this->setModified('description', $description)->isModified()){
            return $this;
        }
        $this->description = $description;

        return $this;
    }
    /**
     * @name            getDescription()
     *  				Gets $description property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->description
     */
    public function getDescription(){
        return $this->description;
    }
	/**
	 * @name            setGroup()
	 *
	 * @author          Can Berkol
	 * @author          Murat Ünal
	 * @since			1.0.0
	 * @version         1.0.5
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
	 * @author          Murat Ünal
	 * @since			1.0.5
	 * @version         1.0.5
	 *
	 * @return          string          $this->member_group
	 */
	public function getGroup(){
		return $this->group;
	}
    /**
     * @name            setLanguage()
     *  				Sets $language property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $language
     *
     * @return          object          $this
     */
    public function setLanguage($language){
        if(!$this->setModified('language', $language)->isModified()){
            return $this;
        }
        $this->language = $language;

        return $this;
    }
    /**
     * @name            getLanguage()
     *  				Gets $language property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->language
     */
    public function getLanguage(){
        return $this->language;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.5                      30.04.2015
 * Can Berkol
 * **************************************
 * CR :: ORM structure has been updated.
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 08.09.2013
 * **************************************
 * M Extends CoreEntity.
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 05.08.2013
 * **************************************
 * M Non-core functionalities have been commented out.
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getDescription()
 * A setDescription()
 * A getLanguage()
 * A setLanguage()
 * A getMemberGroup()
 * A setMemberGroup()
 * A getName()
 * A setName()
 * A getUrlKey()
 * A setUrlKey()
 *
 */