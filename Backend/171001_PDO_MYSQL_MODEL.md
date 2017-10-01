# PDO_MYSQL_MODEL 文档

**Author**:黄飞勤

**个人封装的PDO操作MySQL的Model类，代码（见附录）仅供参考，欢迎指出BUG**

---

## 更新注记
### 2017.9.23更新

 - 仿ThinkPHP3.2.3的模型用法进行开发，支持其大部分语法 
 - 使用prepare+execute对SQL操作进行预处理，参数绑定并执行
 - 额外封装了ThinkPHP常用的方法：dump函数，M函数，I函数

## 使用文档
注：可结合ThinkPHP3.2.3的文档参考使用。
### 1.初始化
配置文件初始化：
```
<?php
require_once "config.php";
require_once "PDO_MySQL.class.php";

$link = M("users");
```
参数初始化：
```
<?php
require_once "PDO_MySQL.class.php";
$dbConfig = array(...);//具体键值参数信息参照配置文件格式
$link = M("users",$dbConfig);
```
**推荐使用配置文件的方法**，支持的配置信息：
```
define("DB_HOST",'127.0.0.1');   //服务器地址
define("DB_USER",'root');        //用户名
define('DB_PWD','root');         //密码
define('DB_NAME','chat');        //数据库名
define('DB_PORT','3306');        //端口号
define('DB_TYPE','mysql');       //数据库类型
define('DB_CHARSET','utf8');     //编码格式
define('DB_DEBUG',true);         //是否开启DEBUG模式，系统上线关闭DEBUG模式
define('MYSQL_LOG','/mysql.log');//定义mysql的log文件路径，请先确保有读权限
```
其中，要定义`MYSQL_LOG`，请先开启mysql的通用查询日志（general_log），开启后才能使用getLastLog()函数，而且这会消耗mysql很大的性能，**这一项仅仅为了debug**。
>只有general_log才是记录所有的操作日志,不过会耗费数据库5%-10%的性能,所以一般没什么特别需要,大多数情况是不开的。
<p id="general_log">开启MySQL通用查询日志</p>

```
//此方法重启后失效，想永久生效需修改配置文件。
//linux上mysql的配置文件路径示例：/etc/mysql/mysql.conf.d/mysqld.cnf
mysql> set global general_log=1;
mysql> set global general_log_file=/path/to/mysql.log
```
### 2.支持的连贯操作
#### 1.WHERE
##### 1.字符串条件
使用字符串条件直接查询和操作,例如:
```
$User	=	M("User");	//	实例化User对象
$User->where('type=1	AND	status=1')->select();
```
最后生成的SQL语句是
```
SELECT	*	FROM	think_user	WHERE	type=1	AND	status=1
```
使用字符串条件的时候,建议配合预处理机制,确保更加安全,例如:
```
$Model->where("id=%d	and	username='%s'	and	xx='%f'",array($id,$username,$xx))->select();
```
或者使用:
```
$Model->where("id=%d	and	username='%s'	and	xx='%f'",$id,$username,$xx)->select();
```
如果 $id 变量来自用户提交或者URL地址的话,如果传入的是非数字类型,则会强制格式化为数字格式后进行查询操作。
字符串预处理格式类型支持指定数字、字符串等,具体可以参考vsprintf方法的参数说明。
##### 2.数组条件
数组条件的where用法是ThinkPHP推荐的用法。

