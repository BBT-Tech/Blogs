# 百步梯之弹幕大作战

曾经的我们，也和在座的大佬一样，背了一个这样的锅。

那么，对于这个小组任务里面，你们能够学习或者是强化哪些部分?

## Websocket

**为什么需要Websocket？**

1. 回想一下，到目前为止所写的任务，通信是不是都由客户端发起。答案是肯定的，这同时也是HTTP协议的缺陷。HTTP的请求只能从客户端开始，客户端不可以接受除响应以外的指令。

2. 紧接着将情景置于弹幕中，你会发现你需要一种技术，当你发布弹幕的同时你需要实时将你所发布的弹幕显示在所有观众的手机上。

   但是由于在👆指出服务器无法利用HTTP协议主动将你的弹幕分发给所有的观众。问题来了，如何解决。

3. 一个很容易想到的方法是：轮询。 客户端可以设置定时器在特定的时间间隔（如每1s）向服务器发起请求，来获取最新的弹幕列表。哈哈哈，问题貌似解决了。那么问题又来了，这种做法是最优解吗？

   事实上，很容易发现这种做法非常浪费服务器资源，尤其是在人数多的时候，会导致服务器压力增大。

4. 所以，Websocket就是基于类似这样的需求被发明出来啦~

5. In fact, 这里同样由一些介于 轮询 与 Websocket之间的方案，

   + 长连接（Comet的实现方式总称）
     + 长轮询
     + ~~基于Iframe即htmlfile的流方式~~
   + AJAX multipart streaming
   + Flash Socket
   + SSE（Server-sent Event）

**Websocket简介**

Websocket，即Web浏览器与Web服务器之间全双工通信标准的一种协议，为C/S两端提供了实时交互通信的能力，是一种区别于HTTP的全新双向数据流协议。

它最大的特点在于，客户端可以主动向服务器发送请求，同时服务器也可以向客户端推送消息。

当然，说是双向通向，事实上在建立的过程中发起方仍然是客户端，在连接建立完成才能真正做到双向通信。

当客户端与服务器之间建立WebSocket协议的连接后，通信过程中可以互相发送图片等任意格式的数据。

**Websocket的主要特点**

+ 平等建立连接

+ 持久性连接

+ 更强实时性

+ 更好的数据格式支持：不论是JSON，文本，还是图片，甚至是二进制数据

**如何实现（仅提供思路）**

