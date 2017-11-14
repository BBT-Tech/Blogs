# 写在前面的话

Author: 邹盛熠

本系列文章是百步梯技术部面向新人的教程，本人也很辣鸡，所以如果报道出现了偏差，还请及时指正。本文也会有着比较强烈的个人偏好因素（逃），如果你有自己的开发或者学习习惯，请相信你自己并坚持下去！

# 关于本篇文章

入坑指南原定的第二篇被我鸽了...应部门培训进度的安排，先谈一谈 Web 中的网络请求吧。

# HTTP 协议基础

## 工作场景 & 请求过程

HTTP，全名 HyperText Transfer Protocol （超文本传输协议），它用于传送 WWW 方式的数据，是一个 client 与 server 之间请求和应答的标准。你们平时接触到的网页访问就是一次 http 请求，client 是你用的浏览器，server 是你访问的目标域名指向的服务器，浏览器发起一个到服务器指定端口的 http 请求，服务器再作出答复，返回一个页面。

我们需要注意的是，client 与 server 只是一个相对的概念，只存在于一个特定的连接期间，即在某个连接中的 client 在另一个连接中可能作为 server。比如你用浏览器向服务器 A 获取了资源（此时 A 是 server），A 也可以向另一个服务器 B 获取资源（此时 A 是 client）。

一次 http 请求包含了四个过程：client 与 server 建立连接、client 发送请求信息、server 发送响应信息、关闭连接。

## 报文结构

### 请求报文

```
GET http://oidiotlin.com/ HTTP/1.1
Host: oidiotlin.com
Connection: keep-alive
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36
Upgrade-Insecure-Requests: 1
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
Accept-Encoding: gzip, deflate
Accept-Language: zh-CN,zh;q=0.8
Cookie: vDDoS=355b2c1cc6ce845d4153e7b6878d99d6;
```

上面是访问某博客页面时的请求报文，通常一个 http 请求报文包含请求行（request line）、请求头部（header）、空行和请求数据（request-body）4个部分。

首先来看请求行的字段（用空格分隔）：

* 请求方法：`GET`
* URL：`http://oidiotlin.com/`
* HTTP 协议版本：`HTTP/1.1`

再来看 Header 部分，由关键字/值对组成，每行一对，关键字和值用英文冒号“:”分隔。请求头部通知服务器有关于客户端请求的信息，典型的请求头有：

* `User-Agent`：产生请求的浏览器类型。
* `Accept`：Client 可以识别的内容类型列表。
* `Host`：请求的主机名（多个域名可以共处一个 IP）

请求数据在上面这个 GET 方法的示例中并不存在，我们待会儿讲 POST 时再做讨论。

### 响应报文

```
HTTP/1.1 200 OK
Server: nginx/1.6.2
Date: Mon, 13 Nov 2017 11:36:43 GMT
Content-Type: text/html; charset=utf-8
Content-Length: 14419
Connection: keep-alive
X-Powered-By: Express
Cache-Control: public, max-age=0
ETag: W/"3853-LXriuiDSMWJJaeQ904NAPBBpw3g"
Vary: Accept-Encoding

<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title>OIdiot&#x27;s Blog</title>
    <meta name="HandheldFriendly" content="True" />
    <!-- 省略 html 后文 -->
```

上面是访问某大佬的博客时，服务器返回的 HTTP 响应报文，通常 HTTP 响应报文包含三个部分：状态行、消息报头、响应正文。

先来看状态行：

* HTTP 协议版本：`HTTP/1.1`
* 状态码：`200`
* 状态码的文本描述：`OK`

再看 Header 部分，格式与请求报文的 Header 相同。消息报头通知客户端有关于响应内容的信息，典型的字段有：

* `Cache-Control`：缓存控制。
* `Content-Type`：响应格式（类型）。
* `Content-Length`：响应的 content-body 的长度。

## 请求方式 (Request Method)

之前我们在请求报文中看到请求方法这一字段是 GET，其实 HTTP 协议的请求方法有GET、POST、HEAD、PUT、DELETE、OPTIONS、TRACE、CONNECT。但是较为常用的主要是 GET 和 POST 这两种。

### GET 请求

GET 应当是最为常见的请求了，每当 client 要从服务器中读取文档时（比如我们访问某个页面），一般都是使用 GET 方式。GET 方法要求服务器将 URL 定位的资源放在响应报文的数据部分，回送给客户端。使用 GET 方法时，请求参数和对应的值附加在 URL 后面，利用一个问号（?）代表 URL 的结尾与请求参数的开始。

比如在必应搜索 'scut' 时，会访问到 `https://cn.bing.com/search?q=scut&qs=n`，我们来看它的请求报文：

