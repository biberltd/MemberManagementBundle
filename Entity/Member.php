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
use Doctrine\Common\Collections\ArrayCollection;
use BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="member",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNFullNameOfMember", columns={"name_first","name_last"}),
 *         @ORM\Index(name="idxNMemberDateRegitration", columns={"date_registration"}),
 *         @ORM\Index(name="idxNMemberDateBirth", columns={"date_birth"}),
 *         @ORM\Index(name="idxNMemberDateActivation", columns={"date_activation"}),
 *         @ORM\Index(name="idxNMemberDateStatusChanged", columns={"date_status_changed"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxUMemberId", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idxUMemberUsername", columns={"username","site"}),
 *         @ORM\UniqueConstraint(name="idxUMemberEmail", columns={"email","site"})
 *     }
 * )
 */
class Member extends CoreLocalizableEntity{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $extra_info;

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     * @var string
     */
    protected $name_first;

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     * @var string
     */
    protected $name_last;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", unique=true, length=155, nullable=false)
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $date_birth;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $file_avatar;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $date_registration;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $date_activation;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $date_status_changed;

    /**
     * @ORM\Column(type="string", length=1, nullable=false, options={"default":"i"})
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $key_activation;

    /**
     * @ORM\Column(type="string", length=1, nullable=true, options={"default":"f"})
     * @var string
     */
    private $gender;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $date_last_login;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberLocalization",
     *     mappedBy="member"
     * )
     * @var array
     */
    protected $localizations;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
     */
    protected $language;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    protected $site;

    /**
     * @var bool
     */
    private $passwordChanged = false;

    /**
     * Member constructor.
     */
    public function __construct(){
        parent::__construct();
        /** Initialize associations to ArrayCollection */
        $this->member_groups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param string $name_first
     *
     * @return $this
     */
    public function setNameFirst(\string $name_first){
        if(!$this->setModified('name_first', $name_first)->isModified()) {
            return $this;
        }
        $this->name_first = $name_first;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameFirst(){
        return $this->name_first;
    }

    /**
     * @param string $name_last
     *
     * @return $this
     */
    public function setNameLast(\string $name_last){
        if(!$this->setModified('name_last', $name_last)->isModified()) {
            return $this;
        }
        $this->name_last = $name_last;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameLast(){
        return $this->name_last;
    }

    /**
     * @return string
     */
    public function getFullName(){
        return $this->name_first.' '.$this->name_last;
    }

	/**
	 * @param string $email
	 *
	 * @return $this
	 */
    public function setEmail(\string $email){
        if(!$this->setModified('email', $email)->isModified()) {
            return $this;
        }
        $this->email = $email;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getEmail(){
        return $this->email;
    }

	/**
	 * @param string $username
	 *
	 * @return $this
	 */
    public function setUsername(\string $username){
        if(!$this->setModified('username', $username)->isModified()){
            return $this;
        }
        $this->username = $username;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getUsername(){
        return $this->username;
    }

	/**
	 * @param string $password
	 *
	 * @return $this
	 */
    public function setPassword(\string $password){
        if(!$this->setModified('password', $password)->isModified()){
            return $this;
        }
        $this->password = $password;
        $this->passwordChanged = true;
        return $this;
    }

	/**
	 * @param bool $status
	 *
	 * @return bool
	 */
    public function setPasswordChanged(\bool $status){
        return $this->passwordChanged = $status;
    }

	/**
	 * @return string
	 */
    public function getPassword(){
        return $this->password;
    }

	/**
	 * @return bool
	 */
    public function isPasswordChanged(){
        return $this->passwordChanged;
    }

	/**
	 * @param \DateTime $date_birth
	 *
	 * @return $this
	 */
    public function setDateBirth(\DateTime $date_birth){
        if(!$this->setModified('date_birth', $date_birth)->isModified()){
            return $this;
        }
        $this->date_birth = $date_birth;

        return $this;
    }

	/**
	 * @return \DateTime
	 */
    public function getDateBirth(){
        return $this->date_birth;
    }

	/**
	 * @param string $file_avatar
	 *
	 * @return $this
	 */
    public function setFileAvatar(\string $file_avatar){
        if(!$this->setModified('file_avatar', $file_avatar)->isModified()){
            return $this;
        }
        $this->file_avatar = $file_avatar;
        return $this;
    }

	/**
	 * @return string
	 */
    public function getFileAvatar(){
        return $this->file_avatar;
    }

	/**
	 * @param \DateTime $date_registration
	 *
	 * @return $this
	 */
    public function setDateRegistration(\DateTime $date_registration){
        if(!$this->setModified('date_registration', $date_registration)->isModified()){
            return $this;
        }
        $this->date_registration = $date_registration;

        return $this;
    }

	/**
	 * @return \DateTime
	 */
    public function getDateRegistration(){
        return $this->date_registration;
    }

	/**
	 * @param \DateTime $date_activation
	 *
	 * @return $this
	 */
    public function setDateActivation(\DateTime $date_activation){
        if(!$this->setModified('date_activation', $date_activation)->isModified()){
            return $this;
        }
        $this->date_activation = $date_activation;

        return $this;
    }

	/**
	 * @return \DateTime
	 */
    public function getDateActivation(){
        return $this->date_activation;
    }

	/**
	 * @param \DateTime $date_status_changed
	 *
	 * @return $this
	 */
    public function setDateStatusChanged(\DateTime $date_status_changed){
        if(!$this->setModified('date_status_changed', $date_status_changed)->isModified()){
            return $this;
        }
        $this->date_status_changed = $date_status_changed;

        return $this;
    }

	/**
	 * @return \DateTime
	 */
    public function getDateStatusChanged(){
        return $this->date_status_changed;
    }

	/**
	 * @return string
	 */
    public function getExtraInfo(){
        return $this->extra_info;
    }

	/**
	 * @param string $extra_info
	 *
	 * @return $this
	 */
    public function setExtraInfo(\string $extra_info){
        if(!$this->setModified('status', $extra_info)->isModified()){
            return $this;
        }
        $this->extra_info = $extra_info;

        return $this;
    }

	/**
	 * @param string $status
	 *
	 * @return $this
	 */
    public function setStatus(\string $status){
        if(!$this->setModified('status', $status)->isModified()){
            return $this;
        }
        $this->status = $status;

        return $this;
    }

	/**
	 * @return string
	 */
    public function getStatus(){
        return $this->status;
    }

	/**
	 * @param string|null $key
	 *
	 * @return $this
	 */
    public function setKeyActivation(\string $key = null){
        if(!$this->setModified('key_activation', $key)->isModified()){
            return $this;
        }
        if(is_null($key)){
            $this->key_activation = md5($this->username.$this->email);
        }
        else{
            $this->key_activation = $key;
        }
        return $this;
    }

	/**
	 * @return string
	 */
    public function getKeyActivation(){
        return $this->key_activation;
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
     * @name            getLanguage()
     *  				Gets $language property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\MemberManagementBundle\MultiLanguageSupportBundle\Language
     */
    public function getLanguage(){
        return $this->language;
    }

	/**
	 * @param \BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site
	 *
	 * @return $this
	 */
    public function setSite(\BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site){
        if(!$this->setModified('site', $site)->isModified()){
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

	/**
	 * @return string
	 */
    public function getGender()
    {
        return $this->gender;
    }

	/**
	 * @param string $gender
	 *
	 * @return $this
	 */
    public function setGender(\string $gender)
    {
        if(!$this->setModified('gender', $gender)->isModified()){
            return $this;
        }
        $this->gender = $gender;

        return $this;
    }

	/**
	 * @return \DateTime
	 */
    public function getDateLastLogin()
    {
        return $this->date_last_login;
    }

	/**
	 * @param \DateTime $date_last_login
	 *
	 * @return $this
	 */
    public function setDateLastLogin(\DateTime $date_last_login){
        if(!$this->setModified('date_last_login', $date_last_login)->isModified()){
            return $this;
        }
        $this->date_last_login = $date_last_login;

        return $this;
    }
}