<?php
/**
 * @name        MemberLocalization
 * @package		BiberLtd\Bundle\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.3
 * @date        08.09.2013
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
 *     name="member_localization",
 *     options={"engine":"innodb","charset":"utf8","collate":"utf8_turkish_ci"},
 *     indexes={
 *         @ORM\Index(name="idx_u_member_localization", columns={"member","language"}),
 *         @ORM\Index(name="idx_n_member_localization_title", columns={"title"})
 *     }
 * )
 */
class MemberLocalization extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $extra_data;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member",
     *     inversedBy="localizations"
     * )
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false)
     */
    private $member;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $language;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            setTitle()
     *  				Sets $title property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $title
     *
     * @return          object          $this
     */
    public function setTitle($title){
        if(!$this->setModified('title', $title)->isModified()){
            return $this;
        }
        $this->title = $title;

        return $this;
    }
    /**
     * @name            getTitle()
     *  				Gets $title property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->title
     */
    public function getTitle(){
        return $this->title;
    }
    /**
     * @name            setBiography()
     *  				Sets $biography property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $biography
     *
     * @return          object          $this
     */
    public function setBiography($biography){
        if(!$this->setModified('biography', $biography)->isModified()){
            return $this;
        }
        $this->biography = $biography;
        return $this;
    }
    /**
     * @name            getBiography()
     *  				Gets $biography property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->biography
     */
    public function getBiography(){
        return $this->biography;
    }
    /**
     * @name            setExtraData()
     *  				Sets $extra_data property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $extra_data
     *
     * @return          object          $this
     */
    public function setExtraData($extra_data){
        if(!$this->setModified('extra_data', $extra_data)->isModified()){
            return $this;
        }
        $this->extra_data = $extra_data;

        return $this;
    }
    /**
     * @name            getExtraData()
     *  				Gets $extra_data property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->extra_data
     */
    public function getExtraData(){
        return $this->extra_data;
    }
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
 * v1.0.3                      Murat Ünal
 * 08.09.2013
 * **************************************
 * M Extends CoreEntity.
 *
 * **************************************
 * v1.0.2                      Murat Ünal
 * 03.08.2013
 * **************************************
 * M Namespace fixed.
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getBiography()
 * A setBiography()
 * A getExtraData()
 * A setExtraData()
 * A getLanguage()
 * A setLanguage()
 * A getMember()
 * A setMember()
 * A getTitle()
 * A setTitle()
 *
 */