```
GET https://cn.bing.com/search?q=scut&qs=n HTTP/1.1
Host: cn.bing.com
Connection: keep-alive
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36
Upgrade-Insecure-Requests: 1
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
# 省略了不必要的信息
```

可以看到，GET 方式的请求一般不包含请求内容部分，请求数据以地址的形式表现在请求行。上文中的 path 是 `/search`，参数有两个：`q` - scut，`qs` - n。

地址中 ? 之后的部分就是通过 GET 发送的请求数据，我们可以在地址栏中清楚的看到，各个数据之间用 & 符号隔开。显然，这种方式不适合传送私密数据。另外，由于不同的浏览器对地址的字符限制也有所不同，一般最多只能识别1024个字符，所以如果需要传送大量数据的时候，也不适合使用 GET 方式。

GET 方法在协议规范也允许像 POST 请求那样拥有 request-body，但一般我们不这样做。

### POST 请求

不适合 GET 方式的请求，我们一般都可以考虑使用 POST。POST 可以请求参数放在 request-body 中，也可以传输大量的数据，不再受 url 长度的拘囿，当然也不会显示在 url 中。

我们依然以在必应搜索 'scut' 为例，POST 请求报文如下：
```
POST https://cn.bing.com/search HTTP/1.1
Host: cn.bing.com
Connection: keep-alive
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
# 省略了不必要的信息

q=scut&qs=n
```

可以看到，我们将参数放在了之前提到过的 request-body 处，各参数之间也是使用 & 符号隔开。开发过程中，request-body 的格式可以多种多样，常用的格式有 json 等，下面会具体的讲到。

### GET 与 POST 之间的差异

* GET提交的数据会在地址栏中显示出来，而 POST 提交，地址栏不会改变。
* 特定浏览器和服务器对 URL 长度有限制，例如 IE 对 URL 长度的限制是2083字节(2K+35)。对于其他浏览器，如 FireFox 等，理论上没有长度限制，其限制取决于操作系统的支持。因此对于 GET 提交时，传输数据就会受到 URL 长度的限制。而 POST 不通过 URL 传值，理论上数据大小就不受限制（虽然各个 web 服务器也会对 POST 数据大小进行限制，那是后话了）。
* POST 的安全性要比 GET 的安全性高。GET 时，数据全部展露在 URL 中，一览无遗。

请按照你们的需求规划选用 GET 或 POST。

## 状态码 (Status Code)

在讨论响应报头的时候，我们说到了状态码这种东西，它表达了服务器对于当前这一 HTTP 请求的响应状态。通常而言，该状态码由三位数组成，第一个数字定义了响应的类别，取值为 1~5：

* 1xx：指示信息--表示请求已接收，继续处理。
* 2xx：成功--表示请求已被成功接收、理解、接受。
* 3xx：重定向--要完成请求必须进行更进一步的操作。
* 4xx：客户端错误--请求有语法错误或请求无法实现。
* 5xx：服务器端错误--服务器未能实现合法的请求。

我们常遇见的状态码及描述如下：

* 200 OK：客户端请求成功。
* 400 Bad Request：客户端请求有语法错误，不能被服务器所理解。
* 401 Unauthorized：请求未经授权，这个状态代码必须和 WWW-Authenticate 报头域一起使用。
* 403 Forbidden：服务器收到请求，但是拒绝提供服务。
* 404 Not Found：请求资源不存在，比如输入了错误的 URL。
* 500 Internal Server Error：服务器发生不可预期的错误。
* 503 Server Unavailable：服务器当前不能处理客户端的请求，一段时间后可能恢复正常。

## Content-Type

请求内容（request-body）和响应内容（response-body）需要一定的格式规范，常用的格式有：

* application/json

```
POST http://localhost/ HTTP/1.1
Host: localhost
Content-Length: 27
Content-Type: application/json
# 省略了不必要的信息

{
	"data": "hello, world"
}
```

JSON 数据将以其本身的格式（RAW）直接放在 body 区域。

* multipart/form-data

```
POST http://localhost/ HTTP/1.1
Host: localhost
Content-Length: 13968
Content-Type: multipart/form-data
# 省略了不必要的信息

------WebKitFormBoundaryeB4XsQPMzE4lGQrR
Content-Disposition: form-data; name="data"

hello, world
------WebKitFormBoundaryeB4XsQPMzE4lGQrR
Content-Disposition: form-data; name="file"; filename="eventlog_provider.dll"
Content-Type: application/x-msdownload
binary file content....
```

可以看到，如果这种格式下，body会被分成多个 boundary，每个 boundary 由

```
------identifier
```

的形式分开，这个 identifier 是由浏览器自动生成的，无需开发者去操心，每个boundary 还有它的 name，用于接收请求端的处理。如果是上传了一个文件，还将包含文件本身的 content-type，所以这个 content-type 是我们在前端中上传文件所选用的主要类型。

