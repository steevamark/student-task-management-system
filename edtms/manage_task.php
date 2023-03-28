<?php
require_once("./DBConnection.php");
$assignees = array();
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM `task_list` where task_id = '{$_GET['id']}'");
    foreach($qry->fetchArray() as $k => $v){
        $$k = $v;
    }
    $assignee_qry = $conn->query("SELECT * FROM `task_assignees` where task_id = '{$task_id}'");
    while($row = $assignee_qry->fetchArray()){
        $assignees[] = $row['employee_id'];
    }
}
?>
<div class="container-fluid">
    <form action="" id="task-form">
        <input type="hidden" name="id" value="<?php echo isset($task_id) ? $task_id : '' ?>">
        <div class="col-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="title" class="control-label">Title</label>
                        <input type="text" name="title" autofocus id="title" required class="form-control form-control-sm rounded-0" value="<?php echo isset($title) ? $title : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="status" class="control-label">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm rounded-0">
                            <option value="0" <?php echo (isset($status) && $status == 0 ) ? 'selected' : '' ?>>Pending</option>
                            <option value="1" <?php echo (isset($status) && $status == 1 ) ? 'selected' : '' ?>>On-Progress</option>
                            <option value="2" <?php echo (isset($status) && $status == 2 ) ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                    <label for="assign_to" class="control-label">Assign To</label>
                        <select type="assign_to" name="assign_to[]" id="assign_to" multiple required class="form-select form-select-sm rounded-0 select2" data-placeholder = "Select Employee Here" >
                            <?php 
                            $employee_qry= $conn->query("SELECT * FROM `employee_list` where department_id = '{$_SESSION['department_id']}' and status = 1 and employee_id !='{$_SESSION['employee_id']}' ".(count($assignees) > 0 ? " OR ( employee_id in (".(implode(',',$assignees))."))" : "")."  order by `fullname` asc");
                            while($row = $employee_qry->fetchArray()):
                            ?>
                            <option value="<?php echo $row['employee_id'] ?>" <?php echo in_array($row['employee_id'],$assignees) ? 'selected' : '' ?>><?php echo $row['employee_code'].'-'.$row['fullname'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="description" class="control-label">Task Description</label>
                        <textarea rows="5" name="description" id="description" required class="form-control form-control-sm rounded-0 summernote" ><?php echo isset($description) ? $description : '' ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
    </form>
</div>

<script>
    $(function(){
        $('#task-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            $('#uni_modal button').attr('disabled',true)
            $('#uni_modal button[type="submit"]').text('submitting form...')
            $.ajax({
                url:'./Actions.php?a=save_task',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
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
                        if("<?php echo isset($task_id) ?>" != 1)
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