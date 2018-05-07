<?php

namespace Expert\Controller;

use Think\Controller;

class EmptyController extends Controller {

    public function _empty() {
        //把所有城市的操作解析到city方法
        $this->index();
    }

    public function index() {
        $this->redirect(U("article/articlelist"));
    }

}
