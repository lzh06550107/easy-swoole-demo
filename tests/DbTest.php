<?php

namespace Tests;

use EasySwoole\Mysqli\QueryBuilder;
use PHPUnit\Framework\TestCase;
use EasySwoole\ORM\DbManager;

class DbTest extends TestCase
{
    public function testCon()
    {
        $builder = new QueryBuilder();
        $builder->raw('select version()');
        $ret = DbManager::getInstance()->query($builder, true)->getResult();
        $this->assertArrayHasKey('version()', $ret[0]);
    }

    public function testDemo()
    {
        $builder = new QueryBuilder();
        $builder->get('user_list');
        // 获取全表记录
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testTablePre()
    {
        $builder = new QueryBuilder();
        $builder->setPrefix('user_')->get('list');
        // 表前缀演示
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testTotalCount()
    {
        $builder = new QueryBuilder();
//        $builder->setPrefix('user_')->withTotalCount()->where('userId', 1, '=')->get('list');
        $builder->setQueryOption('SQL_CALC_FOUND_ROWS')->where('userId', 1, '=')->get('user_list');
        // 获取记录总数
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        var_dump(DbManager::getInstance()->query($builder, true)->getTotalCount());
    }

    public function testField()
    {
        $builder = new QueryBuilder();
        $builder->fields('userId, userName')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        $builder->get('user_list', null, ['userId','userName']);
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testLimit()
    {
        $builder = new QueryBuilder();
        $builder->get('user_list', 1);
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        $builder->getOne('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testFromAndLimit()
    {
        $builder = new QueryBuilder();
        $builder->get('user_list', [1, 2]);
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testDistinctQuery()
    {
        $builder = new QueryBuilder();
        $builder->get('user_list', [1, 3], ['distinct userName','userAccount']);
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        var_dump(DbManager::getInstance()->getLastQuery()); // LZH 结果不是很理想，不能获取到sql语句
    }

    public function testWhereQuery()
    {
        $builder = new QueryBuilder();
        $builder->where('userName', 'xsk')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        // where查询2
        $builder->where('userId', 2, '>')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        // 多条件where
        $builder->where('userName', 'test2')->where('userAccount', 'test3')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        // whereIn, whereNotIn, whereLike，修改相应的operator(IN, NOT IN, LIKE)
        $builder->where('userId', [2,3], 'IN')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        // orWhere
        $builder->where('userId', 2)->orWhere('userName', 'xsk')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
        // 复杂where
        // 生成大概语句：where status = 1 AND (id > 10 or id < 2)
        $builder->where('state', 1)->where(' (userId > 3 or userId <2) ')->get('user_list');
        var_dump(DbManager::getInstance()->query($builder, true)->getResult());
    }

    public function testJoinQuery()
    {
        // https://www.easyswoole.com/Components/Mysqli/mysqli.html
        $builder = new QueryBuilder();

        // join。默认INNER JOIN
        $builder->join('table2', 'table2.col1 = getTable.col2')->get('getTable');
        $builder->join('table2', 'table2.col1 = getTable.col2', 'LEFT')->get('getTable');

        // join Where
        $builder->join('table2', 'table2.col1 = getTable.col2')->where('table2.col1', 2)->get('getTable');
    }

}