* application/x-www-form-urlencoded

```
POST http://localhost/ HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
# 省略了不必要的信息

data=hello%2C+world&status=233333
```

请求的 body 如同 GET 请求中那样被编码成了 url 参数的形式。


无论是何种的类型的 content-type 都要在请求或者响应 header 中明确指出，如果设置了错误的 content-type，无论是后台获取前端数据，还是前端解析后台返回数据都可能会出现意外的问题！

# Javascript 具体实现

在浏览器中，浏览器在遇到网页中显式指定的 URL 时，都会发起 HTTP 请求，比如你在地址栏输入一个地址，按下回车的瞬间就会发起请求去获得对应的资源，页面上`<script src="url">`、`<img src="url">`，甚至 CSS 中的`background-image: url(url)`，都会触发一个 HTTP 请求。利用`<form>`标签与`<input`标签的组合，我们还能手动控制浏览器去发起指定类型的请求。

但是，部门内部前后端分离的任务要求下，以上还远远不够，比如用 `<form>` 发起请求会发生页面跳转造成体验的割裂，又或是不能即时的刷新网页内容，只能依靠用户手动刷新等等。我们还需要借用 Javascript 的力量来完成想要的事情。

在 Javascript 实现 HTTP 请求依靠的是 AJAX(Asynchronous Javascript And XML) 技术，虽然名字中有 XML 的字样，但是不止能请求 XML 哦。

实现 AJAX 的具体方法常见的有 `XMLHttpRequest` 和 `Fetch API`，因为 Fetch 比较新，浏览器兼容性还不够理想，这里不细谈。当然，非常鼓励你们去学习 Fetch API。

以下实现不一定安全，也不一定完美，但是可以满足基础的需求，仅供参考。

相关资料可以在 [MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/XMLHttpRequest) 上找到。

## Get 的实现

```javascript
const get = function get(url, data, successHandle, errorHandle) {
    let xmlhttp = new XMLHttpRequest();
    if (xmlhttp != null) {
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4) { // 4 = "loaded"
                if (xmlhttp.status == 200) { // 200 = "OK"
                    successHandle(xmlhttp.responseText);
                } else {
                    errorHandle(xmlhttp.statusText);
                }
            }
        }
        xmlhttp.open("GET", url + '?' + data, true);
        xmlhttp.send(null);
    }
}

get("http://localhost/", "data=2333", function (res) {
    let data = JSON.parse(res);
    document.getElementById("app").textContent = data.value;   // DOM 操作
}, function(error) {
    alert(error);
})
```

## Post 的实现

与 GET 方法大同小异，可以在看懂 GET 的实现后自己尝试着改一下，不懂就去查上面的 MDN 链接。

## 使用第三方库

常见的带有 AJAX 实现的库有 `axios`, `jQuery`等。如果你了解了上面的基本实现，那么看它们的文档你会很快理解这些第三方库并用于实践。

# 展现请求内容

通过 AJAX 拿到了数据，我们自然需要在页面中进行展现，浏览器提供了一组强大而灵活的 DOM API，使 Javascript 可以更改网页上的内容。

以下是实例代码：
```javascript
let app = document.getElementById("app");
app.classList.add("expanded");    // 对 id 为 app 的元素添加名为 expanded 的 class 以实现动态的对元素与 CSS 的绑定
app.textContent = "Hello!";       // 修改 id 为 app 的元素的文本内容为 Hello

// 向 DOM 中插入一个节点
let newDiv = document.createElement("span");
newDiv.textContent = "2333";
app.appendChild(newDiv);
```

DOM 操作不是本篇文章的重点，但也是完成最近这次任务的关键，请自主去学习。

# Cookie 与 Session

Cookie 指的是浏览器储存在用户本地终端上的数据，开发者可以用来临时存储数据，也可以用来保存用户的登陆状态。但是请务必记住一点：**Cookie 可以很轻易的被用户修改，请务必二次验证，不要完全放心和依赖**

## Cookie 的实现原理

在一个 HTTP 响应的 Header 中，允许有 `Set-Cookie` 字段，其可以设置客户端的 Cookie。例如：

```
Set-Cookie: id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT;
```

上面一行就在浏览器中保存了键为 id，值为 a3fWa 的数据，其过期时间为 Wed, 21 Oct 2015 07:28:00 GMT。

当然 Javascript 也可以使用 `document.cookie` 来得到和修改 cookie，但是这是操作字符串的行为，获取具体的值需要手动用代码去 parse。

当设置好一个 Cookie 后，浏览器在向这个域名（Domain）发起的每个请求就会带上这个 Cookie，声明在请求的 Header 的 `Cookie` 字段中，后台就能拿到 Cookie 并做出相应的判断啦。

