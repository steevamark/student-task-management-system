<?php 
require_once('DBConnection.php');
Class Actions extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function login(){
        extract($_POST);
        $sql = "SELECT * FROM admin_list where username = '{$username}' and `password` = '".md5($password)."' ";
        @$qry = $this->query($sql)->fetchArray();
        if(!$qry){
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid username or password.";
        }else{
            $resp['status'] = "success";
            $resp['msg'] = "Login successfully.";
            foreach($qry as $k => $v){
                if(!is_numeric($k))
                $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
    function employee_login(){
        extract($_POST);
        $sql = "SELECT * FROM employee_list where email = '{$email}' and `password` = '".md5($password)."' ";
        @$qry = $this->query($sql)->fetchArray();
        if(!$qry){
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid email or password.";
        }else{
            if($qry['status'] != 1){
            $resp['status'] = "failed";
            $resp['msg'] = "Your Account has been blocked by the management. Contact the management to settle.";
            }else{
                $resp['status'] = "success";
                $resp['msg'] = "Login successfully.";
                foreach($qry as $k => $v){
                    if(!is_numeric($k))
                    $_SESSION[$k] = $v;
                }
            }
        }
        return json_encode($resp);
    }
    function logout(){
        session_destroy();
        header("location:./admin");
    }
    function employee_logout(){
        session_destroy();
        header("location:./");
    }
    function update_credentials(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id','old_password')) && !empty($v)){
                if(!empty($data)) $data .= ",";
                if($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if(!empty($password) && md5($old_password) != $_SESSION['password']){
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        }else{
            $sql = "UPDATE `admin_list` set {$data} where admin_id = '{$_SESSION['admin_id']}'";
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credential successfully updated.';
                foreach($_POST as $k => $v){
                    if(!in_array($k,array('id','old_password')) && !empty($v)){
                        if(!empty($data)) $data .= ",";
                        if($k == 'password') $v = md5($v);
                        $_SESSION[$k] = $v;
                    }
                }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Updating Credentials Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function update_credentials_employee(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id','old_password')) && !empty($v)){
                if(!empty($data)) $data .= ",";
                if($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if(!empty($password) && md5($old_password) != $_SESSION['password']){
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        }else{
            $sql = "UPDATE `employee_list` set {$data} where employee_id = '{$_SESSION['employee_id']}'";
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credential successfully updated.';
                if(isset($_FILES)){
                    foreach($_FILES as $k=>$v){
                        $$k = $v;
                    }
                }
                if(isset($avatar) && !empty($avatar['tmp_name'])){
                    if(!is_dir(__DIR__."/uploads/employees/"))
                    mkdir(__DIR__."/uploads/employees/");
                    $fname = "/uploads/employees/{$_SESSION['employee_id']}.png";
                    $thumb_file = $avatar['tmp_name'];
                    $file_type = mime_content_type($thumb_file);
                    list($width, $height) = getimagesize($thumb_file);
                    $t_image = imagecreatetruecolor('350', '350');
                    if(in_array($file_type,array('image/png','image/jpeg','image/jpg'))){
                        $gdImg = ($file_type =='image/png') ? imagecreatefrompng($thumb_file) : imagecreatefromjpeg($thumb_file);
                        imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, '350', '350', $width, $height);
                        if($t_image){
                            if(is_file(__DIR__.$fname))
                                unlink(__DIR__.$fname);
                                imagepng($t_image,__DIR__.$fname);
                                imagedestroy($t_image);
                        }else{
                            $resp['msg'] = 'Employee Details Successfully saved but the image has failed to upload.';
                        }
                    }else{
                            $resp['msg'] = 'Employee Details Successfully saved but the image has failed to upload due to invalid file type.';
                    }
                }
                $get = $this->query("SELECT * FROM employee_list where employee_id ='{$_SESSION['employee_id']}'")->fetchArray();
                foreach($get as $k => $v){
                    if(is_numeric($k))
                    continue;
                        if(!empty($data)) $data .= ",";
                        $_SESSION[$k] = $v;
                    }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Updating Credentials Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function save_department(){
        extract($_POST);
        if(empty($id))
            $sql = "INSERT INTO `department_list` (`name`,`status`)VALUES('{$name}','{$status}')";
        else{
            $data = "";
             foreach($_POST as $k => $v){
                 if(!in_array($k,array('id'))){
                     if(!empty($data)) $data .= ", ";
                     $data .= " `{$k}` = '{$v}' ";
                 }
             }
            $sql = "UPDATE `department_list` set {$data} where `department_id` = '{$id}' ";
        }
        @$check= $this->query("SELECT COUNT(department_id) as count from `department_list` where `name` = '{$name}' ".($id > 0 ? " and department_id != '{$id}'" : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'Department Name already exists.';
        }else{
            @$save = $this->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Department successfully saved.";
                else
                    $resp['msg'] = "Department successfully updated.";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Saving New Department Failed.";
                else
                    $resp['msg'] = "Updating Department Failed.";
                $resp['error']=$this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function delete_department(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `department_list` where department_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Department successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function update_stat_department(){
        extract($_POST);
        @$update = $this->query("UPDATE `department_list` set `status` = '{$status}' where department_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Department Status successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_sub_department(){
        extract($_POST);
        if(empty($id))
            $sql = "INSERT INTO `sub_department_list` (`name`,`department_id`,`status`)VALUES('{$name}','{$department_id}','{$status}')";
        else{
            $data = "";
             foreach($_POST as $k => $v){
                 if(!in_array($k,array('id'))){
                     if(!empty($data)) $data .= ", ";
                     $data .= " `{$k}` = '{$v}' ";
                 }
             }
            $sql = "UPDATE `sub_department_list` set {$data} where `sub_department_id` = '{$id}' ";
        }
        @$check= $this->query("SELECT COUNT(sub_department_id) as count from `sub_department_list` where `name` = '{$name}' and `department_id` = '{$department_id}' ".($id > 0 ? " and sub_department_id != '{$id}'" : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'Sub department Name already exists.';
        }else{
            @$save = $this->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Sub department successfully saved.";
                else
                    $resp['msg'] = "Sub department successfully updated.";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Saving New Sub department Failed.";
                else
                    $resp['msg'] = "Updating Sub department Failed.";
                $resp['error']=$this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function delete_sub_department(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `sub_department_list` where sub_department_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Sub department successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function update_stat_sub_cat(){
        extract($_POST);
        @$update = $this->query("UPDATE `sub_department_list` set `status` = '{$status}' where sub_department_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Sub department Status successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_admin(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
        if(!in_array($k,array('id','type'))){
            if(!empty($id)){
                if(!empty($data)) $data .= ",";
                $data .= " `{$k}` = '{$v}' ";
                }else{
                    $cols[] = $k;
                    $values[] = "'{$v}'";
                }
            }
        }
        if(empty($id)){
            $cols[] = 'password';
            $values[] = "'".md5($username)."'";
        }
        if(isset($cols) && isset($values)){
            $data = "(".implode(',',$cols).") VALUES (".implode(',',$values).")";
        }
        

       
        @$check= $this->query("SELECT count(admin_id) as `count` FROM admin_list where `username` = '{$username}' ".($id > 0 ? " and admin_id != '{$id}' " : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Username already exists.";
        }else{
            if(empty($id)){
                $sql = "INSERT INTO `admin_list` {$data}";
            }else{
                $sql = "UPDATE `admin_list` set {$data} where admin_id = '{$id}'";
            }
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id))
                $resp['msg'] = 'New Admin User successfully saved.';
                else
                $resp['msg'] = 'Admin User Details successfully updated.';
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Saving Admin User Details Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function delete_admin(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `admin_list` where rowid = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Admin User successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_user(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
        if(!in_array($k,array('id'))){
            if($k == 'password'){
                if(empty($v))
                    continue;
                else
                    $v= md5($v);
            }
            if(!empty($id)){
                if(!empty($data)) $data .= ",";
                $data .= " `{$k}` = '{$v}' ";
                }else{
                    $cols[] = $k;
                    $values[] = "'{$v}'";
                }
            }
        }
        if(isset($cols) && isset($values)){
            $data = "(".implode(',',$cols).") VALUES (".implode(',',$values).")";
        }
        @$check= $this->query("SELECT count(user_id) as `count` FROM user_list where `username` = '{$username}' ".($id > 0 ? " and user_id != '{$id}' " : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Username already exists.";
        }else{
            if(empty($id)){
                $sql = "INSERT INTO `user_list` {$data}";
            }else{
                $sql = "UPDATE `user_list` set {$data} where user_id = '{$id}'";
            }
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id))
                $resp['msg'] = 'Account successfully Created.';
                else
                $resp['msg'] = 'Account Details successfully updated.';
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Saving Details Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function delete_user(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `user_list` where user_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'User successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_employee(){
        if(isset($_POST['password']) && !empty($_POST['password'])){
            $_POST['password'] = md5($_POST['password']);
        }else{
            unset($_POST['password']);
        }
        extract($_POST);
        @$check= $this->query("SELECT count(employee_id) as `count` FROM `employee_list` where `employee_code` = '{$employee_code}' ".($id > 0 ? " and employee_id != '{$id}'" : ''))->fetchArray()['count'];
        if($check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Employee Code already exists.";
        }else{
            $data = "";
            foreach($_POST as $k =>$v){
                if(!in_array($k,array('id','avatar','img'))){
                    if(empty($id)){
                        $columns[] = "`{$k}`"; 
                        $values[] = "'{$v}'"; 
                    }else{
                        if(!empty($data)) $data .= ", ";
                        $data .= " `{$k}` = '{$v}'";
                    }
                }
            }
            if(isset($columns) && isset($values)){
                $data = "(".(implode(",",$columns)).") VALUES (".(implode(",",$values)).")";
            }
            if(empty($id)){
                $sql = "INSERT INTO `employee_list` {$data}";
            }else{
                $sql = "UPDATE `employee_list` set {$data} where employee_id = '{$id}'";
            } 
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id))
                $resp['msg'] = 'Employee Successfully added.';
                else
                $resp['msg'] = 'Employee Details Successfully updated.';
                if(empty($id))
                $last_id = $this->query("SELECT last_insert_rowid()")->fetchArray()[0];
                $eid = !empty($id) ? $id : $last_id;
                if(isset($_FILES)){
                    foreach($_FILES as $k=>$v){
                        $$k=$v;
                    }
                }
                if(isset($avatar) && !empty($avatar['tmp_name'])){
                    if(!is_dir(__DIR__."/uploads/employees/"))
                    mkdir(__DIR__."/uploads/employees/");
                    $fname = "/uploads/employees/{$eid}.png";
                    $thumb_file = $avatar['tmp_name'];
                    $file_type = mime_content_type($thumb_file);
                    list($width, $height) = getimagesize($thumb_file);
                    $t_image = imagecreatetruecolor('350', '350');
                    if(in_array($file_type,array('image/png','image/jpeg','image/jpg'))){
                        $gdImg = ($file_type =='image/png') ? imagecreatefrompng($thumb_file) : imagecreatefromjpeg($thumb_file);
                        imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, '350', '350', $width, $height);
                        if($t_image){
                            if(is_file(__DIR__.$fname))
                                unlink(__DIR__.$fname);
                                imagepng($t_image,__DIR__.$fname);
                                imagedestroy($t_image);
                        }else{
                            $resp['msg'] = 'Employee Details Successfully saved but the image has failed to upload.';
                        }
                    }else{
                            $resp['msg'] = 'Employee Details Successfully saved but the image has failed to upload due to invalid file type.';
                    }
                }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'An error occured. Error: '.$this->lastErrorMsg();
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);
    }
    function delete_employee(){
        extract($_POST);
        @$delete = $this->query("DELETE FROM `employee_list` where employee_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Employee successfully deleted.';
            if(is_file(__DIR__.'/uploads/employees/'.$id.'.png'))
                unlink(__DIR__.'/uploads/employees/'.$id.'.png');
        }else{
            $resp['status']='failed';
            $resp['msg'] = 'An error occure. Error: '.$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_task(){
        if(empty($_POST['id'])){
            $prefix = "TASK-".date('Ym');
            $code = sprintf("%'.04d",1);
            while(true){
                $check = $this->query("SELECT count(task_id) FROM task_list where task_code = '".$prefix.$code."'")->fetchArray()[0];
                if($check > 0){
                    $code=sprintf("%'.04d",ceil($code) + 1);
                }else{
                    break;
                }
            }
            $_POST['task_code'] = $prefix.$code;
            $_POST['department_id'] = $_SESSION['department_id'];
            $_POST['employee_id'] = $_SESSION['employee_id'];
        }
        extract($_POST);
        $data = "";
        foreach($_POST as $k =>$v){
            if(!in_array($k,array('id','avatar','img','assign_to'))){
                if(!is_numeric($v)){
                    $v = $this->escapeString($v);
                }
                if(empty($id)){
                    $columns[] = "`{$k}`"; 
                    $values[] = "'{$v}'"; 
                }else{
                    if(!empty($data)) $data .= ", ";
                    $data .= " `{$k}` = '{$v}'";
                }
            }
        }
        if(isset($columns) && isset($values)){
            $data = "(".(implode(",",$columns)).") VALUES (".(implode(",",$values)).")";
        }
        if(empty($id)){
            $sql = "INSERT INTO `task_list` {$data}";
        }else{
            $sql = "UPDATE `task_list` set {$data} where task_id = '{$id}'";
        } 
        @$save = $this->query($sql);
        if($save){
            $resp['status'] = 'success';
            if(empty($id))
            $resp['msg'] = 'Task Successfully added.';
            else
            $resp['msg'] = 'Task Details Successfully updated.';
            if(empty($id))
            $last_id = $this->query("SELECT last_insert_rowid()")->fetchArray()[0];
            $task_id = !empty($id) ? $id : $last_id;
            $this->query("DELETE FROM task_assignees where task_id = '{$task_id}'");
            $data = "";
            foreach($assign_to as $k =>$v){
                if(!empty($data)) $data .=", ";
                $data .= "('{$task_id}','{$v}')";
            }
            if(!empty($data)){
                $this->query("INSERT INTO `task_assignees` (`task_id`,`employee_id`) VALUES {$data}");
            }
        }else{
            $resp['status'] = 'failed';
            $resp['msg'] = 'An error occured. Error: '.$this->lastErrorMsg();
            $resp['sql'] = $sql;
        }
        return json_encode($resp);
    }
    function delete_task(){
        extract($_POST);
        @$delete = $this->query("DELETE FROM `task_list` where task_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Task successfully deleted.';
            if(is_file(__DIR__.'/uploads/tasks/'.$id.'.png'))
                unlink(__DIR__.'/uploads/tasks/'.$id.'.png');
        }else{
            $resp['status']='failed';
            $resp['msg'] = 'An error occure. Error: '.$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_comment(){
        if(empty($_POST['id']))
        $_POST['employee_id'] = $_SESSION['employee_id'];
        extract($_POST);
        $data = "";
        foreach($_POST as $k =>$v){
            if(!in_array($k,array('id'))){
                if(!is_numeric($v)){
                    $v = $this->escapeString($v);
                }
                if(empty($id)){
                    $columns[] = "`{$k}`"; 
                    $values[] = "'{$v}'"; 
                }else{
                    if(!empty($data)) $data .= ", ";
                    $data .= " `{$k}` = '{$v}'";
                }
            }
        }
        if(isset($columns) && isset($values)){
            $data = "(".(implode(",",$columns)).") VALUES (".(implode(",",$values)).")";
        }
        if(empty($id)){
            $sql = "INSERT INTO `comment_list` {$data}";
        }else{
            $sql = "UPDATE `comment_list` set {$data} where comment_id = '{$id}'";
        } 
        @$save = $this->query($sql);
        if($save){
            $resp['status'] = 'success';
            if(empty($id))
            $resp['msg'] = 'Comment Successfully added.';
            else
            $resp['msg'] = 'Comment Details Successfully updated.';
        }else{
            $resp['status'] = 'failed';
            $resp['msg'] = 'An error occured. Error: '.$this->lastErrorMsg();
            $resp['sql'] = $sql;
        }
        return json_encode($resp);
    }
    function delete_comment(){
        extract($_POST);
        @$delete = $this->query("DELETE FROM `comment_list` where comment_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Comment successfully deleted.';
            
        }else{
            $resp['status']='failed';
            $resp['msg'] = 'An error occure. Error: '.$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$action = new Actions();
switch($a){
    case 'login':
        echo $action->login();
    break;
    case 'employee_login':
        echo $action->employee_login();
    break;
    case 'logout':
        echo $action->logout();
    break;
    case 'employee_logout':
        echo $action->employee_logout();
    break;
    case 'update_credentials':
        echo $action->update_credentials();
    break;
    case 'update_credentials_employee':
        echo $action->update_credentials_employee();
    break;
    case 'save_department':
        echo $action->save_department();
    break;
    case 'delete_department':
        echo $action->delete_department();
    break;
    case 'update_stat_department':
        echo $action->update_stat_department();
    break;
    case 'save_sub_department':
        echo $action->save_sub_department();
    break;
    case 'delete_sub_department':
        echo $action->delete_sub_department();
    break;
    case 'update_stat_sub_cat':
        echo $action->update_stat_sub_cat();
    break;
    case 'save_admin':
        echo $action->save_admin();
    break;
    case 'delete_admin':
        echo $action->delete_admin();
    break;
    case 'save_user':
        echo $action->save_user();
    break;
    case 'delete_user':
        echo $action->delete_user();
    break;
    case 'save_employee':
        echo $action->save_employee();
    break;
    case 'delete_employee':
        echo $action->delete_employee();
    break;
    case 'save_task':
        echo $action->save_task();
    break;
    case 'delete_task':
        echo $action->delete_task();
    break;
    case 'save_comment':
        echo $action->save_comment();
    break;
    case 'delete_comment':
        echo $action->delete_comment();
    break;
    default:
    // default action here
    break;
}