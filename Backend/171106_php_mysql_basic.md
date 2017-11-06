# **php，mysql入门之增删查改**

标签： php mysql

---
##**第一步，配置数据库**

首先要做的是建立一个合适的数据库表（table），表的结构取决于你任务的需求。
比如这次的任务，要存放在数据库的有**用户名**，**用户密码**，**用户登录次数**，**用户登录时间**。
对于一个表来说，还需要一个`主键`（PRIMARY KEY）。
`主键`的作用是区分开任意的两条记录。在这次的任务中，一条记录包含**用户名**，**用户密码**，**用户登录次数**，**用户登录时间**。

---

###**主键 是什么？**
设想这样的场景：在这次的任务中，你需要从数据库中取出一条记录["用户名","用户密码","用户登录次数","用户登录时间"]，然后把其中的**用户登录次数**更新，然后更新数据库中的对应的记录。
那么，你如何定位到你想要更新的记录呢？最直观的想法是：搜索拥有相同**用户名**的记录。在这次的任务中，这种做法是可行的，因为这次的**用户名**是不能重复的。
如果**用户名**允许重复，那用**用户名**搜索记录就可能搜索出多条记录，分不清究竟哪条才是你要更新的。这时候，可以在记录结构中加入一个**id**，设置第一条记录**id**为1，第二条为2，确保每一条记录的**id**都不相同。这样就可以搜索拥有相同**id**的记录。
`主键`的作用就如上面所述。在这次任务中，**用户名**不会重复，所以它可成为`主键`，用**用户名**搜索记录结果绝对是唯一的（可能为空）。当**用户名**可以重复，可能引入一个不会重复的**id**，它不会重复所以可以成为`主键`，用**id**来搜索结果绝对是唯一的（可能为空）。
每个数据库表都要设置这个表的`主键`。

---

###**如何创建数据库？**
现在，我们已经知道了我们大概要创建一个怎样的数据库表（table）。细心的小伙伴会发现标题是“创建数据库”。数据库（database）和数据库表（table）之间是包含关系。一个数据库中可以包含许多个数据库表。不过对于我们这次的任务来说，“创建一个数据库，在里面只创建一个数据库表”，已经够用了。

