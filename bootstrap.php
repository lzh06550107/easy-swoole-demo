<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');

//用户想要执行自己需要的初始化业务代码：如 注册命令行支持、全局通用函数、启动前调用协程 API等功能，就可以在 bootstrap.php 中进行编写实现。

//在 bootstrap 事件 中注册自定义命令。