<?php
/**
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagemetBundle
 * @subpackage	Services
 * @name	    MemberManagementModel
 *
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. www.biberltd.com (C) 2015
 *
 * @version     1.4.2
 * @date        25.05.2015
 *
 */

namespace BiberLtd\Bundle\MemberManagementBundle\Services;

/** CoreModel */
use BiberLtd\Bundle\CoreBundle\CoreModel;
/** Entities to be used */
use BiberLtd\Bundle\MemberManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Entity as MLSEntity;
/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Services as MLSService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MemberManagementModel extends CoreModel {
    /**
     * @name            __construct()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.4.0
     *
     * @param           object          $kernel
     * @param           string          $dbConnection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $dbConnection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $dbConnection, $orm);
        $this->entity = array(
            'm'			=> array('name' => 'MemberManagementBundle:Member', 'alias' => 'm'),
            'ml' 		=> array('name' => 'MemberManagementBundle:MemberLocalization', 'alias' => 'ml'),
            'mog' 		=> array('name' => 'MemberManagementBundle:MembersOfGroup', 'alias' => 'mog'),
            'mos' 		=> array('name' => 'MemberManagementBundle:MembersOfSite', 'alias' => 'mos'),
            'mg' 		=> array('name' => 'MemberManagementBundle:MemberGroup', 'alias' => 'mg'),
            'mgl' 		=> array('name' => 'MemberManagementBundle:MemberGroupLocalization', 'alias' => 'mgl'),
            'l' 		=> array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            's' 		=> array('name' => 'SiteManagementBundle:Site', 'alias' => 's'),
        );
    }

    /**
     * @name            __destruct()
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @name 			activateMember()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member             Member entity, id, username, or, email.
     * @param           string          $key                Activation key.
     * @param           \DateTime       $activationDate     Date of activation.
     * @param           bool            $bypass             If set to true, it bypasses key check.
     *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function activateMember($member, $key = null, $activationDate = null, $bypass = false) {
        $timeStamp = time();
        if ($activationDate == null) {
			$activationDate = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
        }
		else {
            if (!$activationDate instanceof \DateTime) {
				return $this->createException('InvalidDateTimeException', '$activationDate', 'E:S:006');
            }
        }
        /**
         * $key is required if $bypass is set to false
         */
        if (!$bypass && is_null($key)) {
			return new ModelResponse(null, 0, 0, null, true, 'E:SEC:001', 'Activation key is missing. The account cannot be activated.', $timeStamp, time());
        }
        if ($member instanceof BundleEntity\Member) {
			/**
			 * !! IMPORTANT:
			 * Use bypass = true only in MANAGE/ADMIN controller.
			 */
			if ($bypass) {
				$member->setStatus('a');
				$member->setDateActivation($activationDate);
				$member->setDateStatusChanged($activationDate);
				$member->setKeyActivation(null);
			}
		}
		else{
			$response = $this->getMember($member);
			if ($response->error->exist) {
				return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'A member with the given id / username / email cannot be found in our database.', $timeStamp, time());
			}
			$member = $response->result->set;
		}
        $member->setStatus = 'a';
        $member->setDateActivation($activationDate);
        $member->setDateStatusChanged($activationDate);
        $member->setKeyActivation(null);

        $this->em->persists($member);

        $this->em->flush();
		return new ModelResponse($member, 1, 1, null, false, 'S:SEC:001', 'The account has been successfully activated.', $timeStamp, time());
    }
	/**
	 * @name 			addGroupToMembers()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->getMember()
	 * @use             $this->getGroup()
	 * @use             $this->isMemberOfGroup()
	 * @use             $this->createException()
	 *
	 * @param           mixed           $group
	 * @param           array           $members
	 *
	 * @return          array           $response
	 */
	public function addGroupToMembers($group, $members) {
		$timeStamp = time();
		$response = $this->getGroup($group);
		if($response->error->exist){
			return $response;
		}
		$group = $response->result->set;
		if (!is_array($members)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. $groups parameter must be an array collection', 'E:S:001');
		}
		$to_add = array();
		foreach ($members as $member) {
			$response = $this->getMember($member);
			if($response->error->exist){
				break;
			}
			$member = $response->result->set;
			if (!$this->isMemberOfGroup($member, $group, true)) {
				$to_add[] = $group;
			}
		}
		$now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
		$insertedItems = array();
		foreach ($to_add as $group) {
			$entity = new BundleEntity\MembersOfGroup();
			$entity->setMember($member)->setGroup($group)->setDateAdded($now);
			/**
			 * Increment count_members of MemberGroup
			 */
			$group->incrementMemberCount(1);
			$this->em->persist($entity);
			$this->em->persist($group);
			$insertedItems[] = $entity;
		}
		$countInserts = count($to_add);
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			addMemberToGroups()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->getMember()
	 * @use             $this->getGroup()
	 * @use             $this->isMemberOfGroup()
	 * @use             $this->createException()
	 *
	 * @param           mixed           $member
	 * @param           array           $groups
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function addMemberToGroups($member, $groups) {
		$timeStamp = time();
		$response = $this->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
		if (!is_array($groups)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. $groups parameter must be an array collection', 'E:S:001');
		}
		$toAdd = array();
		foreach ($groups as $group) {
			$response = $this->getGroup($group);
			if($response->error->exist){
				break;
			}
			$group = $response->result->set;
			if (!$this->isMemberOfGroup($member, $group, true)) {
				$toAdd[] = $group;
			}
		}
		$now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
		$insertedItems = array();
		foreach ($toAdd as $group) {
			$entity = new BundleEntity\MembersOfGroup();
			$entity->setMember($member)->setGroup($group)->setDateAdded($now);
			/**
			 * Increment count_members of MemberGroup
			 */
			$group->incrementMemberCount(1);
			$this->em->persist($entity);
			$this->em->persist($group);
			$insertedItems[] = $entity;
		}
		$countInserts = count($toAdd);
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 		checkMemberPassword()
	 *
	 * @since   	1.3.4
	 * @version 	1.4.2
	 *
	 * @use 		$this->getMember()
	 *
	 * @param   	mixed 		$member
	 * @param   	string 		$password
	 *
	 * @return 		BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function checkMemberPassword($member, $password, $bypass = false){
		$timeStamp = time();
		if(!is_string($password)){
			return $this->createException('InvalidParameterValueException', 'Password must be a string.', 'E:S:007');
		}
		$response = $this->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
		/** We will need the encryption service to encrypt password. */
		$enc = $this->kernel->getContainer()->get('encryption');
		$password = $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

		$correct = false;
		if($member->getPasword() === $password){
			$correct = true;
		}

		if($bypass){
			return $correct;
		}
		return new ModelResponse($correct, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name 			countMembers()
     *
     * @since			1.2.6
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function countMembers($filter = null) {
        $timeStamp = time();

        $wStr = $fStr = '';

		$qStr = 'SELECT COUNT('. $this->entity['m']['alias'].')'
                .' FROM '.$this->entity['ml']['name'].' '.$this->entity['ml']['alias']
                .' JOIN '.$this->entity['ml']['alias'].'.member '.$this->entity['m']['alias'];

        if ($filter != null) {
            $fStr = $this->prepareWhere($filter);
			$wStr .= ' WHERE ' . $fStr;
        }
		$qStr .= $wStr;

        $q = $this->em->createQuery($qStr);

        $result = $q->getSingleScalarResult();

		return new ModelResponse($result, 1, 0, null, false, 'S:D:005', 'Entries have been successfully counted.', $timeStamp, time());
    }
    /**
     * @name 			countMembersOfGroup()
     *
     * @since			1.2.7
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $group              object, integer, or string
     * @param           array           $filter             Multi-dimensional array
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function countMembersOfGroup($group, $filter = null) {
        $timeStamp = time();
        $response = $this->getGroup($group);

		if($response->error->exist){
			return $response;
		}
		$group = $response->result->set;
        $wStr = $fStr = '';

		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['mog']['alias'] . '.group', 'comparison' => '=', 'value' => $group->getId()),
				)
			)
		);

		$qStr = 'SELECT COUNT('. $this->entity['m']['alias'].')'
                .' FROM '.$this->entity['mog']['name'].' '.$this->entity['mog']['alias']
                .' JOIN '.$this->entity['mog']['alias'].'.member '.$this->entity['m']['alias'];

        if ($filter != null) {
			$fStr = $this->prepare_where($filter);
			$wStr .= ' WHERE ' . $fStr;
        }

		$qStr .= $wStr;

        $q = $this->em->createQuery($qStr);
        $result = $q->getSingleScalarResult();

		return new ModelResponse($result, 1, 0, null, false, 'S:D:005', 'Entries have been successfully counted.', $timeStamp, time());
	}
    /**
     * @name 			deactivateMember()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member
     * @param           bool            $bypass         if set to true it returns bool instead of response
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deactivateMember($member, $bypass = false) {
		$timeStamp = time();
        $now = new DateTime('now', new DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));

		$response = $this->getMember($member);
		if($response->error->exist){
			if($bypass){
				return false;
			}
			return $response;
		}
        $member->setStatus = 'i';
        $member->setDateStatusChanged($now);

        $this->em->persists($member);

        $this->em->flush();
		if($bypass){
			return true;
		}
		return new ModelResponse($member, 1, 1, null, false, 'S:SEC:002', 'The account has been successfully deactivated.', $timeStamp, time());
    }
	/**
	 * @name 			deleteMember()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->deleteMembers()
	 *
	 * @param           mixed           $member
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteMember($member) {
		return $this->deleteMembers(array($member));
	}
    /**
     * @name 			deleteMembers()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection
	 *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function deleteMembers($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\Member){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getMember($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
	}

	/**
	 * @name 			deleteMemberGroup()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->deleteMemberGroups()
	 *
	 * @param           mixed           $group
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteMemberGroup($group) {
		return $this->deleteMemberGroups(array($group));
	}

    /**
     * @name 			deleteMemberGroups()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function deleteMemberGroups($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\MemberGroup){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getGroup($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
	}
	/**
	 * @name 			doesGroupExist()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->getGroup()
	 *
	 * @param           mixed           $group
	 * @param           bool            $bypass         If set to true does not return response but only the result.
	 *
	 * @return          mixed           $response
	 */
	public function doesGroupExist($group, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getGroup($group);

		if ($response->error->exists) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}
		$exist = true;
		if ($bypass) {
			return $exist;
		}

		return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			doesMemberExist()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->getGroup()
	 *
	 * @param           mixed           $member
	 * @param           bool            $bypass         If set to true does not return response but only the result.
	 *
	 * @return          mixed           $response
	 */
	public function doesMemberExist($member, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getMember($member);

		if ($response->error->exists) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}
		$exist = true;
		if ($bypass) {
			return $exist;
		}

		return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			getGroup()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @param           mixed           $group
	 *
	 * @return          mixed           $response
	 */
	public function getGroup($group) {
		$timeStamp = time();
		if($group instanceof BundleEntity\MemberGroup){
			return new ModelResponse($group, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
		}
		$result = null;
		switch($group){
			case is_numeric($group):
				$result = $this->em->getRepository($this->entity['mg']['name'])->findOneBy(array('id' => $group));
				break;
			case is_string($group):
				$result = $this->em->getRepository($this->entity['mg']['name'])->findOneBy(array('code' => $group));
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			getMember()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @param           mixed           $member
	 *
	 * @return          mixed           $response
	 */
	public function getMember($member) {
		$timeStamp = time();
		if($member instanceof BundleEntity\Member){
			return new ModelResponse($member, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
		}
		$result = null;
		switch($member){
			case is_numeric($member):
				$result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('id' => $member));
				break;
			case is_string($member):
				$result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('username' => $member));
				if(is_null($result)){
					$result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('email' => $member));
				}
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			insertGroup()
	 *
	 * @since			1.4.1
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->insertMemberGroup()
	 *
	 * @param           mixed           $group
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertGroup($group) {
		return $this->insertMemberGroup($group);
	}
	/**
	 * @name 			insertGroups()
	 *
	 * @since			1.4.1
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->insertMemberGroups()
	 *
	 * @param           mixed           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertGroups($collection) {
		return $this->insertMemberGroups($collection);
	}
	/**
	 * @name 			insertMember()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->insertMembers()
	 *
	 * @param           mixed           $member
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMember($member) {
		return $this->insertMembers(array($member));
	}
	/**
	 * @name 			insertMembers()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMembers($collection)	{
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$countLocalizations = 0;
		$countGroups = 0;
		$insertedItems = array();
		$localizations = array();
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\Member) {
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if (is_object($data)) {
				$entity = new BundleEntity\Member;
				if(!property_exists($data, 'date_registration')){
					$data->date_registration = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
				}
				if(!property_exists($data, 'date_status_changed')){
					$data->date_status_changed = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
				}
				if(!property_exists($data, 'language')){
					$data->language = 1;
				}
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				if(isset($data->status) && $data->status == 'a' && !isset($data->date_activation)){
					$data->date_activation = $data->date_registration;
				}
				foreach ($data as $column => $value) {
					$localeSet = false;
					$groupSet = false;
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							$localizations[$countInserts]['localizations'] = $value;
							$localeSet = true;
							$countLocalizations++;
							break;
						case 'language':
							$lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
							$response = $lModel->getLanguage($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response, $sModel);
							break;
						case 'groups':
							$groups[$countInserts]['groups'] = $value;
							$groupSet = true;
							$countGroups++;
							break;
						case 'password':
							/** We will need the encryption service to encrypt password. */
							$enc = $this->kernel->getContainer()->get('encryption');
							$password = $enc->input($value)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();
							$entity->$set($password);
							break;
						default:
							$entity->$set($value);
							break;
					}
					if ($localeSet) {
						$localizations[$countInserts]['entity'] = $entity;
					}
					if($groupSet){
						$groups[$countInserts]['entity'] = $entity;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;

				$countInserts++;
			}
		}
		if ($countInserts > 0) {
			$this->em->flush();
		}
		/** Now handle localizations */
		if ($countInserts > 0 && $countLocalizations > 0) {
			$response = $this->insertMemberLocalizations($localizations);
		}
		if($countInserts > 0 && $countGroups > 0){
			foreach($groups as $group){
				$response =$this->addMemberToGroups($group['entity'], $group['groups']);
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			insertMemberGroup()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->insertMemberGroups()
	 *
	 * @param           mixed           $group
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMemberGroup($group) {
		return $this->insertMemberGroups(array($group));
	}
	/**
	 * @name 			insertMemberGroups()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMemberGroups($collection)	{
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$countLocalizations = 0;
		$insertedItems = array();
		$localizations = array();
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\MemberGroup) {
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if (is_object($data)) {
				$entity = new BundleEntity\MemberGroup;
				if(!property_exists($data, 'date_added')){
					$data->date_added = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
				}
				if(!property_exists($data, 'date_updated')){
					$data->date_updated = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
				}
				if(!property_exists($data, 'count_members')){
					$data->count_members = 0;
				}
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				foreach ($data as $column => $value) {
					$localeSet = false;
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							$localizations[$countInserts]['localizations'] = $value;
							$localeSet = true;
							$countLocalizations++;
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if (!$response->error->exist) {
								$entity->$set($response->result->set);
							} else {
								return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "'.$value.'" does not exist in database.', 'E:D:002');
							}
							unset($response, $sModel);
							break;
						default:
							$entity->$set($value);
							break;
					}
					if ($localeSet) {
						$localizations[$countInserts]['entity'] = $entity;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;

				$countInserts++;
			}
		}
		if ($countInserts > 0) {
			$this->em->flush();
		}
		/** Now handle localizations */
		if ($countInserts > 0 && $countLocalizations > 0) {
			$response = $this->insertMemberGroupLocalizations($localizations);
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			insertMemberLocalizations()
	 *
	 * @since			1.2.9
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection        Collection of entities or post data.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMemberLocalizations($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$insertedItems = array();
		foreach($collection as $data){
			if($data instanceof BundleEntity\MemberLocalization){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if(is_object($data)){
				$entity = new BundleEntity\MemberLocalization();
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'language':
							$lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
							$response = $lModel->getLanguage($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						case 'member':
							$response = $this->getMember($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						default:
							$entity->$set($value);
							break;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			insertMemberGroupLocalizations()
	 *  				Inserts one or more member localizations into database.
	 *
	 * @since			1.3.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection        Collection of entities or post data.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertMemberGroupLocalizations($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$insertedItems = array();
		foreach($collection as $data){
			if($data instanceof BundleEntity\MemberLocalization){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if(is_object($data)){
				$entity = new BundleEntity\MemberLocalization();
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'language':
							$lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
							$response = $lModel->getLanguage($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						case 'group':
							$response = $this->getGroup($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						default:
							$entity->$set($value);
							break;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			isMemberOfGroup()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           mixed           $member
	 * @param           mixed           $group
	 * @param           bool            $bypass                 if set to true returns the result directly.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function isMemberOfGroup($member, $group, $bypass = false) {
		$timeStamp = time();
		$response = $this->getGroup($group);
		if($response->error->exist){
			return $response;
		}
		$group = $response->result->set;
		$response = $this->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
		$qStr = 'SELECT '.$this->entity['mog']['alias']
			. ' FROM '.$this->entity['mog']['name'].' '.$this->entity['mog']['alias']
			. ' WHERE '.$this->entity['mog']['alias'].'.group = '.$group->getId()
			. ' AND '.$this->entity['mog']['alias'].'.member = '.$member->getId();

		$q = $this->em->createQuery($qStr);

		$result = $q->getResult();

		$exist = false;
		if (count($result) > 0) {
			$exist = true;
		}
		if ($bypass) {
			return $exist;
		}
		return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			listGroups()
	 *
	 * @since			1.4.1
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $filter
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listGroups($filter = null, $sortOrder = null, $limit = null) {
		return $this->listMemberGroups($filter, $sortOrder, $limit);
	}
    /**
     * @name 			listGroupsOfMember()
     *
     * @since			1.2.4
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member
     * @param           array           $sortOrder
     * @param           array           $limit
	 *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listGroupsOfMember($member, $sortOrder = null, $limit = null){
        $timeStamp = time();
        $response = $this->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;

        $qStr = 'SELECT ' . $this->entity['mog']['alias'] . ', ' . $this->entity['mg']['alias']
                . ' FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
                . ' JOIN ' . $this->entity['mog']['alias'] . '.mg ' . $this->entity['mg']['alias']
                . ' WHERE ' . $this->entity['mog']['alias'] . '.m = ' . $member->getId();

        $oStr = '';
        if ($sortOrder != null) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'code':
                    case 'date_added':
                    case 'date_updated':
                    case 'id':
                        $column = $this->entity['mg']['alias'] . '.' . $column;
                        break;
                    default:
                        $column = $this->entity['mog']['alias'] . '.' . $column;
                        break;
                }
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
            }
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
        }

		$qStr .= $oStr;

        $q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);

        $result = $q->getResult();
		$groups = array();
		foreach ($result as $mog) {
			$groups[] = $mog->getGroup();
		}
		$totalRows = count($groups);

		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($groups, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			listMemberGroups()
	 *
	 * @since			1.3.8
	 * @version         1.4.1
	 * @author          Can Berkol
	 * @author          Said İmamoğlu
	 *
	 * @use             $this->createException()
	 *
	 * @param   		array   $filter
	 * @param   		array   $sortOrder
	 * @param   		array   $limit
	 *
	 * @return   		BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listMemberGroups($filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
		if(!is_array($sortOrder) && !is_null($sortOrder)){
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
		}
		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['mg']['alias'].', '.$this->entity['mg']['alias']
			.' FROM '.$this->entity['mgl']['name'].' '.$this->entity['mgl']['alias']
			.' JOIN '.$this->entity['mgl']['alias'].'.group '.$this->entity['mg']['alias'];

		if(!is_null($sortOrder)){
			foreach($sortOrder as $column => $direction){
				switch($column){
					case 'id':
					case 'code':
					case 'date_added':
					case 'date_removed':
					case 'date_updated':
					case 'count_members':
						$column = $this->entity['mg']['alias'].'.'.$column;
						break;
					case 'name':
					case 'url_key':
						$column = $this->entity['mgl']['alias'].'.'.$column;
						break;
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
		}

		if(!is_null($filter)){
			$fStr = $this->prepareWhere($filter);
			$wStr .= ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;
		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);

		$result = $q->getResult();

		$entities = array();
		foreach($result as $entry){
			$id = $entry->getMember()->getId();
			if(!isset($unique[$id])){
				$entities[] = $entry->getAction();
			}
		}
		$totalRows = count($entities);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name 			listMembers()
     *
     * @since			1.3.8
	 * @version         1.4.1
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param   		array   $filter
     * @param   		array   $sortOrder
     * @param   		array   $limit
     *
     * @return   		BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function listMembers($filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
		if(!is_array($sortOrder) && !is_null($sortOrder)){
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
		}
		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['m']['alias'].', '.$this->entity['m']['alias']
			.' FROM '.$this->entity['ml']['name'].' '.$this->entity['ml']['alias']
			.' JOIN '.$this->entity['ml']['alias'].'.member '.$this->entity['m']['alias'];

		if(!is_null($sortOrder)){
			foreach($sortOrder as $column => $direction){
				switch($column){
					case 'id':
					case 'name_first':
					case 'name_last':
					case 'email':
					case 'username':
					case 'date_birth':
					case 'date_activation':
					case 'date_last_login':
					case 'date_status_changed':
					case 'status':
					case 'gender':
						$column = $this->entity['m']['alias'].'.'.$column;
						break;
					case 'title':
						$column = $this->entity['ml']['alias'].'.'.$column;
						break;
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
		}

		if(!is_null($filter)){
			$fStr = $this->prepareWhere($filter);
			$wStr .= ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;
		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);

		$result = $q->getResult();

		$entities = array();
		foreach($result as $entry){
			$id = $entry->getMember()->getId();
			if(!isset($unique[$id])){
				$entities[] = $entry->getMember();
			}
		}
		$totalRows = count($entities);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}

    /**
     * @name 			listMembersOfGroup()
     *
     * @since			1.3.3
     * @version         1.4.1
     *
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           mixed		$group
     * @param           array		$filter
     * @param           array		$sortOrder
     * @param           array 		$limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMembersOfGroup($group, $filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
        $response = $this->getGroup($group);
		if($response->error->exist){
			return $response;
		}
		$group = $response->result->set;
        $qStr = 'SELECT '.$this->entity['mog']['alias'].' FROM '.$this->entity['mog']['name'].' '.$this->entity['mog']['alias']
                .' WHERE '.$this->entity['mog']['alias'].'.group = '.$group->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();

        $memberIds = array();
        foreach($result as $item){
            $memberIds[] = $item->getMember()->getId();
        }

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['m']['alias'].'.id', 'comparison' => 'in', 'value' => $memberIds),
                )
            )
        );

        $response = $this->listMembers($filter, $sortOrder, $limit);
		if($response->error->exist){
			return $response;
		}
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
    }

    /**
     * @name 			listMembersOfSite()
     *
     * @since			1.3.8
     * @version         1.4.1
     *
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           integer         $site
     * @param           array           $filter
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMembersOfSite($site, $filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
		$response = $this->getGroup($site);
		if($response->error->exist){
			return $response;
		}
		$site = $response->result->set;
		$qStr = 'SELECT '.$this->entity['mos']['alias'].' FROM '.$this->entity['mos']['name'].' '.$this->entity['mos']['alias']
			.' WHERE '.$this->entity['mos']['alias'].'.group = '.$site->getId();

		$q = $this->em->createQuery($qStr);

		$result = $q->getResult();

		$memberIds = array();
		foreach($result as $item){
			$memberIds[] = $item->getMember()->getId();
		}

		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['m']['alias'].'.id', 'comparison' => 'in', 'value' => $memberIds),
				)
			)
		);

		$response = $this->listMembers($filter, $sortOrder, $limit);
		if($response->error->exist){
			return $response;
		}
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
    }

    /**
     * @name 			listRegularMemberGroups()
     *
     * @since			1.3.1
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->listMemberGroups()
     *
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listRegularMemberGroups($sortOrder = null, $limit = null) {
        $column = $this->entity['mg']['alias'] . '.type';
        $condition = array('column' => $column, 'comparison' => 'eq', 'value' => 'r');
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        return $this->listMemberGroups($filter, $sortOrder, $limit);
    }
	/**
	 * @name 			removeMemberFromOtherGroups()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->doesMemberGroupExist()
	 * @use             $this->createException()
	 *
	 * @param           mixed           $member                 Member Entity, id, username, email.
	 * @param           array           $groups                 MemberGroup Entities, ids, code.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function removeMemberFromOtherGroups($member, $groups) {
		$timeStamp = time();
		$response = $this->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
        $idsToRemove = array();
        foreach ($groups as $group) {
			$response = $this->getGroup($group);
			if($response->error->exist){
				return $response;
			}
			$idsToRemove[] = $response->result->set->getId();
		}
        $notIn = 'NOT IN (' . implode(',', $idsToRemove) . ')';
        $qStr = 'DELETE FROM '.$this->entity['mog']['name'].' '.$this->entity['mog']['alias']
					.' WHERE '.$this->entity['mog']['alias'].'.member '.$member->getId()
					.' AND '.$this->entity['mog']['alias'].'.group '.$notIn;

        $q = $this->em->createQuery($qStr);
        $result = $q->getResult();

        $deleted = true;
        if (!$result) {
			$deleted = false;
		}
        if ($deleted) {
			return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
    }

	/**
	 * @name 			updateGroup()
	 *
	 * @since			1.4.1
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->updateMemberGroups()
	 *
	 * @param           array           $group
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateGroup($group) {
		return $this->updateMemberGroups(array($group));
	}
	/**
	 * @name 			updateGroups()
	 *
	 * @since			1.4.1
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->updateMemberGroups()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateGroups($collection) {
		return $this->updateMemberGroups($collection);
	}
	/**
	 * @name 			updateMember()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->updateMembers()
	 *
	 * @param           array           $member
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateMember($member) {
		return $this->updateMembers(array($member));
	}
    /**
     * @name 			updateMembers()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function updateMembers($collection){
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countUpdates = 0;
		$updatedItems = array();
		$localizations = array();
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\Member) {
				$entity = $data;
				$this->em->persist($entity);
				$updatedItems[] = $entity;
				$countUpdates++;
			}
			else if (is_object($data)) {
				if(!property_exists($data, 'id') || !is_numeric($data->id)){
					return $this->createException('InvalidParameterException', 'Parameter must be an object with the "id" property and id property ​must have an integer value.', 'E:S:003');
				}
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				if(!property_exists($data, 'language')){
					$data->language = 1;
				}
				$response = $this->getMember($data->id);
				if ($response->error->exist) {
					return $this->createException('EntityDoesNotExist', 'Member with id / username / email '.$data->id.' does not exist in database.', 'E:D:002');
				}
				$oldEntity = $response->result->set;
				foreach ($data as $column => $value) {
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							foreach ($value as $langCode => $translation) {
								$localization = $oldEntity->getLocalization($langCode, true);
								$newLocalization = false;
								if (!$localization) {
									$newLocalization = true;
									$localization = new BundleEntity\MemberLocalization();
									$mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
									$response = $mlsModel->getLanguage($langCode);
									$localization->setLanguage($response->result->set);
									$localization->setMember($oldEntity);
								}
								foreach ($translation as $transCol => $transVal) {
									$transSet = 'set' . $this->translateColumnName($transCol);
									$localization->$transSet($transVal);
								}
								if ($newLocalization) {
									$this->em->persist($localization);
								}
								$localizations[] = $localization;
							}
							$oldEntity->setLocalizations($localizations);
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if (!$response->error->exist) {
								$oldEntity->$set($response->result->set);
							} else {
								return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "'.$value.'" does not exist in database.', 'E:D:002');
							}
							unset($response, $sModel);
							break;
						case 'id':
							break;
						default:
							$oldEntity->$set($value);
							break;
					}
					if ($oldEntity->isModified()) {
						$this->em->persist($oldEntity);
						$countUpdates++;
						$updatedItems[] = $oldEntity;
					}
				}
			}
		}
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}

	/**
	 * @name 			updateMemberGroup()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->updateMemberGroups()
	 *
	 * @param           array           $group
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateMemberGroup($group) {
		return $this->updateMemberGroups(array($group));
	}

    /**
     * @name 			updateMemberGroups()
     *
     * @since			1.0.0
     * @version         1.4.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function updateMemberGroups($collection){
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countUpdates = 0;
		$updatedItems = array();
		$localizations = array();
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\MemberGroup) {
				$entity = $data;
				$this->em->persist($entity);
				$updatedItems[] = $entity;
				$countUpdates++;
			}
			else if (is_object($data)) {
				if(!property_exists($data, 'id') || !is_numeric($data->id)){
					return $this->createException('InvalidParameterException', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
				}
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				$response = $this->getGroup($data->id);
				if ($response->error->exist) {
					return $this->createException('EntityDoesNotExist', 'Group with id / code '.$data->id.' does not exist in database.', 'E:D:002');
				}
				$oldEntity = $response->result->set;
				foreach ($data as $column => $value) {
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							foreach ($value as $langCode => $translation) {
								$localization = $oldEntity->getLocalization($langCode, true);
								$newLocalization = false;
								if (!$localization) {
									$newLocalization = true;
									$localization = new BundleEntity\MemberGroupLocalization();
									$mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
									$response = $mlsModel->getLanguage($langCode);
									$localization->setLanguage($response->result->set);
									$localization->setGroup($oldEntity);
								}
								foreach ($translation as $transCol => $transVal) {
									$transSet = 'set' . $this->translateColumnName($transCol);
									$localization->$transSet($transVal);
								}
								if ($newLocalization) {
									$this->em->persist($localization);
								}
								$localizations[] = $localization;
							}
							$oldEntity->setLocalizations($localizations);
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if (!$response->error->exist) {
								$oldEntity->$set($response->result->set);
							} else {
								return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "'.$value.'" does not exist in database.', 'E:D:002');
							}
							unset($response, $sModel);
							break;
						case 'id':
							break;
						default:
							$oldEntity->$set($value);
							break;
					}
					if ($oldEntity->isModified()) {
						$this->em->persist($oldEntity);
						$countUpdates++;
						$updatedItems[] = $oldEntity;
					}
				}
			}
		}
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}

	/**
	 * @name 			validateAccount()
	 *
	 * @since			1.0.0
	 * @version         1.4.1
	 * @author          Can Berkol
	 *
	 * @use             $this->getMember()
	 * @use             $this->createException()
	 *
	 * @param           string          $username
	 * @param           string          $password
	 *
	 * @return          array           $response
	 */
	public function validateAccount($username, $password) {
		$timeStamp = time();
		$response = $this->getMember($username);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
		$enc = $this->kernel->getContainer()->get('encryption');

		$hashedPass= $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

		if ($member->getPassword() != $hashedPass) {
			return new ModelResponse(null, 0, 0, null, true, 'E:SEC:002', 'Invalid credentials. The user cannot be logged in.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:SEC:003', 'The user has been successfully logged in.', $timeStamp, time());
	}
}
/**
 * Change Log
 * **************************************
 * v1.4.2                      25.05.2015
 * Can Berkol
 * **************************************
 * BF :: db_connection is replaced with dbConnection
 *
 * **************************************
 * v1.4.1                      03.05.2015
 * Can Berkol
 * **************************************
 * CR :: Made compatible with CoreBundle v3.3.
 *
 * **************************************
 * v1.3.9                      Can Berkol
 * 30.04.2015
 * **************************************
 * CR :: Fixes based on entity changes.
 *
 * **************************************
 * v1.3.8                      Can Berkol
 * 23.03.2015
 * **************************************
 * A listMembersOfSite()
 *
 * **************************************
 * v1.3.7                      Can Berkol
 * 15.08.2014
 * **************************************
 * U updateMembers()
 *
 * **************************************
 * v1.3.6                   Said İmamoğlu
 * 07.07.2014
 * **************************************
 * A validateAndGetMember()
 *
 * **************************************
 * v1.3.5                   Said İmamoğlu
 * 27.06.2014
 * **************************************
 * U listMembers()
 *
 * **************************************
 * v1.3.4                      Can Berkol
 * 05.06.2014
 * **************************************
 * A checkMemberPassword()
 *
 * **************************************
 * v1.3.3                      Can Berkol
 * 25.05.2014
 * **************************************
 * D listMembersOfGroupByGroup()
 *
 * **************************************
 * v1.3.2                   Said İmamoğlu
 * 22.04.2014
 * **************************************
 * A listMembersOfGroupByGroup()
 * A listMemberOfGroup()
 * U listMembersOfGroup()
 *
 * **************************************
 * v1.3.1                      Can Berkol
 * 22.04.2014
 * **************************************
 * A listRegularMaemberGroups()
 *
 * **************************************
 * v1.3.0                      Can Berkol
 * 19.02.2014
 * **************************************
 * A insertMemberGroupLocalizations()
 * A updateMembers()
 * U insertMemberGroups()
 * U updateMemberGroups()
 *
 * **************************************
 * v1.2.9                      Can Berkol
 * 19.02.2014
 * **************************************
 * A insertMemberLocalizations()
 * U insertMembers()
 *
 * **************************************
 * v1.2.8                      Can Berkol
 * 12.02.2014
 * **************************************
 * B countMembersOfGroup()
 *
 * **************************************
 * v1.2.7                      Can Berkol
 * 17.01.2014
 * **************************************
 * A countMembersOfGroup()
 *
 * **************************************
 * v1.2.6                      Can Berkol
 * 08.01.2014
 * **************************************
 * A countMembers()
 *
 * **************************************
 * v1.2.5                      Can Berkol
 * 01.01.2014
 * **************************************
 * B insertMemberGroups()
 * B insertMembers()
 * B updateMemberGroups()
 * B updateMembers()
 *
 * **************************************
 * v1.2.4                      Can Berkol
 * 31.12.2013
 * **************************************
 * A listGroupsOfMember()
 *
 * **************************************
 * v1.2.3                      Can Berkol
 * 16.12.2013
 * **************************************
 * A listMembersOfGroup()
 * U getGroup()
 * U listMemberGroups()
 *
 * **************************************
 * v1.2.2                      Can Berkol
 * 16.11.2013
 * **************************************
 * A getMemberLocalization()
 * A getGroupLocalization()
 * M Methods are now camelCase.
 *
 * **************************************
 * v1.2.1                      Can Berkol
 * 06.11.2013
 * **************************************
 * M Response messages modified.
 *
 * **************************************
 * v1.2.0                      Can Berkol
 * 08.09.2013
 * **************************************
 * M Extends CoreModel
 * R resetResponse()
 * U __construct()
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 16.08.2013
 * **************************************
 * B list_members() Null filter query  bug fixed.
 * B list_member_goups() Null filter query  bug fixed.
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 11.08.2013
 * **************************************
 * U validate_account() Now supports validation with email.
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 09.08.2013
 * **************************************
 * A add_group_to_members()
 * A add_member_to_groups()
 * A is_member_of_group()
 * A remove_member_from_other_groups()
 * U insert_members()
 * U insert_member_groups()
 * U update_members()
 * U update_member_groups()
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 05.08.2013
 * **************************************
 * A __construct()
 * A __destruct()
 * A delete_member()
 * A delete_member_group()
 * A delete_members()
 * A delete_member_groups()
 * A does_member_exist()
 * A does_member_group_exist()
 * A getMember()
 * A getGroup()
 * A insert_member()
 * A insert_member_groups()
 * A list_members()
 * A list_member_groups()
 * A update_member()
 * A update_member_group()
 * A update_members()
 * A update_member_groups()
 */