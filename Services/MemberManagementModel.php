<?php
/**
 * MemberManagementModel Class
 *
 * This class acts as a database proxy model for MemberManagementBundle functionalities.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagemetBundle
 * @subpackage	Services
 * @name	    MemberManagemetModel
 *
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.3.6
 * @date        07.07.2014
 *
 * @use         Biberltd\Core\Services\Encryption
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 * =============================================================================================================
 *
 */

namespace BiberLtd\Core\Bundles\MemberManagementBundle\Services;

/** CoreModel */
use BiberLtd\Core\CoreModel;
/** Entities to be used */
use BiberLtd\Core\Bundles\MemberManagementBundle\Entity as BundleEntity;
use BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Entity as MLSEntity;
/** Helper Models */
use BiberLtd\Core\Bundles\SiteManagementBundle\Services as SMMService;
use BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Services as MLSService;
/** Core Service */
use BiberLtd\Core\Services as CoreServices;
use BiberLtd\Core\Exceptions as CoreExceptions;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MemberManagementModel extends CoreModel {

    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.2.3
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);
        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'member' => array('name' => 'MemberManagementBundle:Member', 'alias' => 'm'),
            'member_localization' => array('name' => 'MemberManagementBundle:MemberLocalization', 'alias' => 'ml'),
            'members_of_group' => array('name' => 'MemberManagementBundle:MembersOfGroup', 'alias' => 'mog'),
            'member_group' => array('name' => 'MemberManagementBundle:MemberGroup', 'alias' => 'mg'),
            'member_group_localization' => array('name' => 'MemberManagementBundle:MemberGroupLocalization', 'alias' => 'mgl'),
            'language' => array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            'site' => array('name' => 'SiteManagementBundle:Site', 'alias' => 's'),
        );
    }

    /**
     * @name            __destruct()
     *                  Destructor.dumpGroupCodes
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
     *  				Sets the activation status of the member to active.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member             Member entity, id, username, or, email.
     * @param           string          $key                Activation key.
     * @param           DateTime        $activation_date    Date of activation.
     * @param           bool            $bypass             If set to true, it bypasses key check.
     *
     * @return          array           $response
     */
    public function activateMember($member, $key = null, $activation_date = null, $bypass = false) {
        $this->resetResponse();
        if ($activation_date == null) {
            $activation_date = new DateTime('now', new DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
        } else {
            if (!$activation_date instanceof DateTime) {
                return $this->createException('InvalidDateTimeException', '$activation_date', 'invalid.parameter.activation_date');
            }
        }
        /**
         * $key is required if $bypass is set to false
         */
        if (!$bypass && is_null($key)) {
            return $this->createException('MissingActivationKeyException', '', 'err.required.parameter.key');
        }
        if (is_object($member)) {
            /**
             * !! IMPORTANT:
             * Use bypass = true only in MANAGE/ADMIN controller.
             */
            if ($bypass) {
                $member->setStatus = 'a';
                $member->setDateActivation($activation_date);
                $member->setDateStatusChanged($activation_date);
                $member->setKeyActivation(null);
            }
        } else if (is_numeric($member) && $this->doesMemberExist($member, 'id', true)) {
            $response = $this->getMember($member, 'id');
            if (!$response['error']) {
                $member = $response['result']['set'];
            } else {
                return $this->createException('EntityDoesNotExistException', $member, 'err.db.entry.not.exist');
            }
        } else if (is_string($member) && ($this->doesMemberExist($member, 'username', true) || $this->doesMemberExist($member, 'email', true))) {
            $response_username = $this->getMember($member, 'username');
            $response_email = $this->getMember($member, 'email');
            if (!$response_username['error']) {
                $member = $response_username['result']['set'];
            } else if (!$response_email['error']) {
                $member = $response_email['result']['set'];
            } else {
                return $this->createException('InvalidParameterException', $member, 'err.invalid.parameter.member');
            }
        }
        $member->setStatus = 'a';
        $member->setDateActivation($activation_date);
        $member->setDateStatusChanged($activation_date);
        $member->setKeyActivation(null);

        $this->em->persists($member);

        $this->em->flush();
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $member,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.account.activated',
        );
    }
    /**
     * @name 			countMembers()
     *  				Get the total count of members.
     *
     * @since			1.2.6
     * @version         1.2.6
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter             Multi-dimensional array     *
     * @param           string          $query_str              Custom query
     *
     * @return          array           $response
     */
    public function countMembers($filter = null, $query_str = null) {
        $this->resetResponse();
        /**
         * Add filter checks to below to set join_needed to true.
         */
        $where_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['member_localization']['alias'] . '.language', 'comparison' => '=', 'value' => 1),
                )
            )
        );

        if (is_null($query_str)) {
            $query_str = 'SELECT COUNT('. $this->entity['member']['alias'].')'
                .' FROM '.$this->entity['member_localization']['name'].' '.$this->entity['member_localization']['alias']
                .' JOIN '.$this->entity['member_localization']['alias'].'.member '.$this->entity['member']['alias'];
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepare_where($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }
        $query_str .= $where_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare & Return Response
         */
        $result = $query->getSingleScalarResult();

        $this->response = array(
            'result' => array(
                'set' => $result,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			countMembersOfGroup()
     *  				Get the total count of members of a given group.
     *
     * @since			1.2.7
     * @version         1.2.8
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $group              object, integer, or string
     * @param           array           $filter             Multi-dimensional array
     * @param           string          $query_str          Custom query
     *
     * @return          array           $response
     */
    public function countMembersOfGroup($group, $filter = null, $query_str = null) {
        $this->resetResponse();
        if(!is_null($group) && (is_object($group) && !$group instanceof BundleEntity\MemberGroup) && !is_integer($group) && !is_string($group)){
            return $this->createException('InvalidParameterException', $group, 'err.invalid.parameter.group');
        }
        switch($group){
            case is_object($group) && $group instanceof BundleEntity\MemberGroup:
                $groupId = $group->getId();
                break;
            case is_numeric($group):
                $response = $this->getMemberGroup($group, 'id');
                if(!$response['error']){
                    $group = $response['result']['set'];
                    $groupId = $group->getId();
                }
                $groupId = null;
                unset($response);
                break;
            case is_string($group):
                $response = $this->getMemberGroup($group, 'code');
                if(!$response['error']){
                    $group = $response['result']['set'];
                    $groupId = $group->getId();
                    unset($response);
                }
                else{
                    $groupId = null;
                }
                break;
            default:
                $groupId = null;
                break;
        }

        /**
         * Add filter checks to below to set join_needed to true.
         */
        $where_str = '';
        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if(!is_null($groupId)){
            $filter[] = array(
                'glue' => 'and',
                'condition' => array(
                    array(
                        'glue' => 'and',
                        'condition' => array('column' => $this->entity['members_of_group']['alias'] . '.member_group', 'comparison' => '=', 'value' => $groupId),
                    )
                )
            );
        }

        if (is_null($query_str)) {
            $query_str = 'SELECT COUNT('. $this->entity['member']['alias'].')'
                .' FROM '.$this->entity['members_of_group']['name'].' '.$this->entity['members_of_group']['alias']
                .' JOIN '.$this->entity['members_of_group']['alias'].'.member '.$this->entity['member']['alias'];
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepare_where($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare & Return Response
         */
        $result = $query->getSingleScalarResult();

        $this->response = array(
            'result' => array(
                'set' => $result,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			deactivateMember()
     *  				Sets the activation status of the member to inactive.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member         Member entity, id, username, or, email.
     * @param           bool            $bypass         if set to true it returns bool instead of response
     *
     * @return          array           $response
     */
    public function deactivateMember($member, $bypass = false) {
        $this->resetResponse();
        $now = new DateTime('now', new DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
        if (is_object($member)) {
            /**
             * !! IMPORTANT:
             * Use bypass = true only in MANAGE/ADMIN controller.
             */
            if ($bypass) {
                $member->setStatus = 'i';
                $member->setDateStatusChanged($now);
            }
        }
        else if (is_numeric($member) && $this->does_member_exist($member, 'id', true)) {
            $response = $this->getMember($member, 'id');
            if (!$response['error']) {
                $member = $response['result']['set'];
            } else {
                return $this->createException('InvalidParameterException', $member, 'err.invalid.parameter.member');
            }
        }
        else if (is_string($member) && ($this->doesMemberExist($member, 'username', true) || $this->doesMemberExist($member, 'email', true))) {
            $response_username = $this->getMember($member, 'username');
            $response_email = $this->getMember($member, 'email');
            if (!$response_username['error']) {
                $member = $response_username['result']['set'];
            } else if (!$response_email['error']) {
                $member = $response_email['result']['set'];
            } else {
                return $this->createException('InvalidParameterException', $member, 'err.invalid.parameter.member');
            }
        }
        $member->setStatus = 'i';
        $member->setDateStatusChanged($now);

        $this->em->persists($member);

        $this->em->flush();
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $member,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.account.deactivated',
        );
    }

    /**
     * @name 			validateAccount()
     *  				Checks if the user credentials are correct. And returns the member details if user can be logged
     *                  in.
     *
     * @since			1.0.0
     * @version         1.2.2
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
        $this->resetResponse();
        $response = $this->getMember($username, 'username');
        if ($response['error']) {
            /**
             * Try with email!
             */
            $response = $this->getMember($username, 'email');
            if ($response['error']) {
                return $this->createException('EntityDoesNotExistException', 'Checked against username and email: ' . $username, 'err.invalid.username');
            }
        }
        $member = $response['result']['set'];
        $enc = $this->kernel->getContainer()->get('encryption');

        $hashed_password = $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();
        
        
        if ($member->getPassword() != $hashed_password) {
            return $this->response = array(
                'result' => array(
                    'set' => $member,
                    'total_rows' => 1,
                    'last_insert_id' => null,
                ),
                'error' => true,
                'code' => 'err.account.notvalidated',
            );
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'result' => array(
                'set' => $member,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.account.validated',
        );
        return $this->response;
    }

    /**
     * @name 			deleteMembers()
     *  				Deletes provided members from database. If the member does not exist, throws
     *                  MemberDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection     Collection of Member entities, member ids, usernames or emails.
     *
     * @return          array           $response
     */
    public function deleteMembers($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterValue', 'Array', 'err.invalid.parameter.collection');
        }
        $countDeleted = 0;
        foreach ($collection as $entry) {
            if ($entry instanceof BundleEntity\Member) {
                $this->em->remove($entry);
                $countDeleted++;
            } else {
                switch ($entry) {
                    case is_numeric($entry):
                        $response = $this->getMember($entry, 'id');
                        break;
                    case is_string($entry):
                        $response = $this->getMember($entry, 'username');
                        break;
                }
                if ($response['error']) {
                    $this->createException('EntryDoesNotExist', $entry, 'err.invalid.entry');
                }
                $entry = $response['result']['set'];
                $this->em->remove($entry);
                $countDeleted++;
            }
        }
        if ($countDeleted < 0) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.fail.delete';

            return $this->response;
        }
        $this->em->flush();
        $this->response = array(
            'rowCount' => 0,
            'result' => array(
                'set' => null,
                'total_rows' => $countDeleted,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.deleted',
        );
        return $this->response;
    }

    /**
     * @name 			deleteMember()
     *  				Deletes an existing member from database. If the language does not exist, throws
     *                  MemberDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->deleteMember()
     *
     * @param           mixed           $member           Member entity or id.
     * @return          mixed           $response
     */
    public function deleteMember($member) {
        return $this->deleteMembers(array($member));
    }

    /**
     * @name 			deleteMemberGroups()
     *  				Deletes provided member groups from database. If the group does not exist, throws
     *                  MemberGroupDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection         Collection of MemberGroups entities, member ids, usernames or emails.
     * @param           string          $by             Accepts the following options: entity, id, code
     *
     * @return          array           $response
     */
    public function deleteMemberGroups($collection, $by = 'id') {
        $this->resetResponse();
        $by_opts = array('entity', 'id', 'code');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $entries = array();
        /** Loop through languages and collect values. */
        foreach ($collection as $group) {
            $value = '';
            if (is_object($group)) {
                if (!$group instanceof BundleEntity\MemberGroup) {
                    return $this->createException('InvalidEntityException', 'MemberGroup', 'err.invalid.paramter.collection');
                }
                switch ($by) {
                    case 'entity':
                    case 'id':
                        $value = $group->getId();
                        break;
                    case 'code':
                        $value = $group->getCode();
                        break;
                }
            } else if (is_numeric($group) || is_string($group)) {
                $value = $group;
            } else {
                /** If array values are not numeric nor object */
                return $this->createException('InvalidParameterException', 'Integer, String or MemberGroup Entity', 'err.invalid.parameter.group');
            }
            /**
             * Check if member exits in database.
             */
            if ($this->doesMemberGroupExist($value, $by, true)) {
                $entries[] = $value;
            } else {
                new CoreExceptions\MemberGroupDoesNotExistException($this->kernel, $value);
            }
        }
        /**
         * Control if there is any member group id in collection.
         */
        if (count($entries) < 1) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        /**
         * Prepare query string.
         */
        switch ($by) {
            case 'entity':
                $by = 'id';
            case 'id':
                $values = implode(',', $entries);
                break;
            case 'code':
                $values = implode('\',\'', $entries);
                $values = '\'' . $values . '\'';
                break;
        }
        $query_str = 'DELETE '
                . ' FROM ' . $this->entity['member_group']['name'] . ' ' . $this->entity['member_group']['alias']
                . ' WHERE ' . $this->entity['member_group']['alias'] . '.' . $by . ' IN('.$values.')';
        /**
         * Create query object.
         */
        $query = $this->em->createQuery($query_str);
        /**
         * Free memory.
         */
        unset($values);
        /**
         * Run query
         */
        $query->getResult();
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection,
                'total_rows' => count($collection),
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.delete.done',
        );
    }

    /**
     * @name 			deleteMemberGroup()
     *  				Deletes an existing member from database. If the language does not exist, throws
     *                  MemberDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->deleteMembetGroups()
     *
     * @param           mixed           $group           MemberGroup entity or id.
     *
     * @return          mixed           $response
     */
    public function deleteMemberGroup($group) {
        return $this->deleteMemberGroups(array($group));
    }

    /**
     * @name 			listGroupsOfMember()
     *  				List member groups of a specified member from database.
     *
     * @since			1.2.4
     * @version         1.2.4
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member
     *
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @param           mixed           $site                   id or Site Entity.
     *
     * @return          array           $response
     */
    public function listGroupsOfMember($member, $filter = null, $sortorder = null, $limit = null, $site = 1) {
        $this->resetResponse();
        if (!$member instanceof BundleEntity\Member && !is_numeric($member) && !is_string($member)) {
            return $this->createException('InvalidParameterException', 'Member entity', 'err.invalid.parameter.member');
        }
        if (!is_object($member)) {
            switch ($member) {
                case is_numeric($member):
                    $response = $this->getMember($member, 'id');
                    break;
                case is_string($member):
                    $response = $this->getMember($member, 'username');
                    if ($response['error']) {
                        $response = $this->getMember($member, 'email');
                    }
                    break;
            }
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'Member entity', 'err.invalid.parameter.member');
            }
            $member = $response['result']['set'];
            ;
        }
        /**
         * Prepare $filter
         */
        $q_str = 'SELECT ' . $this->entity['members_of_group']['alias'] . ', ' . $this->entity['member_group']['alias']
                . ' FROM ' . $this->entity['members_of_group']['name'] . ' ' . $this->entity['members_of_group']['alias']
                . ' JOIN ' . $this->entity['members_of_group']['alias'] . '.member_group ' . $this->entity['member_group']['alias']
                . ' WHERE ' . $this->entity['members_of_group']['alias'] . '.member = ' . $member->getId();

        /**
         * Prepare ORDER BY section of query.
         *
         * Note that sorting is done through Member table/entity by default.
         */
        $order_str = '';
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'code':
                    case 'date_added':
                    case 'date_updated':
                    case 'id':
                        $column = $this->entity['member_group']['alias'] . '.' . $column;
                        break;
                    default:
                        $column = $this->entity['members_of_group']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        $q_str .= $order_str;

        $query = $this->em->createQuery($q_str);

        /**
         * Prepare LIMIT section of query
         */
        if ($limit != null) {
            if (isset($limit['start']) && isset($limit['count'])) {
                /** If limit is set */
                $query->setFirstResult($limit['start']);
                $query->setMaxResults($limit['count']);
            } else {
                new CoreExceptions\InvalidLimitException($this->kernel, '');
            }
        }

        $result = $query->getResult();
        $total_rows = count($result);
        if ($total_rows < 1) {
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => null,
                    'total_rows' => $total_rows,
                    'last_insert_id' => null,
                ),
                'error' => true,
                'code' => 'err.db.entry.notexist',
            );
            return $this->response;
        }

        $groups = array();
        foreach ($result as $mog) {
            $groups[] = $mog->getMemberGroup();
        }
        $total_rows = count($groups);

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $groups,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'err.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			listMembers()
     *  				List members from database based on a variety of conditions.
     *
     * @since			1.0.0
     * @version         1.3.5
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param   array   $filter
     * @param   array   $sortorder
     * @param   array   $limit
     * @param   int   $language
     * @param   string   $query_str
     *
     * @return   array   $response
     */
    public function listMembers($filter = null, $sortorder = null, $limit = null, $language = 1, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        $specialQuery = true;
        if(is_null($query_str)){
//            $filter[] = array(
//                'glue' => 'and',
//                'condition' => array(
//                    array(
//                        'glue' => 'and',
//                        'condition' => array('column' => $this->entity['member']['alias'] . '.language', 'comparison' => '=', 'value' => $language),
//                    )
//                )
//            );
            $specialQuery = false;
            $query_str = 'SELECT ' . $this->entity['member_localization']['alias'] . ', ' . $this->entity['member']['alias']
                    . ' FROM ' . $this->entity['member_localization']['name'] . ' ' . $this->entity['member_localization']['alias']
                    . ' JOIN ' . $this->entity['member_localization']['alias'] . '.member ' . $this->entity['member']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name_first':
                    case 'name_last':
                    case 'email':
                    case 'username':
                    case 'date_birth':
                    case 'date_activation':
                    case 'date_registration':
                    case 'date_status_changed':
                        $column = $this->entity['member']['alias'] . '.' . $column;
                        break;
                    case 'title':
                        $column = $this->entity['member_group']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;
        $query = $this->em->createQuery($query_str);
        $query = $this->addLimit($query, $limit);

        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $members = array();

        if(!$specialQuery){
            $unique = array();
            foreach ($result as $entry) {
                $id = $entry->getMember()->getId();
                if (!isset($unique[$id])) {
                    $members[] = $entry->getMember();
                    $unique[$id] = $entry->getMember();
                }
            }
            unset($unique);
        }
        else{
            $members = $result;
        }
        $total_rows = count($members);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $members,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			listMembersOfGroup()
     *  				This an alias that use listMembersOfGroupByGroup()
     *
     * @since			1.3.3
     * @version         1.3.3
     *
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           $group
     * @param           $filter
     * @param           $sortorder
     * @param           $limit
     * @param           $site
     *
     * @return          array           $response
     */
    public function listMembersOfGroup($group, $filter = null, $sortorder = null, $limit = null, $site = 1){
        if(!$group instanceof BundleEntity\MembersOfGroup && !is_numeric($group) && !is_string($group)){
            return $this->createException('InvalidParameterException', 'MemberGroup entity, id, or code', 'err.invalid.parameter');
        }
        if(!$group instanceof BundleEntity\MemberGroup){
            if(is_numeric($group)){
                $response = $this->getMemberGroup($group);
                if($response['error']){
                    return $this->createException('InvalidParameterException', 'MemberGroup entity, id, or code', 'err.invalid.parameter');
                }
                $group = $response['result']['set'];
            }
            elseif(is_string($group)){
                $response = $this->getMemberGroup($group, 'code');
                if($response['error']){
                    return $this->createException('InvalidParameterException', 'MemberGroup entity, id, or code', 'err.invalid.parameter');
                }
                $group = $response['result']['set'];
            }
        }
        $qStr = 'SELECT '.$this->entity['members_of_group']['alias'].' FROM '.$this->entity['members_of_group']['name'].' '.$this->entity['members_of_group']['alias']
                .' WHERE '.$this->entity['members_of_group']['alias'].'.member_group = '.$group->getId();

        $query = $this->em->createQuery($qStr);

        $result = $query->getResult();

        if(count($result) < 1){
            $this->response = array(
                'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => null,
                    'total_rows' => 0,
                    'last_insert_id' => null,
                ),
                'error' => true,
                'code' => 'err.db.entry.notexist',
            );
            return $this->response;
        }
        $memberIds = array();
        foreach($result as $item){
            $memberIds[] = $item->getMember()->getId();
        }

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['member']['alias'].'.id', 'comparison' => 'in', 'value' => $memberIds),
                )
            )
        );

        return $this->listMembers($filter, $sortorder, $limit);
    }

    /**
     * @name 			listMemberOfGroups()
     *  				List members of a group from database.
     *
     * @since			1.2.3
     * @version         1.2.3
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           $filter
     * @param           $sortorder
     * @param           $limit
     * @param           $site
     *
     * @return          array           $response
     */
    public function listMemberOfGroups($filter = null, $sortorder = null, $limit = null, $site = 1) {
        /**
         * Prepare $filter
         */
        $q_str = 'SELECT ' . $this->entity['members_of_group']['alias'] . ', ' . $this->entity['member']['alias']
                . ' FROM ' . $this->entity['members_of_group']['name'] . ' ' . $this->entity['members_of_group']['alias']
                . ' JOIN ' . $this->entity['members_of_group']['alias'] . '.member ' . $this->entity['member']['alias'];

        /**
         * Prepare ORDER BY section of query.
         *
         * Note that sorting is done through Member table/entity by default.
         */
        $order_str = '';
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'name_first':
                    case 'name_last':
                    case 'email':
                    case 'id':
                    case 'date_birth':
                    case 'date_registration':
                    case 'sort_order':
                    case 'date_activation':
                        $column = $this->entity['member']['alias'] . '.' . $column;
                        break;
                    default:
                        $column = $this->entity['members_of_group']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        $q_str .= $order_str;

        $query = $this->em->createQuery($q_str);
        $query = $this->addLimit($query, $limit);

        $result = $query->getResult();

        $total_rows = count($result);
        if ($total_rows < 1) {
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => null,
                    'total_rows' => $total_rows,
                    'last_insert_id' => null,
                ),
                'error' => true,
                'code' => 'err.db.entry.notexist',
            );
            return $this->response;
        }

        $members = array();
        foreach ($result as $mog) {
            $members[] = $mog->getMember();
        }
        $total_rows = count($members);

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $members,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'err.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			listMemberGroups()
     *  				List member groups from database based on a variety of conditions.
     *
     * @since			1.0.0
     * @version         1.2.3
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter             Multi-dimensional array
     * @param           array           $sortorder
     * @param           array           $limit
     *
     * @param           string          $query_str
     *
     * @return          array           $response
     */
    public function listMemberGroups($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT ' . $this->entity['member_group_localization']['alias']
                    . ' FROM ' . $this->entity['member_group_localization']['name'] . ' ' . $this->entity['member_group_localization']['alias']
                    . ' JOIN ' . $this->entity['member_group_localization']['alias'] . '.member_group ' . $this->entity['member_group']['alias'];
        }

        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'code':
                    case 'date_added':
                    case 'date_updated':
                    case 'count_members':
                        $column = $this->entity['member_group']['alias'] . '.' . $column;
                        break;
                    case 'name':
                    case 'url_key':
                        $column = $this->entity['member_group_localization']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepare_where($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;
        $query = $this->em->createQuery($query_str);

        $query = $this->addLimit($query, $limit);
        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();
        $memberGroups = array();
        $unique = array();
        foreach ($result as $entry) {
            $id = $entry->getMemberGroup()->getId();
            if (!isset($unique[$id])) {
                $memberGroups[] = $entry->getMemberGroup();
                $unique[$id] = $entry->getMemberGroup();
            }
        }
        unset($unique);
        
        $total_rows = count($memberGroups);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $memberGroups,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			listRegularMemberGroups()
     *  				returns a list of member groups of type "r" only.
     *
     * @since			1.3.1
     * @version         1.3.1
     * @author          Can Berkol
     *
     * @use             $this->listMemberGroups()
     *
     * @param           array           $sortorder
     * @param           array           $limit
     *
     * @return          array           $response
     */
    public function listRegularMemberGroups($sortorder = null, $limit = null) {
        $column = $this->entity['member_group']['alias'] . '.type';
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
        return $this->listMemberGroups($filter, $sortorder, $limit);
    }
    /**
     * @name 			getMember()
     *  				Returns details of a member.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->listMembers()
     *
     * @param           mixed           $member             Member entity or member id, email, or username.
     * @param           string          $by                 entity, id, email or username
     *
     * @return          mixed           $response
     */
    public function getMember($member, $by = 'id') {
        $this->resetResponse();
        if ($by != 'id' && $by != 'username' && $by != 'email' && $by != 'entity') {
            return $this->createException('InvalidParamterException', 'id, username, email, entity', 'err.invalid.parameter.by');
        }
        if (!is_object($member) && !is_numeric($member) && !is_string($member)) {
            return $this->createException('InvalidParameterException', 'Member Entity or string representing username, email, or id.', 'err.invalid.parameter.member');
        }
        if (is_object($member)) {
            if (!$member instanceof BundleEntity\Member) {
                return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
            }
            $member = $member->getId();
            $by = 'id';
        }
        switch ($by) {
            case 'email':
            case 'id':
            case 'username':
                $by = $this->entity['member']['alias'] . '.' . $by;
                break;
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $by, 'comparison' => '=', 'value' => $member),
                )
            )
        );
        $response = $this->listMembers($filter, null, array('start' => 0, 'count' => 1));
        if ($response['error']) {
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			getMemberGroup()
     *  				Returns details of a member group.
     *
     * @since			1.0.0
     * @version         1.2.3
     * @author          Can Berkol
     *
     * @use             $this->listMemberGroups()
     *
     * @param           mixed           $group              Member Group entity or id, email, or username.
     * @param           string          $by                 entity, id, code, url_key
     *
     * @return          mixed           $response
     */
    public function getMemberGroup($group, $by = 'id') {
        $this->resetResponse();
        if ($by != 'id' && $by != 'code' && $by != 'entity' && $by != 'url_key') {
            return $this->createException('InvalidParameterException', 'id, code, url_key', 'err.invalid.parameter.by');
        }
        if (!is_object($group) && !is_numeric($group) && !is_string($group)) {
            return $this->createException('InvalidParameterException', 'Member Group entity, or string representing code or id.', 'err.invalid.parameter.member_Group');
        }
        if (is_object($group)) {
            if (!$group instanceof BundleEntity\MemberGroup) {
                return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.member_group');
            }
            $group = $group->getId();
            $by = 'id';
        }
        switch ($by) {
            case 'code':
            case 'id':
                $by = $this->entity['member_group']['alias'] . '.' . $by;
                break;
            case 'url_key':
                $by = $this->entity['member_group_localization']['alias'] . '.' . $by;
                break;
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $by, 'comparison' => '=', 'value' => $group),
                )
            )
        );
        
        $response = $this->listMemberGroups($filter, null, array('start' => 0, 'count' => 1));
        if ($response['error']) {
            return $response;
        }
        
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			getMemberGroupLocalization()
     *  				Gets a specific member group's localization values from database.
     *
     * @since			1.0.1
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           BundleEntity\MemberGroup        $group
     * @param           MLSEntity\Language              $language
     *
     * @return          array           $response
     */
    public function getMemberGroupLocalization($group, $language) {
        $this->resetResponse();
        if (!$group instanceof BundleEntity\MemberGroup) {
            return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.group');
        }
        /** Parameter must be an array */
        if (!$language instanceof MLSEntity\Language) {
            return $this->createException('InvalidParameterException', 'Language', 'err.invalid.parameter.language');
        }
        $q_str = 'SELECT ' . $this->entity['member_group_localization']['alias'] . ' FROM ' . $this->entity['member_group_localization']['name'] . ' ' . $this->entity['member_group_localization']['alias']
                . ' WHERE ' . $this->entity['member_group_localization']['alias'] . '.member_group = ' . $group->getId()
                . ' AND ' . $this->entity['member_group_localization']['alias'] . '.language = ' . $language->getId();

        $query = $this->em->createQuery($q_str);
        /**
         * 6. Run query
         */
        $result = $query->getResult();
        /**
         * Prepare & Return Response
         */
        $total_rows = count($result);

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist.',
        );
        return $this->response;
    }

    /**
     * @name 			getMemberLocalization()
     *  				Gets a specific member's localization values from database.
     *
     * @since			1.0.1
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           BundleEntity\Member             $member
     * @param           MLSEntity\Language              $language
     *
     * @return          array           $response
     */
    public function getMemberLocalization($member, $language) {
        $this->resetResponse();
        if (!$member instanceof BundleEntity\Member) {
            return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
        }
        /** Parameter must be an array */
        if (!$language instanceof MLSEntity\Language) {
            return $this->createException('InvalidParameterException', 'Language', 'err.invalid.parameter.language');
        }
        $q_str = 'SELECT ' . $this->entity['member_localization']['alias'] . ' FROM ' . $this->entity['member_localization']['name'] . ' ' . $this->entity['member_localization']['alias']
                . ' WHERE ' . $this->entity['member_localization']['alias'] . '.member = ' . $member->getId()
                . ' AND ' . $this->entity['member_localization']['alias'] . '.language = ' . $language->getId();

        $query = $this->em->createQuery($q_str);
        /**
         * 6. Run query
         */
        $result = $query->getResult();
        /**
         * Prepare & Return Response
         */
        $total_rows = count($result);

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result[0],
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist.',
        );
        return $this->response;
    }

    /**
     * @name 			doesMemberExist()
     *  				Checks if member exists in database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->getMember()
     *
     * @param           mixed           $member         Member entity, email, member id, username.
     * @param           string          $by             all, entity, id, username or email
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesMemberExist($member, $by = 'all', $bypass = false) {
        $this->resetResponse();
        $exist = false;
        $error = true;
        if ($by == 'all') {
            $response_by_id = $this->getMember($member, 'id');
            $response_by_username = $this->getMember($member, 'username');
            $response_by_email = $this->getMember($member, 'email');
            $response_by_entity = $this->getMember($member, 'entity');

            if (!$response_by_id['result']['total_rows'] > 0 || !$response_by_username['result']['total_rows'] > 0 || !$response_by_email['result']['total_rows'] > 0 || !$response_by_entity['result']['total_rows'] > 0) {
                $exist = true;
                $error = false;
            }
        } else {
            $response = $this->getMember($member, $by);
        }

        if (!$response['error']) {
            if ($response['result']['total_rows'] > 0) {
                $exist = true;
                $error = false;
                
            }
        }
        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	        'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $response['result']['set'],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			doesMemberGroupExist()
     *  				Checks if member group exists in database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->getMemberGroup()
     *
     * @param           mixed           $group          MemberGroup entity or member group id.
     * @param           string          $by             id, code
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesMemberGroupExist($group, $by = 'id', $bypass = false) {
        $this->resetResponse();
        $exist = false;

        $response = $this->getMemberGroup($group, $by);
        if (!$response['error']) {
            if ($response['result']['total_rows'] > 0) {
                $exist = true;
            }
        }
        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			insertMembers()
     *  				Inserts one or more members into database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Member entities or array of member detail array.
     *
     * @return          array           $response
     */
    public function insertMembers($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $countLocalizations = 0;
        $countGroups = 0;
        $insertedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\Member){
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else if(is_object($data)){
                $localizations = array();
                $groups = array();
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
                foreach($data as $column => $value){
                    $localeSet = false;
                    $groupSet = false;
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'local':
                            $localizations[$countInserts]['localizations'] = $value;
                            $localeSet = true;
                            $countLocalizations++;
                            break;
                        case 'language':
                            $lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                            if(is_numeric($value)){
                                $response = $lModel->getLanguage($value, 'id');
                            }
                            else{
                                $response = $lModel->getLanguage($value, 'iso_code');
                            }
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\EntityDosNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
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
                    if($localeSet){
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
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        /** Now handle localizations */
        if($countInserts > 0 && $countLocalizations > 0){
            $this->insertMemberLocalizations($localizations);
        }
        if($countInserts > 0 && $countGroups > 0){
            foreach($groups as $group){
                $this->addMemberToGroups($group['entity'], $group['groups']);
            }
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name 			insertMemberGroups()
     *  				Inserts one or more members into database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createExcepiton()
     *
     * @param           array           $collection      Collection of MemberGroup entities or array of member detail array.
     *
     * @return          array           $response
     */
    public function insertMemberGroups($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $countLocalizations = 0;
        $insertedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\MemberGroup){
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else if(is_object($data)){
                $localizations = array();
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
                foreach($data as $column => $value){
                    $localeSet = false;
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'local':
                            $localizations[$countInserts]['localizations'] = $value;
                            $localeSet = true;
                            $countLocalizations++;
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        default:
                            $entity->$set($value);
                            break;
                    }
                    if($localeSet){
                        $localizations[$countInserts]['entity'] = $entity;
                    }
                }
                $this->em->persist($entity);
                $insertedItems[] = $entity;

                $countInserts++;
            }
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        /** Now handle localizations */
        if($countInserts > 0 && $countLocalizations > 0){
            $this->insertMemberGroupLocalizations($localizations);
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name 			insertMember()
     *  				Inserts one member into database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->insertMembers()
     *
     * @param           mixed           $member      Member Entity or a collection of post input that stores entity details.
     *
     * @return          array           $response
     */
    public function insertMember($member) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($member) && !is_object($member)) {
            return $this->createException('InvalidParameterException', 'Array or Member Entity', 'err.invalid.parameter.member');
        }
        return $this->insertMembers(array($member));
    }

    /**
     * @name 			insertMemberGroup()
     *  				Inserts one member groups into database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->insertMemberGroups()
     *
     * @param           mixed           $group      MemberGroup Entity or a collection of post input that stores entity details.
     *
     * @return          array           $response
     */
    public function insertMemberGroup($group) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($group) && !is_object($group)) {
            return $this->createException('InvalidParameterException', 'Array or MemberGroup Entity', 'err.invalid.parameter.group');
        }
        return $this->insertMemberGroups(array($group));
    }
    /**
     * @name 			insertMemberLocalizations()
     *  				Inserts one or more member localizations into database.
     *
     * @since			1.2.9
     * @version         1.2.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertMemberLocalizations($collection){
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach($collection as $item){
            if($item instanceof BundleEntity\MemberLocalization){
                $entity = $item;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else{
                foreach($item['localizations'] as $language => $data){
                    $entity = new BundleEntity\MemberLocalization;
                    $entity->setMember($item['entity']);
                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $mlsModel->getLanguage($language, 'iso_code');
                    if(!$response['error']){
                        $entity->setLanguage($response['result']['set']);
                    }
                    else{
                        break 1;
                    }
                    foreach($data as $column => $value){
                        $set = 'set'.$this->translateColumnName($column);
                        $entity->$set($value);
                    }
                    $this->em->persist($entity);
                }
                $insertedItems[] = $entity;
                $countInserts++;
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => -1,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }
    /**
     * @name 			insertMemberLocalizations()
     *  				Inserts one or more member localizations into database.
     *
     * @since			1.3.0
     * @version         1.3.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertMemberGroupLocalizations($collection){
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach($collection as $item){
            if($item instanceof BundleEntity\MemberGroupLocalization){
                $entity = $item;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else{
                foreach($item['localizations'] as $language => $data){
                    $entity = new BundleEntity\MemberGroupLocalization;
                    $entity->setMemberGroup($item['entity']);
                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $mlsModel->getLanguage($language, 'iso_code');
                    if(!$response['error']){
                        $entity->setLanguage($response['result']['set']);
                    }
                    else{
                        break 1;
                    }
                    foreach($data as $column => $value){
                        $set = 'set'.$this->translateColumnName($column);
                        $entity->$set($value);
                    }
                    $this->em->persist($entity);
                }
                $insertedItems[] = $entity;
                $countInserts++;
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => -1,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }
    /**
     * @name 			updateMembers()
     *  				Updates one or more group details in database.
     *
     * @since			1.0.0
     * @version         1.3.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Member entities or array of entity details.
     *
     * @return          array           $response
     */
    public function updateMembers($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countUpdates = 0;
        $updatedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\Member){
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            }
            else if(is_object($data)){
                if(!property_exists($data, 'id') || !is_numeric($data->id)){
                    return $this->createException('InvalidParameterException', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
                }
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                if(!property_exists($data, 'language')){
                    $data->language = 1;
                }
                $response = $this->getMember($data->id, 'id');
                if($response['error']){
                    return $this->createException('EntityDoesNotExist', 'Product with id '.$data->id, 'err.invalid.entity');
                }
                $oldEntity = $response['result']['set'];
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'local':
                            $localizations = array();
                            foreach($value as $langCode => $translation){
                                $localization = $oldEntity->getLocalization($langCode, true);
                                $newLocalization = false;
                                if(!$localization){
                                    $newLocalization = true;
                                    $localization = new BundleEntity\MemberLocalization();
                                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                                    $response = $mlsModel->getLanguage($langCode, 'iso_code');
                                    $localization->setLanguage($response['result']['set']);
                                    $localization->setMember($oldEntity);
                                }
                                foreach($translation as $transCol => $transVal){
                                    $transSet = 'set'.$this->translateColumnName($transCol);
                                    $localization->$transSet($transVal);
                                }
                                if($newLocalization){
                                    $this->em->persist($localization);
                                }
                                $localizations[] = $localization;
                            }
                            $oldEntity->setLocalizations($localizations);
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $oldEntity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'language':
                            $lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                            $response = $lModel->getLanguage($value, 'iso_code');
                            if($response['error']){
                                $response = $lModel->getLanguage($value, 'id');
                            }
                            if(!$response['error']){
                                $oldEntity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'password':
                            /** We will need the encryption service to encrypt password. */
                            $enc = $this->kernel->getContainer()->get('encryption');
                            $password = $enc->input($value)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();
                            $oldEntity->$set($password);
                            break;
                        case 'groups':
                            $this->removeMemberFromOtherGroups($oldEntity, $value);
                            $this->addMemberToGroups($oldEntity, $value);
                            break;
                        case 'id':
                            break;
                        default:
                            $oldEntity->$set($value);
                            break;
                    }
                    if($oldEntity->isModified()){
                        $this->em->persist($oldEntity);
                        $countUpdates++;
                        $updatedItems[] = $oldEntity;
                    }
                }
            }
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countUpdates > 0){
            $this->em->flush();
            $this->response = array(
                'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $updatedItems,
                    'total_rows' => $countUpdates,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.update.done',
            );
        }
        else{
            $this->response = array(
                'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $updatedItems,
                    'total_rows' => 0,
                    'last_insert_id' => null,
                ),
                'error' => true,
                'code' => 'err.db.update',
            );
        }
        return $this->response;
    }

    /**
     * @name 			updateMemberGroups()
     *  				Updates one or more group details in database.
     *
     * @since			1.0.0
     * @version         1.3.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Member entities or array of entity details.
     *
     * @return          array           $response
     */
    public function updateMemberGroups($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countUpdates = 0;
        $updatedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\MemberGroup){
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            }
            else if(is_object($data)){
                if(!property_exists($data, 'id') || !is_numeric($data->id)){
                    return $this->createException('InvalidParameterException', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
                }
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                $response = $this->getMemberGroup($data->id, 'id');
                if($response['error']){
                    return $this->createException('EntityDoesNotExist', 'Product with id '.$data->id, 'err.invalid.entity');
                }
                $oldEntity = $response['result']['set'];
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'local':
                            $localizations = array();
                            foreach($value as $langCode => $translation){
                                $localization = $oldEntity->getLocalization($langCode, true);
                                $newLocalization = false;
                                if(!$localization){
                                    $newLocalization = true;
                                    $localization = new BundleEntity\MemberGroupLocalization();
                                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                                    $response = $mlsModel->getLanguage($langCode, 'iso_code');
                                    $localization->setLanguage($response['result']['set']);
                                    $localization->setMemberGroup($oldEntity);
                                }
                                foreach($translation as $transCol => $transVal){
                                    $transSet = 'set'.$this->translateColumnName($transCol);
                                    $localization->$transSet($transVal);
                                }
                                if($newLocalization){
                                    $this->em->persist($localization);
                                }
                                $localizations[] = $localization;
                            }
                            $oldEntity->setLocalizations($localizations);
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $oldEntity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'language':
                            $by = is_int($value) ? 'id' : 'iso_code';
                            $lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                            $response = $lModel->getLanguage($value, $by);
                            if(!$response['error']){
                                $oldEntity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'id':
                            break;
                        default:
                            $oldEntity->$set($value);
                            break;
                    }
                    if($oldEntity->isModified()){
                        $this->em->persist($oldEntity);
                        $countUpdates++;
                        $updatedItems[] = $oldEntity;
                    }
                }
            }
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countUpdates > 0){
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $updatedItems,
                'total_rows' => $countUpdates,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }
    /**
     * @name 			updateMember()
     *  				Update one member in database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->updateMembers()
     * @use             $this->createException()
     *
     * @param           array           $member      Member Entity or a collection of post input that stores site details.
     *
     * @return          array           $response
     */
    public function updateMember($member) {
        $this->resetResponse();
        return $this->updateMembers(array($member));
    }

    /**
     * @name 			updateMemberGroup()
     *  				Update one member groups in database.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->updateMemberGroups()
     * @use             $this->createException()
     *
     * @param           array           $group      MemberGroup Entity or a collection of post input that stores site details.
     *
     * @return          array           $response
     */
    public function updateMemberGroup($group) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($group) && !is_object($group)) {
            return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.member_group');
        }
        return $this->updateMemberGroups(array($group));
    }

    /**
     * @name 			addMemberToGroups()
     *  				Add member to one or more groups.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->getMember()
     * @use             $this->getMemberGroup()
     * @use             $this->isMemberOfGroup()
     * @use             $this->createException()
     *
     * @param           mixed           $member                 Member Entity, id, username, or email.
     * @param           array           $groups                 Array of MemberGroup entitites, ids, or codes.
     *
     * @return          array           $response
     */
    public function addMemberToGroups($member, $groups) {
        $this->resetResponse();
        if (is_numeric($member)) {
            $response = $this->getMember($member, 'id');
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
            }
            $member = $response['result']['set'];
        } else if (is_string($member)) {
            $response_u = $this->getMember($member, 'username');
            $response_e = $this->getMember($member, 'email');
            if ($response_u['error'] && $response_e['error']) {
                return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
            }
            if (!$response_u['error']) {
                $member = $response_u['result']['set'];
            }
            if (!$response_e['error']) {
                $member = $response_e['result']['set'];
            }
        }
        if (!is_array($groups)) {
            return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.member_groups');
        }
        $to_add = array();
        foreach ($groups as $group) {
            if (is_numeric($group)) {
                $response = $this->getMemberGroup($group, 'id');
                if ($response['error']) {
                    new CoreExceptions\MemberGroupDoesNotExistException($this->kernel, $group);
                    break;
                }
                $group = $response['result']['set'];
            }
            else if (is_string($group)) {
                $response = $this->getMemberGroup($group, 'code');
                if ($response['error']) {
                    new CoreExceptions\MemberGroupDoesNotExistException($this->kernel, $group);
                    break;
                }
                $group = $response['result']['set'];
            }
            /**
             * If not already in group we will add the member to group.
             */
            if (!$this->isMemberOfGroup($member, $group, true)) {
                $to_add[] = $group;
            }
        }
        $now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
        foreach ($to_add as $group) {
            $entity = new BundleEntity\MembersOfGroup();
            $entity->setMember($member)->setMemberGroup($group)->setDateAdded($now);
            /**
             * Increment count_members of MemberGroup
             */
            $group->incrementMemberCount(1);
            $this->em->persist($entity);
            $this->em->persist($group);
        }
        $this->em->flush();
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $to_add,
                'total_rows' => count($to_add),
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name 			addGroupToMembers()
     *  				Add group to one or more groups.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->getMember()
     * @use             $this->getMemberGroup()
     * @use             $this->isMemberOfGroup()
     * @use             $this->createException()
     *
     * @param           mixed           $group                  MemberGroup Entity, id, or code.
     * @param           array           $members                Array of Member entitites, ids, usernames, or emails.
     *
     * @return          array           $response
     */
    public function addGroupToMembers($group, $members) {
        $this->resetResponse();
        if (is_numeric($group)) {
            $response = $this->getMemberGroup($group, 'id');
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.member_group');
            }
            $group = $response['result']['set'];
        } else if (is_string($group)) {
            $response = $this->getMemberGroup($group, 'code');
            if (!$response['error']) {
                $group = $response['result']['set'];
            } else {
                return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.member_group');
            }
        }
        if (!is_array($members)) {
            return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.members');
        }
        $to_add = array();
        foreach ($members as $member) {
            if (is_numeric($member)) {
                $response = $this->getMember($member, 'id');
                if ($response['error']) {
                    new CoreExceptions\MemberDoesNotExistException($this->kernel, $group);
                    break;
                }
                $member = $response['result']['set'];
            } else if (is_string($member)) {
                $response_u = $this->getMember($member, 'username');
                $response_e = $this->getMember($member, 'email');
                if ($response_u['error'] && $response_e['error']) {
                    new CoreExceptions\MemberDoesNotExistException($this->kernel, $member);
                    break;
                }
                if (!$response_u['error']) {
                    $member = $response['result']['set'];
                }
                if (!$response_e['error']) {
                    $member = $response['result']['set'];
                }
            }
            /**
             * If not already in group we will add the member to group.
             */
            if (!$this->isMemberOfGroup($member, $group, true)) {
                $to_add[] = $member;
            }
        }
        $now = new \DateTime('now', new \DateTimezone($this->kernel->getContainer()->getParameter('app_timezone')));
        foreach ($to_add as $member) {
            $entity = new BundleEntity\MembersOfGroup();
            $entity->setMember($member)->set_group($group)->setDateAdded($now);
            /**
             * Increment count_members of MemberGroup
             */
            $group->incremenet_count_members(1);
            $this->em->persist($entity);
            $this->em->persist($group);
        }
        $this->em->flush();

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $to_add,
                'total_rows' => count($to_add),
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name 			isMemberOfGroup()
     *  				Checks if a given member is a part of a given group.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member                 Member Entity, id, username, email.
     * @param           mixed           $group                  MemberGroup Entity, id, code.
     * @param           bool            $bypass                 if set to true returns the resuşt directly.
     *
     * @return          mixed           $response
     */
    public function isMemberOfGroup($member, $group, $bypass) {
        $this->resetResponse();
        if (is_object($group)) {
            $group = $group->getId();
        } else if (is_string($group)) {
            $response = $this->getMemberGroup($group, 'code');
            if (!$response['error']) {
                $group = $response['result']['set']->getId();
            } else {
                return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.group');
            }
        }
        if (is_object($member)) {
            $member = $member->getId();
        } else if (is_string($member)) {
            $response_u = $this->getMember($member, 'username');
            $response_e = $this->getMember($member, 'email');
            if ($response_u['error'] && $response_e['error']) {
                return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
            }
            if (!$response_u['error']) {
                $member = $response['result']['set']->getId();
            }
            if (!$response_e['error']) {
                $member = $response['result']['set']->getId();
            }
        }
        $query_str = 'SELECT ' . $this->entity['members_of_group']['alias']
                . ' FROM ' . $this->entity['members_of_group']['name'] . ' ' . $this->entity['members_of_group']['alias']
                . ' WHERE ' . $this->entity['members_of_group']['alias'] . '.member_group = ' . $group
                . ' AND ' . $this->entity['members_of_group']['alias'] . '.member = ' . $member;

        $query = $this->em->createQuery($query_str);

        $result = $query->getResult();

        $exist = false;
        if (count($result) > 0) {
            $exist = true;
        }
        if ($bypass) {
            return $exist;
        }

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			removeMemberFromOtherGroups()
     *  				Removes member from any other groups than the ones provided by parameter.
     *
     * @since			1.0.0
     * @version         1.2.2
     * @author          Can Berkol
     *
     * @use             $this->doesMemberGroupExist()
     * @use             $this->createException()
     *
     * @param           mixed           $member                 Member Entity, id, username, email.
     * @param           array           $groups                 MemberGroup Entities, ids, code.
     * @param           bool            $bypass                 if set to true returns the resuşt directly.
     *
     * @return          mixed           $response
     */
    public function removeMemberFromOtherGroups($member, $groups) {
        $this->resetResponse();
        if (is_object($member)) {
            $member = $member->getId();
        } else if (is_string($member)) {
            $response_u = $this->getMember($member, 'username');
            $response_e = $this->getMember($member, 'email');
            if ($response_u['error'] && $response_e['error']) {
                return $this->createException('InvalidParameterException', 'Member', 'err.invalid.parameter.member');
            }
            if (!$response_u['error']) {
                $member = $response_u['result']['set']->getId();
            }
            if (!$response_e['error']) {
                $member = $response_e['result']['set']->getId();
            }
        }
        $to_add = array();
        foreach ($groups as $group) {
            if (is_object($group)) {
                $group = $group->getId();
            }
            else if (is_numeric($group)){
                $group = $group;
            }
            else if (is_string($group)) {
                $response = $this->getMemberGroup($group, 'code');
                if (!$response['error']) {
                    $group = $response['result']['set']->getId();
                } else {
                    return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.group');
                }
            }
            $to_add[] = $group;
        }
        $not_in = 'NOT IN (' . implode(',', $to_add) . ')';
        $query_str = 'DELETE FROM ' . $this->entity['members_of_group']['name'] . ' ' . $this->entity['members_of_group']['alias']
                . ' WHERE ' . $this->entity['members_of_group']['alias'] . '.member_group ' . $not_in;

        $query = $this->em->createQuery($query_str);

        $result = $query->getResult();

        $deleted = true;
        if (!$result) {
            $deleted = false;
        }
        if ($deleted) {
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $deleted,
                    'total_rows' => 0,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.delete.done',
            );
        } else {
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $deleted,
                    'total_rows' => 0,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.delete.failed',
            );
        }
        return $this->response;
    }

    /**
     * @name checkMemberPassword()
     *
     * @since   1.3.4
     * @version 1.3.4
     *
     * @use $this->getMember()
     *
     * @param   mixed $member
     * @param   string $password
     *
     * @return bool
     */
    public function checkMemberPassword($member,$password){
        if ((!is_int($member) && !is_object($member) && !$member instanceof BundleEntity\Member) || (is_null($password) && !is_string($password) && $password == '' && $password == null)) {
            return $this->createException('InvalidParameterException', 'MemberGroup', 'err.invalid.parameter.group');
        }

        if ($member instanceof BundleEntity\Member) {
            $member = $member->getId();
        }
        if ($member instanceof \stdClass) {
            $member = $member->id;
        }

        /** We will need the encryption service to encrypt password. */
        $enc = $this->kernel->getContainer()->get('encryption');
        $password = $enc->input($password)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

        /** Prepare Filter */
        $filter = array();
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['member']['alias'] . '.id', 'comparison' => '=', 'value' => $member),
                ),
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['member']['alias'] . '.password', 'comparison' => '=', 'value' => $password),
                ),
            )
        );
        /** Now we can check password */

        $response = $this->listMembers($filter,null,array('start'=>0,'count'=>1));
        if ($response['error']) {
            return false;
        }
        unset($response);
        return true;
    }

    /**
     * @name            validateAndGetMember()
     *                  Validates $member parameter and returns BiberLtd\Core\Bundles\MemberManagementBundle\Entity\Member if found in database.
     *
     * @since           1.3.6
     * @version         1.3.6
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     * @use             $this->getMember()
     *
     * @param           mixed           $member
     *
     * @return          object          BiberLtd\Core\Bundles\MemberManagementBundle\Entity\Member
     */
    public function validateAndGetMember($member){
        if (!is_numeric($member) && !$member instanceof BundleEntity\Member) {
            return $this->createException('InvalidParameter', '$member parameter must hold BiberLtd\\Core\\Bundles\\MemberManagementBundle\\Entity\\Member Entity, string representing url_key or sku, or integer representing database row id', 'msg.error.invalid.parameter.member');
        }
        if ($member instanceof BundleEntity\Member) {
            return $member;
        }
        if (is_numeric($member)) {
            $response = $this->getMember($member, 'id');
            if ($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member, id: ' . $member, 'msg.error.db.member.notfound');
            }
            $member = $response['result']['set'];
        } else if (is_string($member)) {
            $response = $this->getMember($member, 'sku');
            if ($response['error']) {
                $response = $this->getMember($member, 'url_key');
                if ($response['error']) {
                    return $this->createException('EntityDoesNotExist', 'Table : member, id / sku / url_key: ' . $member, 'msg.error.db.member.notfound');
                }
            }
            $member = $response['result']['set'];
        }

        return $member;
    }

}
/**
 * Change Log
 * **************************************
 * v1.3.6                       Said İmamoğlu
 * 07.07.2014
 * **************************************
 * A validateAndGetMember()
 * **************************************
 * v1.3.5                       Said İmamoğlu
 * 27.06.2014
 * **************************************
 * U listMembers()
 * **************************************
 * v1.3.4                       Can Berkol
 * 05.06.2014
 * **************************************
 * A checkMemberPassword()
 * **************************************
 * v1.3.3                       Can Berkol
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
 * U getMemberGroup()
 * U listMemberGroups()
 *
 * **************************************
 * v1.2.2                      Can Berkol
 * 16.11.2013
 * **************************************
 * A getMemberLocalization()
 * A getMemberGroupLocalization()
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
 * A getMemberGroup()
 * A insert_member()
 * A insert_member_groups()
 * A list_members()
 * A list_member_groups()
 * A update_member()
 * A update_member_group()
 * A update_members()
 * A update_member_groups()
 */