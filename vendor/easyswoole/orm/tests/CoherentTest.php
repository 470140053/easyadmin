<?php
/**
 * 连贯操作
 * User: Siam
 * Date: 2019/10/23 0023
 * Time: 22:00
 */

namespace EasySwoole\ORM\Tests;

use EasySwoole\Mysqli\Exception\Exception;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use PHPUnit\Framework\TestCase;

use EasySwoole\ORM\Tests\models\TestUserListModel;

class CoherentTest extends TestCase
{
    /**
     * @var $connection Connection
     */
    protected $connection;
    protected $tableName = 'user_test_list';
    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $config = new Config(MYSQL_CONFIG);
        $this->connection = new Connection($config);
        DbManager::getInstance()->addConnection($this->connection);
        $connection = DbManager::getInstance()->getConnection();
        $this->assertTrue($connection === $this->connection);
    }
    public function testAdd()
    {
         $testUserModel = new TestUserListModel();
         $testUserModel->state = 1;
         $testUserModel->name = '仙士可';
         $testUserModel->age = 100;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);

         $testUserModel = new TestUserListModel();
         $testUserModel->state = 2;
         $testUserModel->name = 'Siam';
         $testUserModel->age = 18;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);

         $testUserModel = new TestUserListModel();
         $testUserModel->state = 2;
         $testUserModel->name = 'Siam';
         $testUserModel->age = 19;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);
    }

    public function testWhere()
    {
        $testUserModel = TestUserListModel::create();
        $get = $testUserModel->get([
         'state' => 1
        ]);
        $model =  TestUserListModel::create();
        $getCoherent = $model->where(['state' => 1])->get();

        // model里的where解析
        $getCoherent2Model = TestUserListModel::create();
        $getCoherent2      = $getCoherent2Model->where(['state' => 2])->get();

        $this->assertEquals($get->age, $getCoherent->age);
        $this->assertNotEquals($get->age, $getCoherent2->age);

        $getCoherent3 = TestUserListModel::create()->where($getCoherent2->id)->get();
        $this->assertEquals($getCoherent3->age, $getCoherent3->age);

        $getCoherent4 = TestUserListModel::create()->where([$getCoherent2->id, $getCoherent->id])->all();
        $this->assertEquals(count($getCoherent4), 2);

        // 走builder原生的where
        $getCoherent5 = TestUserListModel::create()->where('id', $getCoherent3->id, '=')->get();
        $this->assertEquals($getCoherent5->id, $getCoherent3->id);

        $getCoherent6 = TestUserListModel::create()->where('id', $getCoherent3->id, '!=')->get();
        $this->assertNotEquals($getCoherent6->id, $getCoherent3->id);

        // where null
        /** @var AbstractModel $model7 */
        $model7 = TestUserListModel::create();
        $test7 = $model7->where('name', null, 'is')->get();
        $this->assertEquals("SELECT  * FROM `user_test_list` WHERE  `name` is NULL LIMIT 1", $model7->lastQuery()->getLastQuery());
        $test7 = $model7->where('name', null, 'is not')->get();
        $this->assertEquals("SELECT  * FROM `user_test_list` WHERE  `name` is not NULL LIMIT 1", $model7->lastQuery()->getLastQuery());

    }

    public function testGroupAndAll()
    {
        $group = TestUserListModel::create()->field('sum(age) as age, `name`')->group('name')->all(null);

        foreach ($group as $one){
            if ($one->name == 'Siam'){
                $this->assertEquals($one->age, 18+19);
            }else{
                $this->assertEquals($one->age, 100);
            }
        }
    }

    public function testOrder()
    {
        $order = TestUserListModel::create()->order('id', 'DESC')->get();

        $this->assertEquals($order->age, 19);
    }

    public function testJoinData()
    {
        $res = TestUserListModel::create()->field('sum(age) as siam, `name`')->group('name')->all();
        $this->assertNotEmpty($res[0]->siam);
        $this->assertNotEmpty($res[0]['siam']);
    }

    public function testFind()
    {
        $groupDivField = TestUserListModel::create()->field('sum(age), `name`')->group('name')->all();
        $this->assertNotEmpty($groupDivField[0]['sum(age)']);

        $groupDivField = TestUserListModel::create()->field('sum(age), `name`')->group('name')->get();
        $this->assertNotEmpty($groupDivField['sum(age)']);
    }

    /** 别名时 判断isset */
    public function testFieldAlias()
    {
        $fieldAlias = TestUserListModel::create()->field(['name as teeeee'])->get();
        $this->assertTrue(isset($fieldAlias['teeeee']), true);
    }

    public function testColumn()
    {
        $res = TestUserListModel::create()->field('`name`, `age`')->order('age')->column();
        $this->assertIsArray($res);
        $this->assertTrue(count($res) > 0);
        $this->assertTrue($res[0] === '仙士可');

        $res = TestUserListModel::create()->field('`name`')->order('age')->column();
        $this->assertIsArray($res);
        $this->assertTrue(count($res) > 0);
        $this->assertTrue($res[0] === '仙士可');

        $res = TestUserListModel::create()->order('age')->column('name');
        $this->assertIsArray($res);
        $this->assertTrue(count($res) > 0);
        $this->assertTrue($res[0] === '仙士可');

        $res = TestUserListModel::create()->field('`name`')->where(['name' => mt_rand()])->order('age')->column('name');
        $this->assertTrue(is_null($res));

        $res = TestUserListModel::create()->field('`name`')->order('age')->column('age');
        $this->assertEquals(100, $res[0]);
        $this->assertEquals(19, $res[1]);
        $this->assertEquals(18, $res[2]);
    }

    public function testScalar()
    {
        $res = TestUserListModel::create()->field('`name`, `age`')->order('age')->scalar();
        $this->assertTrue($res === '仙士可');

        $res = TestUserListModel::create()->field('`name`')->order('age')->scalar();
        $this->assertTrue($res === '仙士可');

        $res = TestUserListModel::create()->order('age')->scalar('name');
        $this->assertTrue($res === '仙士可');

        $res = TestUserListModel::create()->field('`name`')->where(['name' => mt_rand()])->order('age')->scalar('name');
        $this->assertTrue(is_null($res));

        $res = TestUserListModel::create()->field('`name`')->order('age')->scalar('age');

        $this->assertEquals(100,$res);
    }

    public function testIndexBy()
    {
        $res = TestUserListModel::create()->order('age')->indexBy('age');
        $this->assertTrue(isset($res['100']['name']));
        $this->assertTrue($res['100']['name'] === '仙士可');
        $this->assertTrue($res['18']['name'] === 'Siam');

        $res = TestUserListModel::create()->field('`name`')->where(['name' => mt_rand()])->order('age')->indexBy('name');
        $this->assertTrue(is_null($res));

        $res = TestUserListModel::create()->field('`name`')->order('age')->indexBy('age');
        $this->assertTrue(is_null($res));
    }

    public function testAlias()
    {
        $res = TestUserListModel::create()->alias('siam')->where(['siam.name' => '仙士可'])->all();
        $this->assertEquals($res[0]->name, '仙士可');
    }

    public function testMax()
    {
        $max = TestUserListModel::create()->max('age');
        $this->assertEquals($max, 100);
    }

    public function testMin()
    {
        $min = TestUserListModel::create()->min('age');
        $this->assertEquals($min, 18);
    }

    public function testCount()
    {
        $count = TestUserListModel::create()->count();
        $this->assertEquals($count, 3);
    }

    public function testCountZero()
    {
        $count = TestUserListModel::create()->where('name', 'undefined')->count();
        $this->assertEquals($count, 0);
    }

    public function testAvg()
    {
        $avg = TestUserListModel::create()->avg('age');
        $this->assertEquals($avg, 45.6667);
    }
    public function testSum()
    {
        $sum = TestUserListModel::create()->sum('age');
        $this->assertEquals($sum, 100+18+19);
    }

    public function testWhereUpdate()
    {
        $res = TestUserListModel::create()->where(['age' => 18])->update([
            'name' => 'Siam18'
        ]);

        $user = TestUserListModel::create()->where(['age'  =>  18])->get();
        $this->assertEquals($user->name, 'Siam18');

        $res = TestUserListModel::create()->where(['age' =>  18])->update([
            'name' => 'Siam'
        ]);
    }

    public function testAllUpdate()
    {
        $res = TestUserListModel::create()->update([
            'name' => 'Siam'
        ], null, true);
        $this->assertEquals($res, true);
    }

    /**
     * 测试issue inc报错 测试结果正常
     * @throws Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function testUpdateInc()
    {
        $model = TestUserListModel::create();
        $res   = $model->update([
            'age' => QueryBuilder::inc(2),
        ], [
            'age' => 100
        ]);
        $this->assertEquals($res, true);

        $user = TestUserListModel::create()->get([
            'age' => 102
        ]);
        $this->assertInstanceOf(TestUserListModel::class, $user);

        $user->age = QueryBuilder::inc(3);
        $res = $user->update();
        $this->assertEquals($res, true);
        $this->assertEquals(1, $user->lastQueryResult()->getAffectedRows());
    }

    public function testWhereDelete()
    {
        $res = TestUserListModel::create()->where([
            'name' => 'Siam'
        ])->destroy();

        $this->assertEquals($res, true);
    }

    public function testTempTableName()
    {
        $model = TestUserListModel::create();
        $res = $model->tableName('test_user_model', true)->get();
        $this->assertEquals($model->lastQuery()->getLastQuery(), "SELECT  * FROM `test_user_model` LIMIT 1");

        $res2 = $model->get();
        $this->assertEquals($model->lastQuery()->getLastQuery(), "SELECT  * FROM `user_test_list` LIMIT 1");
    }

    public function testDeleteAll()
    {
        $res = TestUserListModel::create()->destroy(null, true);
        $this->assertIsInt($res);
    }
}