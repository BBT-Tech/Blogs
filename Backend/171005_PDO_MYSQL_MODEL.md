# PDO_MYSQL_MODEL

**Author**:黄飞勤

---

[代码在此][1]

注：**个人封装的PDO操作MySQL的Model类，代码仅供学习和参考，欢迎指出BUG**

## 使用前提
熟悉PHP语法，用过ThinkPHP3.x

## 更新注记
### 2017.9.23更新

 - 仿ThinkPHP3.2.3的模型用法进行开发，支持其大部分语法 
 - 使用prepare+execute对SQL操作进行预处理，参数绑定并执行
 - 额外封装了ThinkPHP常用的方法：dump函数，M函数，I函数

### 2017.10.02更新

 - 增加了ajaxReturn函数，不过仅支持返回json格式数据
 - 更新dump方法，使用thinkphp原封的dump方法

### 2017.10.05更新
 - 增加get_client_ip函数（TP原封照搬）
 - field方法支持获取全部字段和字段过滤
 - 优化和解决了一两个小bug

### 2017.10.10更新
 - 修复BUG
 - 解决使用count()等统计函数时使用fetchSql(true)出现问题的BUG

### 2017.10.23更新
 - 修复表达式查询和多条件查询情况下无限加反引号的Bug
 - 增加 TO DO list （其实是BUG清单，先挖好坑）

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
**建议：在CURD链式调用放于首位。**
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
支持获取所有字段和过滤字段(详见ThinkPHP3.2.3文档)。
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
~~高仿~~tp的dump方法。
#### 4.ajaxReturn()
Ajax方式返回数据到客户端
暂时只支持返回json格式数据
#### 5.get_client_ip()
获取客户端IP地址
#### 6.getLastSql() / _sql() / getLastLog()
`getLastSql()`和`_sql()`等效，用于打印最后一条执行的**SQL语句**（由系统封装）
`getLastLog()`则是读取**MySQL通用查询日志**记录的最后一条SQL语句

其中，当且仅当DEBUG模式开启，以上方法才生效。注意，使用`getLastLog()`须开启MySQL的通用查询日志以及指定MySQL通用日志目录（见[开启MySQL通用查询日志](#general_log)）。

## TO DO list
 - 事务暂时不支持跨模型操作
 - field之字段过滤目前仅支持单表查询

## Github永久更新地址
[frankie-huang/PDO_MYSQL_MODEL][2]

## 参考链接
[ThinkPHP3.2.3完全开发手册在线文档][3]


  [1]: https://github.com/BBT-Tech/Blogs/tree/master/Backend/PDO_MYSQL_MODEL
  [2]: https://github.com/frankie-huang/PDO_MYSQL_MODEL
  [3]: https://www.kancloud.cn/manual/thinkphp/1678