+ 客户端

  通过Web API中的`Websocket对象`，它提供了创建和管理连接，并且通过连接进行数据的传送和接收的API

  通过给`Websocket对象`添加监听函数、调用对象方法 进行数据的传送和接收

  代码可供参考链接 [MDN WebsocketAPI](https://developer.mozilla.org/zh-CN/docs/Web/API/WebSocket) 及 [阮一峰的Websocket教程](http://www.ruanyifeng.com/blog/2017/05/websocket.html)

+ 服务器

  大概流程：

  1. 创建支持`websocket`的服务
  2. 当服务器收到通过`Webcoket连接`的请求后，对数据进行处理（如敏感词过滤，储存在数据库等），之后通过广播的形式，将处理后的数据发送给所有与服务器建立`Webcoket连接`的用户。
  3. 当你搜索Demo的时候你可能会发现，哈哈，好多nodejs样例！

  具体实现方法请大伙发挥双一流大学生的学习能力和搜索能力吧，冲冲冲！


## 动画

真实生活中随处可见的动态视野如何将其应用在电视中？随着聪明机智的`歪果仁`发现并加以发展下，在很久的以后造福了这么一群二次元爱好者~。它的原理是什么呢？

医学证明人类具有“视觉暂留”的特性，人的眼睛看到一幅画或一个物体后，在0.34秒内不会消失。利用这一原理，在一幅画还没有消失前播放下一幅画，就会给人造成一种流畅的视觉变化效果。[引自[百度百科](https://baike.baidu.com/item/%E5%8A%A8%E7%94%BB/206564?fr=aladdin)]。并且在[维基百科](https://zh.wikipedia.org/wiki/%E8%AE%A1%E7%AE%97%E6%9C%BA%E5%8A%A8%E7%94%BB)中建议，在每秒内至少**需要12帧**，而在**70帧**以后，真实度和平滑度不能在有所改善。（可能你会发现很多人写的定时器是50ms，或许并不是随意捏造的，反正它就是都在这个帧数范围内）

同样，对于计算机动画而言也是基于这种原理。通过利用人眼的视觉暂留特性，快速更换元素，最后制作出动画来。

**纯CSS实现**

关键属性： `animation`，`transition`，`Keyframe`，`position`

**定时器实现**

+ **纯JS实现**

+ **Canvas实现**

你也许有**可能（概率应该极低，简简单单的一个弹幕怎么会难到你们呢）**会遇到过一个问题，当弹幕的条数过高的时候竟然发生了些许卡顿现象，这时候你可能需要了解一下 浏览器渲染元素的过程。



#### **回流与重回**

在查找资料的时候，发现[两篇博文](#参考链接)（<u>你真的了解回流和重绘吗?</u>  <u>前端性能优化</u>）个人觉得写的非常棒，菜鸡学长推荐给大伙学习学习。

当你看完上面的博文后，为了让你的弹幕更加🐮逼，你可能在给自己的弹幕加点小优化呦。

这里主要大概介绍下

对于一个网页来说，从客户端接收到文件直至它被渲染在屏幕上需要经过这么几个步骤

![render](../Image/190402_barrage_render.png)

1. 解析HTML，生成DOM树

2. 解析CSS，生成CSSOM树

3. 将DOM树与CSSOM树进行组合，生成渲染树（Render Tree）

   ![CSSOM+DOM](../Image/190402_barrage_render_tree.png)

4. Layout（回流）：根据渲染树进行回流，在所属的图层上生成图形和位置，并且得到其几何属性（尺寸等）

5. Painting（重绘）：填充节点的修饰内容（如字体颜色，背景等）

6. Composite Layers：将所有的图层组合到页面上。

**重绘代价**

重绘一般只是改变元素的外观。当你改变某节点的某些属性时，它仅仅只会发生重绘。

主要有以下属性会进行重绘：

> * color
>
> * border-style
>
> * border-radius
>
> * visibility
>
> * text-decoration
>
> * background
>
> * background-image
>
> * background-position
>
> * background-repeat
>
> * background-size
>
> * outline-color
>
> * outline
>
> * outline-style
>
> * outline-width
>
> * box-shadow
>
>   **opacity（竟然不需要重绘，原因竟是。。。**

**回流代价**

有些节点，当你改变它时，会需要重新布局（这也意味着需要重新计算其他被影响的节点的位置和大小）。这种情况下，被影响的DOM树越大（可见节点），重绘所需要的时间就会越长，而渲染一帧动画的时间也相应变长。所以需要尽力避免这些属性，主要有

- 添加或删除可见的DOM元素

+ 盒子模型相关属性（位置或者尺寸的改变）

  > * width
  > * height
  > * padding
  > * margin
  > * display
  > * border-width
  > * border
  > * min-height

+ 定位属性及浮动

  > * top
  > * bottom
  > * left
  > * right
  > * position
  > * float
  > * clear

+ 改变节点内部文字结构（内容发生变化）

  > * text-align
  > * overflow-y
  > * font-weight
  > * overflow
  > * font-family
  > * line-height
  > * vertival-align
  > * white-space
  > * font-size

+ 浏览器的窗口尺寸变化

**注意：回流一定会触发重绘，而重绘不一定会回流**

**transform**在何方： 没错，`transform`属性在`Composite Layers`,它既不属于回流，也不属于重绘

**优化**

1. CSS属性改变一次性写入

2. 多使用`transform 和 opacity `吧

   例如这次弹幕中可能有人的动画会这么干

   > @keyframes move{
   >
   > ​	from{
   >
   > ​		left: 100%;
   >
   > ​		transform: translateX(0);
   >
   > ​	}
   >
   > ​	to{
   >
   > ​		left: 0%;
   >
   > ​		transform: translateX(-100%);
   >
   > ​	}
   >
   > }

   为什么不直接用JS计算 视频窗口的宽度作为translateX的参数，而抛弃掉left呢（由于left的变化会导致回流）

   

   

#### 参考链接

[WebSocket 教程- 阮一峰的网络日志](http://www.ruanyifeng.com/blog/2017/05/websocket.html)

[WebSocket - Web API 接口参考| MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/WebSocket)

[全双工通信的Websocket](https://halfrost.com/websocket)

[Websocket简介及其应用实例](https://juejin.im/post/5ae3eb9b51882567382f5767)

[你真的了解回流和重绘吗？](https://segmentfault.com/a/1190000017329980)

[前端性能优化](https://segmentfault.com/a/1190000000490328)



#### 最后

本小编好像很辣鸡，所以嘞如果在教程中你认为描述的不对，还请及时指正。各位的进步是我们最大的快乐hhh。



Author: 陈嘉奖 

日期：2019/4/1