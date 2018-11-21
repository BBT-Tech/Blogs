Title: 百步梯技术部 2018 级 前后端交互原理入门

Date: 2018.03.09

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
// json对象
{"username":"xs","password":"xs_pwd"}
```

php的数组,不能直接解析为js数组,js获取的只有`字符串`,js根据某种规则(比如json)从字符串解析出来一个数组.

比如这里是:   php数组->json字符串->js数组

* 问: 为什么不直接按照php数组的形式来请求呢?就不需要转化为JSON了.

答: 浏览器接受到的http请求可以由python/c++等语言来发送,每种语言格式都不一样.如果只根据php的数组格式来转化.
那c++发送数组的时候,就要转化为php数组形式的字符串, 这样不如定义一种各种语言通用的格式来传输(比如json)


# HTTP请求
![http_request.png](http://images.jefung.cn/http_request.png)

## 简单的`http post`请求(删除部分请求头)
* chrome控制台截图

![微信图片_20181112194357.png](http://images.jefung.cn/微信图片_20181112194357.png)

* 简化请求

```
POST /ajax_practice/php_get_post_arguments.php HTTP/1.1
Content-type: application/x-www-form-urlencoded
Content-Length: 23

username=XS&password=NB
```

* 发送请求(传输字符流到服务端)

```
POST /ajax_practice/php_get_post_arguments.php HTTP/1.1\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: 23\r\n\r\nusername=XS&password=NB
```

* 过程解释

![http_from_brower_to_server.png](http://images.jefung.cn/http_from_brower_to_server.png)

## 请求头/Request Header的作用?
* Content-type: 表示内容类型
    * Content-type: application/x-www-form-urlencoded
    ```javascript
    // 发送请求,让php能够使用$_POST获取
    function send_post() {
    	var xhttp = new XMLHttpRequest();
  	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
      		document.getElementById("demo").innerHTML = this.responseText;
    	}
  	};
	xhttp.open("POST", "php_get_post_arguments.php", true);
  	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  	xhttp.send("username=XS&password=NB");
  	}
    ```
服务器/浏览器会根据`Content-type`的值帮你处理一些问题.比如上面的`application/x-www-form-urlencoded`
会让服务器帮你把传输内容`"username=XS&password=NB"`处理到`$_POST`里,你可以直接在`php`获取到

    
   *  Content-type: application/application/json
   
   ```javascript
   function send_post_with_json() {
  	var xhttp = new XMLHttpRequest();
  	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
      		document.getElementById("demo2").innerHTML = this.responseText;
    	}
	};
   xhttp.open("POST", "php_get_json.php");
   xhttp.setRequestHeader("Content-Type", "application/json");
   xhttp.send(JSON.stringify({username:"xs", password:"xs_pwd"}));
   }
   ```

* Content-Length: 表明传输内容的大小. 如果没有这个,服务器/浏览器怎么知道你的请求结束了呢?
要知道,http请求字符流最后没有`\r\n`作为结尾符的
