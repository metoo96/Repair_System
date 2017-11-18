<?php
namespace backgroundAdmin\Controller;
use Think\Controller;
class UserController extends Controller {
    public function index(){

    }
    private function successReturn($msg){
    	$res['msg'] = $msg;
    	$res['success'] = true;
    	$this->ajaxReturn($res);
    }
    private function errorReturn($errmsg){
    	$res['success'] = false;
    	$res['errmsg'] = $errmsg;
    	$this->ajaxReturn($res);
    }
    private function checkData($data){
    	foreach ($data as $key => $value) {
    		if($data[$key] == null){
    			$this->errorReturn("信息不全");
    		}
    	}
    }
    private function setAdminMsg(){
        $data = M('admin')->where(array('student_number'=>cookie('admin')))->find();
        $this->assign('admin', $data);
        return $data['id'];
    }
    private function checkCookie($account,$cookie){
        $data = M('admin_cookie')->where(array('account'=>$account))->find();
        if($data == null){
            $this->error('登录状态出现不可预料的意外');
        }
        if($data['cookie'] != $cookie){
            cookie('login', null);
            cookie('admin', null);
            $this->error('登录超时');
        }
    }
    private function checkLogin(){
        if(cookie('admin') == null){
            $this->error('登录超时');
        }
        $this->checkCookie(cookie('admin'),cookie('login'));
        $adminId = $this->setAdminMsg();
        return $adminId;
    }
    private function checkAuth(){
    	//TODO:检查进来的用户权限是否可以新增或修改管理员
    }

    /*
    * 增加管理员 用学号登陆
    */
    private function getNewAdminInput(){
    	$data['mobile'] = I('post.mobile');
    	$data['name'] = I('post.name');
    	$data['student_number'] = I('post.student_number');
    	$data['auth'] = I('post.auth');
    	$data['password'] = I('post.password');

    	$data['salt'] = rand(1111,9998);
    	$this->checkData($data);
    	return $data;
    }
    private function checkNewAdminData($data){
    	$admin = M('admin')->select();
    	foreach ($admin as $key => $value) {
    		if($admin[$key]['student_number'] == $data['student_number']){
    			$this->errorReturn("学号已存在");
    		}
    	}
    }
    private function insertNewAdmin($data){
    	/*
    	* 加入admin表中
    	*/
    	$admin['name'] = $data['name'];
    	$admin['mobile'] = $data['mobile'];
    	$admin['student_number'] = $data['student_number'];
    	$admin['auth'] = $data['auth'];
      /*
      1:老干事
      2:干事
      3:副部长
      4:部长
      */

      switch($data['auth']){
        case "1":
          $admin['job_name'] = '老干事';
        break;
        case "2":
          $admin['job_name'] = '干事';
        break;
        case "3":
          $admin['job_name'] = '副部长';
        break;
        case "4":
          $admin['job_name'] = '部长';
        break;
        default :
          $admin['job_name'] = false;
      }
      if(!$admin['job_name']){
        $this->errorReturn('权限错误');
      }
    	$this->checkNewAdminData($data);
    	$result = M('admin')->add($admin);
    	if($result === false){
    		$this->errorReturn("admin表写入错误");
    	}
    	$admin = M('admin')->where(array('student_number'=>$data['student_number']))->find();


    	/*
    	* 加入admin_password表中
    	*/
    	$admin_password['salt'] = $data['salt'];
    	$admin_password['password'] = md5(md5($data['password'].$data['salt']).$data['salt']);
    	$admin_password['account'] = $data['student_number'];
    	$admin_password['id'] = $admin['id'];

    	$result = M('admin_password')->add($admin_password);
    	if($result === false){
    		$this->errorReturn('admin_password表写入错误');
    	}

    	/*
    	* 加入admin_cookie表中
    	*/
    	$admin_cookie['admin_id'] = $admin['id'];
    	$admin_cookie['time'] = time();
    	$admin_cookie['account'] = $data['student_number'];
    	$admin_cookie['cookie'] = 0;

    	$result = M('admin_cookie')->add($admin_cookie);
    	if($result === false){
    		$this->errorReturn("admin_cookie表写入错误");
    	}

    	return true;

    }
    public function addAdminAjax(){
    	$data = $this->getNewAdminInput();
    	$this->insertNewAdmin($data);
    	$this->successReturn('新增成功');
    }
    public function addAdmin(){
        $this->checkLogin();
    	$this->display();
    }

