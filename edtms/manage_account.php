<?php
require_once("DBConnection.php");
$qry = $conn->query("SELECT * FROM `employee_list` where employee_id = '{$_SESSION['employee_id']}'");
    foreach($qry->fetchArray() as $k => $v){
        $$k = $v;
    }
?>
<style>
    #img-avatar{
        height:100px;
        width:100px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:50% 50%;
    }
</style>
<h3>Manage Account</h3>
<hr>
<div class="col-md-6">
    <form action="" id="employee-form">
        <input type="hidden" name="id" value="<?php echo isset($employee_id) ? $employee_id : '' ?>">
        <div class="form-group">
            <label for="fullname" class="control-label">Full Name</label>
            <input type="text" name="fullname" id="fullname" required class="form-control form-control-sm rounded-0" value="<?php echo isset($fullname) ? $fullname : '' ?>">
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
            <input type="text" name="contact" id="contact" required class="form-control form-control-sm rounded-0" value="<?php echo isset($contact) ? $contact : '' ?>">
        </div>
        <div class="form-group">
            <label for="email" class="control-label">Email</label>
            <input type="text" name="email" id="email" required class="form-control form-control-sm rounded-0" value="<?php echo isset($email) ? $email : '' ?>">
        </div>
        <div class="form-group">
            <label for="address" class="control-label">Address</label>
            <textarea name="address" id="address" cols="30" rows="3" class="form-control rounded-0"><?php echo isset($address) ? $address : '' ?></textarea>
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
            <input type="text" name="email" id="email" required class="form-control form-control-sm rounded-0" value="<?php echo isset($email) ? $email : '' ?>">
        </div>
        <div class="form-group">
            <label for="password" class="control-label">New Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-sm rounded-0" value="">
        </div>
        <div class="form-group">
            <label for="old_password" class="control-label">Old Password</label>
            <input type="password" name="old_password" id="old_password" class="form-control form-control-sm rounded-0" value="">
        </div>
        <div class="form-group">
            <small>Leave the password field blank if you don't want update your password.</small>
        </div>
        <div class="form-group">
            <label for="avatar" class="control-label">Image</label>
            <input type="file" name="avatar" class="form-control form-control-sm rounded-0" id="avatar" onchange="readURL(this)" accept="image/png, image/jpeg, image/jpg">
        </div>
        <div class="form-group text-center">
            <img src="<?php echo "./uploads/employees/{$_SESSION['employee_id']}.png?v=".(strtotime($_SESSION['date_updated'])) ?>" alt="Employee Avatar" id="img-avatar">
        </div>
        <div class="form-group d-flex w-100 justify-content-end">
            <button class="btn btn-sm btn-primary rounded-0 my-1">Update</button>
        </div>
    </form>
</div>

<script>
     function readURL(input){
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#img-avatar').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }else{
            $('#img-avatar').attr('src', '<?php echo "./uploads/employees/{$_SESSION['employee_id']}.png?v=".(strtotime($_SESSION['date_updated'])) ?>');
        }
    }
    $(function(){
        $('#employee-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            $('#uni_modal button').attr('disabled',true)
            $('#uni_modal button[type="submit"]').text('submitting form...')
            $.ajax({
                url:'Actions.php?a=update_credentials_employee',
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
                            location.reload()
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