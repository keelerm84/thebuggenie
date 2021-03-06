<?php

    /**
     * Client class
     *
     * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
     * @version 3.1
     * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
     * @package thebuggenie
     * @subpackage main
     */

    /**
     * Client class
     *
     * @package thebuggenie
     * @subpackage main
     *
     * @Table(name="TBGClientsTable")
     */
    class TBGClient extends TBGIdentifiableScopedClass
    {

        protected $_members = null;

        protected $_num_members = null;

        /**
         * The name of the object
         *
         * @var string
         * @Column(type="string", length=200)
         */
        protected $_name;

        /**
         * Email of client
         *
         * @param string
         * @Column(type="string", length=200)
         */
        protected $_email = null;

        /**
         * Telephone number of client
         *
         * @param integer
         * @Column(type="string", length=200)
         */
        protected $_telephone = null;

        /**
         * URL for client website
         *
         * @param string
         * @Column(type="string", length=200)
         */
        protected $_website = null;

        /**
         * Fax number of client
         *
         * @param integer
         * @Column(type="string", length=200)
         */
        protected $_fax = null;

        /**
         * List of client's dashboards
         *
         * @var array|\thebuggenie\core\entities\Dashboard
         * @Relates(class="\thebuggenie\core\entities\Dashboard", collection=true, foreign_column="client_id", orderby="name")
         */
        protected $_dashboards = null;

        protected static $_clients = null;

        public static function doesClientNameExist($client_name)
        {
            return TBGClientsTable::getTable()->doesClientNameExist($client_name);
        }

        public static function getAll()
        {
            if (self::$_clients === null)
            {
                self::$_clients = TBGClientsTable::getTable()->getAll();
            }
            return self::$_clients;
        }

        public static function loadFixtures(TBGScope $scope)
        {

        }

        public function __toString()
        {
            return "" . $this->_name;
        }

        /**
         * Get the client's website
         *
         * @return string
         */
        public function getWebsite()
        {
            return $this->_website;
        }

        /**
         * Get the client's email address
         *
         * @return string
         */
        public function getEmail()
        {
            return $this->_email;
        }

        /**
         * Get the client's telephone number
         *
         * @return integer
         */
        public function getTelephone()
        {
            return $this->_telephone;
        }

        /**
         * Get the client's fax number
         *
         * @return integer
         */
        public function getFax()
        {
            return $this->_fax;
        }

        /**
         * Set the client's website
         *
         * @param string
         */
        public function setWebsite($website)
        {
            $this->_website = $website;
        }

        /**
         * Set the client's email address
         *
         * @param string
         */
        public function setEmail($email)
        {
            $this->_email = $email;
        }

        /**
         * Set the client's telephone number
         *
         * @param integer
         */
        public function setTelephone($telephone)
        {
            $this->_telephone = $telephone;
        }

        /**
         * Set the client's fax number
         *
         * @param integer
         */
        public function setFax($fax)
        {
            $this->_fax = $fax;
        }

        /**
         * Adds a user to the client
         *
         * @param TBGUser $user
         */
        public function addMember(TBGUser $user)
        {
            if (!$user->getID()) throw new Exception('Cannot add user object to client until the object is saved');

            TBGClientMembersTable::getTable()->addUserToClient($user->getID(), $this->getID());

            if (is_array($this->_members))
                $this->_members[$user->getID()] = $user->getID();
        }

        public function getMembers()
        {
            if ($this->_members === null)
            {
                $this->_members = array();
                foreach (TBGClientMembersTable::getTable()->getUIDsForClientID($this->getID()) as $uid)
                {
                    $this->_members[$uid] = TBGContext::factory()->TBGUser($uid);
                }
            }
            return $this->_members;
        }

        public function removeMember(TBGUser $user)
        {
            if ($this->_members !== null)
            {
                unset($this->_members[$user->getID()]);
            }
            if ($this->_num_members !== null)
            {
                $this->_num_members--;
            }
            TBGClientMembersTable::getTable()->removeUserFromClient($user->getID(), $this->getID());
        }

        protected function _preDelete()
        {
            TBGClientMembersTable::getTable()->removeUsersFromClient($this->getID());
        }

        public static function findClients($details)
        {
            $crit = new \b2db\Criteria();
            $crit->addWhere(TBGClientsTable::NAME, "%$details%", \b2db\Criteria::DB_LIKE);
            $clients = array();
            if ($res = \b2db\Core::getTable('TBGClientsTable')->doSelect($crit))
            {
                while ($row = $res->getNextRow())
                {
                    $clients[$row->get(TBGClientsTable::ID)] = TBGContext::factory()->TBGClient($row->get(TBGClientsTable::ID), $row);
                }
            }
            return $clients;
        }

        public function getNumberOfMembers()
        {
            if ($this->_members !== null)
            {
                return count($this->_members);
            }
            elseif ($this->_num_members === null)
            {
                $this->_num_members = TBGClientMembersTable::getTable()->getNumberOfMembersByClientID($this->getID());
            }

            return $this->_num_members;
        }

        public function hasAccess()
        {
            return (bool) (TBGContext::getUser()->hasPageAccess('clientlist') || TBGContext::getUser()->isMemberOfClient($this));
        }

        /**
         * Return the items name
         *
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * Set the edition name
         *
         * @param string $name
         */
        public function setName($name)
        {
            $this->_name = $name;
        }

        /**
         * Returns an array of client dashboards
         *
         * @return array|\thebuggenie\core\entities\Dashboard
         */
        public function getDashboards()
        {
            $this->_b2dbLazyload('_dashboards');
            return $this->_dashboards;
        }

    }
