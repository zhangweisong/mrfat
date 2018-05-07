<?php
namespace Admin\Controller;
use Think\Controller;

class DatabakController extends Controller {

    function __construct() {
		//继承父类
        parent::__construct();
		//判断登录状态
       if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }

	//空方法，防止报错
    public function _empty() {
        $this->index();
    }

	//备份列表
    public function index() {
        $fileArr = $this->MyScandir(C("DB_BACKUP"));
        foreach ($fileArr as $key => $value) {
            if ($key > 1) {
                //获取文件创建时间
                $fileTime = date('Y-m-d H:i:s', filemtime(C("DB_BACKUP"). $value));
                $fileSize = filesize(C("DB_BACKUP") . '/' . $value) / 1024;
                //获取文件大小
                $fileSize = $fileSize < 1024 ? number_format($fileSize, 2) . ' KB' : number_format($fileSize / 1024, 2) . ' MB';
                //构建列表数组
                $listinfo[] = array(
                    'name' => $value,
                    'time' => $fileTime,
                    'size' => $fileSize
                );
            }
        }
        $this->assign('listinfo', $listinfo);
        $this->display();
    }
	//备份数据库
	public function backdb(){
		$tables=M()->query("SHOW TABLE STATUS FROM ".C('DB_NAME'));
        $content = '/* This file is created by MySQLReback ' . date('Y-m-d H:i:s') . ' */';
	    //dd($tables);
        foreach ($tables as $i => $table) {
            $table = $this->backquote($table['name']);                 //为表名增加 ``
            $tableRs = M()->query("SHOW CREATE TABLE {$table}");
			//获取当前表的创建语句
            if (!empty($tableRs[0]["create view"]) || !empty($tableRs[0]["create view"])) {
                $content .= "\r\n /* 创建视图结构 {$table}  */\r\n";
                $content .= "\r\n DROP VIEW IF EXISTS {$table};/* MySQLReback Separation */ " . $tableRs[0]["create view"]?$tableRs[0]["create view"]:$tableRs[0]["create view"] . ";/* MySQLReback Separation */";
            }
            if (!empty($tableRs[0]["create table"]) || !empty($tableRs[0]["create table"])) {
                $content .= "\r\n /* 创建表结构 {$table}  */\r\n";
                $content .= "\r\n DROP TABLE IF EXISTS {$table};/* MySQLReback Separation */ " . $tableRs[0]["create table"]?$tableRs[0]["create table"]:$tableRs[0]["create table"] . ";/* MySQLReback Separation */";
                $tableDateRow = M()->query("SELECT * FROM {$table}");
                $valuesArr = array();
                $values = '';
                if (false != $tableDateRow) {
                    foreach ($tableDateRow as &$y) {
                        foreach ($y as &$v) {
                           if ($v=='')                                  //纠正empty 为0的时候  返回tree
                                $v = 'null';                                    //为空设为null
                            else
                                $v = "'" . mysql_escape_string($v) . "'";       //非空 加转意符
                        }
                        $valuesArr[] = '(' . implode(',', $y) . ')';
                    }
                }
                $temp = $this->chunkArrayByByte($valuesArr);
                if (is_array($temp)) {
                    foreach ($temp as $v) {
                        $values = implode(',', $v) . ';/* MySQLReback Separation */';
                        if ($values != ';/* MySQLReback Separation */') {
                            $content .= "\r\n /* 插入数据 {$table} */";
                            $content .= "\r\n INSERT INTO {$table} VALUES {$values}";
                        }
                    }
                }
            }
		}
		$fileName = C("DB_BACKUP"). date('YmdHis') . '_' . mt_rand(100000000, 999999999) . '.sql';
		if (!file_put_contents($fileName, $content, LOCK_EX)) {
			jump('写入文件失败,请检查磁盘空间或者权限!',U('databak/index'),3);
		}
		jump('备份数据库成功！',U('databak/index'),3);
	}
	//删除备份
	public function delbak() {
		$filepath=C("DB_BACKUP").I("name");
		@unlink($filepath);
		jump('删除备份成功！',U('databak/index'),3);
    }
	//备份下载
	public function downloadbak() {
		$filepath=C("DB_BACKUP").I("name");
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filepath));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		readfile($filepath);
    }

	/* -
     * +------------------------------------------------------------------------
     * * @ 获取 目录下文件数组
     * +------------------------------------------------------------------------
     * * @ $FilePath 目录路径
     * * @ $Order    排序
     * +------------------------------------------------------------------------
     * * @ 获取指定目录下的文件列表，返回数组
     * +------------------------------------------------------------------------
     */

    private function MyScandir($FilePath = './', $Order = 0) {
        $FilePath = opendir($FilePath);
        while ($filename = readdir($FilePath)) {
            $fileArr[] = $filename;
        }
        $Order == 0 ? sort($fileArr) : rsort($fileArr);
        return $fileArr;
    }
	/* -
     * +------------------------------------------------------------------------
     * * @ 给字符串添加 ` `
     * +------------------------------------------------------------------------
     * * @ $str 字符串
     * +------------------------------------------------------------------------
     * * @ 返回 `$str`
     * +------------------------------------------------------------------------
     */

    private function backquote($str) {
        return "`{$str}`";
    }
	/* -
     * +------------------------------------------------------------------------
     * * @ 把传过来的数据 按指定长度分割成数组
     * +------------------------------------------------------------------------
     * * @ $array 要分割的数据
     * * @ $byte  要分割的长度
     * +------------------------------------------------------------------------
     * * @ 把数组按指定长度分割,并返回分割后的数组
     * +------------------------------------------------------------------------
     */
    private function chunkArrayByByte($array, $byte = 5120) {
        $i = 0;
        $sum = 0;
        $return = array();
        foreach ($array as $v) {
            $sum += strlen($v);
            if ($sum < $byte) {
                $return[$i][] = $v;
            } elseif ($sum == $byte) {
                $return[++$i][] = $v;
                $sum = 0;
            } else {
                $return[++$i][] = $v;
                $i++;
                $sum = 0;
            }
        }
        return $return;
    }
}
