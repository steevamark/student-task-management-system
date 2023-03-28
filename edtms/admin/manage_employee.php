<?php
require_once("../DBConnection.php");
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM `employee_list` where employee_id = '{$_GET['id']}'");
        foreach($qry->fetchArray() as $k => $v){
            $$k = $v;
        }
    }
?>
<div class="container-fluid">
<form action="" id="Student-form">
    <input type="hidden" name="id" value="<?php echo isset($employee_id)? $employee_id : '' ?>">
    <div class="col-12">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="employee_code" class="control-label">Student Code/ID</label>
                    <input type="text" name="employee_code" id="employee_code" required class="form-control form-control-sm rounded-0" value="<?php echo isset($employee_code)? $employee_code : '' ?>">
                </div>
                <div class="form-group">
                    <label for="fullname" class="control-label">Full Name</label>
                    <input type="text" name="fullname" id="fullname" required class="form-control form-control-sm rounded-0" value="<?php echo isset($fullname)? $fullname : '' ?>">
                </div>
                <div class="form-group">
                    <label for="gender" class="control-label">Gender</label>
                    <select name="gender" id="gender" required class="form-select form-select-sm rounded-0">
                    <option <?php echo isset($gender) &&  $gender == 'Male' ? "selected" : '' ?>>Male</option>
                    <option <?php echo isset($gender) &&  $gender == 'Female' ? "selected" : '' ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dob" class="control-label">Date of Birth</label>
                    <input type="date" name="dob" id="dob" required class="form-control form-control-sm rounded-0" value="<?php echo isset($dob)? $dob : '' ?>">
                </div>
                <div class="form-group">
                    <label for="contact" class="control-label">Contact</label>
                    <input type="text" name="contact" id="contact" required class="form-control form-control-sm rounded-0" value="<?php echo isset($contact)? $contact : '' ?>">
                </div>
                <div class="form-group">
                    <label for="address" class="control-label">Address</label>
                    <textarea rows="2" name="address" id="address" required class="form-control form-control-sm rounded-0"><?php echo isset($address)? $address : '' ?></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Student Type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="manager" value="Manager" required <?php echo isset($type) && $type == 'Manager' ? "checked" : "" ?>>
                        <label class="form-check-label" for="manager">
                            proffessor
                        </label>
                        </div>
                        <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="staff" value="Staff" <?php echo isset($type) && $type == 'Staff' ? "checked" : "" ?>>
                        <label class="form-check-label" for="staff" >
                            Student
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="department" class="control-label">Department</label>
                    <select type="department_id" name="department_id" id="department_id" required class="form-select form-select-sm rounded-0 select2" data-placeholder = "SELECT DEPARTMENT HERE" >
                        <option value="" <?php echo !isset($department_id) ? "selected" : "" ?> disabled></option>
                        <?php 
                        $department_qry= $conn->query("SELECT * FROM `department_list` order by `name` asc");
                        while($row = $department_qry->fetchArray()):
                        ?>
                        <option value="<?php echo $row['department_id'] ?>" <?php echo isset($department_id) && $department_id == $row['department_id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" id="email" required class="form-control form-control-sm rounded-0" value="<?php echo isset($email)? $email : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="control-label">Password</label>
                    <input type="password" name="password" id="password" required class="form-control form-control-sm rounded-0" value="">
                    <?php if(isset($password)): ?>
                        <small class="text-info"><i>Leave this blank if you don't wish to update the password.</i></small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="avatar" class="control-label">Image</label>
                    <input type="file" name="avatar" class="form-control form-control-sm rounded-0" id="avatar" required accept="image/png, image/jpeg, image/jpg" required>
                    <?php if(isset($book_id)): ?>
                        <small class="text-info"><i>Upload Only if you update student image.</i></small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="status" class="control-label">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm rounded-0">
                        <option value="1" <?php echo (isset($status) && $status == 1 ) ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?php echo (isset($status) && $status == 0 ) ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<script>
    $(function(){
        $('#Student-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            $('#uni_modal button').attr('disabled',true)
            $('#uni_modal button[type="submit"]').text('submitting form...')
            $.ajax({
                url:'../Actions.php?a=save_employee',
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error:err=>{
                    console.log(err)
                    _el.addClass('alert alert-danger')
                    _el.text("An error occurred.")
                    _this.prepend(_el)
                    _el.show('slow')
                     $('#uni_modal button').attr('disabled',false)
                     $('#uni_modal button[type="submit"]').text('Save')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success')
                        $('#uni_modal').on('hide.bs.modal',function(){
                            location.reload()
                        })
                        if("<?php echo isset($employee_id) ?>" != 1)
                        _this.get(0).reset();
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                     $('#uni_modal button').attr('disabled',false)
                     $('#uni_modal button[type="submit"]').text('Save')
                }
            })
        })
    })
</script>