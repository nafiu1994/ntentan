<?php
namespace ntentan\test_cases;

require_once 'lib/models/Model.php';
require_once 'tests/mocks/modules/users/Users.php';
require_once 'tests/mocks/modules/roles/Roles.php';
require_once 'tests/mocks/modules/departments/Departments.php';
require_once 'lib/Ntentan.php';
require_once 'lib/models/exceptions/ModelNotFoundException.php';
require_once 'lib/models/exceptions/DataStoreException.php';
require_once 'lib/caching/Cache.php';

/**
 * Test class for DataStore.
 * Generated by PHPUnit on 2010-08-12 at 08:44:31.
 */
abstract class SqlDatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var DataStore
     */
    protected $users;
    protected $roles;
    protected $departments;

    /**
     * Returns an instance of the datastore of the database being tested.
     */
    abstract protected function getInstance();

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_XmlDataSet(
            'tests/fixtures/sqldatabase.xml'
        );
    }

    protected function getSetUpOperation()
    {
        return $this->getOperations()->CLEAN_INSERT(TRUE);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->users = \ntentan\models\Model::load('users');
        $this->roles = \ntentan\models\Model::load('roles');
        $this->departments = \ntentan\models\Model::load('departments');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testSetModel()
    {
    	$this->assertEquals($this->users->dataStore->table, "users");
        $this->assertEquals($this->roles->dataStore->table, "roles");
        $this->assertEquals($this->departments->dataStore->table, "departments");
    }

    public function testDescribe()
    {
        $rolesDescription = array(
            'name' => 'roles',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => '',
                    'unique' => true,
                    'unique_violation_message' => 'Two roles cannot have the same name'
                ),
            )
        );

        $departmentsDescription = array(
            'name' => 'departments',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
            )
        );

        $usersDescription = array(
            'name' => 'users',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'username' => array(
                    'name' => 'username',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => '',
                    'unique' => true
                ),
                'password' => array(
                    'name' => 'password',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'role_id' => array(
                    'name' => 'role_id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'model' => 'roles',
                    'foreign_key' => true,
                    'field_name' => 'role_id',
                    'alias' => 'role'
                ),
                'firstname' => array(
                    'name' => 'firstname',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'lastname' => array(
                    'name' => 'lastname',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'othernames' => array(
                    'name' => 'othernames',
                    'type' => 'string',
                    'required' => false,
                    'length' => 255,
                    'comment' => ''
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => ''
                ),
                'email' => array(
                    'name' => 'email',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'phone' => array(
                    'name' => 'phone',
                    'type' => 'string',
                    'required' => false,
                    'length' => 64,
                    'comment' => ''
                ),
                'office' => array(
                    'name' => 'office',
                    'type' => 'integer',
                    'required' => false,
                    'length' => null,
                    'comment' => '',
                    'model' => 'departments',
                    'foreign_key' => true,
                    'field_name' => 'office',
                    'alias' => 'office'
                ),
                'last_login_time' => array(
                    'name' => 'last_login_time',
                    'type' => 'datetime',
                    'required' => false,
                    'length' => null,
                    'comment' => ''
                ),
                'is_admin' => array(
                    'name' => 'is_admin',
                    'type' => 'boolean',
                    'required' => false,
                    'length' => null,
                    'comment' => ''
                ),
            ),
            'belongs_to' => array (
                'role',
                'department'
            )
        );
        
        $this->assertEquals($this->roles->describe(), $rolesDescription);
        $this->assertEquals($this->departments->describe(), $departmentsDescription);
    }

    public function testDescribeModel()
    {
        $description = array(
            'tables' => array(
                'departments' => array(
                    'belongs_to' => array(),
                    'has_many' => array(
                        'users'
                    )
                ),
                'roles' => array(
                    'belongs_to' => array(),
                    'has_many' => array(
                        'users'
                    )
                ),
                'users' => array(
                    'belongs_to' => array(
                        'role',
                        array('department', 'as' => 'office')
                    ),
                    'has_many' => array()
                )
            )
        );

        $rolesDescription = $this->roles->dataStore->describeModel();
        $this->assertEquals($rolesDescription['roles'], $description['roles']);
        $usersDescription = $this->users->dataStore->describeModel();
        $this->assertEquals($usersDescription['users'], $description['users']);
        $departmentsDescription = $this->departments->dataStore->describeModel();
        $this->assertEquals($departmentsDescription['departments'], $description['departments']);
        //$this->assertEquals($this->departments->dataStore->describeModel(), $description);
    }

    public function testGetName()
    {
        $this->assertEquals($this->roles->getName(), 'roles');
        $this->assertEquals($this->users->getName(), 'users');
        $this->assertEquals($this->departments->getName(), 'departments');
    }

    public function testGet()
    {
        $rolesData = array(
            array('id' => '1', 'name' => 'System Administrator'),
            array('id' => '2', 'name' => 'System Auditor'),
            array('id' => '3', 'name' => 'Content Author'),
            array('id' => '4', 'name' => 'Site Member'),
        );

        $roleNameData = array(
            array('name' => 'System Administrator'),
            array('name' => 'System Auditor'),
            array('name' => 'Content Author'),
            array('name' => 'Site Member'),
        );

        $filteredRolesData = array(
            array('id' => '1', 'name' => 'System Administrator'),
            array('id' => '2', 'name' => 'System Auditor'),
        );

        $this->assertEquals($rolesData, $this->roles->get()->toArray());
        $this->assertEquals(true, is_object($this->roles->get()));
        $this->assertObjectHasAttribute('belongsTo', $this->roles->get());
        $this->assertEquals($rolesData, $this->roles->get()->getData());
        $this->assertEquals($this->roles->get('count'), '4');

        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id<' => 3
                    )
                )
            )->toArray()
        );

        $this->assertEquals(
            array('id' => '1', 'name' => 'System Administrator'),
            $this->roles->get(
                'first', array(
                    'conditions' => array(
                        'id' => 1
                    )
                )
            )->toArray()
        );

        $this->assertEquals(
            array('id' => '1', 'name' => 'System Administrator'),
            $this->roles->get(
                'first', array(
                    'conditions' => array(
                        'id<' => 2
                    )
                )
            )->toArray()
        );
        
        $this->assertEquals(
            $roleNameData,
            $this->roles->get(
                'all', array(
                    'fields' => array(
                        'name'
                    )
                )
            )->toArray()
        );
        
        $roles = $this->roles->get(
            'all', array(
                'fetch_related' => true
            )
        )->toArray();
        
        $this->assertEquals(
            $roles[1],
            array(
                'id' => 2,
                'name' => 'System Auditor',
                'users' => array(
                    array(
                        'id' => 3,
                        'username' => 'edonkor',
                        'password' => '92b9ef8d24335bb046db8e292e7de098',
                        'role_id' => '2',
                        'firstname' => 'Edward',
                        'lastname' => 'Donkor',
                        'othernames' => '',
                        'status' => '4',
                        'email' => 'edonkor@ntentan.com',
                        'phone' => null,
                        'office' => '2',
                        'last_login_time' => null,
                        'is_admin' => null
                    )
                )
            )
        );
        
        $users = $this->users->get('all', array('fetch_related'=>true, 'fetch_belongs_to'=>true))->toArray();
        $this->assertEquals(
            $users[0],
            array(
                'id' => '1',
                'username' => 'odadzie',
                'password' => 'df9aaa54a00098488bebbec623a88bab',
                'role' => array(
                    'id' => '1',
                    'name' => 'System Administrator'
                ),
                'firstname' => 'Osofo',
                'lastname' => 'Dadzie',
                'othernames' => null,
                'status' => '2',
                'email' => 'odadzie@ntentan.com',
                'phone' => null,
                'office' => array(
                    'id' => '1',
                    'name' => 'Software Developers',
                ),
                'last_login_time' => null,
                'is_admin' => '1'
            )
        );
    }

    public function testSetData()
    {
        $this->roles->setData(
            array('name' => 'Dummy Role')
        );
        $this->assertEquals($this->roles->getData(), array('name'=>'Dummy Role'));
        $this->roles->setData(
            array('id' => '2')
        );
        $this->assertEquals($this->roles->getData(),
            array(
                'id' => '2',
                'name' => 'Dummy Role'
            )
        );

        $this->roles->setData(
            array('name' => 'Dummiest Role'),
            true
        );
        
        $this->assertEquals($this->roles->getData(),
            array(
                'name' => 'Dummiest Role'
            )
        );
    }

    public function testSave()
    {
        
    }
    
    public function testUpdate()
    {
    	
    }
    
    public function testDelete()
    {
    	
    }
}
