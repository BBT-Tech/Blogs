# FAQ

这里用于整理新手入门前端时遇到的常见问题，欢迎各位在此补充。

## 为什么我写了代码后在页面上没有看到改动

首先确认你已保存了文件(ctrl + s)

其次可能是由于浏览器缓存的原因，浏览器会对访问过的文件进行缓存，导致了文件更改后浏览器获取到的文件是旧的。

在chrome浏览器中，可以通过ctrl+ F5进行强制刷新。

或者按F12打开控制台，点击 `network` 标签，将 `Disable cache` 的勾勾上，就可以强制浏览器每次都拉取最新的文件。

## 我的网页显示出来是乱码的

如果你使用非专门用于写代码的文本编辑器编辑代码文件(如windows自带的记事本)，文件在保存时很有可能是保存为`GB2312`编码。而浏览器在解析html文件时，需要使用某种编码方式对文件进行解析，这样才能得到正确的结果。即浏览器认为的该html文件的编码 == 该html文件实际编码时，才不会出现乱码。

所以只需要统一这俩边的编码即可，一般来说我们都是用UTF-8编码的。

### 告知浏览器该html文件的编码：在head标签内加入

```html
<meta charset="UTF-8">
```

### 调整文件的实际编码

例如在vscode中，右下角可以选择编码，将其设为`UTF-8`即可

## 我该通过何种方式引用资源文件

#### 前置知识

在windows系统中，文件名分隔符是`\`，例如下面就是常见的一种路径

`C:\Users\yxz\Documents\github\Blogs\Frontend`

但是在除windows外的其他地方，基本上都是用`/`作为文件名分隔符的，在html中也是如此

同时，亦有一些特别的符号有不同的意义，如单个`.`表示当前目录，`..`表示上一级目录

例如，我现在有这样的文件目录架构

```
.
├── admin
│   └── other.html
├── css
│   └── style.css
└── index.html
```

我想在index.html中引入style.css，只需要在head中加入

```html
<link rel="stylesheet" type="text/css" href="./css/style.css" />
```

或者

```html
<link rel="stylesheet" type="text/css" href="css/style.css" />
```

而我想在other.html中引入style.css，则需要

```html
<link rel="stylesheet" type="text/css" href="../css/style.css" />
```

