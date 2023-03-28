<?php 
$assignees = array();
$qry = $conn->query("SELECT * FROM `task_list` where task_id = {$_GET['id']}");
$res = $qry->fetchArray();
if($res){
    foreach($res as $k => $v){
        $$k= $v;
    }
    $assignee_qry = $conn->query("SELECT * FROM `task_assignees` where task_id = '{$task_id}'");
    while($row = $assignee_qry->fetchArray()){
        $assignees[] = $row['employee_id'];
    }
}else{
    echo "<script>alert('Unknown Task ID.'); location.replace(document.referrer)</script>";
}
$employees = $conn->query("SELECT * FROM `employee_list` where employee_id in (".(implode(',',$assignees)).") or employee_id = '{$employee_id}' or employee_id in (SELECT employee_id FROM comment_list where task_id = '{$task_id}')");
while($row= $employees->fetchArray()){
    $emp_arr[$row['employee_id']] = $row['fullname'];
    $emp_updated[$row['employee_id']] =strtotime($row['date_created']);
}
$date_created = new DateTime($date_created, new DateTimeZone(dZone));
$date_created->setTimezone(new DateTimeZone(tZone));
$date_created = $date_created->format('Y-m-d H:i');
?>
<style>
    .employee-avatar{
        width:45px;
        height:45px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:50% 50%
    }
</style>
<div class="py-5">
    <div class="col-12">
        <div class="row">
            <div class="col-md-8">
                <div class="card" style="min-height:60vh">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title"><?php echo $task_code ?></h5>
                        <?php if($status == 1): ?>
                            <span class="badge bg-primary rounded-pill"><small>On-Progress</small></span>
                        <?php elseif($status == 2): ?>
                            <span class="badge bg-danger rounded-pill"><small>Closed</small></span>
                        <?php else: ?>
                            <span class="badge bg-dark text-light rounded-pill"><small>Pending</small></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="text-info"><?php echo $title ?></h3>
                        <hr>
                        <div class="lh-1 mx-2" id="task-description"><?php echo $description ?></div>
                    </div>
                    <div class="card-footer">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <small><span class="text-muted">Created By:</span> <span class="text-info"><?php echo isset($emp_arr[$employee_id]) ? ucwords($emp_arr[$employee_id]) : "Employee has been deleted" ?></span></small>
                                </div>
                                <div>
                                    <small><span class="text-muted">Created DateTime:</span> <span class="text-info"><?php echo isset($date_created) ? ucwords($date_created) : "N/A" ?></span></small>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card rounded-0">
                    <div class="card-header rounded-0">
                        Assignees:
                    </div>
                    <div class="card-body rounded-0">
                        <ul class="list-group">
                            <?php 
                            foreach($assignees as $k => $v):
                            ?>
                                <li class="list-group item">
                                    <div class="d-flex align-items-center">
                                        <span class="cols-1">
                                            <img src="<?php echo "./../uploads/employees/{$v}.png?v=".(isset($emp_updated[$v]) ? $emp_updated[$v] : time()) ?>" alt="Employee Avatar" class="employee-avatar">
                                        </span>
                                        <span class="cols-11"><?php echo isset($emp_arr[$v]) ? $emp_arr[$v] : "" ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix my-2"></div>
        <div class="row">
            <div class="col-md-8">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <h3 class="text-info">Comments:</h3>
                </div>
                <hr>
                <?php 
                    $comments = $conn->query("SELECT * FROM comment_list where task_id='{$task_id}' order by strftime('%s',date_created) desc");
                    while($row = $comments->fetchArray()):
                        $row['date_created'] = new DateTime($row['date_created'], new DateTimeZone(dZone));
                        $row['date_created']->setTimezone(new DateTimeZone(tZone));
                        $row['date_created'] = $row['date_created']->format('Y-m-d H:i');
                ?>
                    <div class="card rounded-0 mb-3 shadow">
                        <div class="card-header rounded-0 d-flex justify-content-between align-items-centerm">
                            <div class="w-100 d-flex align-items-center">
                                <span class="col-auto">
                                    <img src="<?php echo "./../uploads/employees/{$row['employee_id']}.png?v=".(isset($emp_updated[$v]) ? $emp_updated[$v] : time()) ?>" alt="Employee Avatar" class="employee-avatar">
                                </span>
                                <span class="col-auto">
                                    <strong class="text-info"><?php echo isset($emp_arr[$row['employee_id']]) ? $emp_arr[$row['employee_id']] : '' ?></strong><br>
                                    <small class="text-muted"><?php echo $row['date_created'] ?></small>
                                </span>
                            </div>
                            <div>
                                <div class="btn-group">
                                    <a href="#" class="text-decoreation-none text-dark px-3" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item edit_comment" href="javascript:void(0)" data-id="<?php echo $row['comment_id'] ?>">Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item delete_comment" href="javascript:void(0)" data-id="<?php echo $row['comment_id'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body rounded-0">
                            <?php echo $row['message'] ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php if(!$comments->fetchArray()): ?>
                    <center><small><i>No Comment yet.</i></small></center>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
    $("#new_comment").click(function(){
        uni_modal("New Comment","manage_comment.php?task_id=<?php echo $task_id ?>",'mid-large')
    })
    $(".edit_comment").click(function(){
        uni_modal("Edit Comment","manage_comment.php?task_id=<?php echo $task_id ?>&id="+$(this).attr('data-id'),'mid-large')
    })
    $(".delete_comment").click(function(){
        _conf("Are you sure to delete this comment?","delete_comment",[$(this).attr('data-id')])
    })
    $('#edit_task').click(function(){
        uni_modal('Edit Task Details',"manage_task.php?id=<?php echo $task_id ?>",'large')
    })
})
function delete_comment($id){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'./Actions.php?a=delete_comment',
            method:'POST',
            data:{id:$id},
            dataType:'JSON',
            error:err=>{
                console.log(err)
                alert("An error occurred.")
                $('#confirm_modal button').attr('disabled',false)
            },
            success:function(resp){
                if(resp.status == 'success'){
                    location.reload()
                }else{
                    alert("An error occurred.")
                    $('#confirm_modal button').attr('disabled',false)
                }
            }
        })
    }
</script>