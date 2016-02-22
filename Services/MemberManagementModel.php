<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        15.01.2016
 */
namespace BiberLtd\Bundle\MemberManagementBundle\Services;

use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\MemberManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Entity as MLSEntity;
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Services as MLSService;
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MemberManagementModel extends CoreModel
{
    /**
     * MemberManagementModel constructor.
     *
     * @param object      $kernel
     * @param string|null $dbConnection
     * @param string|null $orm
     */
    public function __construct($kernel, string $dbConnection = null, string $orm = null)
    {
        parent::__construct($kernel, $dbConnection ?? 'default', $orm ?? 'doctrine');
        $this->entity = array(
            'f' => array('name' => 'FileManagementBundle:File', 'alias' => 'f'),
            'fom' => array('name' => 'MemberManagementBundle:FilesOfMember', 'alias' => 'fom'),
            'm' => array('name' => 'MemberManagementBundle:Member', 'alias' => 'm'),
            'ml' => array('name' => 'MemberManagementBundle:MemberLocalization', 'alias' => 'ml'),
            'mog' => array('name' => 'MemberManagementBundle:MembersOfGroup', 'alias' => 'mog'),
            'mos' => array('name' => 'MemberManagementBundle:MembersOfSite', 'alias' => 'mos'),
            'mg' => array('name' => 'MemberManagementBundle:MemberGroup', 'alias' => 'mg'),
            'mgl' => array('name' => 'MemberManagementBundle:MemberGroupLocalization', 'alias' => 'mgl'),
            'l' => array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            's' => array('name' => 'SiteManagementBundle:Site', 'alias' => 's'),
        );
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @param mixed $member
     * @param string|null    $key
     * @param \DateTime|null $activationDate
     * @param bool|null      $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function activateMember($member, string $key = null, \DateTime $activationDate = null, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $activationDate = $activationDate ?? new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
        /**
         * $key is required if $bypass is set to false
         */
        if (!$bypass && is_null($key)) {
            return new ModelResponse(null, 0, 0, null, true, 'E:SEC:001', 'Activation key is missing. The account cannot be activated.', $timeStamp, microtime(true));
        }
        if ($member instanceof BundleEntity\Member) {
            if ($bypass) {
                $member->setStatus('a');
                $member->setDateActivation($activationDate);
                $member->setDateStatusChanged($activationDate);
                $member->setKeyActivation(null);
            }
        } else {
            $response = $this->getMember($member);
            if ($response->error->exist) {
                return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'A member with the given id / username / email cannot be found in our database.', $timeStamp, microtime(true));
            }
            $member = $response->result->set;
        }
        $member->setStatus = 'a';
        $member->setDateActivation($activationDate);
        $member->setDateStatusChanged($activationDate);
        $member->setKeyActivation(null);

        $this->em->persists($member);

        $this->em->flush();
        return new ModelResponse($member, 1, 1, null, false, 'S:SEC:001', 'The account has been successfully activated.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     * @param array $members
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function addGroupToMembers($group, array $members)
    {
        $timeStamp = microtime(true);
        $response = $this->getGroup($group);
        if ($response->error->exist) {
            return $response;
        }
        $group = $response->result->set;
        $to_add = [];
        foreach ($members as $member) {
            $response = $this->getMember($member);
            if ($response->error->exist) {
                break;
            }
            $member = $response->result->set;
            if (!$this->isMemberOfGroup($member, $group, true)) {
                $to_add[] = $member;
            }
        }
        $now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
        $insertedItems = [];
        foreach ($to_add as $member) {
            $entity = new BundleEntity\MembersOfGroup();
            $entity->setMember($member)->setGroup($group)->setDateAdded($now);
            /**
             * Increment count_members of MemberGroup
             */
            $group->incrementMemberCount(1);
            $this->em->persist($entity);
            $insertedItems[] = $entity;
        }
        $countInserts = count($to_add);
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param array $groups
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function addMemberToGroups($member, array $groups)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $toAdd = [];
        foreach ($groups as $group) {
            $response = $this->getGroup($group);
            if ($response->error->exist) {
                break;
            }
            $group = $response->result->set;
            if (!$this->isMemberOfGroup($member, $group, true)) {
                $toAdd[] = $group;
            }
        }
        $now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
        $insertedItems = [];
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
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param array $sites
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function addMemberToSites($member, array $sites)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;

        $toAdd = [];
        $sModel = new SMMService\SiteManagementModel($this->kernel, $this->dbConnection, $this->orm);
        foreach ($sites as $site) {
            $response = $sModel->getSite($site);
            if ($response->error->exist) {
                break;
            }
            $site = $response->result->set;
            if (!$this->isMemberOfSite($member, $site, true)) {
                $toAdd[] = $site;
            }
        }
        $now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
        $insertedItems = [];
        foreach ($toAdd as $site) {
            $entity = new BundleEntity\MembersOfSite();
            $entity->setMember($member)->setSite($site)->setDateAdded($now);
            /**
             * Increment count_members of MemberGroup
             */
            $this->em->persist($entity);
            $this->em->persist($site);
            $insertedItems[] = $entity;
        }
        $countInserts = count($toAdd);
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param string    $password
     * @param bool|null $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
     */
    public function checkMemberPassword($member, string $password, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        /** We will need the encryption service to encrypt password. */
        $enc = $this->kernel->getContainer()->get('encryption');
        $password = $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

        $correct = false;
        if ($member->getPasword() === $password) {
            $correct = true;
        }

        if ($bypass) {
            return $correct;
        }
        return new ModelResponse($correct, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param array|null $filter
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function countMembers(array $filter = null)
    {
        $timeStamp = microtime(true);

        $wStr = $fStr = '';

        $qStr = 'SELECT COUNT(' . $this->entity['m']['alias'] . ')'
            . ' FROM ' . $this->entity['ml']['name'] . ' ' . $this->entity['ml']['alias']
            . ' JOIN ' . $this->entity['ml']['alias'] . '.member ' . $this->entity['m']['alias'];

        if ($filter != null) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }
        $qStr .= $wStr;

        $q = $this->em->createQuery($qStr);

        $result = $q->getSingleScalarResult();

        return new ModelResponse($result, 1, 0, null, false, 'S:D:005', 'Entries have been successfully counted.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     * @param array|null $filter
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function countMembersOfGroup($group, array $filter = null)
    {
        $timeStamp = microtime(true);
        $response = $this->getGroup($group);

        if ($response->error->exist) {
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

        $qStr = 'SELECT COUNT(' . $this->entity['m']['alias'] . ')'
            . ' FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
            . ' JOIN ' . $this->entity['mog']['alias'] . '.member ' . $this->entity['m']['alias'];

        if ($filter != null) {
            $fStr = $this->prepare_where($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr;

        $q = $this->em->createQuery($qStr);
        $result = $q->getSingleScalarResult();

        return new ModelResponse($result, 1, 0, null, false, 'S:D:005', 'Entries have been successfully counted.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param bool $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
     */
    public function deactivateMember($member, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $now = new DateTime('now', new DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));

        $response = $this->getMember($member);
        if ($response->error->exist) {
            if ($bypass) {
                return false;
            }
            return $response;
        }
        $member->setStatus = 'i';
        $member->setDateStatusChanged($now);

        $this->em->persists($member);

        $this->em->flush();
        if ($bypass) {
            return true;
        }
        return new ModelResponse($member, 1, 1, null, false, 'S:SEC:002', 'The account has been successfully deactivated.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deleteMember($member)
    {
        return $this->deleteMembers(array($member));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deleteMembers(array $collection)
    {
        $timeStamp = microtime(true);
        $countDeleted = 0;
        foreach ($collection as $entry) {
            if ($entry instanceof BundleEntity\Member) {
                $this->em->remove($entry);
                $countDeleted++;
            } else {
                $response = $this->getMember($entry);
                if (!$response->error->exist) {
                    $entry = $response->result->set;
                    $this->em->remove($entry);
                    $countDeleted++;
                }
            }
        }
        if ($countDeleted < 0) {
            return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, microtime(true));
        }
        $this->em->flush();

        return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, microtime(true));
    }

    /**
     * @param $group
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deleteMemberGroup($group)
    {
        return $this->deleteMemberGroups(array($group));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deleteMemberGroups(array $collection)
    {
        $timeStamp = microtime(true);
        $countDeleted = 0;
        foreach ($collection as $entry) {
            if ($entry instanceof BundleEntity\MemberGroup) {
                $this->em->remove($entry);
                $countDeleted++;
            } else {
                $response = $this->getGroup($entry);
                if (!$response->error->exist) {
                    $entry = $response->result->set;
                    $this->em->remove($entry);
                    $countDeleted++;
                }
            }
        }
        if ($countDeleted < 0) {
            return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, microtime(true));
        }
        $this->em->flush();

        return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     * @param bool|null $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool
     */
    public function doesGroupExist($group, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $exist = false;

        $response = $this->getGroup($group);

        if ($response->error->exist) {
            if ($bypass) {
                return $exist;
            }
            $response->result->set = false;
            return $response;
        }
        $exist = true;
        if ($bypass) {
            return $exist;
        }

        return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param bool|null $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
     */
    public function doesMemberExist($member, bool $bypass = null)
    {
        $bypass = $bypass ?? false;
        $timeStamp = microtime(true);
        $exist = false;

        $response = $this->getMember($member);

        if ($response->error->exist) {
            if ($bypass) {
                return $exist;
            }
            $response->result->set = false;
            return $response;
        }
        $exist = true;
        if ($bypass) {
            return $exist;
        }

        return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getGroup($group)
    {
        $timeStamp = microtime(true);
        if ($group instanceof BundleEntity\MemberGroup) {
            return new ModelResponse($group, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
        }
        $result = null;
        switch ($group) {
            case is_numeric($group):
                $result = $this->em->getRepository($this->entity['mg']['name'])->findOneBy(array('id' => $group));
                break;
            case is_string($group):
                $result = $this->em->getRepository($this->entity['mg']['name'])->findOneBy(array('code' => $group));
                break;
        }
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }

        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function getMember($member)
    {
        $timeStamp = microtime(true);
        if ($member instanceof BundleEntity\Member) {
            return new ModelResponse($member, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
        }
        $result = null;
        switch ($member) {
            case is_numeric($member):
                $response = $this->getMemberById((int) $member);
                if (!$response->error->exist) {
                    return $response;
                }
                break;
            case is_string($member):
                $response = $this->getMemberByEmail($member);
                if (!$response->error->exist) {
                    return $response;
                }
                break;
        }
        $response = $this->getMemberByUsername($member);
        if (!$response->error->exist) {
            return $response;
        }
        return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
    }

    /**
     * @param int $id
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getMemberById(int $id)
    {
        $timeStamp = microtime(true);
        $result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('id' => $id));
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param string $email
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getMemberByEmail(string $email)
    {
        $timeStamp = microtime(true);
        $result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('email' => $email));
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param string $username
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getMemberByUsername(string $username)
    {
        $timeStamp = microtime(true);
        $result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('username' => $username));
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param string $key
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getMemberByKey(string $key)
    {
        $timeStamp = microtime(true);
        $result = $this->em->getRepository($this->entity['m']['name'])->findOneBy(array('key_activation' => $key));
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param m,xed $group
     *
     * @return \BiberLtd\Bundle\MemberManagementBundle\Services\ModelResponse
     */
    public function insertGroup($group)
    {
        return $this->insertMemberGroup($group);
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\MemberManagementBundle\Services\ModelResponse
     */
    public function insertGroups(array $collection)
    {
        return $this->insertMemberGroups($collection);
    }

    /**
     * @param mixed $member
     *
     * @return ModelResponse
     */
    public function insertMember($member)
    {
        return $this->insertMembers(array($member));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertMembers(array $collection)
    {
        $timeStamp = microtime(true);
        $countInserts = 0;
        $countLocalizations = 0;
        $countGroups = 0;
        $countSites = 0;
        $insertedItems = [];
        $localizations = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\Member) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else if (is_object($data)) {
                $entity = new BundleEntity\Member;
                if (!property_exists($data, 'date_registration')) {
                    $data->date_registration = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
                }
                if (!property_exists($data, 'date_status_changed')) {
                    $data->date_status_changed = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
                }
                if (!property_exists($data, 'language')) {
                    $data->language = 1;
                }
                if (!property_exists($data, 'site')) {
                    $data->site = 1;
                }
                if (isset($data->status) && $data->status == 'a' && !isset($data->date_activation)) {
                    $data->date_activation = $data->date_registration;
                }
                $localeSet = false;
                $groupSet = false;
                $siteSet = false;
                foreach ($data as $column => $value) {
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
                            if (!$response->error->exist) {
                                $entity->$set($response->result->set);
                            }
                            unset($response, $lModel);
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value);
                            if (!$response->error->exist) {
                                $entity->$set($response->result->set);
                            }
                            unset($response, $sModel);
                            break;
                        case 'groups':
                            $groups[$countGroups]['groups'] = $value;
                            $groupSet = true;
                            $countGroups++;
                            break;
                        case 'sites':
                            $sites[$countSites]['sites'] = $value;
                            $siteSet = true;
                            $countSites++;
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
                    if ($groupSet) {
                        $groups[$countInserts]['entity'] = $entity;
                    }
                    if ($siteSet) {
                        $sites[$countInserts]['entity'] = $entity;
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
        if ($countInserts > 0 && $countGroups > 0) {
            foreach ($groups as $group) {
                $response = $this->addMemberToGroups($group['entity'], $group['groups']);
            }
        }
        if ($countInserts > 0 && $countSites > 0) {
            foreach ($sites as $site) {
                $response = $this->addMemberToSites($site['entity'], $site['sites']);
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     *
     * @return ModelResponse
     */
    public function insertMemberGroup($group)
    {
        return $this->insertMemberGroups(array($group));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertMemberGroups(array $collection)
    {
        $timeStamp = microtime(true);

        $countInserts = 0;
        $countLocalizations = 0;
        $insertedItems = [];
        $localizations = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\MemberGroup) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else if (is_object($data)) {
                $entity = new BundleEntity\MemberGroup;
                if (!property_exists($data, 'date_added')) {
                    $data->date_added = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
                }
                if (!property_exists($data, 'date_updated')) {
                    $data->date_updated = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
                }
                if (!property_exists($data, 'count_members')) {
                    $data->count_members = 0;
                }
                if (!property_exists($data, 'site')) {
                    $data->site = 1;
                }
                foreach ($data as $column => $value) {
                    $localeSet = false;
                    $set = 'set' . $this->translateColumnName($column);
                    switch ($column) {
                        case 'local':
                            $localizations[$countInserts] = $value;
                            $localeSet = true;
                            $countLocalizations++;
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value);
                            if (!$response->error->exist) {
                                $entity->$set($response->result->set);
                            } else {
                                return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "' . $value . '" does not exist in database.', 'E:D:002');
                            }
                            unset($response, $sModel);
                            break;
                        default:
                            $entity->$set($value);
                            break;
                    }
                    if ($localeSet) {
                        $localizations[$countInserts]->member = $entity;
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
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertMemberLocalizations(array $collection)
    {
        $timeStamp = microtime(true);
        $countInserts = 0;
        $insertedItems = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\MemberLocalization) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                $member = $data['entity'];
                foreach ($data['localizations'] as $locale => $translation) {
                    $entity = new BundleEntity\MemberLocalization();
                    $lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $lModel->getLanguage($locale);
                    if ($response->error->exist) {
                        return $response;
                    }
                    $entity->setLanguage($response->result->set);
                    unset($response);
                    $entity->setMember($member);
                    foreach ($translation as $column => $value) {
                        $set = 'set' . $this->translateColumnName($column);
                        switch ($column) {
                            default:
                                if (is_object($value) || is_array($value)) {
                                    $value = json_encode($value);
                                }
                                $entity->$set($value);
                                break;
                        }
                    }
                    $this->em->persist($entity);
                    $insertedItems[] = $entity;
                    $countInserts++;
                }
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertMemberGroupLocalizations(array $collection)
    {
        $timeStamp = microtime(true);
        $countInserts = 0;
        $insertedItems = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\MemberGroupLocalization) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                $member = $data['entity'];
                foreach ($data['localizations'] as $locale => $translation) {
                    $entity = new BundleEntity\MemberGroupLocalization();
                    $lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $lModel->getLanguage($locale);
                    if ($response->error->exist) {
                        return $response;
                    }
                    $entity->setLanguage($response->result->set);
                    unset($response);
                    $entity->setGroup($member);
                    foreach ($translation as $column => $value) {
                        $set = 'set' . $this->translateColumnName($column);
                        switch ($column) {
                            default:
                                if (is_object($value) || is_array($value)) {
                                    $value = json_encode($value);
                                }
                                $entity->$set($value);
                                break;
                        }
                    }
                    $this->em->persist($entity);
                    $insertedItems[] = $entity;
                    $countInserts++;
                }
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
            return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param mixed $group
     * @param bool $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
     */
    public function isMemberOfGroup($member, $group, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $response = $this->getGroup($group);
        if ($response->error->exist) {
            return $response;
        }
        $group = $response->result->set;
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $qStr = 'SELECT ' . $this->entity['mog']['alias']
            . ' FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
            . ' WHERE ' . $this->entity['mog']['alias'] . '.group = ' . $group->getId()
            . ' AND ' . $this->entity['mog']['alias'] . '.member = ' . $member->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();

        $exist = false;
        if (count($result) > 0) {
            $exist = true;
        }
        if ($bypass) {
            return $exist;
        }
        return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $member
     * @param mixed $site
     * @param bool $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
     */
    public function isMemberOfSite($member, $site, bool $bypass = null)
    {
        $timeStamp = microtime(true);
        $bypass = $bypass ?? false;
        $sModel = new SMMService\SiteManagementModel($this->kernel, $this->dbConnection, $this->orm);
        $response = $sModel->getSite($site);
        if ($response->error->exist) {
            return $response;
        }
        $site = $response->result->set;
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $qStr = 'SELECT ' . $this->entity['mos']['alias']
            . ' FROM ' . $this->entity['mos']['name'] . ' ' . $this->entity['mos']['alias']
            . ' WHERE ' . $this->entity['mos']['alias'] . '.site = ' . $site->getId()
            . ' AND ' . $this->entity['mos']['alias'] . '.member = ' . $member->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();

        $exist = false;
        if (count($result) > 0) {
            $exist = true;
        }
        if ($bypass) {
            return $exist;
        }
        return new ModelResponse($exist, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return ModelResponse
     */
    public function listGroups(array $filter = null, array $sortOrder = null, array $limit = null)
    {
        return $this->listMemberGroups($filter, $sortOrder, $limit);
    }

    /**
     * @param mixed $member
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function listGroupsOfMember($member, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;

        $qStr = 'SELECT ' . $this->entity['mog']['alias'] . ', ' . $this->entity['mg']['alias']
            . ' FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
            . ' JOIN ' . $this->entity['mog']['alias'] . '.group ' . $this->entity['mg']['alias']
            . ' WHERE ' . $this->entity['mog']['alias'] . '.member = ' . $member->getId();

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
                $oStr .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        $qStr .= $oStr;

        $q = $this->em->createQuery($qStr);
        $q = $this->addLimit($q, $limit);

        $result = $q->getResult();
        $groups = [];
        foreach ($result as $mog) {
            $groups[] = $mog->getGroup();
        }
        $totalRows = count($groups);

        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime(true));
        }
        return new ModelResponse($groups, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMemberGroups(array $filter = null, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $oStr = $wStr = $gStr = $fStr = '';

        $qStr = 'SELECT ' . $this->entity['mgl']['alias']
            . ' FROM ' . $this->entity['mgl']['name'] . ' ' . $this->entity['mgl']['alias']
            . ' JOIN ' . $this->entity['mgl']['alias'] . '.group ' . $this->entity['mg']['alias'];

        if (!is_null($sortOrder)) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'code':
                    case 'date_added':
                    case 'date_removed':
                    case 'date_updated':
                    case 'count_members':
                        $column = $this->entity['mg']['alias'] . '.' . $column;
                        break;
                    case 'name':
                    case 'url_key':
                        $column = $this->entity['mgl']['alias'] . '.' . $column;
                        break;
                }
                $oStr .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if (!is_null($filter)) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr . $gStr . $oStr;
        $q = $this->em->createQuery($qStr);
        $q = $this->addLimit($q, $limit);

        $result = $q->getResult();
        $entities = [];
        foreach ($result as $entry) {
            $id = $entry->getGroup()->getId();
            if (!isset($unique[$id])) {
                $unique[$id] = '';
                $entities[] = $entry->getGroup();
            }
        }
        $totalRows = count($entities);
        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime(true));
        }
        return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMembers(array $filter = null, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $oStr = $wStr = $gStr = $fStr = '';

        $qStr = 'SELECT ' . $this->entity['m']['alias'] . ', ' . $this->entity['ml']['alias']
            . ' FROM ' . $this->entity['ml']['name'] . ' ' . $this->entity['ml']['alias']
            . ' JOIN ' . $this->entity['ml']['alias'] . '.member ' . $this->entity['m']['alias'];

        if (!is_null($sortOrder)) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
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
                        $column = $this->entity['m']['alias'] . '.' . $column;
                        break;
                    case 'title':
                        $column = $this->entity['ml']['alias'] . '.' . $column;
                        break;
                }
                $oStr .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if (!is_null($filter)) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr . $gStr . $oStr;
        $q = $this->em->createQuery($qStr);
        $q = $this->addLimit($q, $limit);

        $result = $q->getResult();

        $entities = [];
        foreach ($result as $entry) {
            $id = $entry->getMember()->getId();
            if (!isset($unique[$id])) {
                $unique[$id] = '';
                $entities[] = $entry->getMember();
            }
        }
        $totalRows = count($entities);
        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime(true));
        }
        return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param mixed $group
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMembersOfGroup($group, array $filter = null, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $response = $this->getGroup($group);
        if ($response->error->exist) {
            return $response;
        }
        $group = $response->result->set;
        $qStr = 'SELECT ' . $this->entity['mog']['alias'] . ' FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
            . ' WHERE ' . $this->entity['mog']['alias'] . '.group = ' . $group->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();
        if (empty($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        $memberIds = [];
        foreach ($result as $item) {
            $memberIds[] = $item->getMember()->getId();
        }

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['m']['alias'] . '.id', 'comparison' => 'in', 'value' => $memberIds),
                )
            )
        );

        $response = $this->listMembers($filter, $sortOrder, $limit);
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);

        return $response;
    }

    /**
     * @param mixed $site
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listMembersOfSite($site, array $filter = null, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
        $response = $sModel->getSite($site);
        if ($response->error->exist) {
            return $response;
        }
        $site = $response->result->set;
        $qStr = 'SELECT ' . $this->entity['mos']['alias'] . ' FROM ' . $this->entity['mos']['name'] . ' ' . $this->entity['mos']['alias']
            . ' WHERE ' . $this->entity['mos']['alias'] . '.site = ' . $site->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();
        if (empty($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }

        $memberIds = [];
        foreach ($result as $item) {
            $memberIds[] = $item->getMember()->getId();
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['m']['alias'] . '.id', 'comparison' => 'in', 'value' => $memberIds),
                )
            )
        );

        $response = $this->listMembers($filter, $sortOrder, $limit);
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);

        return $response;
    }

    /**
     * @param mixed $member
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listFilesOfMember($member, array $filter = null, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $oStr = $wStr = $gStr = '';

        $qStr = 'SELECT ' . $this->entity['fom']['alias']
            . ' FROM ' . $this->entity['fom']['name'] . ' ' . $this->entity['fom']['alias'];

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['fom']['alias'] . '.member', 'comparison' => '=', 'value' => $member->getId()),
                )
            )
        );

        $qStr .= $wStr . $gStr . $oStr;
        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();

        $totalRows = count($result);
        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime(true));
        }
        $fileIds = [];
        foreach($result as $fomItem){
            $fileIds[] = $fomItem->getFile()->getId();
        }
        $fModel = $this->kernel->getContainer()->get('filemanagement.model');

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['f']['alias'] . '.id', 'comparison' => 'in', 'value' => $fileIds),
                )
            )
        );

        $response = $fModel->listFiles($filter, $sortOrder, $limit);
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);

        return $response;

        return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listRegularMemberGroups(array $sortOrder = null, array $limit = null)
    {
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
     * @param mixed $member
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function listSitesOfMember($member, array $sortOrder = null, array $limit = null)
    {
        $timeStamp = microtime(true);
        $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $qStr = 'SELECT ' . $this->entity['mos']['alias'] . ' FROM ' . $this->entity['mos']['name'] . ' ' . $this->entity['mos']['alias']
            . ' WHERE ' . $this->entity['mos']['alias'] . '.member = ' . $member->getId();

        $q = $this->em->createQuery($qStr);

        $result = $q->getResult();
        if (empty($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }
        $siteIds = [];
        foreach ($result as $item) {
            $siteIds[] = $item->getSite()->getId();
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['s']['alias'] . '.id', 'comparison' => 'in', 'value' => $siteIds),
                )
            )
        );

        $response = $sModel->listSites($filter, $sortOrder, $limit);
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);

        return $response;
    }

    /**
     * @param mixed $member
     * @param array $groups
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function removeMemberFromOtherGroups($member, array $groups)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($member);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $idsToRemove = [];
        foreach ($groups as $group) {
            $response = $this->getGroup($group);
            if ($response->error->exist) {
                return $response;
            }
            $idsToRemove[] = $response->result->set->getId();
        }
        $notIn = 'NOT IN (' . implode(',', $idsToRemove) . ')';
        $qStr = 'DELETE FROM ' . $this->entity['mog']['name'] . ' ' . $this->entity['mog']['alias']
            . ' WHERE ' . $this->entity['mog']['alias'] . '.member = ' . $member->getId()
            . ' AND ' . $this->entity['mog']['alias'] . '.group ' . $notIn;

        $q = $this->em->createQuery($qStr);
        $result = $q->getResult();
        $deleted = true;
        if (!$result) {
            $deleted = false;
        }
        if ($deleted) {
            return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, microtime(true));
    }

	/**
	 * @param mixed $group
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateGroup($group)
    {
        return $this->updateMemberGroups(array($group));
    }

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateGroups(array $collection)
    {
        return $this->updateMemberGroups($collection);
    }

	/**
	 * @param $member
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateMember($member)
    {
        return $this->updateMembers(array($member));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function updateMembers(array $collection)
    {
        $timeStamp = microtime(true);
        $countUpdates = 0;
        $updatedItems = [];
        $localizations = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\Member) {
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            } else if (is_object($data)) {
                if (!property_exists($data, 'id') || !is_numeric($data->id)) {
                    return $this->createException('InvalidParameterException', 'Parameter must be an object with the "id" property and id property ​must have an integer value.', 'E:S:003');
                }
                if (!property_exists($data, 'site')) {
                    $data->site = 1;
                }
                if (!property_exists($data, 'language')) {
                    $data->language = 1;
                }
                $response = $this->getMember($data->id);
                if ($response->error->exist) {
                    return $this->createException('EntityDoesNotExist', 'Member with id / username / email ' . $data->id . ' does not exist in database.', 'E:D:002');
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
                                return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "' . $value . '" does not exist in database.', 'E:D:002');
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
        if ($countUpdates > 0) {
            $this->em->flush();
            return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, microtime(true));
    }

	/**
	 * @param mixed $group
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateMemberGroup($group)
    {
        return $this->updateMemberGroups(array($group));
    }

    /**
     * @param array $collection
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function updateMemberGroups(array $collection)
    {
        $timeStamp = microtime(true);
        $countUpdates = 0;
        $updatedItems = [];
        $localizations = [];
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\MemberGroup) {
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            } else if (is_object($data)) {
                if (!property_exists($data, 'id') || !is_numeric($data->id)) {
                    return $this->createException('InvalidParameterException', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
                }
                if (!property_exists($data, 'site')) {
                    $data->site = 1;
                }
                $response = $this->getGroup($data->id);
                if ($response->error->exist) {
                    return $this->createException('EntityDoesNotExist', 'Group with id / code ' . $data->id . ' does not exist in database.', 'E:D:002');
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
                                return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "' . $value . '" does not exist in database.', 'E:D:002');
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
        if ($countUpdates > 0) {
            $this->em->flush();
            return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, microtime(true));
        }
        return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, microtime(true));
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     */
    public function validateAccount(string $username, string $password)
    {
        $timeStamp = microtime(true);
        $response = $this->getMember($username);
        if ($response->error->exist) {
            return $response;
        }
        $member = $response->result->set;
        $enc = $this->kernel->getContainer()->get('encryption');

        $hashedPass = $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

        if ($member->getPassword() != $hashedPass) {
            return new ModelResponse(null, 0, 0, null, true, 'E:SEC:002', 'Invalid credentials. The user cannot be logged in.', $timeStamp, microtime(true));
        }
        return new ModelResponse($member, 0, 0, null, false, 'E:SEC:003', 'The user has been successfully logged in.', $timeStamp, microtime(true));
    }
}