
<div class="card h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Maintenance</h3>
        <div class="card-tools align-middle">
            <!-- <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Add New</button> -->
        </div>
    </div>
    <div class="card-body flex-grow-1">
        <div class="col-12 h-100">
            <div class="row h-100">
                <div class="col-md-6 h-100 d-flex flex-column">
                    <div class="w-100 d-flex border-bottom border-dark py-1 mb-1">
                        <div class="fs-5 col-auto flex-grow-1"><b>Department List</b></div>
                        <div class="col-auto flex-grow-0 d-flex justify-content-end">
                            <a href="javascript:void(0)" id="new_department" class="btn btn-dark btn-sm bg-gradient rounded-2" title="Add Department"><span class="fa fa-plus"></span></a>
                        </div>
                    </div>
                    <div class="h-100 overflow-auto border rounded-1 border-dark">
                        <ul class="list-group">
                            <?php 
                            $dept_qry = $conn->query("SELECT * FROM `department_list` order by `name` asc");
                            while($row = $dept_qry->fetchArray()):
                            ?>
                            <li class="list-group-item d-flex">
                                <div class="col-auto flex-grow-1">
                                    <?php echo $row['name'] ?>
                                </div>
                                <div class="col-auto">
                                    <?php if($row['status'] == 1): ?>
                                        <a href="javascript:void(0)" class="update_stat_department badge bg-success bg-gradiend rounded-pill text-decoration-none me-1" title="Update Status" data-toStat = "0" data-id="<?php echo $row['department_id'] ?>" data-name="<?php echo $row['name'] ?>"><small>Active</small></a>
                                        <?php else: ?>
                                        <a href="javascript:void(0)" class="update_stat_department badge bg-secondary bg-gradiend rounded-pill text-decoration-none me-1" title="Update Status" data-toStat = "1" data-id="<?php echo $row['department_id'] ?>"  data-name="<?php echo $row['name'] ?>"><small>Inactive</small></a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto d-flex justify-content-end">
                                    <a href="javascript:void(0)" class="edit_department btn btn-sm btn-primary bg-gradient py-0 px-1 me-1" title="Edit Department Details" data-id="<?php echo $row['department_id'] ?>"  data-name="<?php echo $row['name'] ?>"><span class="fa fa-edit"></span></a>

                                    <a href="javascript:void(0)" class="delete_department btn btn-sm btn-danger bg-gradient py-0 px-1" title="Delete Department" data-id="<?php echo $row['department_id'] ?>"  data-name="<?php echo $row['name'] ?>"><span class="fa fa-trash"></span></a>
                                </div>
                            </li>
                            <?php endwhile; ?>
                            <?php if(!$dept_qry->fetchArray()): ?>
                                <li class="list-group-item text-center">No data listed yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#new_department').click(function(){
            uni_modal('Add New Department',"manage_department.php")
        })
        $('.edit_department').click(function(){
            uni_modal('Edit Department Details',"manage_department.php?id="+$(this).attr('data-id'))
        })
        $('.update_stat_department').click(function(){
            var changeTo = $(this).attr('data-toStat') == 1 ? "Active" : "Inactive";
            _conf("Are you sure to change status of Department <b>"+$(this).attr('data-name')+"</b> to "+changeTo+"?",'update_stat_department',[$(this).attr('data-id'),$(this).attr('data-toStat')])
        })
        $('.delete_department').click(function(){
            _conf("Are you sure to delete <b>"+$(this).attr('data-name')+"</b> from Department List?",'delete_department',[$(this).attr('data-id')])
        })

        $('table').dataTable({
            columnDefs: [
                { orderable: false, targets:6 }
            ]
        })
    })
    function update_stat_department($id,$status){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'../Actions.php?a=update_stat_department',
            method:'POST',
            data:{id:$id,status:$status},
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
    function delete_department($id){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'../Actions.php?a=delete_department',
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