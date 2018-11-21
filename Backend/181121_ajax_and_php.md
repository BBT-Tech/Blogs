Title: 百步梯技术部 2018 级 前后端交互原理入门

Date: 2018.03.09

Author: Jefung

# 前后端交互原理粗略介绍

[本文对应例子](../Demo/ajax_and_php_demo)

ps: 题外话

这篇文章只是简单粗略介绍下原理, 了解原理, 你debug的效率更高, 起码知道具体哪一步
没有成功(请求发出了没?请求头是否设置正确?返回内容是否正确?是前端出错还是后台出错).

最主要是学会抓包(chrome控制台)排错, 有时间照着demo理解下,仔细理解每一个语句/函数的作用.

## 大致原理

![前后台交互大致原理图.png](http://images.jefung.cn/前后台交互大致原理图.png)

## 例子

ajax发送POST请求(带参数), PHP通过$_POST获取数据, 并返回JSON字符串, JS获取字符串并转化为数组
1. ajax控制浏览器发送POST请求
* 代码
```javascript
function send_post() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      // 直接打印数据
      document.getElementById("show_data").innerHTML = "php返回的json字符串: " + this.responseText;

      // 将字符串转为数组,并打印
      var arr = JSON.parse(this.responseText);
      document.getElementById("show_array").innerHTML = "解析后数组: <br/>";
      for(var k in arr){
        document.getElementById("show_array").innerHTML += k + " : " + arr[k] + "<br/>";
      }
    }
  };
  xhttp.open("POST", "php_get_post_arguments.php", true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("username=XS&password=NB"); //参数是有格式要求的
}
```
* chrome抓包

![ajax_post_with_arg.png](http://images.jefung.cn/ajax_post_with_arg.png)

`setRequestHeader`设置请求头,`send()`参数是发送数据,你可以在`Form Data`那里看到

2. http请求从浏览器传输到http服务器

* HTTP请求格式

![http_request.png](http://images.jefung.cn/http_request.png)

* 这里我对例子的请求头简化下,简化后的http请求如下
```
POST /ajax_practice/php_get_post_arguments.php HTTP/1.1
Content-type: application/x-www-form-urlencoded
Content-Length: 23

username=XS&password=NB
```

* http请求转化为*字符流*到`http服务器`,为了美观,我手动换了一行,其实是一行连续的字符串的

```
POST /ajax_practice/php_get_post_arguments.php HTTP/1.1\r\n
Content-type: application/x-www-form-urlencoded\r\nContent-Length: 23\r\n\r\nusername=XS&password=NB
```

上面这一串字符以二进制`0101010101`形式在网络中传输,到达服务器电脑,
然后经过操作系统某些软件(涉及更底层的知识点)处理,会被`http服务器`以字符串形式获取到,然后解析字符串

3. 服务器处理字符流
* 对于获取的字符流,Apache会分析它,重新组成http请求.
* 分析http请求

如果是请求html文件,则直接返回`http响应报文`,报文内容为html文件内容

如果是请求php文件, 则执行php文件(具体怎么执行就是看兴趣深究了)

* 服务器根据请求头进行额外操作,比如服务器会根据 `Content-type : application/x-www-form-urlencoded`
会把传输内容`"username=XS&password=NB"`处理到`$_POST`里,你可以直接在`php`里`$_POST["username"]`获取到
(怎么处理又是另外一回事)

PS: 你可以把不设置`http请求`的`Content-type : application/x-www-form-urlencoded`,看看能不能`$_POST`获取到

4. php文件执行

```php
<?php
// 其实不设置Content-type也没什么问题,前端直接拿到字符串解析.
// 如果不设置的话,默认是 Content-Type: text/html; charset=UTF-8
// 有兴趣自己注释看看chrome的抓包的Content-Type
header('Content-type: application/json');
// 获取前端传来参数
$username =  $_POST["username"];
$password =  $_POST["password"];
$arr = [
	"backend_username" => $username,
	"backend_password" => $password,
];
echo json_encode($arr);
```

PS: php echo的内容会作为响应内容被http服务器发送回前端. 你应该还记得多次`echo`过,
但是只有一个响应,里面是所有`echo`的内容,这里应该是`php`会缓存数据,直到php文件执行完,
再返回一个响应

1. http服务器发送响应
* http响应报文格式

![http_respond.png](http://images.jefung.cn/http_respond.png)

* chrome抓包

![respond_with_json.png](http://images.jefung.cn/respond_with_json.png)

* php控制响应报文
php通过`header()`函数控制响应头的内容.其`echo/var_dump/print`等输出函数,会作为
响应内容返回给前端.

* 同样的,http服务器会将响应报文转为字符流返回给前端. 传输过程和请求报文的传输一样.

6. js 获取返回数据

* 代码
```javascript
function send_post() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      // 直接打印数据
      document.getElementById("show_data").innerHTML = "php返回的json字符串: " + this.responseText;

      // 将字符串转为数组,并打印
      var arr = JSON.parse(this.responseText);
      document.getElementById("show_array").innerHTML = "解析后数组: <br/>";
      for(var k in arr){
        document.getElementById("show_array").innerHTML += k + " : " + arr[k] + "<br/>";
      }
    }
  };
  xhttp.open("POST", "php_get_post_arguments.php", true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("username=XS&password=NB");
}
```
其中,
```javascript
xhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
       // 这里就是有响应时的处理函数,在响应报文来时会自动调用这个函数
       // this.responseText就是获取响应内容,也就是你在chrome控制台respond看到的内容
   }
}
```

* 问: 某个xs的小迷妹问我,这里`readyState == 4`是什么?

答: 发送请求肯定有个过程:

```
0: 请求未初始化
1: 服务器连接已建立
2: 请求已接收
3: 请求处理中
4: 请求已完成，且响应已就绪
```

`xhttp.onreadystatechange`表示每次过程/状态变化就调用这个函数一次,所以,最终执行处理函数
前已经执行过4次,只是`this.readyState == 4 && this.status == 200`这个条件没满足,就跳过了

# JSON
* 为什么需要json? 
方便统一格式传输
* 例子
```php
// php数组
$arr = [
	"username" => $data["username"],
	"password" => $data["password"]
];
// echo $arr; 错误,不能echo数组
// print_r($arr); 正确,可以返回前端
// var_dump($arr); 正确,可以返回前端
// echo json_encode($arr);  正确,可以返回前端
```

```javascript
//js 字典
var arr = {
	username:"xs",
    	password:"xs_pwd"
}
```

```json
// json字符串
{"username":"xs","password":"xs_pwd"}
```

php的数组,不能直接解析为js数组,js获取的只有`字符串`,js根据某种规则(比如json)从字符串解析出来一个数组.

比如这里是:   php数组->json字符串->js数组

* 问: 为什么不直接按照php数组的形式来请求呢?就不需要转化为JSON了.

答: 浏览器接受到的http请求可以由python/c++等语言来发送,每种语言格式都不一样.如果只根据php的数组格式来转化.
那c++发送数组的时候,就要转化为php数组形式的字符串, 这样不如定义一种各种语言通用的格式来传输(比如json)


# http报文中的一个问题

报文头`Content-Length`作用: 表明传输内容的大小. 如果没有这个,服务器/浏览器怎么知道你的请求结束了呢?
要知道,http请求字符流最后没有`\r\n`作为结尾符的.你可以仔细观察我上面我转化的字符串流.http服务器读取到
`\r\n\r\n`就知道报文头结束了,这时候如果`Content-Length`=0,就判断为该请求报文结束, 如果不为0,就读取N个字符
(`Content-Length`=N)然后把该N个字符作为请求内容,这时候请求报文结束


