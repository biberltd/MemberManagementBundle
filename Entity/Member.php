<?php
/**
 * @name        Member
 * @package		BiberLtd\Bundle\CoreBundle\MemberManagementBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 *              Said İmamoğlu
 * @version     1.1.4
 * @date        09.02.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
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
 *         @ORM\Index(name="idx_n_full_name", columns={"name_first","name_last"}),
 *         @ORM\Index(name="idx_u_member_email", columns={"email"}),
 *         @ORM\Index(name="idx_n_member_date_registeration", columns={"date_registration"}),
 *         @ORM\Index(name="idx_n_member_date_birth", columns={"date_birth"}),
 *         @ORM\Index(name="idx_n_member_date_activation", columns={"date_activation"}),
 *         @ORM\Index(name="idx_n_member_date_status_changed", columns={"date_status_changed"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idx_u_member_id", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idx_u_member_username", columns={"username"})
 *     }
 * )
 */
class Member extends CoreLocalizableEntity{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
    @ORM\Column(type="text", nullable=true)*/
    protected $extra_info;
    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $name_first;

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $name_last;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", unique=true, length=155, nullable=false)
     */
    protected $username;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_birth;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $file_avatar;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_registration;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_activation;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_status_changed;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $key_activation;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_last_login;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\FilesOfMember",
     *     mappedBy="member"
     * )
     */
    protected $files_of_members;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberLocalization",
     *     mappedBy="member"
     * )
     */
    protected $localizations;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $language;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $site;

    private $passwordChanged = false;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            __construct()
     *  				Initializes entity..
     * .
     * @author          Can Berkol
     * @since			1.0.0
     * @version         1.0.3
     *
     *
     */
    public function __construct(){
        parent::__construct();
        /** Initialize associations to ArrayCollection */
        $this->member_groups = new ArrayCollection();
    }
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
     * @return          string          $this->id
     */
    public function getId(){
        return $this->id;
    }
    /**
     * @name            setNameFirst()
     *  				Sets $name_first property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $name_first
     *
     * @return          object          $this
     */
    public function setNameFirst($name_first){
        if(!$this->setModified('name_first', $name_first)->isModified()) {
            return $this;
        }
        $this->name_first = $name_first;

        return $this;
    }
    /**
     * @name            getNameFirst()
     *  				Gets $name_first property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->name_first
     */
    public function getNameFirst(){
        return $this->name_first;
    }
    /**
     * @name            setNameLast()
     *  				Sets $name_last property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $name_last
     *
     * @return          object          $this
     */
    public function setNameLast($name_last){
        if(!$this->setModified('name_last', $name_last)->isModified()) {
            return $this;
        }
        $this->name_last = $name_last;

        return $this;
    }
    /**
     * @name            getNameLast()
     *  				Gets $name_last property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->name_last
     */
    public function getNameLast(){
        return $this->name_last;
    }
    /**
     * @name            getFullName()
     *  				Gets $name_first.' '.$name_last property.
     * .
     * @author          Can Berkol
     * @since			1.0.0
     * @version         1.0.3
     *
     * @return          string
     */
    public function getFullName(){
        return $this->name_first.' '.$this->name_last;
    }
    /**
     * @name            setEmail()
     *  				Sets $email property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $email
     *
     * @return          object          $this
     */
    public function setEmail($email){
        if(!$this->setModified('email', $email)->isModified()) {
            return $this;
        }
        $this->email = $email;

        return $this;
    }
    /**
     * @name            getEmail()
     *  				Gets $email property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->email
     */
    public function getEmail(){
        return $this->email;
    }
    /**
     * @name            setUsername()
     *  				Sets $username property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $username
     *
     * @return          object          $this
     */
    public function setUsername($username){
        if(!$this->setModified('username', $username)->isModified()){
            return $this;
        }
        $this->username = $username;

        return $this;
    }
    /**
     * @name            getUsername()
     *  				Gets $username property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->username
     */
    public function getUsername(){
        return $this->username;
    }
    /**
     * @name            setPassword()
     *  				Sets $password property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.1.2
     *
     * @param           string          $password
     *
     * @return          object          $this
     */
    public function setPassword($password){
        if(!$this->setModified('password', $password)->isModified()){
            return $this;
        }
        $this->password = $password;
        $this->passwordChanged = true;
        return $this;
    }
    /**
     * @name            setPasswordChanged()
     *  				Sets $passwordChanged property.
     * .
     * @author          Can Berkol
     * @since			1.1.2
     * @version         1.1.2
     *
     * @param           bool            $status
     *
     * @return          bool
     */
    public function setPasswordChanged($status){
        return $this->passwordChanged = $status;
    }
    /**
     * @name            getPassword()
     *  				Gets $password property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->password
     */
    public function getPassword(){
        return $this->password;
    }
    /**
     * @name            isPasswordChanged()
     *  				Gets $passwordChanged property.
     * .
     * @author          Can Berkol
     * @since			1.1.2
     * @version         1.1.2
     *
     * @return          bool
     */
    public function isPasswordChanged(){
        return $this->passwordChanged;
    }
    /**
     * @name            setDateBirth()
     *  				Sets $date_birth property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           DateTime          $date_birth
     *
     * @return          object          $this
     */
    public function setDateBirth($date_birth){
        if(!$this->setModified('date_birth', $date_birth)->isModified()){
            return $this;
        }
        $this->date_birth = $date_birth;

        return $this;
    }
    /**
     * @name            getDateBirth()
     *  				Gets $date_birth property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          DateTime          $this->date_birth
     */
    public function getDateBirth(){
        return $this->date_birth;
    }
    /**
     * @name            setFileAvatar()
     *  				Sets $file_avatar property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $file_avatar
     *
     * @return          object          $this
     */
    public function setFileAvatar($file_avatar){
        if(!$this->setModified('file_avatar', $file_avatar)->isModified()){
            return $this;
        }
        $this->file_avatar = $file_avatar;
        return $this;
    }
    /**
     * @name            getFileAvatar()
     *  				Gets $file_avatar property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->file_avatar
     */
    public function getFileAvatar(){
        return $this->file_avatar;
    }
    /**
     * @name            setDateRegistration()
     *  				Sets $date_registration property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.9
     *
     * @param           DateTime          $date_registration
     *
     * @return          object          $this
     */
    public function setDateRegistration($date_registration){
        if(!$this->setModified('date_registration', $date_registration)->isModified()){
            return $this;
        }
        $this->date_registration = $date_registration;

        return $this;
    }
    /**
     * @name            getDateRegistration()
     *  				Gets $date_registration property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          DateTime          $this->date_registration
     */
    public function getDateRegistration(){
        return $this->date_registration;
    }
    /**
     * @name            setDateActivation()
     *  				Sets $date_activation property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $date_activation
     *
     * @return          object          $this
     */
    public function setDateActivation($date_activation){
        if(!$this->setModified('date_activation', $date_activation)->isModified()){
            return $this;
        }
        $this->date_activation = $date_activation;

        return $this;
    }
    /**
     * @name            getDateActivation()
     *  				Gets $date_activation property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          DateTime          $this->date_activation
     */
    public function getDateActivation(){
        return $this->date_activation;
    }
    /**
     * @name            setDateStatusChanged()
     *  				Sets $date_status_changed property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           DateTime          $date_status_changed
     *
     * @return          object          $this
     */
    public function setDateStatusChanged($date_status_changed){
        if(!$this->setModified('date_status_changed', $date_status_changed)->isModified()){
            return $this;
        }
        $this->date_status_changed = $date_status_changed;

        return $this;
    }
    /**
     * @name            getDateStatusChanged()
     *  				Gets $date_status_changed property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          DateTime          $this->date_status_changed
     */
    public function getDateStatusChanged(){
        return $this->date_status_changed;
    }
    /**
     * @name            getExtraInfo()
     *  				Gets $extra_info property.
     * .
     * @author          Said İmamoğlu
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->extra_info
     */
    public function getExtraInfo(){
        return $this->extra_info;
    }
    /**
     * @name            setExtraInfo()
     *  				Sets $extra_info property.
     * .
     * @author          Said İmamoğlu
     *
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $extra_info
     *
     * @return          object          $this
     */
    public function setExtraInfo($extra_info){
        if(!$this->setModified('status', $extra_info)->isModified()){
            return $this;
        }
        $this->extra_info = $extra_info;

        return $this;
    }
    /**
     * @name            setStatus()
     *  				Sets $status property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $status
     *
     * @return          object          $this
     */
    public function setStatus($status){
        if(!$this->setModified('status', $status)->isModified()){
            return $this;
        }
        $this->status = $status;

        return $this;
    }
    /**
     * @name            getStatus()
     *  				Gets $status property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->status
     */
    public function getStatus(){
        return $this->status;
    }
    /**
     * @name            setKeyActivation()
     *  				Sets activation key of the member. The key is
     *                  by default the md5 hash of username and user email.
     *
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           mixed          $key        Optional. string or null.
     *
     * @return          object          $this
     */
    public function setKeyActivation($key = null){
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
     * @name            getKeyActivation()
     *  				Gets $key_activation property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->key_activation
     */
    public function getKeyActivation(){
        return $this->key_activation;
    }

    /**
     * @name            setLanguage()
     *  				Sets $language property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
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
     * @return          BiberLtd\Bundle\MemberManagementBundle\MultiLanguageSupportBundle\Language
     */
    public function getLanguage(){
        return $this->language;
    }
    /**
     * @name            setSite()
     *  				Sets $site property.
     * .
     * @author          Can Berkol
     *                  Murat Ünal
     * @since			1.0.0
     * @version         1.0.7
     *
     * @param           string          $site
     *
     * @return          object          $this
     */
    public function setSite($site){
        if(!$this->setModified('site', $site)->isModified()){
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
     * @return          BiberLtd\Core\Bunles\SiteManagementBundle\Entity\Site
     */
    public function getSite(){
        return $this->site;
    }

    /**
     * @name        getGender ()
     *
     * @author      Can Berkol
     *
     * @since       1.1.3
     * @version     1.1.3
     *
     * @return      mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @name        setGender ()
     *
     * @author      Can Berkol
     *
     * @since       1.1.3
     * @version     1.1.3
     *
     * @param       mixed $gender
     *
     * @return      $this
     */
    public function setGender($gender)
    {
        if(!$this->setModifiled('gender', $gender)->isModified()){
            return $this;
        }
        $this->gender = $gender;

        return $this;
    }

    /**
     * @name        getDateLastLogin ()
     *
     * @author      Can Berkol
     *
     * @since       1.1.4
     * @version     1.1.4
     *
     * @return      mixed
     */
    public function getDateLastLogin()
    {
        return $this->date_last_login;
    }

    /**
     * @name        setDateLastLogin ()
     *
     * @author      Can Berkol
     *
     * @since       1.1.4
     * @version     1.1.4
     *
     * @param       mixed $date_last_login
     *
     * @return      $this
     */
    public function setDateLastLogin($date_last_login){
        if(!$this->setModifiled('date_last_login', $date_last_login)->isModified()){
            return $this;
        }
        $this->date_last_login = $date_last_login;

        return $this;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.1.4                      Can Berkol
 * 09.02.2015
 * **************************************
 * A getDateLastlogin()
 * A setDateLastlogin()
 *
 * **************************************
 * v1.1.3                      Can Berkol
 * 28.01.2015
 * **************************************
 * A getGender()
 * A setGender()
 *
 * **************************************
 * v1.1.2                      Can Berkol
 * 15.08.2014
 * **************************************
 * passwordChanged private member added.
 * A isPasswordChanged()
 * A setPasswordChanged()
 * U setPassword()
 *
 * **************************************
 * v1.1.1                   Said İmamoğlu
 * 07.07.2014
 * **************************************
 * A getExtraInfo()
 * A setExtraInfo()
 *
 * **************************************
 * v1.1.0                      Can Berkol
 * 31.12.2013
 * **************************************
 * D dumpGroupCodes()
 *
 * **************************************
 * v1.0.9                      Can Berkol
 * 18.12.2013
 * **************************************
 * U set methods now call setModified()
 *
 * **************************************
 * v1.0.7                      Can Berkol
 * 08.09.2013
 * **************************************
 * D get_localization()
 * M exdends CoreLocalizedEntity
 *
 * **************************************
 * v1.0.6                      Can Berkol
 * 06.09.2013
 * **************************************
 * A get_localization()
 *
 * **************************************
 * v1.0.5                      Can Berkol
 * 10.08.2013
 * **************************************
 * A dumpGroupCodes()
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 07.08.2013
 * **************************************
 * A __construct()
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 05.08.2013
 * **************************************
 * M Non-core functionalitirs commented out.
 *
 * **************************************
 * v1.0.1                      Murat Ünal
 * 22.07.2013
 * **************************************
 * A getBlogPostModerations()
 * A setBlogPostModerations()
 * A getDateActivation()
 * A setDateActivation()
 * A getDateBirth()
 * A setDateBirth()
 * A getDateRegistration()
 * A setDateRegistration()
 * A getDateStatusChanged()
 * A setDateStatusChanged()
 * A getEmail()
 * A setEmail()
 * A getFileAvatar()
 * A setFileAvatar()
 * A get_files_of_members()
 * A set_files_of_members()
 * A getId()
 * A setId()
 * A getKeyActivation()
 * A setKeyActivation()
 * A getLanguage()
 * A setLanguage()
 * A get_members_address()
 * A set_members_address()
 * A get_member_localizations()
 * A set_member_localizations()
 * A get_members_of_groups()
 * A set_members_of_groups()
 * A getNameFirst()
 * A setNameFirst()
 * A getNameLast()
 * A set_name last()
 * A getPassword()
 * A setPassword()
 * A get_redeemed_coupons()
 * A set_redeemed_coupons()
 * A getSite()
 * A setSite()
 * A getStatus()
 * A setStatus()
 * A getUsername()
 * A setUsername()
 *
 */