```
GET http://app.delbertbeta.cc/messageboard/apis/get_messages.php
Connection:keep-alive
Cookie:id=1; token=054c34f3fede60da3ab0d3ed97dde6cb     # Cookie 在这里
Host:app.delbertbeta.cc
Referer:http://app.delbertbeta.cc/messageboard/index.html
```

## Session

Session 其实就是一个特殊的 Cookie，但其在 Cookie 中储存的是一个键为 SESSIONID 的数据，真正的数据其实是保存在服务器上，服务器接受到 SESSIONID 后，就会在自身的数据结构中寻找该 ID 对应的储存的真正的数据。这些数据对于客户端（浏览器）来说是完全不透明的。所以可以用来存放稍微隐私或更重要的数据。但要注意，**滥用 SESSION 在请求并发量大时会造成服务端性能问题**。

# 跨域问题

如果你尝试在你自己写的站点中去 ```GET baidu.com```，一般是不会成功的，因为浏览器的安全机制，为了防止跨站攻击，一般拒绝了你跨域发起请求。

## 什么是跨域？

如果你当前的域名是 `delbertbeta.cc`，但是你请求的是 `baidu.com`，这是一种跨域，以下情况都属于跨域现象：

* 域名不同，如 `delbertbeta.cc` 和 `baidu.com`
* 端口不同，如 `delbertbeta.cc` 和 `delbertbeta.cc:8000`
* 协议不同，如 `http://delbertbeta.cc` 和 `ftp://delbertbeta.cc`

## 开发过程如何解决跨域问题

如果你是在本机上利用 php 进行开发，那多半不会遇到跨域问题，但是如果你使用 node 或 python开发，它们往往会另开端口提供服务，触发跨域。或者你需要调用远程的 API，这种情况在你合作开发或调第三方 API 时很常见。

* 在开发时利用 Apache 或 Nginx 对 API 进行反向代理，上线时同样部署反代。
* 配置 Access-Control-Allow-Origin 头
* 转用 JSONP

跨域属于比较进阶的内容，以上只提供思路，可以自己搜索学习解决。

# 开发/调试工具

浏览器中开发者工具已经在上一篇的前端教程中详细阐述了。如果忘记了请回去复习吧。

[Tutorial-1](https://delbertbeta.cc/2017/09/24/frontend-tutorial-1/)

## Fiddler

对 HTTP/HTTPS 协议请求抓包的软件，除了可以调试浏览器，也可以调试其他的 App。是对整个系统全局抓包的工具。

![Fiddler](/content/images/2017/11/Fiddler.png)

## Postman

Postman可以用来模拟发送 HTTP 请求，用于测试接口。Postman 不需要单独下载安装，是个Chrome App，直接在 Chrome 商店下载即可，如果下载困难，其实这类软件的替代品有很多，比如 [Apizza](http://apizza.cc/)

![Postman](/content/images/2017/11/Postman.png)

# 设计一个 Web 程序请求接口

## 什么是 Web 程序请求接口

之前你们的代码都是未经过前后端分离处理的，前端代码混杂着后台业务逻辑，不便于管理维护，也不便于阅读，而现在你们将要学习全程使用 API 来实现数据的交互。

而我们用 AJAX 发起请求，请求的其实就是接口。所谓接口简单来说就是向指定的 URL 发送指定的参数，服务器按照要求完成指定的操作并返回数据的一系列行为的约束。这个行为就是向接口发起请求。

## 接口文档

实现了接口，我们要告知别人如何使用，便于团队合作，并确定锅的归属，同时也是提醒自己到底写了什么代码起到一个备忘的作用。

### 包含要素

一个最基本的接口文档应包含一下要素：

* 接口的 URL
* 接口的功能描述
* 接口的请求方法(GET/POST)
* 接口的参数类型、参数列表和示例请求
* 接口的返回示例
* 接口的特殊说明（如有附加要求等）

### 参考范例

以下是多种风格的 API 文档

* [百步梯 2017 年爱上你主播投票·前后台接口文档](https://github.com/BBT-Tech/BBT-17-VOICERS-VOTE/blob/master/Docs/百步梯%202017%20年爱上你主播投票·前后台接口文档.md)

* [TuYang](https://github.com/sticnarf/TuYangAPI)

* [Mastodon API](https://github.com/tootsuite/documentation/blob/master/Using-the-API/API.md)

* [Vultr API](https://www.vultr.com/api/)

* [云片网 短信API](https://www.yunpian.com/doc/zh_CN/scene/smsverify.html)

大家可以参考，并找到自己喜欢的文档风格进行学习模仿。

Delbertbeta

2017-11-14 22:08