    /*
    * 登陆
    */
    private function checkLoginPassword($account,$password){
    	$user = M('admin_password')->where(array('account'=>$account))->find();
    	if($user == null){
    		$this->errorReturn('用户名或密码错误');
    	}
    	$salt = $user['salt'];
    	$realpwd = md5( md5( $password.$salt ).$salt );
    	if($realpwd === $user['password']){
    		return true;
    	}
    	return false;
    }
    private function getLoginData(){
    	$data['account'] = I('post.account');
    	$data['password'] = I('post.password');
    	return $data;
    }
    private function successLogin($account){
        $cookie = md5($account.time());
        M('admin_cookie')->where(array('account'=>$account))->setField('cookie',$cookie);
    	cookie('admin',$account,2560000);
    	cookie('login',$cookie,2560000);
    }
    public function loginAjax(){
    	$data = $this->getLoginData();
    	if($this->checkLoginPassword($data['account'],$data['password'])){
    		$this->successLogin($data['account']);
    		$this->successReturn('登陆成功');
    	}else {
    		$this->errorReturn('用户名或密码错误');
    	}
    }
    public function login(){
    	$this->display();
    }

    /*
    * 注销
    */

    public function logoutAjax(){
    	cookie('admin',null);
    	cookie('login',null);
    	$this->successReturn('登出成功');
    }

    /*
    * 删除管理员
    */
    private function getDeleteInput(){
        $id = I('post.id');
        return $id;
    }
        /*
        * 检查该管理员是否可以删除，如果有订单任务，则不可以删除。
        */
    private function checkAdminAbleToDeleted($id){
        $ato = M('admin_to_order')->where(array('admin_id'=>$id))->select();
        if(count($ato) !== 0){
            $this->errorReturn('无法删除：该管理员有任务尚未完成');
        }
    }
    private function deleteAdminDb($id){
        $result = M('admin')->where(array('id'=>$id))->setField('auth',0);
        if($result!==false){
            $this->successReturn('删除成功');
        }else {
            $this->errorReturn('删除失败');
        }
    }
    public function deleteAdminAjax(){
        $this->checkLogin();
        $id = $this->getDeleteInput();
        $this->checkAdminAbleToDeleted($id);
        $this->deleteAdminDb($id);
    }

    /*
    * 修改管理员密码
    */

    /*
    * 更新管理员信息
    */
    private function getAdmins(){
        $admins = M('admin')->where('auth < 5 and auth > 0')->order('auth desc')->select();
        foreach ($admins as $key => $value) {
            switch($admins[$key]['auth']){
                case '5':
                    $admins[$key]['job_name'] = $admins[$key]['job_name'] != null ? $admins[$key]['job_name'] : "超级管理员";
                    $admins[$key]['auth_name'] = "超级管理员";
                break;

                case '4':
                    $admins[$key]['job_name'] = $admins[$key]['job_name'] != null ? $admins[$key]['job_name'] : "部长";
                    $admins[$key]['auth_name'] = "部长级";
                break;

                case '3':
                    $admins[$key]['job_name'] = $admins[$key]['job_name'] != null ? $admins[$key]['job_name'] : "副部长";
                    $admins[$key]['auth_name'] = "副部长级";
                break;

                case '2':
                    $admins[$key]['job_name'] = $admins[$key]['job_name'] != null ? $admins[$key]['job_name'] : "干事";
                    $admins[$key]['auth_name'] = "干事级";
                break;

                case '1':
                    $admins[$key]['job_name'] = $admins[$key]['job_name'] != null ? $admins[$key]['job_name'] : "老干事";
                    $admins[$key]['auth_name'] = "老干事级";
                break;
            }
        }
        return $admins;
    }
    public function manageAdmin(){
        $this->checkLogin();
        $admins = $this->getAdmins();
        $this->assign('admins', $admins);
        $this->display();
    }


}