操作数据库的方法有很多，为了精简内容，这次使用phpmyadmin工具。
那么，让我们来创建数据库吧。
启动wamp或者xampp之后，在浏览器中输入`http://localhost/phpmyadmin/`，会进入phpmyadmin的登陆界面。
![phpadmin登陆界面](http://lintean.club/phpmy.png)

输入你的用户名和密码之后进入开始界面，按左上方的新增按钮。
![phpadmin开始界面](http://lintean.club/phpmyadmin2.png)

输入数据库的名字后，直接按创建。注意，数据库的名字是自定义的，在这里我使用了task这个名字，随喜好也可以用其它名字。
![创建数据库](http://lintean.club/phpmyadmin3.png)

输入数据库表的名字和字段的数量，点击执行。在这里，我们习惯性的加入一个字段**id**。所以我们需要5个字段数，分别是**用户名**，**用户密码**，**用户登录次数**，**用户登录时间**，**id**。
![创建数据库表1](http://lintean.club/phpmyadmin4.png)

我们给字段**name**和**password**定义了VARCHAR类型，分别分配了20和32的长度。那么20长度到底是多长呢？长度是指当前字符集的字符长度。比如，20长度可以是20个汉字，20个字母，只要是20以内的都可以装下。
我们给字段**times**定义了INT类型，用来记载用户登陆的次数。因为计数从0开始，所以这个值默认为0。
我们给字段**last_time**定义了TIMESTAMP类型，用来记载用户上次登录的时间。随便给了一个默认值 2017-01-01 00:00:00。我们在属性一栏选择ON UPDATE CURRENT_TIMESTAMP，这个属性会让一条记录的**last_time**在这条记录修改的时候自动分配为当前时间。
我们给字段**id**定义了INT类型，用来作为数据库表的`主键`。我们在A_I栏处勾取，A_I全称为autoincrease，勾取后代表这个字段的值会在创建时自动分配。（如果它是表中第二条数据则分配2）。还要在索引处选择PRIMARY，代表我们选择**id**作为这个表的`主键`。
![创建数据库表2](http://lintean.club/phpmyadmin5.png)


我们可以按下右下角的“预览SQL语句”，可以看到下面的代码。当然，我们也可以不使用phpmyadmin的图形化界面，直接点击在上方的“SQL”模块输入下面的代码，效果是一样的。
```sql
CREATE TABLE `task`.`task` (
  `name` VARCHAR(20) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `times` INT NOT NULL DEFAULT 0,
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_time` TIMESTAMP DEFAULT '2017-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));

```

---
###**小结**

 - 数据库结构：一个数据库中可以有许多数据库表，一个数据库表中可存放许多条记录。
 - 每一个数据库表都需要有其`主键`
 - 创建数据库：首先创建数据库，然后在里面创建数据库表，数据库表的记录结构需要根据具体需求设置。我们可以使用phpmyadmin完成创建工作。


---
##**第二步，编写后台文件**
千辛万苦终于搞定了数据库，可以正式编写php代码了。
首先，让我们明确下编写php代码的目的：我们是要用php获取前端传来的数据，处理前端传来的数据，操作数据库。那么，让我们从这三方面开始吧。

---

###**获取前端传来的数据**
根据任务文档，前端使用POST方法传数据。那么我们可以从`$_POST`或者`file_get_contents("php://input")`处获取数据。

下面的区别提供给兴趣使然的同学：

 1. `Content-Type`仅在取值为`application/x-www-data-urlencoded`和`multipart/form-data`两种情况下，PHP才会将http请求数据包中相应的数据填入全局变量`$_POST`
 2. 只有`Content-Type`为`multipart/form-data`的时候，PHP不会将http请求数据包中的相应数据填入`php://input`和`$HTTP_RAW_POST_DATA`，否则其它情况都会。填入的长度，由`Content-Length`指定。
 3. PHP不能识别的`Content-Type`类型的时候，会将http请求包中相应的数据填入变量`$HTTP_RAW_POST_DATA`

总之，这次可以使用`$_POST`数组获取数据。
 
---

###**处理前端传来的数据**
首先，我们可以编写一个函数output。把这次要返回的值`$status`和`$msg`传给它，它会json编码之后发给前端，并退出php。这个操作可能会用到很多次，写成函数可以很好的发挥代码重用性。
解读：
我们需要把数据装入一个数组中并发给前端。array()为创建一个数组。
exit()为退出当前的程序，参数为错误代码，0是无错误。exit(0)代表退出程序，并且程序无错误。
```php
function output($status, $msg){
    $arr = array('status' => $status,'msg' => $msg);    
    echo json_encode($arr); 
    exit(0);
}
```

在这次任务中，前端的数据貌似没有什么好处理的。但是对于前端的数据，可以引用后台入门文档中陈泽锋师兄的一句话：
> 永远不要相信前端传来的数据

所以，在数据处理这一步中，有一件必须要做的事情：检验数据。
如下，我们可以这样检验数据：对于前端传来的三个值`$_POST['ins']`，`$_POST['userName']`，`$_POST['password']`，用`isset()`检验一下是否存在。根据任务文档，`$_POST['ins']`要么是“sign”要么是“login”，如果是其它的值，数据就不规范。
`isset()`是php自带的函数，可能用来检验参数是否存在。`isset($_POST['ins'])`就是检验`$_POST['ins']`是否存在，如果存在函数会返回TRUE，不存在返回FALSE。
我们可以调用上面的`output()`函数向前端发送信息。当值不存在或者不规范的时候，可以`output(0,'XXX为空')`，或者`output(0,'XXX不规范')`。
注意，`$_POST['XXX']`中的引号可有可没有，所以也可以写成`$_POST[XXX]`

```php
function data_checkout(){
    if (isset($_POST['ins'])){
        if ($_POST['ins'] != 'sign' && $_POST['ins'] != 'login') output(0,'ins不规范');
    }
    else output(0,'ins为空');
    if (!isset($_POST['userName'])) output(0,'userName为空');
    if (!isset($_POST['password'])) output(0,'password为空');
}
```

---

###**php操作数据库**
处理完数据，就可以操作数据库了。这里我们使用mysqli库进行相关的操作。
使用mysqli库的方式有两种，可以使用面向过程的，也可以使用面向对象的。我们这里使用较易理解的面向过程式的mysqli。如果是面向对象的用法可以看到很多`->`符号。
那么让我们开始吧。

---

####**使用mysqli链接数据库**
无论php要对数据库做什么操作，都要先链接上数据库。
我们要让php链接上我们的数据库，首先要知道下面四个信息：`$dbhost`，`$dbname`，`$user`，`$user_password`。也就是**拥有数据库的主机**，**数据库名字**，**用来登陆的用户名**，**用户名密码**。

 - `$dbhost`（**拥有数据库的主机**）：也就是告诉php你的数据库在哪台主机（计算机）上。在这次任务中，我们的数据库在我们本机上，所以我们可以填`localhost`，也可以填`127.0.0.1`。前一个是指向本机的域名，后一个是域名对应的IP地址。也就是说，这两个是等效的，都指向本机。关于域名和IP的知识可以观看后台入门文档，这里就不说明了。如果你想连接上其他人的数据库，`$dbhost`需要填其他人的域名或者IP地址。
 - `$dbname`（**数据库名字**）：这里可以回忆下我们上面创建的数据库，我们在上面创建了一个名为task的数据库（database），在其中只创建了一个名为task的数据库表（table）。因为我们的数据库叫task，所以这里我们可以填task。如果你使用了其它的数据库名，就要用你所使用的名字。
 - `$user`（**用来登陆的用户名**）：我们再来回忆下刚才的phpmyadmin之旅，在一开始的登录界面中需要输入我们的用户名和密码，`$user`也就是这个用户名。
 - `$user_password`（**用户名密码**）：同上，是登陆界面输入的用户密码。

知道这四个信息之后，就可以用mysqli链接数据库了。具体代码如下。
使用`mysqli_connect()`链接数据库，需要提供四个参数，也就是我们提到的四个信息，**注意下方代码中四个信息的顺序**。如果成功链接，函数会返回一个代表到mysql服务器的连接的对象。如果失败，会返回FALSE。我们可以检验一下`$con`是不是FALSE，如果不为FALSE就说明链接成功了。

P.S.`mysqli_connect()`一共可以输入六个参数，但是前四个比较常用，这里就只介绍前四个，有兴趣可以自行查询。
 
```php
function connect_init(){
    global $con;
    $dbhost = "127.0.0.1";
    $dbname = "task";
    $user = "root";
    $user_password = "A2817325b@";

    $con = mysqli_connect($dbhost, $user, $user_password, $dbname);
    if (!$con) output(0, "数据库链接失败，请联系管理员");
}
```

---

####**增**
下面我们来试试增删查改四操作的增加操作。
增加操作的目标是增加数据库（database）中的数据库表（table）的一条记录（record）。在这次任务中，我们希望在 task数据库中的 task数据库表 中增加一条包含**用户名**，**用户密码**，**用户登录次数**，**用户登录时间**，**id**的一条记录。
增加的操作用途很广，比如在任务中用户注册时，我们需要应用增加操作。下面我们来围绕这个应用场景来说明增加操作。

 - 如下**代码**，首先我们需要书写一条插入操作的sql语句。sql语句是对数据库进行操作的一种语言。它可以直接使用在数据库上，如下方的图，我们可以在phpmyadmin的task数据库中点击上方的“SQL”模块，在里面可以书写并运行SQL语句。
对于增加（插入）操作来说，sql语句的格式是这样的：`INSERT INTO 表名(字段1,字段2) VALUES (值1，值2)`
让我们来回忆下字段的含义：我们上面为task表创建了5个字段，分别是**name**，**password**，**times**，**last_time**，**id**。
这句sql语句的意思是在XX表中插入 字段1为值1并且字段2为值2 的记录。
所以我们需要书写的sql语句如下所示。注意，表名和字段名可以用 **\`** 符号包起来。
当你所使用的表名或者字段名和mysql关键字相同的时候，mysql就无法得知你书写的究竟是表名/字段名，还是关键字，这时候需要使用 **\`** 符号可以明确你的表名/字段名。比如，如果你有一个名为insert的表，因为insert是插入语句的关键字，你需要这样写：**\`insert\`**，才能让mysql知道你写的是一个表名。~~当然，下面加的**\`**不是这个原因，只是兴趣使然。~~

 - 我们来说一下`mysqli_query()`，第一个参数是mysqli链接对象，也就是使用`musqli_connect()`成功后返回的对象，第二个参数是sql语句，第三个参数是结果集获取模式，可不填，对我们来说不常用，这里不进行讨论。
当sql语句是查询语句时（如SELECT、SHOW、DESCRIBE 或 EXPLAIN），`mysqli_query()`函数将返回查询后的结果集，当不是查询语句时，返回TRUE或FALSE表示sql语句的是否成功执行。这里我们的sql语句是INSERT插入语句，所以会返回TRUE或FALSE。

所以我们的增加操作的代码可以写成这样：
注意，我们这里只设置了**name**，**password**，**times**的值，因为**id**的值在插入记录时会自动分配（有autoincrease属性），**last_time**的值有其默认值，其实我们也设置了**time**的值默认为0，也可以不设置，具体可查看上面建立数据库表的操作。

**代码：**
```php
$sql_str = "INSERT INTO `task`(`name`,password,times) VALUES ($_POST[userName],$_POST[password],0)";

if (mysqli_query($con, $sql_str)) output(1,'注册成功');
    else output(1,"服务器或数据库错误,数据写入失败");
```
**图：**
![在phpmyadmin中使用sql语句](http://lintean.club/php.png)

---

####**删**
我们这次任务并没有删除的需求，但为了照应标题，我们也来说一下删除语句。
跟增加操作异曲同工，首先我们需要书写删除的sql语句：`DELETE FROM 表名 WHERE 字段1 = 值1 and 字段2 = 值2`
这句sql语句的意思是从XX表中删除 字段1为值1并且字段2为值2 的记录。
当然也需要使用`mysqli_query()`语句来执行，代码如下：
```php
$sql_str = "DELETE FROM `task` WHERE `name` = $_POST[userName]";
if (mysqli_query($con, $sql_str)) output(1,'删除成功');
    else output(1,"服务器或数据库错误,数据写入失败");
```

---

####**查**
任务中，我们有一个“用户登陆时查询出用户并把用户登陆次数+1”的需求。我们以这个为背景进行讲述。
首先，查询的sql语句是：`SELECT 字段1,字段2 FROM 表名 WHERE 字段3 = 值1 and 字段4 = 值2`
这句sql语句的意思是从XX表中查询 字段3为值1并且字段4为值2 的记录 中的字段1和字段2。
通常我们喜欢把整条记录查询出来，所以可以这样写：`SELECT * FROM 表名 WHERE 字段3 = 值1 and 字段4 = 值2`
这句sql语句的意思是从XX表中查询 字段3为值1并且字段4为值2 的记录。

因为我们书写的是查询的sql语句，所以`mysqli_query()`会返回一个查询的结果集。我们需要对查询的结果集进行处理，提取出我们想要的数据。
我们可以使用`mysqli_fetch_array()`函数提取，第一个参数是你要提取的结果集，第二个参数是提取出来的数据 的保存方式。第二个参数可以选择`MYSQLI_ASSOC`，`MYSQLI_NUM`，`MYSQLI_BOTH`的其中一个填入。

 - `MYSQLI_ASSOC`：以关联数组的方式保存。
 - `MYSQLI_NUM`：以数字数组的方式保存。
 - `MYSQLI_BOTH`：同时以上面两种方式保存。

假设结果集中有且只有一条记录，记录的第一个字段是**name**，我们用`mysqli_fetch_array()`提取数据并且保存在`$arrays`中。

 - 当以关联数组保存的时候，**name**的值**只**存放在`$arrays[name]`中
 - 当以数字数组保存的时候，因为记录中第一个是**name**，**name**的值**只**存放在`$arrays[0]`中
 - 当以`MYSQLI_BOTH`保存的时候，**name**的值**同时**存放在`$arrays[name]`和`$arrays[0]`中

所以我们的查询操作的代码可以写成这样：
  
```php
$sql_str = "SELECT * FROM `task` WHERE `name`= $name and `password`= $password";
$result = mysqli_query($con, $sql_str);
if (mysqli_num_rows($result) != 0) {
    $arrays = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $arrays['times']++;
}

```

---

####**改**
继续刚才“查询”中的背景，我们已经完成了“用户登陆时查询出用户并把用户登陆次数+1”的需求，但是我们还要把“+1后的用户登陆次数放回数据库中”，换句话说，就是我们要修改数据库中的用户登陆次数**times**。因为last_time字段设置了ON UPDATE CURRENT_TIMESTAMP属性，它会在修改记录的时候自动更新为当前时间。

首先我们需要书写sql语句，修改语句是：`UPDATE 表名 SET 字段1 = 值1 WHERE 字段2 = 值2`
这句sql语句的意思是从XX表中找到 字段2为值2的记录 修改其中的字段1为值1。

当然也需要使用`mysqli_query()`语句来执行，因为不是查询语句，所以返回值为TRUE或FALSE，代码如下：
```php
$sql_str = "UPDATE `task` SET times = $arrays[times] WHERE id = $arrays[id]";
if (mysqli_query($con, $sql_str)) ;
    else output(0,"服务器或数据库错误,数据写入失败");
```

---
###**小结**

 - 获取前端发来的数据：因为前端使用POST方式传递数据，可以从`$_POST`数组处获取。
 - 处理前端发来的数据：通常我们需要用`isset()`检验数据是否存在，然后检验数据是否规范。
 - 链接数据库：使用`mysqli_connect()`建立链接。只有建立链接后才可操作数据库。
 - 运用mysqli增删查改：书写相应的sql语句，并且用`mysqli_query()`执行，如果是查询语句，使用`mysqli_fetch_array()`提取结果集中的数据，如果不是则会返回TRUE或FALSE。

---

##**完整代码**
```php
<?php

//这个文件是接受信息的文件，你的处理信息的代码需要写在这里

//这个函数用来向前端发送数据，具体见上面。
function output($status, $msg){
    $arr = array('status' => $status,'msg' => $msg);
    echo json_encode($arr);

    //因为$con变量在函数外定义，所以对于函数来说$con是不可见的，我们需要用global $con 让它变得可见。
    global $con;
    mysqli_close($con);
    exit(0);
}

//这个函数用来判断拥有此name或者name+password的记录是否存在。$password = null意为可以只传第一个参数，此时第二个参数默认为null（空的意思）。
//如果存在返回TRUE，不存在返回FALSE。
function query($name, $password = null){
    global $con;
    if ($password != null)
        $sql_str = "SELECT * FROM `task` WHERE `name`= $name and `password`= $password";
    else $sql_str = "SELECT * FROM `task` WHERE `name`= $name";

    /*  try{
            语句1;
        }catch(Exception $e){
            语句2;
        }
        
    这种结构的意思为，尝试执行try里的语句1，如果顺利就不用执行语句2，如果出错就在出错的语句1处中断（停止执行），并执行语句2。
    */
    try {
        $result = mysqli_query($con, $sql_str);
        if (mysqli_num_rows($result) == 0) return false;
        else return true;
    }
    catch (Exception $e) {
        output(0,"服务器或数据库错误");
    }
}

//使用mysqli和服务器建立链接
function connect_init(){
    global $con;
    $dbhost = "127.0.0.1";
    $dbname = "task";
    $user = "root";
    $user_password = "A2817325b@";

    $con = mysqli_connect($dbhost, $user, $user_password, $dbname);
    if (!$con) output(0, "数据库链接失败，请联系管理员");
}

//数据检验
function data_checkout(){
    if (isset($_POST['ins'])){
        if ($_POST['ins'] != 'sign' && $_POST['ins'] != 'login') output(0,'ins不规范');
    }
    else output(0,'ins不存在');
    if (!isset($_POST['userName'])) output(0,'userName不存在');
    if (!isset($_POST['password'])) output(0,'password不存在');
}

//我们在这里创建$con变量用来装数据库链接对象。
$con;
connect_init();
data_checkout();

//当用户做登陆操作时
if ($_POST['ins'] == 'sign'){
    try{
        if (!query($_POST['userName'])){
            //如果用户名没有被占用，执行增加操作
            $sql_str = "INSERT INTO `task`(`name`,password,times) VALUES ($_POST[userName],$_POST[password],0)";
            if (mysqli_query($con, $sql_str)) output(1,'注册成功');
            else output(1,"服务器或数据库错误,数据写入失败");
        }
        else output(1,'注册失败，用户名已被占用');

    }
    catch (Exception $e){
        output(0,"服务器或数据库错误");
    }
}
//当用户做注册操作时
else {

    if (!query($_POST['userName'])) output(0,'用户不存在');

    if (!query($_POST['userName'], $_POST['password'])) output(0,'密码错误');
    else {

        try{
            //查询操作，这里和上面的查询操作例子是一样的
            $sql_str = "SELECT * FROM `task` WHERE `name`= $_POST[userName] and `password`= $_POST[password]";
            $result = mysqli_query($con, $sql_str);

            $arrays = mysqli_fetch_array($result,MYSQLI_ASSOC);
            $arrays['times']++;
            //修改操作，这里和上面的修改操作例子是一样的
            $sql_str = "UPDATE `task` SET times = $arrays[times] WHERE id = $arrays[id]";
            if (mysqli_query($con, $sql_str)) ;
            else output(0,"服务器或数据库错误,数据写入失败");

            if ($arrays['last_time'] != '2017-01-01 00:00:00')
                output(1,'登录成功！这是第'.$arrays['times'].'次登录，上一次登录是'.$arrays['last_time']);
            else output(1,'登录成功！这是第'.$arrays['times'].'次登录');

        }
        catch (Exception $e){
            output(0,"服务器或数据库错误");
        }

    }
}

```

---
###**可能的改进**
细心的同学可能会发现一个小细节，我们当初创建数据库表的时候限制了**name**字段在20长度内，**password**在32长度内，但是我们数据检验的时候没有做这样的限制。当我们把过长的数据插入数据库表的时候就会报错。有兴趣的同学可以想想怎么解决这个问题。