支持普通查询
```
$User	=	M("User");	//	实例化User对象
$map['name']	=	'thinkphp';
$map['status']	=	1;
//	把查询条件传入查询方法
$User->where($map)->select();	
```
最后生成的SQL语句是
```
SELECT	*	FROM	think_user	WHERE	`name`='thinkphp'	AND	status=1
```
支持[表达式查询](#expression)
```
$map['字段1']		=	array('表达式','查询条件1');
$map['字段2']		=	array('表达式','查询条件2');
$Model->where($map)->select();	//	也支持
```
支持多次调用。
#### 2.TABLE
除了数据表前缀，支持ThinkPHP支持的所有table用法。
#### 3.ALIAS
支持ThinkPHP支持的所有alias用法。
#### 4.FIELD
用于查询
```
$Model->field('id,title,content')->select();
```
可以给某个字段设置别名，例如：
```
$Model->field('id,nickname as name')->select();
```
使用SQL函数
```
$Model->field('id,SUM(score)')->select();
```
使用数组参数
```
$Model->field(array('id','title','content'))->select();
```
数组方式的定义可以为某些字段定义别名，例如：
```
$Model->field(array('id','nickname'=>'name'))->select();
```
对于一些更复杂的字段要求，数组的优势则更加明显，例如：
```
$Model->field(array('id','concat(name,'-',id)'=>'truename','LEFT(title,7)'=>'sub_title'))->select();
```
执行的SQL相当于：
```
SELECT id,concat(name,'-',id) as truename,LEFT(title,7) as sub_title FROM table
```
获取所有字段和过滤字段暂为支持。
#### 5.ORDER
用法与ThinkPHP相同
#### 6.LIMIT
用法与ThinkPHP相同
#### 7.PAGE
只支持两个数字参数的写法：
```
page(2,10)  //表示单页量为10，取第二页，即取出符合条件的第11-20条数据
```
#### 8.GROUP
用法与ThinkPHP相同
#### 9.HAVING
用法与ThinkPHP相同
#### 10.JOIN
（跟ThinkPHP有较大区别）
只传一个字符串，默认INNER JOIN
```
M('t1')->join('t2 on t1.id=t2.id')->select();
//相当于select * from t1 INNER JOIN t2 on t1.id=t2.id
```
传数组
（前两个元素必须是字符串，第二个元素须是"INNER","LEFT","RIGHT","FULL"之一）
```
M('t1')->join(array('t2 on t1.id=t2.id','LEFT'))->select();
//相当于select * from t1 LEFT JOIN t2 on t1.id=t2.id
```
支持多次调用。
#### 11.fetchSql
用法与ThinkPHP相同
### 3.支持的CURD操作（增删查改）
#### 1.数据读取
##### find()
读取数据（仅一条）
```
$data = $User->where('status=1 AND name="thinkphp"')->find();
```
查询成功返回一维数组，如果无数据返回NULL，失败返回false
##### select()
读取数据集
```
$list = $User->where('status=1')->order('create_time')->limit(10)->select();
```
查询成功返回二维数组，如果无数据返回NULL，失败返回false
#### 2.数据插入
##### add()
传入数组
```
$User = M("User"); // 实例化User对象
$data['name'] = 'ThinkPHP';
$data['email'] = 'ThinkPHP@gmail.com';
$User->add($data);
```
插入成功返回插入数据的ID，失败返回false
##### addAll()
批量写入（须传入二维数组）
```
$dataList[] = array('name'=>'thinkphp','email'=>'thinkphp@gamil.com');
$dataList[] = array('name'=>'onethink','email'=>'onethink@gamil.com');
$User->addAll($dataList);
```
插入成功返回其中第一条插入数据的ID，失败返回false
#### 3.数据更新
返回值都是影响的记录数，失败返回false。
##### save()
```
$User = M("User"); // 实例化User对象
// 要修改的数据对象属性赋值
$data['name'] = 'ThinkPHP';
$data['email'] = 'ThinkPHP@gmail.com';
$User->where('id=5')->save($data); // 根据条件更新记录
```
为了保证数据库的安全，避免出错更新整个数据表，如果没有任何更新条件，数据对象本身也不包含主键字段的话，save方法不会更新任何数据库的记录。
除非使用下面的方式：
```
$User = M("User"); // 实例化User对象
// 要修改的数据对象属性赋值
$data['id'] = 5;
$data['name'] = 'ThinkPHP';
$data['email'] = 'ThinkPHP@gmail.com';
$User->save($data); // 根据条件保存修改的数据
```
如果id是数据表的主键的话，系统自动会把主键的值作为更新条件来更新其他字段的值
##### setField()
如果只是更新个别字段的值，可以使用setField 方法：
```
$User = M("User"); // 实例化User对象
// 更改用户的name值
$User-> where('id=5')->setField('name','ThinkPHP');
```
setField方法支持同时更新多个字段，只需要传入数组即可(这将与save相同)
```
$User = M("User"); // 实例化User对象
// 更改用户的name和email的值
$data = array('name'=>'ThinkPHP','email'=>'ThinkPHP@gmail.com');
$User-> where('id=5')->setField($data);
```
而对于统计字段（通常指的是数字类型）的更新，还提供了setInc 和setDec 方法。
```
$User = M("User"); // 实例化User对象
$User->where('id=5')->setInc('score',3); // 用户的积分加3
$User->where('id=5')->setInc('score'); // 用户的积分加1
$User->where('id=5')->setDec('score',5); // 用户的积分减5
$User->where('id=5')->setDec('score'); // 用户的积分减1
```
不支持延迟更新。
#### 4.数据删除
返回是删除的记录数，删除失败返回false。

不支持传入主键删除数据（与ThinkPHP有区别）

普通用法：
```
$User->where('status=0')->delete(); // 删除所有状态为0的用户数据
```
高级用法，delete与join的结合使用：
```
$User=M('t1');
$User->join('t2 on t2.id = t1.id')->delete('t1');
//DELETE t1 FROM `t1` INNER JOIN t2 on t2.id = t1.id
//表示删除t1表中id与t2的id相同的数据
//delete方法中的参数用于指定删除哪个表中符合条件的数据
```
### 4.查询语言
与ThinkPHP最大的不同在于使用了"_tosingle""_tomulti"关键词：
>**"_tosingle"=>true**：进行单条件对应查询，示例：
`$map['status&title'] =array('1','thinkphp','_tomulti'=>true);`
即`status=1 AND title='thinkphp'`<br/>
**"_tomulti"=>true**：单字段进行多条件查询，示例：
`$map['name'] = array('ThinkPHP',array('like','%a%'),'or','_tomulti'=>true);`
即`name='ThinkPHP' OR name LIKE '%a%'`

其中"_tomulti"关键词主要是为了与表达式查询做区分，举个例子：`$map['name'] = array('ThinkPHP','is null')`和`$map['name'] = array('exp','is null')`，后者本来是表达式查询，但是还可以被辨别为`name='exp' AND name='is null'`。

#### 1.查询方法
支持字符串查询和数组查询，懒得支持对象查询。
以及**推荐使用数组查询**，因为where查询直接传字符串不做任何检查（不安全所以不支持）
<h4 id="expression">2.表达式查询</h4>
用法与ThinkPHP相同

#### 3.快捷查询
与ThinkPHP语法的不同处：
**"不同字段不同的查询条件"处不是指定`'_multi'=>true`，而是`'_tosingle'=>true`**
比如：`$map['status&title'] =array('1','thinkphp','_tosingle'=>true);`
=>`status=1 AND title='thinkphp'`
如果不指定：`$map['status&title'] =array('1','thinkphp');`
=>`(status=1 AND status='thinkphp') AND (title=1 AND title='thinkphp')`

#### 4.区间查询
文档原文：
>区间查询的条件可以支持普通查询的所有表达式，也就是说类似LIKE、GT和EXP这样的表达式都可以支
持。另外区间查询还可以支持更多的条件，只要是针对一个字段的条件都可以写到一起，例如：
`$map['name'] = array(array('like','%a%'), array('like','%b%'), array('like','%c%'), 'ThinkPHP','or');`

在这里需要加'_tomulti'=>true，用于与表达式查询做区分。
```
$map['name'] = array(array('like','%a%'), array('like','%b%'), array('like','%c%'), 'ThinkPHP','or','_tomulti'=>true);
```
最后的查询条件是：
```
( name LIKE '%a%') OR ( name LIKE '%b%') OR ( name LIKE '%c%') OR ( name = 'ThinkPHP')
```

#### 5.组合查询
用法与ThinkPHP相同

#### 6.统计查询
用法与ThinkPHP相同

#### 7.SQL查询
用法与ThinkPHP相同

#### 8.动态查询
不支持动态查询（感觉没什么必要）

#### 9.子查询
用法与ThinkPHP相同

### 5.事务驱动
**仅对支持事务的数据库驱动起作用。**

 1. 开启事务/startTrans
 2. 检查是否在一个事务内/inTrans
 3. 事务回滚/rollback
 4. 事务提交/commit

使用示例：
```
$link = M("test");
dump($link->inTrans());//输出false
$link->startTrans();
dump($link->inTrans());//输出true
$link->where('id=4')->save(array('status'=>array('exp','status+100')));
$link->rollback();//事务回滚，事务内的更新无效
dump($link->inTrans());//输出false
$link->where('id=5')->save(array('status'=>array('exp','status+100')));//处于事务外，更新立即生效
```

### 6.其他
#### 1.M函数
用于初始化对一个数据表的连接。
```
//连接users数据表，如无table方法切换数据表，默认对此表进行操作。
$link = M("users");
```
#### 2.I函数
暂时只支持get和post（需要再说）
使用htmlspecialchars()对数据进行预处理
#### 3.dump()
高仿tp的dump方法。
#### 4.getLastSql() / _sql() / getLastLog()
`getLastSql()`和`_sql()`等效，用于打印最后一条执行的**SQL语句**（由系统封装）
`getLastLog()`则是读取**MySQL通用查询日志**记录的最后一条SQL语句

其中，当且仅当DEBUG模式开启，以上方法才生效。注意，使用`getLastLog()`须开启MySQL的通用查询日志以及指定MySQL通用日志目录（见[开启MySQL通用查询日志](#general_log)）。


## 参考链接
[ThinkPHP3.2.3完全开发手册在线文档][1]


## 附录（代码）

demo.php(使用示例)
```
<?php
require_once "config.php";
require_once "PDO_MySQL.class.php";

$link = M("table");
dump($link->select());
dump($link->_sql());

?>
```

config.php(配置文件)
```
<?php 
//数据库配置信息
define("DB_HOST",'127.0.0.1');
define("DB_USER",'root');
define('DB_PWD','root');
define('DB_NAME','db_name');
define('DB_PORT','3306');
define('DB_TYPE','mysql');
define('DB_CHARSET','utf8');
define('DB_DEBUG',true);//是否开启DEBUG模式，请在系统上线后关闭DEBUG模式
//define('MYSQL_LOG','/path/to/mysql.log');//定义mysql的log文件路径，请先确保有读权限
```

PDO_MySQL.class.php
```
<?php
//header('content-type:text/html;charset=utf-8');
class PDOMySQL
{
    private static $config=array();//设置连接参数，配置信息
    private static $link=null;//保存连接标识符
    private static $dbdebug=false;//是否开启DEBUG模式
    private static $table='';//记录操作的数据表名
    private static $columns=array();//记录表中字段名
    private static $pconnect=false;//是否开启长连接
    private static $dbVersion=null;//保存数据库版本
    private static $connected=false;//是否连接成功
    private static $PDOStatement=null;//保存PDOStatement对象
    private static $queryStr=null;//保存最后执行的操作
    private static $error=null;//报错错误信息
    private static $lastInsertId=null;//保存上一步插入操作产生AUTO_INCREMENT
    private static $numRows=0;//上一步操作产生受影响的记录的条数
    private static $MySQL_log='';//MySQL的日志文件路径

    private static $fieldString='';
    private static $joinString='';
    private static $whereString='';
    private static $groupString='';
    private static $havingString='';
    private static $orderString='';
    private static $limitString='';
    private static $aliasString='';
    private static $tmp_table='';
    private static $fetchSql=false;

    private static $whereStringArray=array();
    private static $whereValueArray=array();

    private static $SQL_logic = array('AND', 'OR', 'XOR');//SQL语句支持的逻辑运算符

    /**
     * 构造函数，连接PDO
     * @param string $dbtable
     * @param array $dbConfig
     * @return boolean
     * $dbConfig数组至少需要指定hostname、username、password、dsn
     * 如果想开启debug模式，指定$dbConfig["DB_DEBUG"]=true
     * 可以通过$dbConfig["MYSQL_LOG"]='/path/to/mysql.log'指定mysql的日志文件路径
     */
    public function __construct($dbtable, $dbConfig = '')
    {
        if (!class_exists("PDO")) {
            self::throw_exception("不支持PDO，请先开启");
            return false;
        }
        if ($dbConfig!=''&&!is_array($dbConfig)) {
            self::throw_exception("数据库配置信息参数需使用数组形式传入");
            return false;
        }
        if ($dbConfig=='') {
            if (defined('DB_DEBUG')&&DB_DEBUG===true) {
                self::$dbdebug = true;
            }
            if (defined('MYSQL_LOG')&&is_string(MYSQL_LOG)) {
                self::$MySQL_log = MYSQL_LOG;
            }
            $dbConfig=array(
                'hostname'=>DB_HOST,
                'username'=>DB_USER,
                'password'=>DB_PWD,
                'database'=>DB_NAME,
                'hostport'=>DB_PORT,
                'dbms'=>DB_TYPE,
                'dsn'=>DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME
            );
        } else {
            if (isset($dbConfig['DB_DEBUG'])&&$dbConfig['DB_DEBUG']) {
                self::$dbdebug = true;
                unset($dbConfig['DB_DEBUG']);
            }
            if (isset($dbConfig['MYSQL_LOG'])&&is_string($dbConfig['MYSQL_LOG'])) {
                self::$MySQL_log = $dbConfig['MYSQL_LOG'];
                unset($dbConfig['MYSQL_LOG']);
            }
        }
        if (empty($dbConfig['hostname'])) {
            self::throw_exception('没有定义数据库配置，请先定义');
            return false;
        }
        self::$config=$dbConfig;
        if (empty(self::$config['params'])) {
            self::$config['params']=array();
        }
        if (!isset(self::$link)) {
            $configs=self::$config;
            if (self::$pconnect) {
                //开启长连接，添加到配置数组中
                $configs['params'][constant("PDO::ATTR_PERSISTENT")]=true;
            }
            try {
                self::$link=new PDO($configs['dsn'], $configs['username'], $configs['password'], $configs['params']);
            } catch (PDOException $e) {
                self::throw_exception($e->getMessage());
                return false;
            }
            if (!self::$link) {
                self::throw_exception('PDO连接错误');
                return false;
            }
            if (!self::in_db($dbtable)) {
                self::throw_exception('数据库'.DB_NAME.'中不存在'.$dbtable.'表');
                return false;
            }
            self::$table=$dbtable;
            self::$link->exec('SET NAMES '.DB_CHARSET);
            self::set_columns($dbtable);
            self::$dbVersion=self::$link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
            self::$connected=true;
            unset($configs);
        }
    }

    /**
     * 判断数据表是否存在
     * @param string $dbtable
     * @return boolean
     */
    private function in_db($dbtable)
    {
        $stmt = self::$link->query("show tables");
        foreach ($stmt as $row) {
            if ($dbtable==$row[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * 初始化时获取数据表字段，标注主键，存储在self::$columns中
     * @param string $dbtable
     */
    private function set_columns($dbtable)
    {
        $stmt = self::$link->query("SHOW COLUMNS FROM `".$dbtable."`");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $array) {
            if ($array['Key']=='PRI') {
                self::$columns['PRI']=$array['Field'];
            }
            self::$columns[] = $array['Field'];
        }
    }

    /**
     * 解析where子句(懒得支持传对象参数)
     * @param string/array/Variable-length_argument_lists $where
     * @return $this
     */
    public function where(...$where)
    {
        $param_number = count($where);
        if (!is_string($where[0])&&!is_array($where[0])) {
            self::throw_exception("where子句的参数只支持字符串和数组");
            return false;
        }
        if (is_string($where[0])) {
            if ($param_number==1) {
                $whereSubString = '( '.$where[0].' )';
            } elseif ($param_number>1) {
                if (is_array($where[1])) {
                    $whereSubString = vsprintf($where[0], $where[1]);
                } else {
                    $param_array=array();
                    for ($i=1; $i<$param_number; $i++) {
                        $param_array[] = $where[$i];
                    }
                    $whereSubString = sprintf($where[0], ...$param_array);
                }
            }
            $whereSubString = '( '.$whereSubString.' )';
        } elseif (is_array($where[0])) {
            if ($param_number>1) {
                self::throw_exception("where子句传入数组参数仅支持一个参数");
                return false;
            }
            $whereSubString = self::parseWhereArrayParam($where[0]);
        }
        self::$whereStringArray[] = $whereSubString;
        return $this;
    }

    /**
     * 总拼接where子句的SQL字符串
     */
    public function parseWhere()
    {
         $length = count(self::$whereStringArray);
        if ($length == 0) {
            return;
        }
        if ($length>1) {
            self::$whereString = ' WHERE ( '.self::$whereStringArray[0].' )';
            for ($i=1; $i<$length; $i++) {
                self::$whereString .= ' AND ( '.self::$whereStringArray[$i].' )';
            }
        } else {
            self::$whereString = ' WHERE '.self::$whereStringArray[0];
        }
    }

    /**
     * 解析table子句
     * @param string/array $table
     * @return $this
     */
    public function table($table)
    {
        if (is_string($table)) {
            self::$tmp_table = $table;
        } elseif (is_array($table)) {
            if (count($table)==0) {
                self::throw_exception('table子句参数不能传空数组');
                return false;
            }
            self::$tmp_table = '';
            foreach ($table as $key => $val) {
                if (is_string($key)) {
                    $match_times=preg_match('/\./', $key);
                    if (0===$match_times) {
                        self::$tmp_table .= '`'.trim($key).'` AS `'.trim($val).'`,';
                    } elseif (1===$match_times) {
                        self::$tmp_table .= trim($key).' AS `'.trim($val).'`,';
                    } else {
                        self::throw_exception('table子句数组参数的键值非法："'.$key.'"');
                        return false;
                    }
                } else {
                    self::$tmp_table .= '`'.trim($val).'`,';
                }
            }
            self::$tmp_table = rtrim(self::$tmp_table, ',');
        } else {
            self::throw_exception('table子句的参数类型错误："'.$table.'"');
            return false;
        }
        return $this;
    }

    /**
     * 解析alias子句
     * @param string $alias
     * @return $this
     */
    public function alias($alias)
    {
        if (is_string($alias)&&$alias!='') {
            self::$aliasString = ' AS `'.$alias.'`';
        } else {
            self::throw_exception('alias子句的参数须是字符串');
            return false;
        }
        return $this;
    }

    /**
     * 解析field子句
     * @param string/array $field
     * @return $this
     */
    public function field($field)
    {
        if (!is_string($field)&&!is_array($field)) {
            self::throw_exception("field子句的参数只支持字符串和数组");
            return false;
        }
        if (is_array($field)) {
            foreach ($field as $key => $val) {
                if (is_int($key)) {
                    $after_process_val = self::addSpecialChar($val);
                    self::$fieldString .= $after_process_val.',';
                } else {
                    $after_process_key = self::addSpecialChar($key);
                    $after_process_val = self::addSpecialChar($val);
                    self::$fieldString .= $after_process_key.' AS '.$after_process_val.',';
                }
            }
            self::$fieldString = rtrim(self::$fieldString, ',');
        }
        if (is_string($field)) {
            if ($field=='*') {
                self::$fieldString = ' *';
                return $this;
            }
            $field_array = explode(',', $field);
            $length = count($field_array);
            for ($i=0; $i<$length; $i++) {
                $field_array[$i] = self::addSpecialChar($field_array[$i]);
            }
            self::$fieldString = implode(',', $field_array);
        }
        self::$fieldString = ' '.self::$fieldString;
        return $this;
    }

    /**
     * 解析order子句
     * @param string/array $order
     * @return $this
     */
    public function order($order)
    {
        if (!is_string($order)&&!is_array($order)) {
            self::throw_exception("order子句的参数只支持字符串和数组");
            return false;
        }
        if (is_string($order)) {
            self::$orderString = ' ORDER BY '.$oreder;
        }
        if (is_array($order)) {
            self::$orderString = ' ORDER BY ';
            foreach ($order as $key => $val) {
                if (is_int($key)) {
                    self::$orderString .= '`'.trim($val).'`,';
                } else {
                    if (strtolower($val)!='desc'&&strtolower($val)!='asc') {
                        self::throw_exception("order子句请使用desc或asc关键词指定排序，默认为asc，出现未知字符");
                        self::$orderString = '';
                        return false;
                    }
                    self::$orderString .= '`'.trim($key).'` '.$val.',';
                }
            }
            self::$orderString = rtrim(self::$orderString, ',');
        }
        return $this;
    }
    
    /**
     * 解析limit子句
     * @param int/string/Variable-length_argument_lists $limit
     * @return $this
     * 示例：limit(10)/limit('10,25')/limit(10,25)
     */
    public function limit(...$limit)
    {
        $param_number = count($limit);
        if ($param_number==1) {
            if (!is_int($limit[0])&&!is_string($limit[0])) {
                self::throw_exception("limit子句的参数非法");
                return false;
            }
            if (is_string($limit[0])) {
                if (preg_match('/^\d+,\d+$/', $limit[0])==0&&preg_match('/^\d+$/', $limit[0])==0) {
                    self::throw_exception("limit子句的参数非法");
                    return false;
                }
            }
            self::$limitString = ' LIMIT '.$limit[0];
        } elseif ($param_number==2) {
            for ($i=0; $i<2; $i++) {
                if (!is_int($limit[$i])) {
                    self::throw_exception("limit子句的参数非法");
                    return false;
                }
            }
            self::$limitString = ' LIMIT '.$limit[0].','.$limit[1];
        } else {
            self::throw_exception("limit子句的参数数量必须为一或两个");
            return false;
        }
        return $this;
    }

    /**
     * 解析page子句
     * @param int $page_number
     * @param int $amount
     * @return $this
     * 示例：page(2,10)，只支持两个数字参数的写法，此处表示取出第11-20条数据（页码为2，单页显示量,10）
     * 不支持limit和page配合使用
     */
    public function page($page_number, $amount)
    {
        if (!is_numeric($page_number)||!is_numeric($amount)) {
            self::throw_exception("page方法只支持两个数字参数的写法");
            return false;
        }
        $start = ($page_number-1) * $amount;
        self::$limitString = ' LIMIT '.$start.','.$amount;
        return $this;
    }

    /**
     * 解析group子句
     * @param string $group
     * @return $this
     */
    public function group($group)
    {
        if (!is_string($group)) {
            self::throw_exception("group子句的参数只支持字符串");
            return false;
        }
        self::$groupString = ' GROUP BY '.$group;
        return $this;
    }
    
    /**
     * 解析having子句
     * @param string $having
     * @return $this
     */
    public function having($having)
    {
        if (!is_string($having)) {
            self::throw_exception("having子句的参数只支持字符串");
            return false;
        }
        self::$havingString = ' HAVING BY '.$having;
        return $this;
    }

    /**
     * 解析join子句
     * 传字符串默认INNER  JOIN，传数组时第二个元素指定"LEFT""RIGHT""FULL"进行左右全连接的设置
     * 与ThinkPHP有差异
     * @param string $join
     * @return $this
     */
    public function join($join)
    {
        if (!is_string($join)&&!is_array($join)) {
            self::throw_exception("join子句的参数只支持字符串和数组");
            return false;
        }
        if (is_string($join)) {
            self::$joinString .= ' INNER JOIN '.$join;
        } else {
            if (!is_string($join[0])||!is_string($join[1])) {
                self::throw_exception("join子句中的数组参数的前两个元素必须都是字符串");
                return false;
            }
            self::$joinString .= ' '.$join[1].' JOIN '.$join[0];
        }
        return $this;
    }

    /**
     * fetchSql用于直接返回SQL而不是执行查询,适用于任何的CURD操作方法
     * @param boolean $fetchSql
     * @return $this
     */
    public function fetchSql($fetchSql = false)
    {
        self::$fetchSql = $fetchSql;
        return $this;
    }

    /**
     * 统计查询之计数/count
     * @param string $field
     * @return number
     * 示例：SELECT COUNT(*) AS tp_count FROM `users` LIMIT 1
     *      SELECT COUNT(id) AS tp_count FROM `users` LIMIT 1
     */
    public function count($field = '*')
    {
        self::$fieldString = ' COUNT('.$field.') AS f_count';
        self::$limitString = ' LIMIT 1';
        $res = self::select();
        return $res[0]['f_count'];
    }

    /**
     * 统计查询之获取最大值/max
     * @param string $field
     * @return number
     * 示例：SELECT MAX(id) AS tp_max FROM `users` LIMIT 1
     */
    public function max($field)
    {
        self::$fieldString = ' MAX('.$field.') AS f_max';
        self::$limitString = ' LIMIT 1';
        $res = self::select();
        return $res[0]['f_max'];
    }

    /**
     * 统计查询之获取最小值/min
     * @param string $field
     * @return number
     * 示例：SELECT MIN(id) AS tp_min FROM `test` WHERE ( id>34 ) LIMIT 1
     */
    public function min($field)
    {
        self::$fieldString = ' MIN('.$field.') AS f_min';
        self::$limitString = ' LIMIT 1';
        $res = self::select();
        return $res[0]['f_min'];
    }

    /**
     * 统计查询之获取平均值/avg
     * @param string $field
     * @return number
     * 示例：SELECT AVG(id) AS tp_avg FROM `test` LIMIT 1
     */
    public function avg($field)
    {
        self::$fieldString = ' AVG('.$field.') AS f_avg';
        self::$limitString = ' LIMIT 1';
        $res = self::select();
        return $res[0]['f_avg'];
    }

    /**
     * 统计查询之求和/sum
     * @param string $field
     * @return number
     */
    public function sum($field)
    {
        self::$fieldString = ' SUM('.$field.') AS f_sum';
        self::$limitString = ' LIMIT 1';
        $res = self::select();
        return $res[0]['f_sum'];
    }

    /**
     * buildSql:构建select的SQL语句，用于子查询
     * @param string $field
     * @return string
     */
    public function buildSql()
    {
        $sqlString = '';
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        self::$fieldString = self::$fieldString=='' ? ' *' : self::$fieldString;
        self::parseWhere();
        $sqlString .= 'SELECT'.self::$fieldString.' FROM '.$table_name.self::$joinString.self::$whereString.self::$groupString.self::$havingString.self::$orderString.self::$groupString.self::$limitString;
        $buildSql = self::replaceSpecialChar('/\?/', self::$whereValueArray, $sqlString);
        self::clearSubString();
        return '( '.$buildSql.' )';
    }

    /**
     * find方法/查询数据(一条)
     * @param $primary_key_value 用于主键查询
     * @return array 查询成功返回数据(数组),查无返回NULL，查询出错返回false
     */
    public function find($primary_key_value = '')
    {
        $sqlString = '';
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        if ($primary_key_value!='') {
            self::$whereStringArray[] = '`'.self::$columns['PRI'].'` = ?';
            self::$whereValueArray[] = $primary_key_value;
        }
        self::$limitString = ' LIMIT 1';
        self::$fieldString = self::$fieldString=='' ? ' *' : self::$fieldString;
        self::parseWhere();
        $sqlString .= 'SELECT'.self::$fieldString.' FROM '.$table_name.self::$joinString.self::$whereString.self::$groupString.self::$havingString.self::$orderString.self::$groupString.self::$limitString;
        $res = self::query($sqlString, true);
        return $res;
    }

    /**
     * select方法/查询数据集
     * @param $query=true 是否进行查询/否则仅构建SQL
     * @return array/string 查询成功返回数据(二维数组),查无返回NULL，查询出错返回false
     */
    public function select($query = true)
    {
        $sqlString = '';
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        self::$fieldString = self::$fieldString=='' ? ' *' : self::$fieldString;
        self::parseWhere();
        $sqlString .= 'SELECT'.self::$fieldString.' FROM '.$table_name.self::$joinString.self::$whereString.self::$groupString.self::$havingString.self::$orderString.self::$groupString.self::$limitString;
        if (false===$query) {
            self::$fetchSql = true;
        }
        $res = self::query($sqlString);
        return $res;
    }

    /**
     * add方法/插入一条数据
     * @param array $data
     * @return 插入成功返回id值，失败返回false
     */
    public function add($data = '')
    {
        $field_str = '';
        if ($data!='') {
            if (!is_array($data)) {
                self::throw_exception('add方法只支持传入数组');
                return false;
            }
            $length = count($data);
            if ($length===0) {
                $placeholder = '';
            } else {
                foreach ($data as $key => $val) {
                    $field_str .= '`'.$key.'`,';
                    self::$whereValueArray[] = $val;
                }
                $field_str = rtrim($field_str, ',');
                $placeholder = '?';
                for ($i=1; $i<$length; $i++) {
                    $placeholder .= ',?';
                }
            }
        } else {
            $placeholder = '';
        }
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table;
        } else {
            $table_name = '`'.self::$table.'`';
        }
        $sqlString = 'INSERT INTO '.$table_name.' ('.$field_str.') VALUES ('.$placeholder.')';
        self::execute($sqlString);
        $res = self::$link->lastInsertId();
        return $res;
    }

    /**
     * addAll方法/批量写入数据
     * @param array $dataList
     * @return 插入成功返回id值(第一条插入数据的id值)，失败返回false
     * 示例：INSERT INTO `users` (`user_id`,`password`) VALUES ('thinkphp','thinkphp@gamil.com')
     *      INSERT INTO `users` (`user_id`,`password`) VALUES ('thinkphp','thinkphp@gamil.com'),('onethink','onethink@gamil.com')
     */
    public function addAll($dataList)
    {
        if (!is_array($dataList)) {
            self::throw_exception('addAll方法只支持传入数组');
            return false;
        }
        $field_str = '';
        $fieldList = array();
        $number = count($dataList);
        $valueListStr = '';
        if ($number===0) {
            self::throw_exception('addAll方法请勿传入空数组');
            return false;
        }
        if (!isset($dataList[$number-1])) {
            self::throw_exception('addAll方法传入的二维数组参数非法(须是索引数组)');
            return false;
        }
        if (!is_array($dataList[0])) {
            self::throw_exception('addAll方法传入的二维数组参数非法(数组第一个元素非数组)');
            return false;
        }
        $number_field = count($dataList[0]);
        if ($number_field==0) {
            $valueListStr .= '()';
            for ($i=1; $i<$number; $i++) {
                if ($dataList[$i]!=array()) {
                    self::throw_exception('addAll方法传入的二维数组参数非法');
                    return false;
                }
                $valueListStr .= ',()';
            }
        } else {
            $valueStr = '(';
            foreach ($dataList[0] as $key => $val) {
                $fieldList[] = $key;
                self::$whereValueArray[] = $val;
                $field_str .= $key.',';
                $valueStr .= '?,';
            }
            $field_str = rtrim($field_str, ',');
            $valueStr = rtrim($valueStr, ',');
            $valueStr .= ')';
            $valueListStr .= $valueStr;
            for ($i=1; $i<$number; $i++) {
                for ($j=0; $j<$number_field; $j++) {
                    self::$whereValueArray[] = $dataList[$i][$fieldList[$j]];
                }
                $valueListStr .= ','.$valueStr;
            }
        }
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table;
        } else {
            $table_name = '`'.self::$table.'`';
        }
        $sqlString = 'INSERT INTO '.$table_name.' ('.$field_str.') VALUES '.$valueListStr;
        self::execute($sqlString);
        $res = self::$link->lastInsertId();
        return $res;
    }

    /**
     * setField方法/更新字段
     * @param array/string/Variable-length_argument_lists $field
     * @return 更新成功返回影响的记录数，没有更新数据返回0，更新过程出错返回false
     * 示例：update users inner join test set user_id='update' where users.id = test.id;
     */
    public function setField(...$field)
    {
        $param_number = count($field);
        if ($field===0) {
            self::throw_exception('setField子句须传入参数');
            return false;
        }
        self::parseWhere();
        if (self::$whereString=='') {
            if (is_array($field[0])&&isset($field[0][self::$columns['PRI']])) {
                if (is_array($field[0][self::$columns['PRI']])) {
                    if ($field[0][self::$columns['PRI']][0]=='exp') {
                        self::$whereString = ' WHERE `'.self::$columns['PRI'].'` = '.trim($field[0][self::$columns['PRI']][1]);
                    } else {
                        self::throw_exception('setField子句仅支持exp表达式更新');
                        return false;
                    }
                } else {
                    self::$whereString = ' WHERE `'.self::$columns['PRI'].'` = ?';
                    self::$whereValueArray[] = $field[0][self::$columns['PRI']];
                }
                unset($field[0][self::$columns['PRI']]);
            } else {
                self::throw_exception('没有任何更新条件，数据对象本身也不包含主键字段，不被允许执行更新操作');
                return false;
            }
        }
        $setFieldStr = '';
        if (is_string($field[0])) {
            if ($param_number!=2) {
                self::throw_exception('setField子句接收两个参数（属性名，属性值）');
                return false;
            }
            if (strpos($field[0], '.')===false) {
                $setFieldStr .= '`'.trim($field[0]).'` = ?';
            } else {
                $setFieldStr .= trim($field[0]).' = ?';
            }
            self::$whereValueArray[] = $field[1];
        } elseif (is_array($field[0])) {
            if ($param_number!=1) {
                self::throw_exception('setField子句只接收一个数组参数');
                return false;
            }
            foreach ($field[0] as $key => $val) {
                if (is_array($val)) {
                    if ($val[0]=='exp') {
                        if (strpos($key, '.')===false) {
                            $setFieldStr .= '`'.trim($key).'` = '.trim($val[1]).',';
                        } else {
                            $setFieldStr .= trim($key).' = '.trim($val[1]).',';
                        }
                    } else {
                        self::throw_exception('setField子句仅支持exp表达式更新');
                        return false;
                    }
                } else {
                    if (strpos($key, '.')===false) {
                        $setFieldStr .= '`'.trim($key).'` = ?,';
                    } else {
                        $setFieldStr .= trim($key).' = ?,';
                    }
                    self::$whereValueArray[] = $val;
                }
            }
            $setFieldStr = rtrim($setFieldStr, ',');
        } else {
            self::throw_exception('setField子句传入的参数类型错误：'.$field[0]);
            return false;
        }
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        $sqlString = 'UPDATE '.$table_name.self::$joinString.' SET '.$setFieldStr.self::$whereString.self::$orderString.self::$limitString;
        $res = self::execute($sqlString);
        return $res;
    }

    /**
     * setInc方法/字段自增$value(默认1)
     * @param string $field
     * @param int $value
     * @return 更新成功返回影响的记录数，没有更新数据返回0，更新过程出错返回false
     * 示例：UPDATE `users` SET `id`=id+4 WHERE ( password="afad" )
     */
    public function setInc($field, $value = 1)
    {
        $data[$field]=array('exp',$field.' + '.$number);
        return self::save($data);
    }

    /**
     * setDec方法/字段自减$value(默认1)
     * @param string $field
     * @param int $value
     * @return 更新成功返回影响的记录数，没有更新数据返回0，更新过程出错返回false
     */
    public function setDec($field, $value = 1)
    {
        $data[$field]=array('exp',$field.' - '.$number);
        return self::save($data);
    }

    /**
     * save方法/更新数据
     * @param array $data
     * @return 更新成功返回影响的记录数，没有更新数据返回0，更新过程出错返回false
     */
    public function save($data)
    {
        if (!is_array($data)) {
            self::throw_exception('save子句只接收数组参数');
            return false;
        }
        self::parseWhere();
        if (self::$whereString=='') {
            if (isset($data[self::$columns['PRI']])) {
                if (is_array($data[self::$columns['PRI']])) {
                    if ($data[self::$columns['PRI']][0]=='exp') {
                        self::$whereString = ' WHERE `'.self::$columns['PRI'].'` = '.trim($data[self::$columns['PRI']][1]);
                    } else {
                        self::throw_exception('save子句仅支持exp表达式更新');
                        return false;
                    }
                } else {
                    self::$whereString = ' WHERE `'.self::$columns['PRI'].'` = ?';
                    self::$whereValueArray[] = $data[self::$columns['PRI']];
                }
                unset($data[self::$columns['PRI']]);
            } else {
                self::throw_exception('没有任何更新条件，数据对象本身也不包含主键字段，不被允许执行更新操作');
                return false;
            }
        }
        $setFieldStr = '';
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                //支持exp表达式进行数据更新
                if ($val[0]=='exp') {
                    if (strpos($key, '.')===false) {
                        $setFieldStr .= '`'.trim($key).'` = '.trim($val[1]).',';
                    } else {
                        $setFieldStr .= trim($key).' = '.trim($val[1]).',';
                    }
                } else {
                    self::throw_exception('save子句仅支持exp表达式更新');
                    return false;
                }
            } else {
                if (strpos($key, '.')===false) {
                    $setFieldStr .= '`'.trim($key).'` = ?,';
                } else {
                    $setFieldStr .= trim($key).' = ?,';
                }
                self::$whereValueArray[] = $val;
            }
        }
        $setFieldStr = rtrim($setFieldStr, ',');
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        $sqlString = 'UPDATE '.$table_name.self::$joinString.' SET '.$setFieldStr.self::$whereString.self::$orderString.self::$limitString;
        $res = self::execute($sqlString);
        return $res;
    }

    /**
     * delete方法/删除数据
     * @param $table 用于指定主键，删除对应数据
     * @return 删除成功返回影响的记录数，没有删除数据返回0，出错返回false
     */
    public function delete($table = '')
    {
        $sqlString = '';
        if (self::$tmp_table != '') {
            $table_name = self::$tmp_table.self::$aliasString;
        } else {
            $table_name = '`'.self::$table.'`'.self::$aliasString;
        }
        if ($table!='') {
            $table = ' '.$table;
        }
        self::parseWhere();
        if (self::$whereString=='') {
            if (self::$joinString==''||stripos(self::$joinString, ' on ')===false) {
                self::throw_exception('没有传入任何条件，不被允许执行删除操作');
                return false;
            }
        }
        $sqlString = 'DELETE'.$table.' FROM '.$table_name.self::$joinString.self::$whereString.self::$orderString.self::$limitString;
        $res = self::execute($sqlString);
        return $res;
    }

    /**
     * query方法/用于SQL查询
     * @param string $queryStr
     * @param boolean $is_find 指定是否find方法，是则只返回第一条数据
     * @return array 返回查询到的数据
     */
    public function query($queryStr, $is_find = false)
    {
        if (!is_string($queryStr)) {
            self::throw_exception('query查询须传入字符串');
            return false;
        }
        if (self::$fetchSql===true) {
            $buildSql = self::replaceSpecialChar('/\?/', self::$whereValueArray, $queryStr);
            self::clearSubString();
            return $buildSql;
        }
        self::$PDOStatement = self::$link->prepare($queryStr);
        if (count(self::$whereValueArray)>0) {
            self::$PDOStatement->execute(self::$whereValueArray);
        } else {
            self::$PDOStatement->execute();
        }
        self::$queryStr = self::replaceSpecialChar('/\?/', self::$whereValueArray, $queryStr);
        self::clearSubString();
        $haveError = self::haveErrorThrowException();
        if (false===$haveError) {
            return false;
        }
        if ($is_find===true) {
            $res = self::$PDOStatement->fetch(PDO::FETCH_ASSOC);
            if (false===$res) {
                return null;
            }
        } else {
            $res = self::$PDOStatement->fetchAll(PDO::FETCH_ASSOC);
            if (0===count($res)) {
                return null;
            }
        }
        return $res;
    }

    /**
     * execute方法/用于SQL查询
     * @param string $execStr
     * @return int 返回影响的记录数
     */
    public function execute($execStr)
    {
        if (!is_string($execStr)) {
            self::throw_exception('execute查询须传入字符串');
            return false;
        }
        if (self::$fetchSql===true) {
            $buildSql = self::replaceSpecialChar('/\?/', self::$whereValueArray, $execStr);
            self::clearSubString();
            return $buildSql;
        }
        self::$PDOStatement = self::$link->prepare($execStr);
        if (count(self::$whereValueArray)>0) {
            self::$PDOStatement->execute(self::$whereValueArray);
        } else {
            self::$PDOStatement->execute();
        }
        self::$queryStr = self::replaceSpecialChar('/\?/', self::$whereValueArray, $execStr);
        self::clearSubString();
        $haveError = self::haveErrorThrowException();
        if (false===$haveError) {
            return false;
        }
        self::$numRows = self::$PDOStatement->rowCount();
        return self::$numRows;
    }

    /**
     * 开启事务
     */
    public function startTrans()
    {
        $link = self::$link;
        $link->beginTransaction();
    }

    /**
     * 检查是否在一个事务内
     * @return boolean
     */
    public function inTrans()
    {
        return self::$link->inTransaction();
    }

    /**
     * 事务回滚
     */
    public function rollback()
    {
        $link = self::$link;
        if (self::inTrans()===true) {
            $link->rollBack();
        } else {
            self::throw_exception("当前不处于事务中");
        }
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $link = self::$link;
        if (self::inTrans()===true) {
            $link->commit();
        } else {
            self::throw_exception("当前不处于事务中");
        }
    }

    /**
     * 打印封装的最后一条SQL语句（不一定准确）
     * @return string
     */
    public function getLastSql()
    {
        if (self::$dbdebug===false) {
            self::throw_exception('请先开启DEBUG模式');
            return false;
        }
        return self::$queryStr;
    }

    /**
     * 打印封装的最后一条SQL语句（同getLastSql()，不一定准确）
     * @return string
     */
    public function _sql()
    {
        if (self::$dbdebug===false) {
            self::throw_exception('请先开启DEBUG模式');
            return false;
        }
        return self::$queryStr;
    }

    /**
     * 从日志上读取SQL记录
     * @return string
     */
    public function getLastLog()
    {
        if (self::$dbdebug===false) {
            self::throw_exception('请先开启DEBUG模式');
            return false;
        }
        if (self::$MySQL_log=='') {
            self::throw_exception('尚未指定SQL日志文件的路径');
            return false;
        }
        $get_file_lastline = self::get_file_lastline(self::$MySQL_log);
        if ($get_file_lastline===false) {
            return false;
        } else {
            $is_match=preg_match('/(?<=Query).*/', $get_file_lastline, $match);
            if ($is_match!=1) {
                self::throw_exception('SQL日志文件最后一行无Query字符串');
                return false;
            }
            return trim($match[0]);
            return $get_file_lastline;
        }
    }

    /**
     * 解析where中的数组参数
     * @param array $whereArrayParam
     * @return string
     * _logic支持AND、OR、XOR(Thinkphp没有明确指定支持XOR)
     * _tosingle=>true表示字段对应数组元素单条件查询
     * _tomulti=>true表示字段对应数组元素多条件查询
     */
    private function parseWhereArrayParam($whereArrayParam)
    {
        $logic = ' AND ';
        $whereSubString = '';
        if (isset($whereArrayParam['_complex'])) {
            $whereSubString = '( '.self::parseWhereArrayParam($whereArrayParam['_complex']).' )';
            unset($whereArrayParam['_complex']);
        }
        if (isset($whereArrayParam['_logic'])) {
            if (in_array(strtoupper($whereArrayParam['_logic']), self::$SQL_logic)) {
                $logic = ' '.strtoupper($whereArrayParam['_logic']).' ';
            } else {
                self::throw_exception('_logic参数指定的逻辑运算符不被支持："'.$whereArrayParam['_logic'].'"');
                return false;
            }
            unset($whereArrayParam['_logic']);
        }
        if (isset($whereArrayParam['_string'])) {
            $whereSubString .= $logic.'( '.$whereArrayParam['_string'].' )';
            unset($whereArrayParam['_string']);
        }
        if (isset($whereArrayParam['_query'])) {
            $explode_query=explode('&', $whereArrayParam['_query']);
            $explode_array = array();
            foreach ($explode_query as $str) {
                $explode_sub_query = explode('=', $str);
                $explode_array[$explode_sub_query[0]]=$explode_sub_query[1];
            }
            if (isset($explode_array['_logic'])) {
                if (in_array($explode_array['_logic'], self::$SQL_logic)) {
                    $sub_logic = ' '.strtoupper($explode_array['_logic']).' ';
                } else {
                    self::throw_exception('_query中的_logic参数指定的逻辑运算符不被支持："'.$explode_array['_logic'].'"');
                    return false;
                }
                unset($explode_array['_logic']);
            }
            $querySubString = '';
            foreach ($explode_array as $key => $val) {
                $start=strpos($key, '.');
                if ($start!==false) {
                    $querySubString .= $sub_logic.$key." = '".$val."'";
                } else {
                    $querySubString .= $sub_logic."`".$key."` = '".$val."'";
                }
            }
            $querySubString = ltrim($querySubString, $sub_logic);
            $whereSubString .= $logic.'( '.$querySubString.' )';
            unset($whereArrayParam['_query']);
        }
        foreach ($whereArrayParam as $key => $val) {
            $whereArraySubString = '';
            if (!is_array($val)) {
                $have_and = strpos($key, '&');
                $have_or = strpos($key, '|');
                $start = strpos($key, '.');
                if ($have_and===false&&$have_or===false) {
                    //无&和|符号
                    if ($start!==false) {
                        $whereArraySubString .= $logic.$key." = ?";
                    } else {
                        $whereArraySubString .= $logic."`".$key."` = ?";
                    }
                    self::$whereValueArray[] = $val;
                } elseif (($have_and!==false&&$have_or===false)||($have_and===false&&$have_or!==false)) {
                    //有&符号，无|符号 或者 无&符号，有|符号
                    if ($have_and!==false) {
                        $string_logic = '&';
                        $sub_logic = ' AND ';
                    } else {
                        $string_logic = '|';
                        $sub_logic = ' OR ';
                    }
                    $explode_array = explode($string_logic, $key);
                    $whereArraySubString = '';
                    foreach ($explode_array as $explode_val) {
                        $start = strpos($explode_val, '.');
                        if ($start!==false) {
                            $whereArraySubString .= $sub_logic.$explode_val." = ?";
                        } else {
                            $whereArraySubString .= $sub_logic."`".$explode_val."` = ?";
                        }
                        self::$whereValueArray[] = $val;
                    }
                    $whereArraySubString = ltrim($whereArraySubString, $sub_logic);
                    $whereArraySubString = '( '.$whereArraySubString.' )';
                } else {
                    //既有&符号，又有|符号
                    self::throw_exception('快捷查询方式中“|”和“&”不能同时使用');
                    return false;
                }
            } else {
                $have_and = strpos($key, '&');
                $have_or = strpos($key, '|');
                if ($have_and===false&&$have_or===false) {
                    //无&和|符号
                    if (isset($val['_tomulti'])&&$val['_tomulti']===true) {
                        //多条件查询
                        $get_parseMultiQuery = self::parseMultiQuery($key, $val);
                        $whereArraySubString .= $get_parseMultiQuery;
                    } else {
                        //表达式查询
                        $get_parseExpQuery = self::parseExpQuery($key, $val);
                        $whereArraySubString .= $get_parseExpQuery;
                    }
                } elseif (($have_and!==false&&$have_or===false)||($have_and===false&&$have_or!==false)) {
                    //有&符号，无|符号 或者 无&符号，有|符号
                    if ($have_and!==false) {
                        $string_logic = '&';
                        $sub_logic = ' AND ';
                    } else {
                        $string_logic = '|';
                        $sub_logic = ' OR ';
                    }
                    $explode_array = explode($string_logic, $key);
                    $signal = 3;//1代表字段对应数组元素单条件查询，2代表字段对应数组元素多条件查询，3代表表达式查询
                    if (isset($val['_tosingle'])&&isset($val['_tomulti'])) {
                        if ($val['_tosingle']===true&&$val['_tomulti']===true) {
                            self::throw_exception('单条件查询和多条件查询不能同时存在');
                            return false;
                        }
                        if ($val['_tosingle']===true) {
                            $signal = 1;
                        }
                        if ($val['_tomulti']===true) {
                            $signal = 2;
                        }
                    } elseif (isset($val['_tosingle'])) {
                        if ($val['_tosingle']===true) {
                            $signal = 1;
                        }
                    } elseif (isset($val['_tomulti'])) {
                        if ($val['_tomulti']===true) {
                            $signal = 2;
                        }
                    } else {
                        $signal = 3;
                    }
                    if ($signal==1) {
                        //字段对应数组元素单条件查询
                        $index = 0;
                        foreach ($explode_array as $explode_val) {
                            if (is_array($val[$index])) {
                                if (isset($val[$index]['_tomulti'])&&$val[$index]['_tomulti']===true) {
                                    //多条件查询
                                    $get_parseMultiQuery = self::parseMultiQuery($explode_val, $val[$index]);
                                    $whereArraySubString .= $sub_logic.$get_parseMultiQuery;
                                } else {
                                    //表达式查询
                                    $get_parseExpQuery = self::parseExpQuery($explode_val, $val[$index]);
                                    $whereArraySubString .= $sub_logic.$get_parseExpQuery;
                                }
                            } else {
                                $start = strpos($explode_val, '.');
                                if ($start!==false) {
                                    $whereArraySubString .= $sub_logic.$explode_val." = ?";
                                } else {
                                    $whereArraySubString .= $sub_logic."`".$explode_val."` = ?";
                                }
                                self::$whereValueArray[] = $val[$index];
                            }
                            $index++;
                        }
                    } elseif ($signal==2) {
                        //字段对应数组元素多条件查询
                        foreach ($explode_array as $explode_val) {
                            $get_parseMultiQuery = self::parseMultiQuery($explode_val, $val);
                            $whereArraySubString .= $sub_logic.$get_parseMultiQuery;
                        }
                    } else {
                        //表达式查询
                        foreach ($explode_array as $explode_val) {
                            $get_parseExpQuery = self::parseExpQuery($explode_val, $val);
                            $whereArraySubString .= $sub_logic.$get_parseExpQuery;
                        }
                    }
                    $whereArraySubString = ltrim($whereArraySubString, $sub_logic);
                    $whereArraySubString = '( '.$whereArraySubString.' )';
                } else {
                    //既有&符号，又有|符号
                    self::throw_exception('快捷查询方式中“|”和“&”不能同时使用');
                    return false;
                }
            }
            $whereSubString .= $logic.$whereArraySubString;
        }
        $whereSubString = ltrim($whereSubString, $logic);
        return $whereSubString;
    }

    /**
     * 解析表达式查询
     * LIKE/NOTLIKE中支持AND、OR、XOR(Thinkphp没有明确指定支持XOR)
     * @param string $column
     * @param array $array
     * @return string
     */
    private function parseExpQuery($column, $array)
    {
        $expQueryString = '';
        $start = strpos($column, '.');
        if ($start===false) {
            $column = '`'.$column.'`';
        }
        switch (strtoupper($array[0])) {
            case "EQ":
                $expQueryString .= $column.' = ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "NEQ":
                $expQueryString .= $column.' <> ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "GT":
                $expQueryString .= $column.' > ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "EGT":
                $expQueryString .= $column.' >= ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "LT":
                $expQueryString .= $column.' < ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "ELT":
                $expQueryString .= $column.' <= ?';
                self::$whereValueArray[] = $array[1];
                break;
            case "LIKE":
            case "NOTLIKE":
            case "NOT LIKE":
                if (strtoupper($array[0])=='LIKE') {
                    $string = ' LIKE ';
                } else {
                    $string = ' NOT LIKE ';
                }
                if (is_array($array[1])) {
                    $logic = ' AND ';
                    if (isset($array[2])) {
                        if (in_array(strtoupper($array[2]), self::$SQL_logic)) {
                            $logic = ' '.strtoupper($array[2]).' ';
                        } else {
                            if (!is_string($array[2])) {
                                self::throw_exception('[NOT] LIKE查询中的数组第三个元素必须为字符串，用于指定逻辑运算符');
                                return false;
                            }
                            self::throw_exception('[NOT] LIKE查询中的逻辑运算符"'.$array[2].'"不被支持');
                            return false;
                        }
                    }
                    foreach ($array[1] as $val) {
                        $expQueryString .= $logic.$column.$string.' ?';
                        self::$whereValueArray[] = (string)$val;
                    }
                    $expQueryString = ltrim($expQueryString, $logic);
                    $expQueryString = '( '.$expQueryString.' )';
                } else {
                    $expQueryString .= $column.$string.' ?';
                    self::$whereValueArray[] = $array[1];
                }
                break;
            case "BETWEEN":
            case "NOTBETWEEN":
            case "NOT BETWEEN":
                //示例array('between','1,8')/array('between',1,8)/array('between',array('1','8'))
                if (strtoupper($array[0])=='BETWEEN') {
                    $string = ' BETWEEN ';
                } else {
                    $string = ' NOT BETWEEN ';
                }
                $expQueryString .= $column.$string.'? AND ?';
                if (is_array($array[1])) {
                    self::$whereValueArray[] = $array[1][0];
                    self::$whereValueArray[] = $array[1][1];
                } elseif (is_string($array[1])) {
                    $explode_array = explode(',', $array[1]);
                    if (count($explode_array)!=2) {
                        self::throw_exception('表达式查询之[NOT]BETWEEN后的参数错误：'.$array[1]);
                        return false;
                    }
                    self::$whereValueArray[] = trim($explode_array[0]);
                    self::$whereValueArray[] = trim($explode_array[1]);
                } elseif (is_numeric($array[1])) {
                    if (!isset($array[2])||!is_numeric($array[2])) {
                        self::throw_exception('表达式查询之[NOT]BETWEEN后的参数错误(two number expected)');
                        return false;
                    }
                    self::$whereValueArray[] = $array[1];
                    self::$whereValueArray[] = $array[2];
                } else {
                    self::throw_exception('表达式查询之[NOT]BETWEEN后的参数错误：'.$array[1]);
                    return false;
                }
                break;
            case "IN":
            case "NOTIN":
            case "NOT IN":
                //示例：array('not	in',array('a','b','c'))/array('not	in','a,b,c')
                if (strtoupper($array[0])=='IN') {
                    $string = ' IN ';
                } else {
                    $string = ' NOT IN ';
                }
                if (is_array($array[1])) {
                    $length = count($array[1]);
                    if ($length==0) {
                        self::throw_exception('表达式查询之[NOT]IN后的数组参数为空：array()');
                        return false;
                    }
                    $expQueryString .= $column.$string.'(';
                    $expQueryString .= '?';
                    self::$whereValueArray[] = $array[1][0];
                    for ($i=1; $i<$length; $i++) {
                        $expQueryString .= ',?';
                        self::$whereValueArray[] = $array[1][$i];
                    }
                    $expQueryString .= ')';
                } elseif (is_string($array[1])) {
                    $explode_array = explode(',', $array[1]);
                    $length = count($explode_array);
                    $expQueryString .= $column.$string.'(';
                    $expQueryString .= '?';
                    self::$whereValueArray[] = $explode_array[0];
                    for ($i=1; $i<$length; $i++) {
                        $expQueryString .= ',?';
                        self::$whereValueArray[] = $explode_array[$i];
                    }
                    $expQueryString .= ')';
                } else {
                    self::throw_exception('表达式查询之[NOT]IN后的参数错误：'.$array[1]);
                    return false;
                }
                break;
            case "EXP":
                if (is_string($array[1])) {
                    $expQueryString .= $column.$array[1];
                } else {
                    self::throw_exception('表达式查询之exp后的参数错误：'.$array[1]);
                    return false;
                }
                break;
            default:
                self::throw_exception('表达式查询之表达式错误："'.$array[0].'"');
                return false;
        }
        return $expQueryString;
    }

    /**
     * 解析多条件查询
     * 支持AND、OR、XOR运算符(Thinkphp文档指定)
     * @param string $column
     * @param array $array
     * @return string
     */
    private function parseMultiQuery($column, $array)
    {
        $multiQueryString = '';
        $start = strpos($column, '.');
        if ($start===false) {
            $column = '`'.$column.'`';
        }
        foreach ($array as $key => $val) {
            if (!is_numeric($key)) {
                unset($array[$key]);
            }
        }
        $length = count($array);
        $logic = ' AND ';
        if (is_string($array[$length-1])&&(in_array($array[$length-1], self::$SQL_logic))) {
            $length--;
            $logic = ' '.strtoupper($array[$length]).' ';
        }
        for ($i=0; $i<$length; $i++) {
            if (is_array($array[$i])) {
                if (isset($array[$i]['_tomulti'])&&$array[$i]['_tomulti']===true) {
                    //多条件查询
                    $get_parseMultiQuery = self::parseMultiQuery($column, $array[$i]);
                    $multiQueryString .= $logic.$get_parseMultiQuery;
                } else {
                    //表达式查询
                    $get_parseExpQuery = self::parseExpQuery($column, $array[$i]);
                    $multiQueryString .= $logic.$get_parseExpQuery;
                }
            } else {
                $multiQueryString .= $logic.$column.' = ?';
                self::$whereValueArray[] = $array[$i];
            }
        }
        $multiQueryString = ltrim($multiQueryString, $logic);
        $multiQueryString = '( '.$multiQueryString.' )';
        return $multiQueryString;
    }

    /**
     * 通过反引号引用字段，
     * @param unknown $value
     * @return string
     */
    private function addSpecialChar(&$value)
    {
        $value = trim($value);
        if (stripos($value, ' as ')!==false) {
            //字符串中有" as "
            $match_number = preg_match('/(?<=as\s{1}).*/i', $value, $match);
            if ($match_number==0||preg_match('/\w+/', $match[0])==0) {
                self::throw_exception('"'.$value.'"的as关键词后无字符');
                return false;
            }
            if (preg_match('/\s+/', trim($match[0]))!=0) {
                self::throw_exception('"'.$value.'"的as关键词后出现两个单词');
                return false;
            }
            $value=preg_replace('/(?<=as\s{1}).*/i', '`'.trim($match[0]).'`', $value);
            $value=preg_replace('/\s+/', ' ', $value);
        } elseif (1===preg_match('/^\w+\.\w+$/', $value)) {
            //字符串是dbname.tablename
            if (preg_match('/\s/', $value)!=0) {
                self::throw_exception('"'.$value.'"中间存在非法的空格字符');
                return false;
            }
        } else {
            //其他
            if (0===preg_match('/\W+/', $value)) {
                $value='`'.$value.'`';
            }
        }
        return $value;
    }

    /**
     * 将匹配的字符进行替换，支持字符串替换和数组对应替换
     * @param string $pattern
     * @param string/array $replacement
     * @param string $subject
     * @return string
     */
    private function replaceSpecialChar($pattern, $replacement, $subject)
    {
        if (is_array($replacement)) {
            $length = count($replacement);
            for ($i=0; $i<$length; $i++) {
                $subject = preg_replace($pattern, self::$link->quote($replacement[$i]), $subject, 1);
            }
        } elseif (is_string($replacement)) {
            $subject = preg_replace($pattern, self::$link->quote($replacement), $subject);
        } else {
            self::throw_exception('replaceSpecialChar函数的第二个参数类型错误');
            return false;
        }
        return $subject;
    }

    /*
     * 获取文件最后一行/倒数第$n行
     */
    private function get_file_lastline($file_name, $n = 1)
    {
        if (file_exists($file_name)!=1) {
            echo "failed to open stream: File does not exist";
            return false;
        }
        if (!$fp=fopen($file_name, 'r')) {
            echo "failed to open stream: Permission denied";
            return false;
        }
        fseek($fp, -1, SEEK_END);
        $content = '';
        while (($c = fgetc($fp)) !== false) {
            if ($c == "\n" && $content) {
                $n--;
                if (!$n) {
                    break;
                }
                $content='';
            }
            $content = $c . $content;
            fseek($fp, -2, SEEK_CUR);
        }
        fclose($fp);
        return $content;
    }

    /**
     * 每次执行完sql语句清空连贯操作的sql子句
     */
    private function clearSubString()
    {
        self::$fieldString='';
        self::$joinString='';
        self::$whereString='';
        self::$groupString='';
        self::$havingString='';
        self::$orderString='';
        self::$limitString='';
        self::$aliasString='';
        self::$tmp_table='';
        self::$fetchSql=false;
        self::$whereStringArray=array();
        self::$whereValueArray=array();
    }

    /**
     * 判断SQL是否执行有误，有误则抛出异常(throw_exception)
     */
    public function haveErrorThrowException()
    {
        $obj=empty(self::$PDOStatement)?self::$link: self::$PDOStatement;
        $arrError=$obj->errorInfo();
        //print_r($arrError);
        if ($arrError[0]!='00000') {
            self::$error='SQLSTATE: '.$arrError[0].' <br/>SQL Error: '.$arrError[2].'<br/>Error SQL:'.self::$queryStr;
            self::throw_exception(self::$error);
            return false;
        }
        if (self::$queryStr=='') {
            self::throw_exception('没有执行SQL语句');
            return false;
        }
        return true;
    }
     
    /**
     * 自定义错误处理
     * @param unknown $errMsg
     */
    public static function throw_exception($errMsg)
    {
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        if (self::$dbdebug) {
            $errMsg .= '</b><br/><br/><b>错误位置</b><br>FILE: '.$caller['file'].'   LINE: '.$caller['line'];
            $caller = array_shift($bt);
            $number = 0;
            if ($caller != null) {
                $errMsg .= '<br/><br/><b>TRACE</b><br/>';
            }
            while ($caller != null) {
                $number++;
                $errMsg .= '#'.$number.' '.$caller['file'].'('.$caller['line'].')<br/>';
                $caller = array_shift($bt);
            }
        } else {
            $errMsg = "系统出错，请联系管理员。</b>";
        }
        echo '<div style="width:80%;background-color:#ABCDEF;color:black;padding:20px 0px;"><b style="font-size:25px;">
				'.$errMsg.'
        </div>';
        exit(0);
    }

    /**
     * 获取类绑定的数据表名
     * @return string
     */
    public function getTableName()
    {
        return self::$table;
    }

    /**
     * 获取类绑定的数据表中的字段信息
     * @return array
     */
    public function getColumns()
    {
        return self::$columns;
    }

    /**
     * 获取上一步操作产生受影响的记录的条数
     * @return string
     */
    public function getDbVersion()
    {
        return self::$dbVersion;
    }

    /**
     * 获取类绑定的数据表中的字段信息
     * @return array
     */
    public function getNumRows()
    {
        return self::$numRows;
    }

    /**
     * 销毁连接对象，关闭数据库
     */
    public static function close()
    {
        self::$link=null;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        self::close();
    }
}
//M函数
function M($dbtable, $dbConfig = '')
{
    return new PDOMySQL($dbtable, $dbConfig);
}

function filter(&$value)
{
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
//I函数
function I($str)
{
    if (!is_string($str)) {
        PDOMySQL::throw_exception("I函数参数类型错误：".$str);
        return false;
    }
    $pos = strrpos($str, '.', -1);
    if ($pos===false) {
        PDOMySQL::throw_exception("I函数参数错误");
        return false;
    }
    $type = substr($str, 0, $pos);
    $param = substr($str, $pos+1);
    switch (strtoupper($type)) {
        case 'GET':
            if ($param!='') {
                $result_set = $_GET[$param];
            } else {
                $result_set = $_GET;
            }
            break;
        case 'POST':
            if ($param!='') {
                $result_set = $_POST[$param];
            } else {
                $result_set = $_POST;
            }
            break;
        default:
            PDOMySQL::throw_exception("I函数不支持此参数：".$str);
            return false;
    }
    if (is_array($result_set)) {
        array_walk_recursive($result_set, "filter");
    }
    return $result_set;
}

function part_dump($var, $layer = 1)
{
    $str = '';
    $blank = '&nbsp;&nbsp;';
    $_blank = '';
    if ($layer>1) {
        for ($i=1; $i<$layer; $i++) {
            $_blank .= '&nbsp;&nbsp;';
        }
    }
    $blank .= $_blank;
    if (is_array($var)) {
        $length = count($var);
        $str .= 'array('.$length.') {<br/>'.$blank;
        foreach ($var as $key => $val) {
            if (is_int($key)) {
                $str .= '['.$key.'] => '.part_dump($val, $layer+1).'<br/>'.$blank;
            } else {
                $str .= '["'.$key.'"] => '.part_dump($val, $layer+1).'<br/>'.$blank;
            }
        }
        $str = rtrim($str, '&nbsp;');
        $str .= $_blank;
        $str .= '}';
    } elseif (is_object($var)) {
        $length = count((array)$var);
        $str .= 'object('.$length.') {<br/>'.$blank;
        foreach ($var as $key => $val) {
            $str .= '["'.$key.'"] => '.part_dump($val, $layer+1).'<br/>'.$blank;
        }
        $str = rtrim($str, '&nbsp;');
        $str .= $_blank;
        $str .= '}';
    } elseif (is_string($var)) {
        $str .= 'string'.'('.strlen($var).') "'.$var.'"';
    } elseif (is_bool($var)) {
        $boolean_str = ($var===true)?'true':'false';
        $str .= 'bool('.$boolean_str.')';
    } elseif (is_null($var)) {
        $str .= 'NULL';
    } else {
        $str .= gettype($var).'('.$var.')';
    }
    return $str;
}
//dump函数
function dump($var)
{
    $str = part_dump($var);
    $str = '<pre>'.$str.'<pre>';
    print($str);
}
```



  [1]: https://www.kancloud.cn/manual/thinkphp/1678