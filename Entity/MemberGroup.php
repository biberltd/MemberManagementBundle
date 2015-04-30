<?php
/**
 * @name        MemberGroup
 * @package		BiberLtd\Bundle\CoreBundle\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.8
 * @date        30.04.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
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
	 */
	public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $date_removed;
	/**
    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroupLocalization",
     *     mappedBy="member_group"
     * )
     */
    protected $localizations;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", unique=true, length=45, nullable=false)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $type;
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $count_members;
    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *  				Gets $id property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          integer          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name            setCode()
     *  				Sets $code property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $code
     *
     * @return          object          $this
     */
    public function setCode($code){
        if(!$this->setModified('code', $code)->isModified()) {
            return $this;
        }
        $this->code = $code;

        return $this;
    }

    /**
     * @name            getCode()
     *  				Gets $code property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->code
     */
    public function getCode(){
        return $this->code;
    }

    /**
     * @name            setType()
     *  				Sets $type property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $type
     *
     * @return          object          $this
     */
    public function setType($type){
        if(!$this->setModified('type', $type)->isModified()) {
            return $this;
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @name            getType()
     *  				Gets $type property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->type
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @name            setCountMembers()
     *  				Sets $count_members property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           integer          $count_members
     *
     * @return          object          $this
     */
    public function setCountMembers($count_members){
        if(!$this->setModified('count_members', $count_members)->isModified()) {
            return $this;
        }
        $this->count_members = $count_members;

        return $this;
    }

    /**
     * @name            getCountMembers()
     *  				Gets $count_members property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          integer          $this->count_members
     */
    public function getCountMembers(){
        return $this->count_members;
    }

    /**
     * @name            setSite()
     *  				Sets $site property.
     *
     * @author          Can Berkol
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           BiberLtd\Bundle\SiteManagementBundle\Entity\Site          $site
     *
     * @return          object          $this
     */
    public function setSite($site){
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
        $this->site = $site;

        return $this;
    }

    /**
     * @name            getSite()
     *  				Gets $site property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\SiteManagementBundle\Entity\Site          $this->site
     */
    public function getSite(){
        return $this->site;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @name            increment_member_count()
     *  				Increments the member count.
     * .
     * @author          Can Berkol
     * @since			1.0.4
     * @version         1.0.4
     *
     * @param           integer         $value
     *
     * @return          mixed           $this
     */
    public function incrementMemberCount($value){
        if(!is_integer($value)){
            return $this;
        }
        $this->count_members = $this->count_members + $value;
        return $this;
    }

    /**
     * @name            decrement_member_count()
     *  				Decrements the member count.
     * .
     * @author          Can Berkol
     * @since			1.0.4
     * @version         1.0.4
     *
     * @param           integer         $value
     *
     * @return          mixed           $this
     */
    public function decrementMemberCount($value){
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
/**
 * Change Log:
 * **************************************
 * v1.0.8                      30.04.2015
 * Can Berkol
 * **************************************
 * CR :: ORM structure has been updated.
 *
 * **************************************
 * v1.0.7                      Can Berkol
 * 01.01.2014
 * **************************************
 * B setSite()
 *
 * **************************************
 * v1.0.5                      Can Berkol
 * 08.09.2013
 * **************************************
 * M Extends CoreEntity
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 09.08.2013
 * **************************************
 * A decrement_member_count()
 * A increment_member_count()
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 05.08.2013
 * **************************************
 * M Non-core functionalities are commented out.
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getCode()
 * A getCountMembers()
 * A getDateCreated()
 * A getDateUpdated()
 * A getId()
 * A getLocalizations()
 * A getSite()
 * A getType()
 *
 * A setCode()
 * A setCountMembers()
 * A setDateCreated()
 * A setDateUpdated()
 * A setLocalizations()
 * A setSite()
 * A setType()
 *
 */